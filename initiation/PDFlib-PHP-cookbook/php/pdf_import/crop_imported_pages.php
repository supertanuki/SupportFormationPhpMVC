<?php
/* $Id: crop_imported_pages.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Crop imported pages:
 * Crop pages of an existing PDF document
 *
 * Import a PDF page and reduce the page by some fixed amount before placing
 * it in the output document. The retrieve the dimensions of the imported page
 * and place it on a page size of 1/4 of the original size.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Crop Imported Pages";

$pdffile = "kraxi_business_cards.pdf";

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

	/* Place the imported page without performing
	 * any changes on the output page
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");

	$p->end_page_ext("");

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page, adjust the page size
	 * to the size of the imported page, and crop the page by 51.
	 */
	$p->fit_pdi_page($page, -51, -51, "adjustpage");
	
	$p->end_page_ext("");
	
	/* Retrieve the dimensions of the imported page and adjust the page
	 * size by cropping it to a certain percentage of the imported page
	 * size.
	 */
	
	/* Retrieve the width and height of the current page (note that
	 * indices for the pages pseudo object start at 0):
	 */
	$pagewidth = 
	    $p->pcos_get_number($indoc, "pages[" . ($pageno - 1) . "]/width");
	$pageheight = 
	    $p->pcos_get_number($indoc, "pages[" . ($pageno - 1) . "]/height");
	
	/* Set the new page size to 1/4 of the imported page size */
	$p->begin_page_ext($pagewidth/4, $pageheight/4, "");
	
	/* Place the imported page and scale it to 1/4 of its page size */
	$p->fit_pdi_page($page, 0, 0, "boxsize={" . $pagewidth/4 . " " .
	    $pageheight/4 . "} fitmethod=meet");
	
	$p->end_page_ext("");
	
	$p->close_pdi_page($page);
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=crop_imported_pages.pdf");
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
