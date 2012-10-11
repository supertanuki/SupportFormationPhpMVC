<?php
/* $Id: import_in_reverse_order.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Import in reverse order:
 * Read the pages of an input PDF document and output them in reverse order
 *  
 * Open the pages of the input document in reverse order and place them in
 * the output PDF.
 *  
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Import in Reverse Order";

$pdffile = "PDFlib-datasheet.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Retrieve the overall number of pages for the input document */
    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");

    /* Loop over all pages of the input document in reverse order */
    for ($pageno = $endpage; $pageno > 0; $pageno--)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page without performing
	 * any changes on the output page
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");
	
	$p->end_page_ext("");

	$p->close_pdi_page($page);
    }
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=import_in_reverse_order");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
