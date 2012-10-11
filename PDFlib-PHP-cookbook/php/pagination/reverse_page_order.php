<?php
/* $Id: reverse_page_order.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Reverse page order:
 * Create pages in reverse page order
 *
 * Create page no. 5 first, then page no. 4, etc.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Reverse Page Order";

$lastpage = 5; $step = 1;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Loop over all subsequent pages in reverse order */
    for ($pageno = $lastpage, $step = 1; $pageno > 0; $pageno--, $step++)
    {
	if ($pageno == $lastpage)
	    /* Create the first page */
	    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	else
	    /* Insert subsequent pages before what is (currently) the
	     * first page */
	    $p->begin_page_ext(0, 0,
		"width=a4.width height=a4.height pagenumber=1");

	/* Place a text line indicating the page number */
	$p->setfont($font, 24);
	$p->fit_textline("Page " . $pageno . " created in step " . $step,
	    100, 500, "");

	$p->end_page_ext("");
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=reverse_page_order.pdf");
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
