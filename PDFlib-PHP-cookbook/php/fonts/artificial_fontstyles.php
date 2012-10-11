<?php
/* $Id: artificial_fontstyles.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Artificial font styles:
 * Create bold or italic text if you don't have a suitable font.
 *
 * Create bold, italic, or bold-italic text even if you don't have the
 * corresponding bold or italic font, but only the regular font style.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Artificial Font Styles";

$x=20; $y=400;

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
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Load the TrueType font "Gentium-Italic"
     * (see http://scripts.sil.org/gentium) with the "embedding" option to
     * make sure that the font will be embedded in the PDF
     */
    $normalfont = $p->load_font("GenI102", "winansi", "embedding");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("Gentium-Italic", $x, $y, "font=" . $normalfont .
	" fontsize=20");

    /* Load the font "Gentium-Italic" with artificial Bold font style and
     * "embedding". If the font is embedded the artificial Bold font style
     * will be created by PDFlib. (If you didn't embed the font, the
     * artificial Bold style would be created by Acrobat instead of by
     * PDFlib.)
     */
    $artificialfont = $p->load_font("GenI102", "winansi", 
	"embedding fontstyle=bold");
    if ($artificialfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("Gentium-Italic loaded with \"embedding fontstyle=" .
	"bold\"", $x, $y-=40, "font=" . $artificialfont . " fontsize=20");

    /* Load the Acrobat standard font "Courier" */
    $normalfont = $p->load_font("Courier", "winansi", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("Courier", $x, $y-=70, "font=" . $normalfont .
	" fontsize=20");

    /* Load the Acrobat standard font "Courier" with artificial Bold-Italic
     * font style. In this case, PDFlib will automatically map the font to
     * the Acrobat standard font Courier-BoldOblique.
     */
    $artificialfont = $p->load_font("Courier", "winansi", 
	"fontstyle=bolditalic");
    if ($artificialfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("Courier loaded with \"fontstyle=bolditalic\"", $x, $y-=40,
	"font=" . $artificialfont . " fontsize=20");
    $p->fit_textline("        and mapped to Courier-BoldOblique", $x, $y-=30,
	"font=" . $artificialfont . " fontsize=20");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=artificial_fontstyle.pdf");
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

