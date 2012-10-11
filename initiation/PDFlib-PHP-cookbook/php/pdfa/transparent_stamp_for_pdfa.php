<?php
/* $Id: transparent_stamp_for_pdfa.php,v 1.7 2012/05/03 14:00:37 stm Exp $
* Transparent stamp for PDF/A:
* Apply a transparent stamp to an existing PDF/A document while maintaining
* PDF/A conformance.
* 
* Import all pages from an existing PDF/A document and place a stamp on the
* page. The stamp is filled with a pattern color, where the pattern consists of
* a bitmap The bitmap is used as a mask to create a certain percentage of
* transparency. This is required since real transparency is not allowed in
* PDF/A. Transparency by pattern color is PDF/A compatible, so we use it to
* apply a transparent stamp on a PDF/A document while maintaining PDF/A
* conformance.
* 
* Note: On Windows systems the "Arial-Bold" font is installed by default.
* On other systems, you may have to load another font if the "Arial-Bold" font
* is not available.
*
* Required software: PDFlib+PDI/PPS 7
* Required data: PDF document
*/

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Transparent Stamp for PDF/A";

$pdffile = "PLOP-datasheet-PDFA-1b.pdf";

/* data set for our halftoning bitmap */
$data = array(array( 
    0x00, 0x00,  /* 30% */
    0x00, 0x00,
    0x00, 0x00,
    0x03, 0xC0,
    0x07, 0xE0,
    0x0F, 0xF0,
    0x1F, 0xF8,
    0x1F, 0xF8,

    0x1F, 0xF8,
    0x1F, 0xF8,
    0x0F, 0xF0,
    0x07, 0xE0,
    0x03, 0xC0,
    0x00, 0x00,
    0x00, 0x00,
    0x00, 0x00,
),array(
    0x00, 0x00, /* 20% */
    0x00, 0x00,
    0x00, 0x00,
    0x00, 0x00,
    0x03, 0xC0,
    0x07, 0xE0,
    0x0F, 0xF0,
    0x0F, 0xF0,

    0x0F, 0xF0,
    0x0F, 0xF0,
    0x07, 0xE0,
    0x03, 0xC0,
    0x00, 0x00,
    0x00, 0x00,
    0x00, 0x00,
    0x00, 0x00,
));

$ht = 1; /* index in halftone array */


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "pdfa=PDF/A-1b:2005") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* -----------------------------------------------------------------
     * Open the input PDF.
     * This must be done before creating the pattern because the output
     * intent must be set before defining the pattern.
     * -----------------------------------------------------------------
     */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");

    /* Since the input document contains its own output intent retrieve
     * the output intent from the input document and copy it to the output
     * document.
     */
    $res = $p->pcos_get_string($indoc, "type:/Root/OutputIntents");
    if ($res == "array") {
	$ret = $p->process_pdi($indoc, -1, "action=copyoutputintent");
	if ($ret == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* -------------------------------------------------------------------
     * Define a bitmap pattern based on an image mask.
     * We scale down the image to provide a smoother appearance on screen.
     * -------------------------------------------------------------------
     */
    $bitmap ="";

    for ($j=0; $j < count($data[$ht]); $j++) {
	$bitmap .= sprintf("%c",$data[$ht][$j]);
    }

	// $bitmap[$j] = $data[$ht][$j];
    $p->create_pvf("/pvf/image/bitmap", $bitmap, "");

    $image = $p->load_image("raw", "/pvf/image/bitmap",
	"bpc=1 components=1 height=16 width=16 invert mask");

    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $w = 16/32;
    $pattern = $p->begin_pattern($w, $w, $w, $w, 1);

    $p->fit_image($image, 0, 0, "scale=" .  1/32);

    $p->end_pattern();

    $p->close_image($image);

    /* Loop over all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page on the output page, and adjust the page
	 * size.
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");

	/*
         * Load the font for the stamp.
         */
	$font = $p->load_font("DejaVuSerif", "unicode", "embedding");

	if ($font == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Place the stamp, filled with the pattern color */
	$p->setcolor("fill", "pattern", $pattern, 0, 0, 0);

	$p->fit_textline("PUBLISHED", 20, 20,
	    "font=" . $font . " fontsize=1 boxsize={550 800} stamp=ll2ur");

	$p->close_pdi_page($page);

	$p->end_page_ext("");
    }

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=transparent_stamp_for_pdfa.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
	"[" . $e->get_errnum() . "] " . $e->get_apiname() .
	": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;
?>
