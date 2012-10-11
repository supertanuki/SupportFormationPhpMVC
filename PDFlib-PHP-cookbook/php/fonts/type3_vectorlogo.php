<?php
/* $Id: type3_vectorlogo.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Type 3 vector logo font:
 * Create a Type 3 font which contains a single logo derived from a vector
 * based PDF page
 *
 * Import vector data from a PDF file to create a Type 3 logo font containing
 * one glyph. Add the glyph to a custom encoding and output text with that
 * glyph.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Type 3 Vector Logo Font";

$logofile = "kraxi_logo.pdf";

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Import vector data from "kraxi_logo.pdf" to create the "Logofont"
     * Type 3 font with one glyph "k" (0x6B). Add a glyph to the
     * "LogoEncoding" encoding with the glyphname "kraxi" and the slot
     * "k" (0x6B). Output text containing the glyph by addressing via slot
     * values and "LogoEncoding".
     */

    /* Load vector data from PDF file */
    $indoc = $p->open_pdi_document($logofile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $page = $p->open_pdi_page($indoc, 1, "");
    if ($page == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create the font Logofont. The matrix entries are chosen to create
     * the common 1000x1000 coordinate system. These numbers are also
     * used when placing the logo within the glyph box below (option
     * "boxsize").
     */
    $p->begin_font("Logofont", 0.001, 0.0, 0.0, 0.001, 0.0, 0.0, "colorized");

    /* Add a glyph with the name "kraxi" and width 1000.
     * Colorized fonts do not need any bounding box, so we supply the
     * values 0, 0, 0, 0.
     * With colorized=false (the default) we could use 0, 0, 1000, 1000
     * for the common 1000x1000 coordinate system.
     */
    $p->begin_glyph("kraxi", 1000, 0, 0, 0, 0);

    /* Fit the contents of the PDF in a box similar to the dimensions
     * of the glyph box. We place the glyph at (0, 100) in order to
     * slightly move up the logo so that it better matches standard
     * text.
     */
    $p->fit_pdi_page($page, 0, 100, "boxsize={1000 1000} fitmethod=auto");
    $p->end_glyph();

    /* The .notdef (fallback) glyph should be contained in all Type 3
     * fonts to avoid problems with some PDF viewers. It is usually
     * empty.
     */
    $p->begin_glyph(".notdef", 1000, 0, 0, 0, 0);
    $p->end_glyph();

    $p->end_font();

    $p->close_pdi_page($page);
    $p->close_pdi_document($indoc);

    /* Assign the logo to slot 0x6B in our "LogoEncoding" encoding */
    $p->encoding_set_char("LogoEncoding", 0x6B, "kraxi", 0);

    /* Load the new "Logofont" font with the encoding "LogoEncoding" */
    $logofont = $p->load_font("Logofont", "LogoEncoding", "");
    if ($logofont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the "Helvetica" font */
    $normalfont = $p->load_font("Helvetica", "winansi", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=200 height=100");

    /* Print the character "k" (0x6B) of the "Logofont" font */
    $p->fit_textline("k", 10, 50, "font=" . $logofont . " fontsize=14");

    /* Print standard text */
    $p->fit_textline("This is the kraxi logo.", 30, 50, "font=" . $normalfont .
		   " fontsize=14");

    /* Alternatively, select the logo glyph via a character reference
     * which refers to the glyph name "kraxi". Use the "charref" option
     * to enable character referencing.
     */
    $p->fit_textline("&.kraxi;", 170, 50, "font=" . $logofont .
	" fontsize=14 charref");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=type3_vectorlogo.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() .
        ": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;
?>


