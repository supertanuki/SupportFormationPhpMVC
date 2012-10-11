<?php
/* $Id: embed_xmp.php,v 1.4 2012/05/03 14:00:41 stm Exp $
 * Embed XMP:
 * Embed custom XMP metadata in a document
 * 
 * Use the "metadata" option of begin_document() to read XMP metadata from an
 * XMP file and embed it in the output document.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: XMP file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Embed XMP";

$xmpfile = "simple.xmp";

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Start the document with an XMP file supplied, containing a few
     * commonly used XMP properties. The XMP metadata will be read from the
     * file and embedded in the document. PDFlib will merge several
     * internally generated entries into the user-supplied XMP, e.g.
     * xmp:CreateDate. 
     */
    if ($p->begin_document($outfile, 
	"metadata={filename=" . $xmpfile . "}") == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $p->setfont($font, 12);
    
    $p->fit_textline("XMP metadata is read from an XMP file and embedded " .
	"in the document.", 20, 600, "");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=embed_xmp.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e->getMessage());
}

$p = 0;
?>
