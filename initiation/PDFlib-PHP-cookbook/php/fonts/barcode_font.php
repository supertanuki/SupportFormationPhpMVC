<?php
/* $Id: barcode_font.php,v 1.5 2012/05/07 13:25:28 stm Exp $
 * Barcode font:
 * Output text in a barcode font.
 * 
 * Load a barcode font and output text. Enclose the text in the start and stop 
 * characters which are individually defined by the respective barcode font.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Barcode Font";


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
    $p->begin_page_ext(150, 120, "");

    /* Load the barcode font.
     * "FRE3OF9X.TTF" is a free 3 of 9 Barcode created by Matthew Welch.
     * See http://www.barcodesinc.com/free-barcode-font/.
     * 
     * For PDFlib Lite: change "unicode" to "winansi"
     *
     * For a symbol barcode font, please change "unicode" to "builtin".
     * Please See PDFlib 8.0.4 Tutorial, chapter 5.4.2 and 5.4.3.
     */
    $barcodefont = $p->load_font("FRE3OF9X", "unicode", "embedding");
    if ($barcodefont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Output text with the barcode font. Note the following when creating
     * barcode text: To create a valid 3 of 9 barcode you have to begin and
     * end it with a special character. Scanners look for this character to
     * know where to start and stop reading the barcode. It is represented
     * in this font with the '*' character. So, to create a barcode for the
     * text "ABC123" you have to type out "*ABC123*". Note that barcode
     * readers will not include the *'s in the text they return. They will
     * just give you the "ABC123".
     */

    $p->fit_textline("*ABC123*", 10, 75, "font=" . $barcodefont .
	" fontsize=20");
    
    /* For PDFlib Lite: change "unicode" to "winansi" */
    $normalfont = $p->load_font("Helvetica", "unicode", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_textline("*ABC123* ", 10, 60, "font=" . $normalfont .
	" fontsize=10");
    $p->fit_textline("which will be returned by the", 10, 30, "font=" .
	$normalfont . " fontsize=10");
    $p->fit_textline("barcode reader as ABC123", 10, 10, "font=" .
	$normalfont . " fontsize=10");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=barcode_font.pdf");
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
