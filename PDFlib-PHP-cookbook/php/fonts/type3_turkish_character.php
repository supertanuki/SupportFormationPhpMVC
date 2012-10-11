<?php
/* $Id: type3_turkish_character.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Type 3 turkish character:
 * Create a Type 3 font by cloning another font and adding a further glyph
 *
 * We use encoding iso8859-9 (since unicode doesn't work well with 8-bit Type 3
 * fonts). Create a Type 3 font "CourierTurkish" on the basis of the core font
 * "Courier". 
 * Add the synthetic glyph U+0130 LATIN CAPITAL LETTER I WITH DOT ABOVE 
 * (0xDD in iso8859-9) and output text with that glyph.
 * To construct the synthetic character use the auxiliary diacritical character
 * "dotaccent" U+02D9 DOT ABOVE plus U+0049 LATIN CAPITAL LETTER I.
 * Place "dotaccent" on auxiliary slot 0x90 which is unused in iso8859-9.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Type 3 Turkish Character";


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Start page */
    $p->begin_page_ext(0, 0, "width=200 height=200");


    /* -----------------------------
     * Step 1: Construct an encoding
     * -----------------------------
     */

    /* Load an auxiliary font with the encoding of interest "iso8859-9" */
    $auxfont = $p->load_font("Helvetica", "iso8859-9", "");

    /* Fetch all glyph names for the encoding */
    for ($i = 0; $i < 255; $i++) {
	$info = $p->info_font($auxfont, "glyphname", "code=" . $i);
	$glyphnames[$i] = $p->get_parameter("string", $info);
    }

    /* Add the auxiliary diacritical character "dotaccent" U+02D9 DOT ABOVE
     * to the extended encoding to slot 0x90. This character will be used to
     * construct the new glyph.
     */
    $glyphnames[0x90] = "dotaccent";

    /* Construct an auxiliary encoding based on iso8859-9 and add all wanted
     * glyph names
     */
    for ($i = 0; $i < 255; $i++)
	$p->encoding_set_char("iso8859-9-extended", $i, $glyphnames[$i], 0);

    
    /* --------------------------------------------------------------
     * Step 2: Derive the glyph metrics by fetching the glyph widths; 
     * actually not required for monospaced fonts like Courier
     * --------------------------------------------------------------
     */

    /* To avoid the base font in the Output PDF perform the task with a 
     * scratch PDF document. Supply an empty file name to begin_document()
     * to create the scratch file in memory.
     */
    try {
	$q = new PDFlib();
	
	/* Set the errorpolicy to "exception". With this setting PDFlib
	 * functions will throw an exception if an error occurs
	 */
	$q->set_parameter("errorpolicy", "exception");

	$q->begin_document("", "");

	/* Load the base font with "iso8859-9" encoding.
	 * 
	 * Note: Since the "iso8859-9-extended" encoding is not available in
	 * the scratch document context we use "iso8859-9". "iso8859-92" has
	 * .notdef at position 0x90, and glyphwidths[0x90] will contain the
	 * widths of .notdef. "iso8859-9-extended" has dotaccent at 0x90, 
	 * and the corresponding width. However, since we don't use the
	 * width of the accent (only the width of the base character) it
	 * doesn't hurt; for other situations (different pair of combined
	 * characters) it could be relevant.
	 */
	$basefont = $q->load_font("Courier", "iso8859-9", "");
		
	$q->begin_page_ext(595, 842, "");
	
	$q->setfont($basefont, 1000);
	for ($i = 0; $i < 255; $i++)
	    $glyphwidths[$i] = 
		$q->info_textline(chr($i), "width", "");

		/* Set the glyph width of the synthetic glyph (0xDD) to the width 
		 * of the 'T' character
		 */
	$glyphwidths[0xDD] = 
		    $q->info_textline('T', "width", "");
	
		$q->end_page_ext("");

	$q->end_document("");
	
    } catch (PDFlibException $e){
	die("PDFlib exception occurred:\n" .
	    "[" . $e->get_errnum() . "] " . $e->get_apiname() .
	    ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
	die($e->getMessage());
    }
    $q=0;
   

    /* --------------------------------------------
     * Step 3: Define the new font "CourierTurkish"
     * --------------------------------------------
     */
    $basefont = $p->load_font("Courier", "iso8859-9-extended", "");
    if ($basefont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_font("CourierTurkish", 0.001, 0.0, 0.0, 0.001, 0.0, 0.0, "");

    /* .notdef is required; the width of .notdef does not really matter */
    $p->begin_glyph(".notdef", 600, 0, 0, 0, 0);
    $p->end_glyph();

    /* Clone all available glyphs from the input font with the exception of
     * any ".notdef" glyphs
     */
    for ($i = 0; $i < 255; $i++) {
	if ($glyphnames[$i] == ".notdef") {
			    continue;
		    }
	/* The bounding box must be large enough to contain the respective
	 * glyph. We are lazy and use the font bounding box as a safe
	 * estimate. Better create the bounding box too large than risk
	 * problems.
	 */
	$p->begin_glyph($glyphnames[$i], $glyphwidths[$i], -23, -250, 623, 805);
	$p->setfont($basefont, 1000);

	/* Now for the crucial trick:
	 * Create a new glyph at position "0xDD" by combining the two
	 * existing glyphs "I" and "dotaccent". The name of the new glyph 
	 * has already been set to "Idotaccent" above.
	 */
	if ($i == 0xDD) {
	    /* Combine the "I" glyph... */
	    $p->show_xy("I", 0, 0);
	    
	    /* ...with the character at position 0x90 (which has been set
	     * to "dotaccent" above. Shift the accent upwards to nicely
	     * position it on top of the "I".
	     */
	    $p->show_xy(chr(0x90), 0.0, 160.0);
	}
	else {
	    $p->show_xy(chr($i), 0, 0);
	}
	$p->end_glyph();
    }

    $p->end_font();


    /* ------------------------
     * Step 4: Use the new font
     * ------------------------
     */

    /* With the Courier core font output "Istanbul" */
    $basefont = $p->load_font("Courier", "iso8859-9", "");
    if ($basefont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->fit_textline("Istanbul", 50, 140, "font=" . $basefont . " fontsize=14");

    /* With the new Type 3 font output "\335stanbul" with "\335" being the
     * octal representation of the "Idotaccent" glyph (0xDD) defined above
     */
    $newfont = $p->load_font("CourierTurkish", "iso8859-9", "");
    if ($newfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->fit_textline("\335stanbul", 50, 100, "font=" . $newfont .
	" fontsize=14 charref");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=type3_turkish_character.pdf");
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

