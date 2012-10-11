<?php
/* $Id: retain_font.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * 
 * Retain fonts:
 * Demonstrate performance benefits of keeping a font open across multiple 
 * documents.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: Fallback font
 */

/**
 * Name of the font to load.
 */
define("FONTNAME", "fallback");

/**
 * Number of documents to generate.
 */
define("N_DOCS", 100);

/**
 * This is where the data files are. Adjust as necessary.
 */
define("SEARCH_PATH", dirname(dirname(dirname(__FILE__)))."/input");

/**
 * Page width
 */
define("WIDTH", 595);

/**
 * Page height
 */
define("HEIGHT", 842);

/**
 * Method that creates N_DOCS documents in memory.
 * 
 * @param keepfont
 *            if true, retain font across all generated documents, otherwise
 *            load it again for each document
 */
function make_test_docs($keepfont) {
    try {
	$font = -1;
	
	$p = new PDFlib();

	$p->set_parameter("SearchPath", SEARCH_PATH);

	/*
	 * Load a font
	 */
	if ($keepfont) {
	    /*
	     * keepfont=true is default here, so it does not need to be
	     * specified explicitly.
	     */
	    $font = $p->load_font(FONTNAME, "unicode", "keepfont=true");
	    if ($font == -1)
		throw new Exception("Error: " . $p->get_apiname() . ": "
			. $p->get_errmsg());
	}

	for ($i = 0; $i < N_DOCS; $i += 1) {
	    /*
	     * Create a simple document that makes use of the font. The
	     * document is generated in memory and immediately discarded.
	     */
	    if ($p->begin_document("", "") == -1)
		throw new Exception("Error: " . $p->get_apiname() . ": "
			. $p->get_errmsg());

	    $p->set_info("Creator", "PDFlib Cookbook");
	    $p->set_info("Title", "Dummy test document");

	    $p->begin_page_ext(WIDTH, HEIGHT, "");

	    if (!$keepfont) {
		/*
		 * keepfont=false is default here.
		 */
		$font = $p->load_font(FONTNAME, "unicode", "keepfont=false");

		if ($font == -1)
		    throw new Exception("Error: " . $p->get_apiname() . ": "
			    . $p->get_errmsg());
	    }

	    $p->setfont($font, 24);

	    $p->set_text_pos(50, 700);
	    $p->show("Hello world!");

	    $p->end_page_ext("");

	    $p->end_document("");
	}
    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;

}

$outfile = "";
$title = "Retain Fonts";

/*
 * Time creation of test documents with and without retaining of font.
 */
$start_date1 = microtime(true);
make_test_docs(false);
$time_diff1 = sprintf("%.2f", microtime(true) - $start_date1);

$start_date2 = microtime(true);
make_test_docs(true);
$time_diff2 = sprintf("%.2f", microtime(true) - $start_date2);

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", SEARCH_PATH);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == -1)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $p->begin_page_ext(595, 842, "");

    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == -1)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->setfont($font, 18);

    $p->set_text_pos(50, 700);
    $p->show("Performance benefit of retaining a font across documents:");
    
    $p->setfont($font, 16);
    $p->continue_text("");
    $p->continue_text("Time spent for creating " . N_DOCS 
		. " documents without retaining font:");
    $p->continue_text($time_diff1 . " seconds");
    
    $p->continue_text("");
    $p->continue_text("Time spent for creating " . N_DOCS 
		. " documents while retaining font:");
    $p->continue_text($time_diff2 . " seconds");
    
    $p->continue_text("");
    $p->continue_text("Note: Actual results will vary depending on various factors,");
    $p->continue_text("including font, complexity of the document and platform.");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=opentype_features_for_cjk.pdf");
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
