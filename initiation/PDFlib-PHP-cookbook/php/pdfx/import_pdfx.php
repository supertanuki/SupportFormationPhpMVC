<?php
/* $Id: import_pdfx.php,v 1.5 2012/05/03 14:00:41 stm Exp $
 * Import PDF/X:
 * Import an existing PDF/X-3 document and output it as PDF/X-3
 * 
 * Only the pages from the imported documents are merged; interactive elements
 * will be ignored.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Import PDF/X";

$pdffile = "PLOP-datasheet-PDFX-3-2002.pdf";
try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    
    /* Set the desired PDF/X conformance level */
    if ($p->begin_document($outfile, "pdfx=PDF/X-3:2002") == 0)
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
    else {
	throw new Exception("Error: " .
	    "Invalid PDF/X (output intent is not an array)"); 
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
    header("Content-Disposition: inline; filename=import_pdfx.pdf");
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
