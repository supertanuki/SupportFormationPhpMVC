<?php
/* $Id: font_metrics_info.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Font metrics info:
 * Get various font related metrics such as the ascender or descender
 *
 * Use the info_font() function to get the metrics values for the capheight,
 * ascender, descender, or xheight of the font. Visualize these metrics
 * using the "matchbox" feature.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Font Metrics Info";

$text = "ABCdefghij";
$x=150; 
$y=140;


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
    $p->begin_page_ext(0, 0, "width=300 height=200");

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("LuciduxSans-Oblique", "unicode", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Retrieve the font metrics for a font size of 10. If no fontsize
     * is supplied the metrics will be based on a font size of 1000.
     */
    $capheight = $p->info_font($font, "capheight", "fontsize=10");
    $ascender = $p->info_font($font, "ascender", "fontsize=10");
    $descender = $p->info_font($font, "descender", "fontsize=10");
    $xheight = $p->info_font($font, "xheight", "fontsize=10");

    /* Set the format of the metrics values to two fractional digits */

    $p->setfont($font, 10);

    /* Output each of the font metrics with appropriate formatting as well
     * as the sample $text "ABCdefghij" with the metrics value colorized
     */
    $p->fit_textline("capheight for font size 10: " . sprintf("%.2f", $capheight),
	$x, $y, "alignchar :");
    $optlist = "matchbox={fillcolor={rgb 1 0.8 0.8} " . 
	"boxheight={capheight none}}";
    $p->fit_textline($text, $x+60, $y, $optlist);

    $p->fit_textline("ascender for font size 10: " . sprintf("%.2f", $ascender),
	$x, $y-=30, "alignchar :");
    $optlist = "matchbox={fillcolor={rgb 1 0.8 0.8} " . 
	"boxheight={ascender none}}";
    $p->fit_textline($text, $x+60, $y, $optlist);

    $p->fit_textline("descender for font size 10: " . sprintf("%.2f", $descender),
	$x, $y-=30, "alignchar :");
    $optlist = "matchbox={fillcolor={rgb 1 0.8 0.8} " . 
	"boxheight={none descender}}";
    $p->fit_textline($text, $x+60, $y, $optlist);

    $p->fit_textline("xheight for font size 10: " . sprintf("%.1f", $xheight),
	$x, $y-=30, "alignchar :");
    $optlist = "matchbox={fillcolor={rgb 1 0.8 0.8} " .
	"boxheight={xheight none}}";
    $p->fit_textline($text, $x+60, $y, $optlist);

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=font_metrics_info.pdf");
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
