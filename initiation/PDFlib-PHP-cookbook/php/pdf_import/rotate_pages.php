<?php
/* $Id: rotate_pages.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Rotate Pages:
 * Rotate the pages of an existing PDF document
 *
 * Import a PDF page and place it in the output document with a different
 * orientation.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Rotate Pages";

$pdffile = "PDFlib-real-world.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

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

	/* Place the imported page on the output page. Adjust the page size
	 * automatically to the size of the imported page. Orientate the
	 * page to the west; similarly you can orientate it to the east or
	 * south, if required.
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage orientate=west");

	$p->close_pdi_page($page);

	$p->end_page_ext("");
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=rotate_pages.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
