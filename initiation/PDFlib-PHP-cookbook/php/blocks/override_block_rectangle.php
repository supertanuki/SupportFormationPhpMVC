<?php
/* $Id: override_block_rectangle.php,v 1.3 2012/05/03 14:00:41 stm Exp $
 * Override block rectangle:
 * Fill some blocks of an imported PDF page while changing the rectangle 
 * coordinates.
 * 
 * Output an imported PDF page containing blocks. The blocks "name", 
 * "business_address", and "business_city" have a defined font, font size, and
 * color. Fill those blocks by using a different font and a larger font size.
 * In addition, due to the increased font, increase the block rectangle and
 * move its position to prevent it from overlapping with the other blocks.   
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Override Block Rectangle";

$infile = "stationery_blocks.pdf";
$blockname = ""; $blocktype = "";

$incrfactor = 3;
$percentage = 30;
 
$nblocks = 3; // number of blocks with properties to be overridden

/* Names of blocks with properties to be overridden */
$blocknames = array(
    "name", "business_address", "business_city" 
);

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Open a PDF containing blocks */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page */
    $inpage = $p->open_pdi_page($indoc, 1, "");
    if ($inpage == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Get the width and height of the imported page */
    $width = $p->pcos_get_number($indoc, "pages[0]/width");
    $height = $p->pcos_get_number($indoc, "pages[0]/height");
    
    /* Start the output page with the size given by the imported page */ 
    $p->begin_page_ext($width, $height, "");

    /* Place the imported page on the output page */
    $p->fit_pdi_page($inpage, 0, 0, "");
    
    /* Retrieve the number of blocks contained on the first page (which is
     * page no. 0) of the PDF opened
     */     
    $blockcount = (int) $p->pcos_get_number($indoc,
	"length:pages[0]/blocks");

    if ($blockcount == 0)
	throw new Exception("Error: " . $infile .
	    "does not contain any PDFlib blocks");
    
    /* For three Text blocks on the first page, increase
     * the font size by 20%. Increase the size of the block rectangle by
     * a defined percentage and move the block down to prevent it from
     * overlapping with another block.
     */
    for ($i = 0; $i < $blockcount; $i++)
    {
	/* Get the name of the block */
	$blockname = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Name");
	
	/* Get the type of the block */
	$blocktype = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Subtype");
	
	for ($j = 0; $j < $nblocks; $j++) {
	    if ($blockname == $blocknames[$j]) {
		/* Check if it is a Text block */
		if ($blocktype == "Text")
		{
		/* Get the font size of the block */
		$blockfontsize = $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/fontsize");
		    
		/* Retrieve the rectangle coordinates of the block */
		$block_llx = (int) $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/Rect[0]");
		
		$block_lly = (int) $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/Rect[1]");
		
		$block_urx = (int) $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/Rect[2]");
		
		$block_ury = (int) $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/Rect[3]");
		
		/* Increase the font size by the percentage defined */
		$newfontsize = $blockfontsize * ($percentage + 100) / 100;
		
		/* Specify the enlargement by the difference between the old
		 * and the new font size multiplied by an increment factor 
		 */
		$enlarge = $incrfactor * ($newfontsize - $blockfontsize);
		
		/* Increase the block rectangle by the enlargement 
		 * specified. In addition, move the rectangle down by j
		 * times the enlargment to prevent the blocks from 
		 * overlapping.
		 */
		$newblock_llx = $block_llx;
		$newblock_lly = $block_lly - (1 + $j) * $enlarge;
		$newblock_urx = $block_urx + $enlarge;
		$newblock_ury = $block_ury - $j * $enlarge;
		
		$text_optlist = 
		    "fontname=Courier encoding=unicode fontsize=" . 
		    $newfontsize .
		    " refpoint={" . $block_llx . " " . $newblock_lly . "}" . 
		    " boxsize={" . ($newblock_urx - $newblock_llx) . " " . 
		    ($newblock_ury - $newblock_lly) . "}" .
		    " fillcolor={rgb 0 0.7 0}";
		
		if ($p->fill_textblock($inpage, $blockname, $blockname, 
		    $text_optlist) == 0)
		    trigger_error("Warning: " . $p->get_errmsg() ."\n");
		}
		break;
	    }
	}
    }

    $p->end_page_ext("");

    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=override_block_rectangle.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;

?>


