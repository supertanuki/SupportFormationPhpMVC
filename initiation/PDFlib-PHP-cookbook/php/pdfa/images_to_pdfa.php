<?php
/* $Id: images_to_pdfa.php,v 1.3 2012/05/03 14:00:37 stm Exp $
 * Images to PDF/A:
 * Convert grayscale, CMYK or RGB image files in TIFF or JPEG formats to 
 * PDF/A-1b, taking care of color space issues.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image files
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Images to PDF/A";

$imagefileRGB = "nesrin.jpg";
$imagefileCMYK = "kraxi_header.tif";
$imagefileGray = "nesrin_gray.jpg";


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    /* Output all contents conforming to PDF/A-1b */
    if ($p->begin_document($outfile, "pdfa=PDF/A-1b:2005") == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title  );

    /* Use sRGB as output intent since it allows the color spaces ICC-based,
     * grayscale, and RGB. CMYK images must be tagged with a suitable CMYK
     * ICC profile.
     */
    $p->load_iccprofile("sRGB", "usage=outputintent");
    
    /* Load an ICC profile for CMYK images */
    $icc = $p->load_iccprofile("ISOcoated.icc", "");
    if ($icc == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(595, 842, "");

    /* We can use an RGB image without any further options since we
     * supplied an RGB output intent profile.
     */
    $image = $p->load_image("auto", $imagefileRGB, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, 100, 550, "scale=0.5");
    $p->close_image($image);
    
    /* Similarly, we can use a grayscale image without any further options
     * since we supplied an RGB output intent profile.
     */
    $image = $p->load_image("auto", $imagefileGray, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, 100, 250, "scale=0.5");
    $p->close_image($image);
    
    /* For CMYK images we explicitly assign a CMYK ICC profile */
    $image = $p->load_image("auto", $imagefileCMYK, "iccprofile=" . $icc);
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, 100, 80, "");
    $p->close_image($image);

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=images_to_pdfa.pdf");
    print $buf;


    } catch (PDFlibException $e){
	die("PDFlib exception occurred:\n" . 
	    "[" . $e->get_errnum() . "] " . $e->get_apiname() .
	    ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
	die($e->getMessage());
    }

$p = 0;
?>
