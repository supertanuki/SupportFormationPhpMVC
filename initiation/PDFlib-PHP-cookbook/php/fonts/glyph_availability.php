<?php
/* $Id: glyph_availability.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Glyph availability:
 * Check the availability of glyphs in a font
 *
 * Load a font with "winansi" encoding and check using info_font() with the 
 * "code" keyword if the font contains the glyphs you need.
 * Then. load the font with "unicode" encoding and check using info_font() with
 * the "glyphid" or "glyphname" options if the font contains particular glyphs.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Glyph Availability";

$x=30; $y=700; $yoff=30;
$info = -1;

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
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    
    /* -------------------------------------------------------
     * In the first case, load the font with an 8-bit encoding
     * and perform a glyph check
     * -------------------------------------------------------
     */
	   
    /* Load the font "Gentium-Italic" (see http://scripts.sil.org/gentium)
     * with "winansi" encoding and "embedding"
     * 
     */
    $font = $p->load_font("GenI102", "winansi", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->setfont($font, 22);
    
    /* Output some descriptive text */
    $p->fit_textline("Check the Gentium-Italic font loaded with \"winansi\"" .
	" encoding", $x, $y, "");
    
    /* Check whether the Euro glyph is contained in a font by retrieving its
     * code. The "unicode" option expects a Unichar. For Unichars, glyph 
     * name references are always used in lowercase letters without the &
     * and ; decoration. This is safer than checking the glyph name which
     * may be euro, Euro, or something different.
     */
    $info = $p->info_font($font, "code", "unicode=euro");
    
    if ($info > -1) {
	/* Output the Euro glyph via a character reference by name. Then, 
	 * output the code retrieved.
	 */
	$p->fit_textline("The Euro glyph is available: &euro;      Code: " . 
	    $info, $x, $y-=$yoff, "charref");
    }
    else { 
	$p->fit_textline("No glyph for Euro available", $x, $y-=$yoff, "");
    }
    
    $y-=$yoff;
    
    /* ---------------------------------------------------------
     * In the second case, load the font with "unicode" encoding
     * and perform various glyph check
     * ---------------------------------------------------------
     */        

    /* Load the font "Gentium-Italic" with "unicode" encoding
     * (see http://scripts.sil.org/gentium)
     */
    $font = $p->load_font("GenI102", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->setfont($font, 22);
    
    /* Output some descriptive text */
    $p->fit_textline("Check the Gentium-Italic font loaded with \"unicode\"" .
	" encoding", $x, $y-=$yoff, "");

    /* Check if the Euro glyph is contained in the font by using 
     * info_font() with the "glyphname" keyword and the "unicode" option
     * with a character reference by name
     */
    $info = $p->info_font($font, "glyphname", "unicode=euro");
    if ($info > -1) {
	/* Output the Euro glyph via a character reference by name. Then,
	 * output the glyph name retrieved.
	 */
	$istr = $p->get_parameter("string", $info);
	$p->fit_textline("The Euro glyph is available: &euro;      " .
	    "Glyph name: " . $istr, $x, $y-=$yoff, "charref");
    }
    else {
	$p->fit_textline("No glyph for Euro available", $x, $y-=$yoff, "");
    }
		   
    /* Check if the glyph for the Polish "zacute" character is contained
     * in the font by using info_font() with the "glyphname" keyword and the
     * "unicode" option with the Unicode value
     */
    $info = $p->info_font($font, "glyphname", "unicode=U+017A");
    if ($info > -1) {
	/* Output the Polish zacute glyph via the Unicode value. Then, 
	 * output the glyph name retrieved.
	 */
	$istr = $p->get_parameter("string", $info);
	$p->fit_textline("The zacute glyph is available: &#x017A;      " .
	    "Glyph name: " . $istr, $x, $y-=$yoff, "charref"); 
    }
    else {
	$p->fit_textline("No glyph for zacute available", $x, $y-=$yoff, "");
    }
    
    /* Check if the glyph for the Russian "ya" character is contained in the
     * font by using info_font() with the "glyphid" keyword and the
     * "unicode" option with the Unicode value
     */
    $info = $p->info_font($font, "glyphid", "unicode=U+042F");
    if ($info > -1) {
	/* Output the ya glyph via the Unicode value. Then, output the 
	 * glyph id retrieved
	 */
	$p->fit_textline("The glyph for Russian ya is available: &#x042F;" .
	    "      Glyph id: " . $info, $x, $y-=$yoff, "charref");
    }
    else {
	$p->fit_textline("No glyph for Russian ya available", $x, $y-=$yoff, "");
    }
    
    /* Check if the glyph for the alternative g character is contained in
     * the font by using info_font() with the "glyphid" keyword and the
     * "unicode" option with the glyph name
     */
    $info = $p->info_font($font, "glyphid", "unicode=.g.alt");
    if ($info > -1) {
	/* Output the alternative g glyph via a character reference by
	 * name. Then, output the glyph id.
	 */
	$p->fit_textline("The glyph for alternative g is available: " .
	    "&.g.alt;      Glyph id: " . $info, $x, $y-=$yoff, "charref");
    }
    else {
	$p->fit_textline("No glyph for alternative g available", 
	    $x, $y-=$yoff, "");
    }
    
    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=glyph_availability.pdf");
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
