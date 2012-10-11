<?php
/* $Id: pdfimpose.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * PDF impose:
 * Import all pages from one more existing PDFs, and place c x r pages on each
 * sheet of the output PDF (imposition).
 * 
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF documents
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "PDF Impose";

$pdffiles = array(
    "PDFlib-real-world.pdf",
    "PDFlib-datasheet.pdf",
    "TET-datasheet.pdf",
    "PLOP-datasheet.pdf",
    "pCOS-datasheet.pdf"
);
$c = 0;
$r = 0;
$scale = 1;          // scaling factor of a page
$rowheight = 0;      // row height for the page to be placed
$colwidth = 0;       // column width for the page to be placed
$sheetwidth = 595;   // width of the sheet
$sheetheight = 842;  // height of the sheet
$cols = 3; $rows = 4;    // cols x rows pages will be placed on one sheet

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* ---------------------------------------------------------------------
     * Define the sheet width and height, and the number of rows and columns
     * and calculate the scaling factor and cell dimensions for the 
     * multi-page imposition
     * ---------------------------------------------------------------------
     */
    if ($rows > $cols)
	$scale = 1.0 / $rows;
    else
	$scale = 1.0 / $cols;

    $rowheight = $sheetheight * $scale;
    $colwidth = $sheetwidth * $scale;

    $pageopen = false; // is a page open that must still be closed?
    
    /* Loop over all input documents */
    for ($i=0; $i < count($pdffiles); $i++) {

	/* Open the input PDF */
	$indoc = $p->open_pdi_document($pdffiles[$i], "");
	if ($indoc == 0){
	    print("Error: " . $p->get_errmsg());
	    continue;
	}

	$endpage = $p->pcos_get_number($indoc, "length:pages");
	
	/* Loop over all pages of the input document */
	for ($pageno = 1; $pageno <= $endpage; $pageno++) {
	    $page = $p->open_pdi_page($indoc, $pageno, "");

	    if ($page == 0) {
		print("Error: " . $p->get_errmsg());
		continue;
	    }
	    
	    /* Start a new page */
	    if ($r == 0 && $c == 0) {
		$p->begin_page_ext($sheetwidth, $sheetheight, "");
		$pageopen = true;
	    }
	    
	    /* The save/restore pair is required to get the clipping
	     * right, and helps PostScript printing manage its memory
	     * efficiently.
	     */
	    $p->save();
	    $p->rect($c * $colwidth, $sheetheight - ($r + 1) * $rowheight,
		$colwidth, $rowheight);
	    $p->clip();

	    $optlist = "boxsize {" . $colwidth . " " . $rowheight . "}" .
		" position 0 fitmethod meet";
		
	    $p->fit_pdi_page($page, $c * $colwidth, 
		$sheetheight - ($r + 1) * $rowheight, $optlist);

	    $p->close_pdi_page($page);
	    
	    /* Draw a frame around the mini page */ 
	    $p->setlinewidth($scale);
	    $p->rect($c * $colwidth, $sheetheight - ($r + 1) * $rowheight,
		$colwidth, $rowheight);
	    $p->stroke();
	   
	    $p->restore();

	    $c++;
	    if ($c == $cols) {
		$c = 0;
		$r++;
	    }
	    if ($r == $rows) {
		$r = 0;
		$p->end_page_ext("");
		$pageopen = false;
	    }
	}
	$p->close_pdi_document($indoc);
    }
    
    if ($pageopen) {
	$p->end_page_ext("");
    }
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=pdfimpose.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in pdfimpose sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
