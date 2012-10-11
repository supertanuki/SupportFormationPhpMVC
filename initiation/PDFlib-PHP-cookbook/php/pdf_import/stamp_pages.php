<?php
/* $Id: stamp_pages.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Stamp PDF pages:
 * Import all pages from an existing PDF document and place a stamp somewhere
 * on the page
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Stamp Pages";

$pdffile = "PDFlib-real-world.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");

    /* Loop over all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page on the output page, and
	 * adjust the page size
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");

	/* For PDFlib Lite: change "unicode" to "winansi" */
	$font = $p->load_font("Helvetica-Bold", "unicode", "");

	if ($font == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Fit the text line like a stamp with green outlines in the
	 * specified box. The stamp will be placed diagonally from the 
	 * upper left to the lower right.
	 */
	$p->fit_textline("PRELIMINARY", 50, 50, "font=" . $font .
	   " fontsize=1 textrendering=1 boxsize={500 700} stamp=ul2lr" .
	   " strokecolor={rgb 1 0 0} strokewidth=2");

	$p->close_pdi_page($page);

	$p->end_page_ext("");
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=stamp_pages.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in stamp_pages sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
