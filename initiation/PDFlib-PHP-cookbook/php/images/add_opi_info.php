<?php
/* $Id: add_opi_info.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Add OPI information:
 * Attach OPI information to an image
 * 
 * Use the "OPI-1.3" or "OPI-2.0" options of load_image() to attach OPI
 * information according to OPI 1.3 or OPI 2.0 to an image, respectively.
 * 
 * From the PDF Reference:
 * The Open Prepress Interface (OPI) is a mechanism, originally developed by
 * Aldus Corporation, for creating low-resolution placeholders, or proxies, for
 * such high-resolution images. The proxy typically consists of a downsampled
 * version of the full-resolution image, to be used for screen display and
 * proofing. Before the document is printed, it passes through a filter known as
 * an OPI server, which replaces the proxies with the original full-resolution
 * images.
 * 
 * OPI information in PostScript files is generally referred to as 
 * "OPI comments". Note that replacing images with OPI always requires suitable
 * OPI processing software. PDF display with Acrobat will not be affected by
 * OPI.
 *
 * For more details refer to the OPI 2.0 specification, which is available at
 * http://partners.adobe.com/public/developer/en/ps/5660_OPI_2_0.pdf.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
    /* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Add OPI Information";

$imagefile = "nesrin.jpg";
$x = 20; $y = 500;
$llx = 20; $urx = 430; $lly = 20; $ury = 460;
$tf = 0;

/* The "normalizefilename" option is required for some OPI servers. If it is
 * false (default), the supplied image file name will be used without any
 * modification; if it is true the image file name will be normalized as
 * mandated by the PDF reference.
 * 
 * The examples below use only the required OPI options. For more OPI 1.3
 * and 2.0 options refer to the PDFlib API reference. The set of useful 
 * options depends on the type of OPI server used.
 */
$optlist13 =
    "OPI-1.3={normalizefilename                                  " .
	     "ALDImageFilename={nesrin_hires.jpg}                " .
	     "ALDImageDimensions={745 493}                       " .
	     "ALDImageCropRect={10 10 550 390}                   " .
	     "ALDImagePosition={10 10  10 540  390 540  390 10}  " .
	     "ALDImageColor={0 0 0 0.5 CustomGray}               " .
	     "ALDImageColorType=Separation                      }";

$optlist20 =
    "OPI-2.0={normalizefilename                                  " .
	     "ImageFilename={nesrin_hires.jpg}                   " .
	     "ImageCropRect={10 10 550 390}                      " .
	     "ImageDimensions={745 493}                         }";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Load the font */
    $font = $p->load_font("Courier", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
 
    /* -------------------------------------------------------------
     * Place the image in its original size with OPI 1.3 information 
     * attached
     * -------------------------------------------------------------
     */
    /* Load the image with the option list for OPI 1.3 information */
    $image = $p->load_image("auto", $imagefile, $optlist13);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page 1 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    $p->fit_image($image, $x, $y, "scale=0.5");
    
    $tf = $p->add_textflow($tf, "", "font=" . $font . " fontsize=12");
    
    $tf = $p->add_textflow($tf,
	"Option list for load_image() for OPI 1.3 information:\n\n", "");
    
    $tf = $p->add_textflow($tf, $optlist13, "");
    
    $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    
    $p->end_page_ext("");
    
    $p->close_image($image);  
    
    $tf = 0;
	    
    
    /* -------------------------------------------------------------
     * Place the image in its original size with OPI 2.0 information 
     * attached
     * -------------------------------------------------------------
     */
    /* Load the image with the option list for OPI 2.0 information */
    $image = $p->load_image("auto", $imagefile, $optlist20);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
		  
    $p->fit_image($image, $x, $y, "scale=0.5");
    
    $tf = $p->add_textflow($tf, "", "font=" . $font . " fontsize=12");
    
    $tf = $p->add_textflow($tf,
	"Option list for load_image() for OPI 2.0 information:\n\n", "");
    
    $tf = $p->add_textflow($tf, $optlist20, "");
    
    $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    
    $p->end_page_ext("");
	    
    $p->close_image($image);

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=add_opi_info.pdf");
    print $buf;
}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in add_opi_info sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;
?>

