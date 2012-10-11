<?php
/* $Id: image_dimensions.php,v 1.4 2012/05/03 14:00:40 stm Exp $
 * Image dimensions:
 * Get the dimensions of an image for various purposes
 *
 * Place an image in its original size, retrieve its dimensions using the
 * "matchbox" option and place text directly to the right of the image. Fit an
 * image into a box and use the box dimensions to center text directly below
 * the image. Create a page with the dimensions of an image and place the image
 * so that it covers the page completely.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Image Dimensions";

$imagefile = "websurfer.jpg";
$imagefile2 = "nesrin.jpg";
$x2 = 0.0; $y2 = 0.0; $y3 = 0.0;

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

    /* On page 1, place an image in its original size, retrieve its
     * dimensions using the "matchbox" option and place text directly
     * to the right of the image
     */
    $p->begin_page_ext(0, 0, "width=100 height=100");

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Fit the image with a matchbox called "web" respresenting
     * the image dimensions */
    $p->fit_image($image, 20, 20, "matchbox={name=web}");
    $p->close_image($image);

    /* Retrieve the coordinates of the lower right (x2, y2) and upper right
     * (x3, Y3) image corners via the matchbox "web" specified above
     */
    if ($p->info_matchbox("web", 1, "exists") == 1)
    {
	$x2 = $p->info_matchbox("web", 1, "x2");
	$y2 = $p->info_matchbox("web", 1, "y2");
	$y3 = $p->info_matchbox("web", 1, "y3");
    }

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start the text line in x direction at the lower right corner of the
     * image and in y direction 1/4 of the image height up
     */
    $p->fit_textline("www.kraxi.com", $x2, $y2 + ($y3 - $y2)/4, "font=" . $font
	. " fontsize=8");

    $p->end_page_ext("");

    /* On page 2, fit an image into a box and use the box dimensions to
     * center text directly below the image
     */
    $p->begin_page_ext(0, 0, "width=100 height=100");

    /* Load the image */
    $image = $p->load_image("auto", $imagefile2, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Fit the image with a matchbox called "web" respresenting
     * the image dimension
     */
    $p->fit_image($image, 10, 20, "boxsize={80 50} position={center bottom} " .
	"fitmethod=meet");
    $p->close_image($image);

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start the text line in x direction at the lower right corner of the
     * image and in y direction 1/4 of the image height up
     */
    $p->fit_textline("My holiday photo", 10, 10,
	"boxsize={80 50} position={center bottom} font=" . $font .
	" fontsize=8");

    $p->end_page_ext("");

    /* Create page 3 with the original dimensions of the image and place
     * the image so that it covers the page completely
     *
     * Load the image
     */
    $image = $p->load_image("auto", $imagefile2, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Get the width and height of the image */
    $imagewidth = $p->info_image($image, "imagewidth", "");
    $imageheight = $p->info_image($image, "imageheight", "" );

    /* Start page 3 with the dimensions of the image to be placed */
    $p->begin_page_ext($imagewidth, $imageheight, "");

    /* Fit the image */
    $p->fit_image($image, 0, 0, "");

    $p->close_image($image);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=image_dimensions.pdf");
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

