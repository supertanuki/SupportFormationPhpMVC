<?php
/* $Id: import_pdfa.php,v 1.4 2012/05/03 14:00:37 stm Exp $
* Import PDF/A:
* Import an existing PDF/A-1b document and output it as PDF/A-1b
*
* Required software: PDFlib+PDI/PPS 7
* Required data: PDF document
*/
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Import PDF/A";

$pdffile = "PLOP-datasheet-PDFA-1b.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "pdfa=PDF/A-1b:2005") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");
    
    /* Since the input document contains its own output intent retrieve
     * the output intent from the input document and copy it to the output
     * document
     */
    $res = $p->pcos_get_string($indoc, "type:/Root/OutputIntents");
    if ($res == "array") {
	$ret = $p->process_pdi($indoc, -1, "action=copyoutputintent");
	if ($ret == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Loop over all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page without performing any changes on the
	 * output page
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");
	
	$p->close_pdi_page($page);
	$p->end_page_ext("");
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=import_pdfa.pdf");
    print $buf;

} catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n" .
	"[" . $e->get_errnum() . "] " . $e->get_apiname() .
	": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
} 
$p = 0;
?>
