<?php
/* $Id: type3_bitmaptext.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Type 3 bitmap text:
 * Create a simple Type 3 font from image data
 *
 * Use the "inline" option of load_image() for loading glyph bitmaps.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image files
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Type 3 Bitmap Text";

$optlist = "inline bpc=1 components=1 height=16 width=8 mask invert";

$data = array(
    "\x00\x00\x00\x00\x00\x78\x84\x04\x7C\x84\x84\x8C\x76\x00\x00\x00", /* "a" */
    "\x00\x00\xC0\x40\x40\x5C\x62\x42\x42\x42\x42\x62\xDC\x00\x00\x00",  /* "b" */
    "\x00\x00\x00\x00\x00\x3E\x42\x40\x40\x40\x42\x42\x3C\x00\x00\x00" /* "c" */
    ); 

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* From the data defining three glyphs, create three PVFs
     * "/pvf/font/bitmap0" ... "/pvf/font/bitmap2"
     */


    for ($i=0; $i < count($data); $i++) {
	$p->create_pvf("/pvf/font/bitmap" . $i, $data[$i], "");
    }

    /* Create the "BitmapFont" font */
    $p->begin_font("BitmapFont", 1/16.0, 0, 0, 1/16.0, 0, -3/16.0, "");

    /* Start the glyph definition for "a" */
    $p->begin_glyph("a", 8, 0, 0, 8, 16);

    /* Load the bitmap data for the glyph from the PVF.
     * The "inline" option is provided so that load_image() will internally
     * perform the equivalent of fit_image(image, 0, 0, "") and
     * close_image(image).
     */
    $image = $p->load_image("raw", "/pvf/font/bitmap0", $optlist);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->end_glyph();

    /* Define the glyph "b" */
    $p->begin_glyph("b", 8, 0, 0, 8, 16);

    $image = $p->load_image("raw", "/pvf/font/bitmap1", $optlist);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->end_glyph();

    /* Define the glyph "c" */
    $p->begin_glyph("c", 8, 0, 0, 8, 16);

    $image = $p->load_image("raw", "/pvf/font/bitmap2", $optlist);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->end_glyph();

    /* The .notdef (fallback) glyph should be contained in all Type 3
     * fonts to avoid problems with some PDF viewers. It is usually
     * empty.
     */
    $p->begin_glyph(".notdef", 8, 0, 0, 0, 0);
    $p->end_glyph();

    $p->end_font();

    $p->begin_page_ext(0, 0, "width=200 height=300");

    /* Load the new "BitmapFont" font */
    $bitmapfont = $p->load_font("BitmapFont", "winansi", "embedding");
    if ($bitmapfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Output the characters "a", "b", and "c" */
    $p->fit_textline("a", 70, 200, "font=" . $bitmapfont . " fontsize=36");
    $p->fit_textline("b", 70, 150, "font=" . $bitmapfont . " fontsize=36");
    $p->fit_textline("c", 70, 100, "font=" . $bitmapfont . " fontsize=36");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=type3_bitmaptext.pdf");
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
