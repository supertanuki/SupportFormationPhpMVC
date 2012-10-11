<?php
/* $Id: text_to_pdfa.php,v 1.3 2012/05/03 14:00:37 stm Exp $
 * Text to PDF/A:
 * Output text conforming to PDF/A-1b, taking care of color space and font
 * issues
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Text to PDF/A";

$x = 30; $y = 800;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Output all contents conforming to PDF/A-1b */
    if ($p->begin_document($outfile, "pdfa=PDF/A-1b:2005") == 0)
    throw new Exception("Error: " . $p->get_errmsg());
    
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Use sRGB as output intent since it allows the color spaces CIELab,
     * ICC-based, Grayscale, and RGB
     */
    $p->load_iccprofile("sRGB", "usage=outputintent");
    
    $p->begin_page_ext(595, 842, "");

    /* Load the font "LuciduxSans-Oblique" with embedding, since font
     * embedding is required for PDF/A
     */
    $font = $p->load_font("LuciduxSans-Oblique", "unicode", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->setfont($font, 20);

    /* You might want to embed a PDF core font, e.g. "Helvetica". For a PDF
     * core font, the font metrics are already built into PDFlib, but
     * the font outline file has to be explicitly configured with the
     * "FontOutline" ressource category, e.g.
     * 
     * $p->set_parameter("FontOutline", "Helvetica=HV______.pfb");
     * 
     */
    
    /* We can use RGB text without any further color related options since
     * we already supplied an output intent profile.
     */
    $p->setcolor("fill", "rgb", 0.7, 0.3, 0.3, 0);
  
    $p->fit_textline("Text with an RGB color conforming to PDF/A-1b:2005",
	$x, $y-=100, "");
  
    /* Similarly, we can use Grayscale text without any further options
     * since we already supplied an output intent profile.
     */
    $p->setcolor("fill", "gray", 0.5, 0, 0, 0);

    $p->fit_textline("Text with a Grayscale color conforming to " .
	"PDF/A-1b:2005", $x, $y-=100, "");
   
    /* For CMYK text we could use an ICC profile explicitly assigned; the 
     * code would be as follows:
     *
     * icc = $p->load_iccprofile("ISOcoated.icc", "usage=iccbased");
     * $p->set_value("setcolor:iccprofilecmyk", icc);
     * $p->setcolor("fill", "iccbasedcmyk", 0.6, 0.6, 0, 0);
     * $p->fit_textline("Text with a CMYK color conforming to PDF/A-1b:2005",
     *     x, y-=100, "");
     * 
     */

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=text_to_pdfa.pdf");
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
