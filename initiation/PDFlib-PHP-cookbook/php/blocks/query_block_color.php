<?php
/* $Id: query_block_color.php,v 1.2 2012/05/03 14:00:41 stm Exp $
 * Query block color:
 * Query the background color of blocks
 * 
 * Import a page containing blocks and query the background color space and 
 * color of all blocks.
 *
 * Required software: PPS 7
 * Required data: PDF document with blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Query Block Color";

$infile = "blocks_bgcolor.pdf";

$x = 30;
$y = 750;
$yoff = 30;
$ncomp = 1;

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
    
    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open a PDF containing blocks */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Open the first input page */
    $page = $p->open_pdi_page($indoc, 1, "");
    if ($page == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start the first output page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set the font with a font size of 14 */
    $p->setfont($font, 14);
    
    /* Set the number of fraction digits to 2 */
	  
    /* Get the number of blocks on the page */
    $blockcount = (int) $p->pcos_get_number($indoc, "length:pages[0]/blocks");

    if ($blockcount == 0)
	throw new Exception("Error: " . $infile .
	    "does not contain any PDFlib blocks");

    
    /* -------------------------------------------------------------------
     * Query the background color space and color of all blocks. The color
     * space can be DeviceGray, DeviceRGB, DeviceCMYK, Separation, or Lab.
     * -------------------------------------------------------------------
     */
    
    for ($i = 0; $i < $blockcount; $i++) 
    {
	/* Retrieve the block name */
	$blockname = $p->pcos_get_string($indoc,
	    "pages[0]/blocks[" . $i . "]/Name");
	
	/* Output the block name */
	$textbuf = "Background color of \"" . $blockname . "\" is:\n";
	$p->fit_textline($textbuf,$x, $y-=$yoff, "");
  
	/* Set the pCOS path for the background color */
	$path = "pages[0]/blocks[" . $i . "]/backgroundcolor";
	
	$type = $p->pcos_get_string($indoc, "type:" . $path);
		   
	/* Check, if a background color is available */
	if ($type == "null") {
	    $textbuf = "not available";   
	}
	else if ($type == "array") {
	    /* Get the path type */
	    $type = $p->pcos_get_string($indoc, "type:" . $path . "[0]");
	    
	    /* Check for DeviceGray, DeviceRGB, or DeviceCMYK color space */
	    if ($type == "name") {
		$colorspace = $p->pcos_get_string($indoc, $path . "[0]");
			    
		$textbuf = $colorspace . " ";
	
		if ($colorspace == "DeviceGray")
		    $ncomp = 1;
		else if ($colorspace == "DeviceRGB")
		    $ncomp = 3;
		else if ($colorspace == "DeviceCMYK")
		    $ncomp = 4;
		else
		    throw new Exception("Unknown color space");
	    }
	    else if ($type == "array"){
		/* Check for Separation or Lab color space; in these cases 
		 * the color space itself is an array
		 */ 
		$colorspace = $p->pcos_get_string($indoc, $path . "[0][0]"); 
	
		$textbuf = $colorspace . " ";
	    
		if ($colorspace == "Separation") {
		    $name = $p->pcos_get_string($indoc, $path . "[0][1]");
		    $textbuf = $textbuf . "\"" . $name . "\"" . " ";
		    $ncomp = 1;
		}
		else if ($colorspace == "Lab")
		    $ncomp = 3;
		else 
		    throw new Exception("Unknown color space");
	    }
	    else {
		throw new Exception("Unknown color space");
	    }
			
	    /* Get one or more color components */
	    if ($ncomp == 1) {
		$comp = $p->pcos_get_number($indoc, $path . "[1]");
		$textbuf = $textbuf . sprintf("%.2f", $comp);
	    }
	    else
	    {
		$textbuf = $textbuf . " [";
		for ($j = 0; $j < $ncomp; ++$j) {
		    $comp = 
			$p->pcos_get_number($indoc, $path . "[1][" . $j . "]");
		    $textbuf = $textbuf . " " . sprintf("%.2f",$comp) . " ";
		}
		$textbuf = $textbuf . "]";
	    }
	}
	else {
	    throw new Exception("Unknown color space");
	}
		    
	/* Output the color space and color of the block */
	$p->fit_textline($textbuf, $x, $y-=$yoff, "fillcolor={rgb 0.2 0.4 0.7}");
	
    } /* for */
    $p->end_page_ext("");
	  
    $p->close_pdi_page($page);

    $p->end_document("");
    $p->close_pdi_document($indoc);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=query_block_color.pdf");
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
