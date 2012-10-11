<?php
/* $Id: query_block_properties.php,v 1.4 2012/05/03 14:00:41 stm Exp $
 * Query block properties:
 * Import a PDF page containing blocks, and query various block properties
 *
 * Use pcos_get_number() and pcos_get_string() to process all blocks contained
 * on an imported PDF page. Query block properties which require special
 * treatment:
 * List the names and rectangle coordinates of all blocks contained on the first
 * page of the imported PDF.
 * Query the "leading" property of a Textflow block.
 * Query the version of the block specification to which the block PDF adheres. 
 * List the key and value of custom properties of all blocks.
 * List all fonts required in Text blocks by querying the "fontname" property of
 * each block.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Query Block Properties";

$infile = "announcement_blocks.pdf";
$x = 30;
$xoff = 200;
$y = 800;
$yoff = 25;

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
    
    /* Load the standard font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the bold font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open a PDF containing blocks. You don't need to call open_pdi_page()
     * since pCOS calls do not require a PDI page handle but simply the
     * page number.
     */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start the page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set the bold font with a font size of 14 */
    $p->setfont($boldfont, 14);
    
    /* Output a heading line */
    $p->fit_textline("Querying various block properties in " . $infile . 
	" with pCOS", $x, $y, "");
    
    /* Set the standard font with a font size of 14*/
    $p->setfont($font, 14);

    /* Retrieve the number of blocks contained on the first page (which is
     * page no. 0) of the PDF opened
     */     
    $blockcount = $p->pcos_get_number($indoc,
	"length:pages[0]/blocks");

    if ($blockcount == 0)
	throw new Exception("Error: " . $infile .
	    "does not contain any PDFlib blocks");
    
    
    /* -------------------------------------------------------------------
     * List the names and rectangle coordinates of all blocks contained on
     * the first page of the imported PDF.
     * -------------------------------------------------------------------
     */
    
    /* Output some heading lines */
    $p->fit_textline("Name and rectangle coordinates of blocks:", 
	$x, $y-=2*$yoff, "");
    $p->fit_textline("block name", $x, $y-=$yoff, "underline");
    $p->fit_textline("block rectangle x1 y1 x2 y2", $x+$xoff, $y, "underline");
	  
    /* Loop over all all blocks on the first page */
    for ($i = 0; $i < $blockcount; $i++)
    {
	/* Retrieve the block name */
	$blockname = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Name");
	
	/* Output the block name */
	$p->fit_textline($blockname, $x, $y-=$yoff, "");
       
	for ($j = 0; $j < 4; $j++) {
	    $coord = sprintf("%.0d", $p->pcos_get_number($indoc,
		"pages[0]/blocks[" . $i . "]/Rect[" . $j . "]"));
	    
	    /* Output the block name */
	    $p->fit_textline($coord, $x+$xoff+$j*40, $y, "");
	}
    }

    
    /* ---------------------------------------------------------------------
     * Query the "leading" property of a Textflow block. The "leading"
     * property is of type "float or percentage". Percentages are stored as
     * array with two elements, e.g. /leading [120.0(%)].
     * Check the object type of the leading value (using the "type" prefix);
     * if it has type "number", use PDF_pcos_get_number(). If it has type 
     * "array", fetch the 0-th element of the array, and treat it as a
     * percentage.
     * ---------------------------------------------------------------------
     */
    $blockname = "offer";

    $istextflow =
        $p->pcos_get_string($indoc,
	       "type:pages[0]/blocks/" . $blockname . "/textflow") == "boolean"
	    && $p->pcos_get_string($indoc,
                    "pages[0]/blocks/" . $blockname . "/textflow") == "true";
	    
    if ($istextflow)
    {
	/* Initialize leading and unit with default values */
	$unit = "%";
	$leading=100; 
	
	/* Get the current leading value of the block */
	$type = $p->pcos_get_string($indoc, 
	    "type:pages[0]/blocks/" . $blockname . "/leading");
	
	if ($type == "number") 
	{
	    $unit = "pt";
	    $leading = sprintf("%.1f", $p->pcos_get_number($indoc,
		"pages[0]/blocks/" . $blockname . "/leading"));
	}
	else if ($type == "array")
	{
	    $unit = "%";
	    $leading = sprintf("%.1f", $p->pcos_get_number($indoc,
		"pages[0]/blocks/" . $blockname . "/leading[0]"));
	}
	
	/* Output the leading value */
	$p->fit_textline("Leading value of the \"offer\" text block: " . 
	    $leading . $unit, $x, $y-=2*$yoff, "");
    }
    
    
    /* --------------------------------------------------------------------
     * Query the version of the block specification to which the block PDF
     * adheres. 
     * This is sometimes relevant when blocks are prepared with a newer
     * version of the Block plugin, but the PPS version on the server does
     * not accept the higher version of the blocks. Using the pCOS path
     * below users can check for this condition themselves before risking a
     * PPS exception.
     * --------------------------------------------------------------------
     */
    $version = $p->pcos_get_number($indoc,
	"pages[0]/PieceInfo/PDFlib/Private/Version");
			
    /* Output the version number of the block specification */
    $p->fit_textline("Version of the block specification to which the " .
	"block PDF adheres: " . $version, $x, $y-=2*$yoff, "");
    
   
    /* -------------------------------------------------------------------
     * List the key and value of custom properties of all blocks contained
     * on the first page of the imported PDF.
     * -------------------------------------------------------------------
     */
    
    /* Output some heading lines */
    $p->fit_textline("Custom properties used in blocks:",$x , $y-=2*$yoff, "");
    $p->fit_textline("block name", $x, $y-=$yoff, "underline");
    $p->fit_textline("custom key", $x+$xoff, $y, "underline");
    $p->fit_textline("custom value", $x+2*$xoff, $y, "underline");
    
    /* Loop over all blocks on the first page */
    for ($i = 0; $i < $blockcount; $i++)
    {
	/* Fetch the block name */
	$blockname = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Name");
	
	/* Fetch the number of custom properties contained in the block */
	$propcount = $p->pcos_get_number($indoc,
	    "length:pages[0]/blocks[" . $i . "]/Custom");
	
	for ($j = 0; $j < $propcount; $j++)
	{
	    /* Output the name of the custom property */
	    $p->fit_textline($blockname, $x, $y-=$yoff, "");
	    
	    /* Fetch the name of the custom property */
	    $propname = $p->pcos_get_string($indoc, 
		"pages[0]/blocks[" . $i . "]/Custom[" . $j . "].key");
	    
	    /* Output the name of the custom property */
	    $p->fit_textline($propname, $x+$xoff, $y, "");
	    
	    /* Query the type of the custom property: 
	     * "string", "name", "number", or "list of numbers"
	     */
	    $proptype = $p->pcos_get_string($indoc, 
		"type:pages[0]/blocks[" . $i . "]/Custom[" . $j . "]");
	    
	    /* Process the custom property depending on the type */
	    if ($proptype == "string")
	    {
		/* Get the string value of the custom property */
		$propvalue = $p->pcos_get_string($indoc, 
		    "pages[0]/blocks[" . $i . "]/Custom[" . $j . "].val");
		
		/* Output the value of the custom property */
		$p->fit_textline($propvalue, $x+2*$xoff, $y, "");
	    }
	    else if ($proptype == "number")
	    {
		$propvalue = sprintf("%.1f", $p->pcos_get_number($indoc,
		    "pages[0]/blocks[" . $i . "]/Custom[" . $j . "].val")); 
		
		/* Output the name of the custom property */
		$p->fit_textline($propvalue, $x+2*$xoff, $y, "");
	    }
	    else
	    {
		/* Read the remaining types "name" or "list of numbers"
		 * and perform appropriate actions
		 */
	    }
	}
    }
    
    
    /* -----------------------------------------------------------------
     * List all fonts required in Text blocks by querying the "fontname"
     * property of each block
     * -----------------------------------------------------------------
     */
    
    /* Output a heading line */
    $p->fit_textline("Font names used in blocks:",$x , $y-=2*$yoff, "");
    $p->fit_textline("block name", $x, $y-=$yoff, "underline");
    $p->fit_textline("font", $x+$xoff, $y, "underline");
    
    /* Loop over all blocks on the page */
    for ($i = 0; $i < $blockcount; $i++)
    {
	/* Fetch the name and type of the i-th block on the first
	 * page (one of Text/Image/PDF)
	 */
	$blockname = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Name");
	
	$blocktype = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Subtype");
	
	/* Check if it is a Text block */
	if ($blocktype == "Text")
	{
	    $fontname = $p->pcos_get_string($indoc,
		"pages[0]/blocks[" . $i . "]/fontname");
	    
	    /* Output the block name and the font name */
	    $p->fit_textline($blockname, $x, $y-=$yoff, "");
	    $p->fit_textline($fontname, $x+$xoff, $y, "");
	}
    }
    
    $p->end_page_ext("");
	  
    $p->end_document("");
    $p->close_pdi_document($indoc);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=query_block_properties.pdf");
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

