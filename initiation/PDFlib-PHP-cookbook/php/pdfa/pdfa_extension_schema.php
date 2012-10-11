<?php
/* $Id: pdfa_extension_schema.php,v 1.3 2012/05/03 14:00:37 stm Exp $
 * PDF/A extension schema:
 * Demonstrate the use of an XMP extension schema as defined in PDF/A-1
 * 
 * Create a PDF/A document and use the "metadata" option of begin_document() to
 * supply an XMP file containing an XMP extension schema.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7.0.3
 * Required data: XMP file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "PDF/A Extension Schema";


/* Required minimum PDFlib version */
$requiredversion = 703;
$requiredvstr = "7.0.3";

$xmpfile = "machine_extension_schema_1.xmp";

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0); 
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);
	   
    if ($major*100 + $minor*10 + $revision < $requiredversion) 
	throw new Exception("Error: PDFlib " . $requiredvstr . 
	    " or above is required");

    /* Start a PDF/A document with an XMP file supplied, containing an XMP
     * extension schema. The XMP metadata will be read from the file and
     * embedded in the document. PDFlib will merge several internally
     * generated entries into the user-supplied XMP, e.g. xmp:CreateDate. 
     */
    if ($p->begin_document($outfile, 
	"pdfa=PDF/A-1b:2005 metadata={filename=" . $xmpfile . "}") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Font embedding is required for PDF/A */
    $font = $p->load_font("LuciduxSans-Oblique", "unicode", "embedding");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 12);

    $p->fit_textline("An XMP extension schema is read from an XMP file and " .
	     "the XMP metadata is embedded in the document.", 50, 400, "");

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=pdfa_extension_schema.pdf");
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
