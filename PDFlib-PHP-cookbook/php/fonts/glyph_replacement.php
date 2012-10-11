<?php
/* $Id: glyph_replacement.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Glyph replacement:
 * Show the effects of glyph substitution in case of glyphs missing in the font
 * 
 * Load the font and specify a replacement character to be used to output
 * missing glyphs.
 * Demonstrate various notations to output the Euro sign as U.20AC which is
 * available in the font.
 * Then, various notations to output the ligature "ffi" are shown, the glyph of
 * which is available in the font.
 * Output the glyph for an alternative g which is available in the font.
 * Output the glyph for an alternative s which is not available in the font. 
 * Output the glyph for the ligature "st" which is not available in the font. 
 * Use the "glyphcheck=replace" or "glyphcheck=none" option to determine if the
 * missing glyph will be replaced. Depending on the "glyphcheck" setting the
 * ligature "st" will be replaced by the two characters "s" and "t".
 * Output the Ohm glyph which is not available in the font. Depending on the
 * "glyphcheck" setting it will be replaced by the Omega character.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Glyph Replacement";

$x=30;
$x2=180; 
$x3=360; 
$x4=520; 
$y=550; 
$yoff=30;

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
    
    /* Load the font "Gentium-Italic" (see http://scripts.sil.org/gentium)
     * with "unicode" encoding.
     * "replacementchar=?" defines a question mark to be used for glyph
     * substitution in case of a missing glyph.
     */
    $gfont = $p->load_font("GenI102", "unicode", "replacementchar=?");
    if ($gfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the Helvetica font */
    $hfont = $p->load_font("Helvetica", "unicode", "");
    if ($hfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the Helvetica-Bold font */
    $hbfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($hbfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
	   
    
    /* Define three option lists for fit_textline().
     * The "inp" option list is just for descriptive text.
     * The "repl" option list enables the interpretation of character 
     * references using "charref" and uses glyph substitution which is 
     * enabled by default ("glyphcheck=replace").
     * The second option list enables the interpretation of character 
     * references using "charref" but explicitly disables glyph 
     * substitution. Alternatively, glyph substitution can be disabled
     * with $p->set_parameter("glyphcheck", "none");
     */
    $inp = "font=" . $hfont . " fontsize=14";
    $repl = "charref font=" . $gfont . " fontsize=22";
    $norepl = "charref glyphcheck=none font=" . $gfont . " fontsize=22";
    
    /* Output some descriptive header lines for the input, output, and
     * remark column
     */
    $p->fit_textline("Glyphs in the Gentium-Italic font", $x, $y, 
	"font=" . $hbfont . " fontsize=16");
    $opts = " underline underlinewidth=1";
    $p->fit_textline("Input", $x, $y-=2*$yoff, $inp . $opts);
    $p->fit_textline("Output", $x2, $y, $inp . $opts);
    $p->fit_textline("Remark", $x4, $y, $inp . $opts);
    $p->fit_textline("glyphcheck=replace", $x2, $y-=$yoff, $inp . $opts);
    $p->fit_textline("glyphcheck=none", $x3, $y, $inp . $opts);
    
    
    /* ------------------------------------------------------------------
     * Use various notations to output the Euro symbol as U+20AC which is
     * available in the font.
     * ------------------------------------------------------------------ 
     */
    
    /* PHP Unicode notation, 
     * The first string "\\x20\\xAC" is a descriptive text and results in an 
     * output of "\x20\xAC" since the two slashes are resolved by the PHP 
     * interpreter to one slash. 
     */
    $p->fit_textline("\\xAC\\x20", $x, $y-=$yoff, $inp);  
    $p->fit_textline("\xAC\x20", $x2, $y, $repl . " textformat=utf16le"); 
    $p->fit_textline("\xAC\x20", $x3, $y, $norepl . " textformat=utf16le");
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    
    /* Character reference in HTML style with hexadecimal number */
    $p->fit_textline("&#x20AC;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&#x20AC;", $x2, $y, $repl);
    $p->fit_textline("&#x20AC;", $x3, $y, $norepl);
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    
    /* Character reference in HTML style with decimal number */
    $p->fit_textline("&#8364;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&#8364;", $x2, $y, $repl);
    $p->fit_textline("&#8364;", $x3, $y, $norepl);
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    
    /* Character reference in HTML style with entity name */
    $p->fit_textline("&euro;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&euro;", $x2, $y, $repl);
    $p->fit_textline("&euro;", $x3, $y, $norepl);
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    
    /* Character reference using a glyph name provided by the font or the
     * Adobe Glyph List (AGL)
     */
    $p->fit_textline("&.Euro;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&.Euro;", $x2, $y, $repl);
    $p->fit_textline("&.Euro;", $x3, $y, $norepl);
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    
    /* Character reference using a glyph name provided by the Adobe Glyph
     * list (AGL)
     */
    $p->fit_textline("&.uni20AC;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&.uni20AC;", $x2, $y, $repl); 
    $p->fit_textline("&.uni20AC;", $x3, $y, $norepl);
    $p->fit_textline("selected glyph available in the font", $x4, $y, $inp);
    

    /* ---------------------------------------------------------------------
     * Use various notations to output the ligature "ffi" which is available
     * in the font
     * --------------------------------------------------------------------- 
     */
    
    /* PHP Unicode notation, 
     * The first string "\\xFB\\x03" is a descriptive text and results in an 
     * output of "\xFB\x03" since the two slashes are resolved by the PHP
     * interpreter to one slash. 
     */
    $p->fit_textline("\\x03\\xFB", $x, $y-=$yoff, $inp);
    $p->fit_textline("\x03\xFB", $x2, $y, $repl . " textformat=utf16le");
    $p->fit_textline("\x03\xFB", $x3, $y, $norepl . " textformat=utf16le");
    $p->fit_textline("ffi ligature available in the font", $x4, $y, $inp);
    
    /* Character reference using a glyph name provided by the font or the
     * Adobe Glyph List (AGL)
     */
    $p->fit_textline("&.ffi;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&.ffi;", $x2, $y, $repl);
    $p->fit_textline("&.ffi;", $x3, $y, $norepl);
    $p->fit_textline("ffi ligature available in the font", $x4, $y, $inp);
	   
    
    /* ---------------------------------------------------------------------
     * Output the glyph for the ligature "st" which is not available in the
     * font. It will be replaced by the two glyphs "s" and "t".
     * --------------------------------------------------------------------- 
     */
    
    /* PHP Unicode notation, 
     * The first string "\\xFB\\x06" is a descriptive text and results in an 
     * output of "\xFB\x06" since the two slashes are resolved by the PHP
     * interpreter to one slash. 
     */
    $p->fit_textline("\\x06\\xFB", $x, $y-=$yoff, $inp);
    $p->fit_textline("\x06\xFB", $x2, $y, $repl . " textformat=utf16le");
    $p->fit_textline("\x06\xFB", $x3, $y, $norepl . " textformat=utf16le");
    $p->fit_textline("unavailable st ligature replaced with s and t glyphs",
	$x4, $y, $inp);
    
    
    /* --------------------------------------------------------------------
     * Output the glyph for an alternative g which is available in the font
     * --------------------------------------------------------------------
     */
    $p->fit_textline("&.g.alt;", $x, $y-=$yoff, $inp);      
    $p->fit_textline("&.g.alt;", $x2, $y, $repl); 
    $p->fit_textline("&.g.alt;", $x3, $y, $norepl);
    $p->fit_textline("g.alt variant glyph available in the font", $x4, $y, $inp);
    
    
    /* -------------------------------------------------------------------
     * Output the Ohm glyph which is not available in the font. It will be
     * replaced by the Omega character.
     * -------------------------------------------------------------------
     */
    $p->fit_textline("&.Ohm;", $x, $y-=$yoff, $inp);
    $p->fit_textline("&.Ohm;", $x2, $y, $repl);
    $p->fit_textline("&.Ohm;", $x3, $y, $norepl);
    $p->fit_textline("unavailable Ohm replaced by Omega glyph", $x4, $y, $inp);
    
    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=glyph_replacement.pdf");
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
