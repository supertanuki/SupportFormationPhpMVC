<?php
/* $Id: integrated_clipping_path.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Integrated clipping path:
 * Place an image with its integrated clipping path being applied to it
 * 
 * Use an image containing a clipping path. Place it without any clipping 
 * taken place. Then, place the image clipped according to its clipping path.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Integrated Clipping Path";

$imagefile = "child_clipped.jpg";

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

    /* Load the image with its integrated clipping path being ignored */
    $image = $p->load_image("auto", $imagefile, "honorclippingpath=false");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the same image with its integrated clipping path being
     * considered (which is the default setting)
     */
    $imageclipped = $p->load_image("auto", $imagefile, "");
    if ($imageclipped == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == -1)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start a page with the image dimensions */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Place the image without the clipping path being applied to it */
    $p->fit_image($image, 100, 450, "boxsize {400 300} fitmethod=meet");
    $p->fit_textline("Image without its integrated clipping path being " .
	"applied.", 100, 430, "font=" . $font . " fontsize=14");

    /* Place the image with the clipping path being applied to it */
    $p->fit_image($imageclipped, 100, 100, "boxsize {400 300} fitmethod=meet");
    $p->fit_textline("Image with its integrated clipping path being applied.",
	100, 80, "font=" . $font . " fontsize=14");
    
    /* Close the images */
    $p->close_image($image);
    $p->close_image($imageclipped);
 
    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=integrated_clipping_path.pdf");
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

