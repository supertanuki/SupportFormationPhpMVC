<?php
/* $Id: image_mask.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Image mask:
 * Place an image with a mask applied to it
 * 
 * Place an image and apply a Grayscale mask on it. Depending on the values
 * contained in the mask, the image pixels will be displayed more or less 
 * transparent. Smaller (darker) mask values result in more transparent image 
 * pixels and larger (lighter) values in less transparency.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Image Mask";

$imagefile = "sky.jpg";
$maskfile = "image_mask.jpg";

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
    
    /* Load the original image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the Grayscale image to be used as the transparency mask */
    $mask = $p->load_image("auto", $maskfile, "mask");
    if ($mask == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the image with the transparency mask being assigned to it */
    $masked_image = $p->load_image("auto", $imagefile, "masked " . $mask);
    if ($masked_image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start a page with a size similar to the image dimensions. 
     * The "transparencygroup" option improves output quality when placing
     * the image on a page which contains transparent objects (image or
     * other).
     * PDFlib 7.0.3 will automatically create the "transparencygroup"
     * option, so it is only required in earlier versions.
     */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width " .
	"transparencygroup={CS=DeviceRGB}");
    
    /* Place the original image */
    $p->fit_image($image, 50, 150, "boxsize {200 500} fitmethod=meet");
    $p->fit_textline("Original image", 50, 130, "font=" . $font .
	" fontsize=14");
    
    /* Place the image without the clipping path being applied to it */
    $p->fit_image($mask, 300, 150, "boxsize {200 500} fitmethod=meet");
    $p->fit_textline("Transparency mask", 300, 130, "font=" . $font .
	" fontsize=14");
    
    /* Place the image with the clipping path being applied to it */
    $p->fit_image($masked_image, 550, 150, "boxsize {200 500} fitmethod=meet");
    $p->fit_textline("Image with the transparency mask applied.", 550, 130,
	"font=" . $font . " fontsize=10");
    $p->fit_textline("Darker mask values result in more transparent image " .
	"values.", 550, 110, "font=" . $font . " fontsize=10");
	    
    /* Close the images */
    $p->close_image($image);
    $p->close_image($mask);
    $p->close_image($masked_image);
 
    $p->end_page_ext("");
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=image_mask.pdf");
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
