<?php
/* $Id: clone_page_boxes.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * 
 * Clone page boxes:
 * Import a PDF page, and clone all of its ArtBox, TrimBox, BleedBox, CropBox,
 * and MediaBox entries.
 *
 * Note: You can visualize the page boxes in Acrobat 8 as follows:
 * Choose "Edit, Preferences, General, Page Display, Page Content and
 * Information" and select "Show art, trim & bleed boxes".
 * This setting will create colorized rectangles which indicate the size of the
 * art, trim, and bleed boxes.
 * 
 * Required software: PDFlib+PDI/PPS 8
 * Required data: PDF document
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Clone Page Boxes";

$pdffile = "pageboxes.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");

    /* Loop over all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++) {
	/*
	 * "cloneboxes" must be used with open_pdi_page() to read all
	 * relevant box values.
	 */
	$page = $p->open_pdi_page($indoc, $pageno, "cloneboxes");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());

	/*
	 * Start and place the page. "cloneboxes" must be used again as
	 * an option to fit_pdi_page() to apply the box values to the
	 * current page.
	 */
	$p->begin_page_ext(0, 0, "");

	$p->fit_pdi_page($page, 0, 0, "cloneboxes");

	$p->end_page_ext("");

	$p->close_pdi_page($page);
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=clone_page_boxes.pdf");
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
