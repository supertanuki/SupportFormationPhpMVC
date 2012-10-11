<?php
/* $Id: character_references.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Character references:
 * Demonstrate the usefulness of character references, using a suitable font.
 *
 * Output text containing a character with the glyph name "g.alt". Output the
 * Euro glyph via a character reference by name or by numerical value.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Character References";

$x=20; 
$y=220; 
$width=300; 
$height=300;

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the font "Gentium-Italic" with unicode encoding
     * (see http://scripts.sil.org/gentium)
     */
    $gentiumfont = $p->load_font("GenI102", "unicode", "");
    if ($gentiumfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the font "Courier" */
    $courierfont = $p->load_font("Courier", "unicode", "");
    if ($courierfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());


    /* Start page */
    $p->begin_page_ext($width, $height, "");

    $p->fit_textline("Input", $x, $y, "font=" . $courierfont . " fontsize=16");
    $p->fit_textline("Output", $x+160, $y, "font=" . $gentiumfont .
	" fontsize=20");


    /* Select a glyph via a character reference which refers to the glyph
     * name. Use the "charref" option to enable the character referencing
     * feature.
     */

    /* First for comparison purposes output the text with the usual glyph "g" */
    $p->fit_textline("Wing", $x, $y-=30, "font=" . $courierfont .
	" fontsize=16");
    $p->fit_textline("Wing", $x+160, $y, "font=" . $gentiumfont .
	" fontsize=20 charref");

    /* With the "Gentium-Italic" font, output the text "Wing" containing
     * a character with the glyph name "g.alt". (Such a glyph name is used
     * to choose among multiple glyphs in the font which have the same
     * Unicode value and therefore cannot uniquely be addressed via Unicode
     * values.) "g.alt" is addressed via a character reference by name
     * "&.g.alt;"
     */
    $p->fit_textline("Win&.g.alt;", $x, $y-=23, "font=" . $courierfont .
	" fontsize=16");
    $p->fit_textline("Win&.g.alt;", $x+160, $y, "font=" . $gentiumfont .
	" fontsize=20 charref");

    /* Output the Euro glyph via a character reference by name */
    $p->fit_textline("500 &euro;", $x, $y-=23, "font=" . $courierfont .
	" fontsize=16");
    $p->fit_textline("500 &euro;", $x+160, $y, "font=" . $gentiumfont .
	" fontsize=20 charref");

    /* Output the Euro glyph via a character reference by numerical value */
    $p->fit_textline("500 &#x20AC;", $x, $y-=23, "font=" . $courierfont .
	" fontsize=16");
    $p->fit_textline("500 &#x20AC;", $x+160, $y, "font=" . $gentiumfont .
	" fontsize=20 charref");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=character_references.pdf");
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

