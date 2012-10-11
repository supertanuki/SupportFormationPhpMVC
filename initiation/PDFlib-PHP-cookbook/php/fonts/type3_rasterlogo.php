<?php
/* $Id: type3_rasterlogo.php,v 1.4 2012/05/03 14:00:38 stm Exp $
 * Type 3 raster logo font:
 * Create a Type 3 font which contains a single logo derived from an image
 *
 * Import image data from a bitmap TIF image to create a Type 3 logo font
 * containing one glyph. Add the glyph to a custom encoding and output text
 * with that glyph.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: bitmap TIFF image
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Type 3 Raster Logo Font";

$logofile = "phone.tif";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Import an image from "phone.tif" to create the "PhoneLogofont" 3
     * Type font with one glyph "p" (0x70). Add a glyph to the
     * "PhoneLogoEncoding" encoding with the glyphname "phone" and
     * the slot "p" (0x70). Output text containing the glyph by
     * addressing via slot values and "PhoneLogoEncoding".
     */

    /* Load the bitmap data for the glyph from the file.  */
    $image = $p->load_image("auto", $logofile, "mask");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create the font PhoneLogofont. The matrix entries are chosen to
     * create the common 1000x1000 coordinate system. These numbers are
     * also used when placing the logo within the glyph box below
     * (option "boxsize").
     */
    $p->begin_font("PhoneLogofont", 0.001, 0.0, 0.0, 0.001, 0.0, 0.0, "");

    /* The .notdef (fallback) glyph should be contained in all Type 3
     * fonts to avoid problems with some PDF viewers. It is usually
     * empty.
     */
    $p->begin_glyph(".notdef", 1000, 0, 0, 0, 0);
    $p->end_glyph();

    /* Add a Glyph with the name "phone" and width 1000 */
    $p->begin_glyph("phone", 1000, 0, 0, 1000, 1000);

   /* Fit the image in a box similar to the dimensions of the glyph box.
    */
    $p->fit_image($image, 0, 0, "boxsize={1000 1000} fitmethod=auto");

    $p->end_glyph();

    $p->end_font();

    $p->close_image($image);

    /* Assign the logo to slot 0x70 in our "PhoneLogoEncoding" encoding */
    $p->encoding_set_char("PhoneLogoEncoding", 0x70, "phone", 0xF000);

    /* Load the new "PhoneLogofont" font with the encoding "LogoEncoding" */
    $logofont = $p->load_font("PhoneLogofont", "PhoneLogoEncoding", "");
    if ($logofont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the "Helvetica" font */
    $normalfont = $p->load_font("Helvetica", "winansi", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=300 height=100");

    /* Output the character "p" (0x70) of the "PhoneLogofont" font */
    $p->fit_textline("p", 50, 50, "font=" . $logofont . " fontsize=14");

    /* Output standard text */
    $p->fit_textline("This is the phone logo", 70, 50, "font=" . $normalfont .
		   " fontsize=14");

    /* Alternatively, we can select the logo glyph via a character
     * reference which refers to the glyph name "phone". Use the
     * "charref" option to enable character referencing. This requires
     * a Unicode assignment for the glyph (we use a PUA value), and
     * "unicode" encoding:

    $logofont = $p->load_font("PhoneLogofont", "unicode", "");
    if ($logofont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("&.phone;", 215, 50, "font=" . $logofont .
	" fontsize=14 charref");
    */

    /* The third method addresses the logo glyph via its Unicode
     * PUA value directly:

    $p->fit_textline("\uF000", 215, 50, "font=" . $logofont .
	" fontsize=14 charref");
    */

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=type3_rasterlogo.pdf");
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
