<?php
/* $Id: image_rounded_corners.php,v 1.4 2012/05/03 14:00:40 stm Exp $
 * Image with rounded corners:
 * Place an image with rounded corners
 *
 * Get the dimensions of an image and create a clipping path as a rectangle of
 * 0.5 of the width and height of the image and with rounded corners. Fit the
 * image into a box with the position and size of the clipping path.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Image with Rounded Corners";

$imagefile = "nesrin.jpg";
$image;
$radius=50;          // radius of the circle describing a rounded corner
$x=20; $y=20;         // lower left position of the image

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Get the width and height of the image */
    $width = $p->info_image($image, "imagewidth", "");
    $height = $p->info_image($image, "imageheight", "");

    /* Start a page with the image dimensions */
    $p->begin_page_ext($width, $height, "");

    /* Save the current graphics state including the clipping path */
    $p->save();

    /* Define a path as a rectangle of 0.5 of the original width and height
     * of the image and with rounded corners
     */
    $width *= 0.5;
    $height *= 0.5;

    $p->moveto($x + $radius, $y);
    $p->lineto($x + $width - $radius, $y);
    $p->arc($x + $width - $radius, $y + $radius, $radius, 270, 360);
    $p->lineto($x + $width, $y + $height - $radius );
    $p->arc($x + $width - $radius, $y + $height - $radius, $radius, 0, 90);
    $p->lineto($x + $radius, $y + $height);
    $p->arc($x + $radius, $y + $height - $radius, $radius, 90, 180);
    $p->lineto($x , $y + $radius);
    $p->arc($x + $radius, $y + $radius, $radius, 180, 270);

    /* Set clipping path to defined path */
    $p->clip();

    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");

    /* Fit the image into a box with the size and start point (x,y) of the
     * clipping path. The image is placed in the center of the box using a
     * fit method of "meet" which will scale the image proportionally until
     * it completely covers the box */
    $p->fit_image($image, $x, $y, "boxsize {" . $width . " " . $height .
	"} position center fitmethod=meet");

    /* Close image and restore original clipping (no clipping) */
    $p->close_image($image);
    $p->restore();

    $p->end_page_ext("");
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=image_rounded_corners.pdf");
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

