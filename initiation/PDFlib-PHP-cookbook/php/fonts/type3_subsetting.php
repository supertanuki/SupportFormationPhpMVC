<?php
/* $Id: type3_subsetting.php,v 1.5 2012/05/03 14:00:38 stm Exp $
 * Type 3 subsetting:
 * Demonstrate Type 3 font definition, use, and subsetting
 * 
 * In the first definition pass, create the "T3font" widths-only font by 
 * supplying all font and glyph metrics.
 * Load the "T3font" font with "subsetting" and output some text.
 * In the second definition pass, supply all glyph descriptions for the font.
 * With the "subsetting" option PDFlib will include only those glyphs in the
 * font which are actually used in the document in order to reduce the overall
 * file size. However, the full metrics must be supplied in the first pass of
 * the font definition. 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Type 3 Subsetting";

function define_font($p, $pass){
    $data =array(
    "\x00\x00\x00\x00\x00\x78\x84\x04\x7C\x84\x84\x8C\x76\x00\x00\x00", /* "a" */
    "\x00\x00\xC0\x40\x40\x5C\x62\x42\x42\x42\x42\x62\xDC\x00\x00\x00"); /* "b" */
    
    /* From the data defining three glyphs, create three PVFs
     * "/pvf/font/bitmap0" ... "/pvf/font/bitmap2"
     */
    if ($pass == 2) {
    
	for ($i=0; $i < 2; $i++) {
	    $p->create_pvf("/pvf/font/bitmap" . $i, $data[$i], "");
	}
    }
    
    /* Create the "T3font" widths-only font in pass 1
     */
    $optlist = "";
    if ($pass == 1) $optlist =  "widthsonly=true";
    $p->begin_font("T3Font", 1/16.0, 0, 0, 1/16.0, 0, -3/16.0, $optlist);
		  
    /* The .notdef (fallback) glyph should be contained in all Type 3
     * fonts to avoid problems with some PDF viewers. It is usually empty.
     * Therefore we don't have to distinguish between pass 1 and pass 2.
     */
    $p->begin_glyph(".notdef", 8, 0, 0, 0, 0);
    $p->end_glyph();
    
    /* Define the glyph "a" */
    $p->begin_glyph("a", 8, 0, 0, 8, 16);
    
    /* In pass 2, load the bitmap data for the glyph from the PVF.
     * The "inline" option is provided so that load_image() will
     * internally perform the equivalent of fit_image(image, 0, 0, "")
     * and close_image(image).
     */
    $optlist = 
	"inline bpc=1 components=1 height=16 width=8 mask invert";
    
    if ($pass == 2) {
	$image = $p->load_image("raw", "/pvf/font/bitmap0", $optlist);
	if ($image == 0 && $p->get_errnum() > 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    $p->end_glyph();
    
    /* Define the glyph "b" */
    $p->begin_glyph("b", 8, 0, 0, 8, 16);
    
    if ($pass == 2) {
	$image = $p->load_image("raw", "/pvf/font/bitmap1", $optlist);
	if ($image == 0 && $p->get_errnum() > 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
   
    $p->end_glyph();

    /* ...define all glyph descriptions... */

    $p->end_font();
}

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Pass 1: Create the widths-only font to make it known to PDFlib */
    define_font($p, 1);

    /* Create some text on the page with glyphs from the font. Loading the
     * font with the "subsetting" option  will include only those glyphs in
     * the font which are actually used in the document.
     */
    $p->begin_page_ext(595, 842, "");
    
    $font = $p->load_font("T3Font", "winansi", "subsetting");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->fit_textline("a", 50, 700, "font=" . $font . " fontsize=36");
    
    $p->end_page_ext("");
    
    /* Pass 2: Supply glyph descriptions for the font */
    define_font($p, 2);

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=type3_subsetting.pdf");
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

