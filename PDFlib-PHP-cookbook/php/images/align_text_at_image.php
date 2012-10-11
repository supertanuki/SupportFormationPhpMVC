<?php
/* $Id: align_text_at_image.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Align text at image:
 * Align text at an image
 *
 * Align text orientated to the west at the lower right corner of an image by
 * retrieving the coordinates of the image matchbox.
 * Align text orientated to the west at the lower right corner of an image
 * orientated to the west.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Align Text at Image";

$imagefile = "kraxi_logo.tif";
$optlist;
$x1 = 0; $x2 = 0; $y1 = 0; $y2 = 0;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    

    /* -----------------------------------------------------------------
     * Align text orientated to the west at the lower right corner of an
     * image
     * -----------------------------------------------------------------
     */
    
    /* Place the image in the center of a box using the "boxsize" and 
     * "position" options. Maintain its proportions using "fitmethod=meet".
     * Use the "matchbox" option with the "borderwidth" suboption to draw a
     * small rectangle around the image with the "strokecolor" suboption
     * determining the border color. 
     */
    
    /* Fit the image */
    $optlist = "boxsize={300 200} position={center} " .
	"fitmethod=meet matchbox={name=giantwing borderwidth=3 " .
	"strokecolor={rgb 0.85 0.83 0.85}}";
    
    $p->fit_image($image, 100, 500, $optlist);
    
    /* Retrieve the coordinates of the second (lower right) matchbox corner.
     * The parameter "1" indicates the first instance of the "giantwing"
     * matchbox.
     */
    if ($p->info_matchbox("giantwing", 1, "exists") == 1) {
	    $x2 = $p->info_matchbox("giantwing", 1, "x2");
	$y2 = $p->info_matchbox("giantwing", 1, "y2");
    }
    
    /* Start the text line orientated to the west at the corner coordinates
     * retrieved (x2, y2) with a small offset of 3 or 2, respectively.
     */
    $optlist = "font=" . $font . " fontsize=12 orientate=west";
    
    $p->fit_textline("Foto: Kraxi", $x2+3, $y2+2, $optlist);
    
    
    /* -----------------------------------------------------------------
     * Align text orientated to the west at the lower right corner of an
     * image orientated to the west as well.
     * -----------------------------------------------------------------
     */
    
    /* Place the image in the center of a box using the "boxsize" and 
     * "position" options. Maintain its proportions using "fitmethod=meet".
     * Using the "orientate" option orientate the image to the west. Use the
     * "matchbox" option with the "borderwidth" suboption to draw a small
     * rectangle around the image with the "strokecolor" suboption
     * determining the border color. 
     */
    $optlist = "boxsize={200 300} position={center} fitmethod=meet " .
	"orientate=west matchbox={name=giantwing borderwidth=3 " .
	"strokecolor={rgb 0.85 0.83 0.85}}";
    
    $p->fit_image($image, 100, 100, $optlist);
    
    /* Retrieve the coordinates of the first matchbox corner; usually this
     * will be the lower left corner but with being orientated to the west
     * it will be moved to the bottom right. The parameter "2" indicates the
     * second instance of the "giantwing" matchbox.
     */
    if ($p->info_matchbox("giantwing", 2, "exists") == 1) {
	    $x1 = $p->info_matchbox("giantwing", 2, "x1");
	$y1 = $p->info_matchbox("giantwing", 2, "y1");
    }
    
    /* Start the text line orientated to the west at the corner coordinates
     * retrieved (x1, y1) with a small offset of 3 or 2, respectively.
     */
    $optlist = "font=" . $font . " fontsize=12 orientate=west";
    
    $p->fit_textline("Foto: Kraxi", $x1+3, $y1+2, $optlist);
    
    $p->close_image($image);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=align_text_at_image.pdf");
    print $buf;
}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in align_text_at_image sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;
?>

