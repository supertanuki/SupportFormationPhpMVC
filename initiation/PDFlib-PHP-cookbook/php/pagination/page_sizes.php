<?php
/* $Id: page_sizes.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Page sizes:
 * Create some pages with various page sizes
 *
 * Create pages with A4 format and Portrait orientation, with A4 format and
 * Landscape orientation, with Letter format and Portrait orientation, and
 * with Letter format and Landscape orientation.
 *
 * Create a page with a size according to the dimensions of the image to be
 * placed on the page.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Page Sizes";

$imagefile = "nesrin.jpg";

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

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create a page in A4 format and Portrait orientation */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    $p->fit_textline("A4 format in Portrait orientation.", 50, 50,
		   "font=" . $font . " fontsize=20");
    $p->end_page_ext("");

    /* Create a page in A4 format and Landscape orientation */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    $p->fit_textline("A4 format in Landscape orientation.", 50, 50,
		   "font=" . $font . " fontsize=20");
    $p->end_page_ext("");

    /* Create a page in Letter format and Portrait orientation */
    $p->begin_page_ext(0, 0, "width=letter.width height=letter.height");

    $p->fit_textline("Letter format in Portrait orientation.", 50, 50,
		   "font=" . $font . " fontsize=20");
    $p->end_page_ext("");

    /* Create a page in Letter format and Landscape orientation */
    $p->begin_page_ext(0, 0, "width=letter.height height=letter.width");

    $p->fit_textline("Letter format in Landscape orientation.", 50, 50,
		   "font=" . $font . " fontsize=20");
    $p->end_page_ext("");

    /* Create a page with a size according to the dimensions of
     * the image to be placed on the page
     */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Get the width and height of the image */
    $imagewidth = $p->info_image($image, "imagewidth", "");
    $imageheight = $p->info_image($image, "imageheight", "");

    /* Start the page with the size of the image to be fit on the page */
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
    header("Content-Disposition: inline; filename=page_size.pdf");
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
