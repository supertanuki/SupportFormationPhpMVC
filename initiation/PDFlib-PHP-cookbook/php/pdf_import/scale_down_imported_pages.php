<?php
/* $Id: scale_down_imported_pages.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Scale down imported pages:
 * Place A4 pages from an imported PDF as A5 pages in the output document
 * 
 * Import the pages of an A4 document and output them unchanged. Then, place
 * them in the output document while scaling them down to A5. 
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Scale down Imported Pages";

$pdffile = "pCOS-datasheet.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Open the input PDF having A4 page size */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $endpage = $p->pcos_get_number($indoc, "length:pages");

    /* Loop over all pages of the input document:
     * Place the imported page without any scaling
     */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Start an A4 output page (210mm x 297mm) */
	$p->begin_page_ext(595, 842, "");
	
	/* Place the imported page without any scaling */
	$p->fit_pdi_page($page, 0, 0, "");
	
	$p->end_page_ext("");
	
	$p->close_pdi_page($page);
    }
    
    /* Loop over all pages of the input document:
     * Place the imported page while scaling it down to A5
     */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Start an A5 output page (148mm x 210mm) */
	$p->begin_page_ext(421, 595, "");

	/* Place the imported page while scaling it down to A5 */
	$p->fit_pdi_page($page, 0, 0, "boxsize={421 595} fitmethod=meet");
	
	$p->end_page_ext("");
	
	$p->close_pdi_page($page);
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=scale_down_imported_pages.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in scale_down_imported_pages sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
