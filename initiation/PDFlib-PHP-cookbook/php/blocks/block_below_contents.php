<?php
/* $Id: block_below_contents.php,v 1.4 2012/05/03 14:00:41 stm Exp $
 * Block below contents:
 * Fill PDFlib blocks so that they are placed below the imported page
 * 
 * Fill PDFlib blocks such that not the block contents are placed on top of the
 * original imported page, but the other way round. 
 * Place the PDF page using PDF_fit_pdi_page(), but supply the "blind" option
 * to suppress the actual page contents. Fill the block(s) as desired. Place the
 * PDF page again, this time without the "blind" option.
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Block below Contents";

$infile = "announcement_blocks.pdf";
$tf = 0; 

/* Name of the block contained on the imported page */
$blockname = "offer"; 

$offer = "SPECIAL\nSEASON\nOFFER\nby the\nPAPERFIELD\nPLANE CENTER";

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

    /* Place the imported page on the output page but supply the "blind"
     * option to suppress the actual page contents
     */
    $p->fit_pdi_page($inpage, 0, 0, "blind");
    
    /* Fill one block with the Textflow */ 
    $text = $offer;
	  
    /* Option list for text blocks; the font and encoding should be 
     * defined and the Textflow handle supplied. "alignment=center" is used
     * to center the text horizontally. Other options have
     * already been set in the properties of the blocks contained in
     * the input document, such as:
     * "fitmethod=clip" to clip the text when it doesn't fit completely
     * into the block while avoiding any text shrinking.
     * "margin=4" to set some space between the text and the borders of
     * the block rectangle.
     * "fontsize=60" as a font size of 60.
     * "fillcolor={gray 0.9}" to output the text in a light gray.
    */
    $optlist = "fontname=Helvetica-Bold encoding=unicode alignment=center " .
	"textflowhandle=" . $tf;
       
    $tf = $p->fill_textblock($inpage, $blockname, $text, $optlist);
	    
    if ($tf == 0)
	trigger_error("Warning: " . $p->get_errmsg());
	    
    $p->delete_textflow($tf);
    
    /* Place the imported page again but this time without the "blind"
     * option supplied
     */
    $p->fit_pdi_page($inpage, 0, 0, "");

    $p->end_page_ext("");
       
    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=block_below_contents.pdf");
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

