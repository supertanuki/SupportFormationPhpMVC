<?php
/* $Id: multi_page_tiff.php,v 1.3 2012/05/07 08:42:22 stm Exp $
 * Multi-page TIFF to PDF converter
 * 
 * Convert an input TIFF image containing one or more frames to PDF.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: multi-page TIFF image
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Multi-page TIFF";

$imagefile = "multi_page.tif";
$x = 50; $y = 700; 

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == -1)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	    throw new Exception ("Error: " . $p->get_errmsg());
    
    /* Loop over all frames of the multi-page TIFF image */
    for ($frame = 1; /* */ ; $frame++)
    {
	    /* Load the next frame of the image */
	$image = $p->load_image("tiff", $imagefile, "page=" . $frame);
	if ($image == 0)
	{
	    if ($frame == 1)
	        /* image not found */
	        throw new Exception("Error: " . $p->get_errmsg());
            else
                 /* no more frames available */
	         break;
	}
	/* Start page and place the frame on the page,
	 * placed at the bottom center.
	 */
	$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	$p->fit_image($image, 0.0, 0.0, "adjustpage");
	$p->close_image($image);
	
	/* Output the number of the frame */
	$p->fit_textline("Frame " . $frame . " of the TIFF image", $x, $y,
	    "font= " . $font . " fontsize=12");
	$p->end_page_ext("");
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=multi_page_tiff.pdf");
    print $buf;
}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in multi_page_tiff sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0; 
?>
