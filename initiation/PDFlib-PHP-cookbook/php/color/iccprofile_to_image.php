<?php
/* $Id: iccprofile_to_image.php,v 1.4 2012/05/03 14:00:39 stm Exp $
* ICC profile to image:
* Assign an ICC profile to an image
* 
* Apply the "sRGB" ICC profile to an imported RGB image.
* Apply the "ISOcoated" ICC profile to an imported CMYK image.
*
* Required software: PDFlib/PDFlib+PDI/PPS 7
* Required data: RGB image file, CMYK image file, ICC profile
*/

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "ICC profile to image";

$x = 100; 
$y = 0;

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	    throw new Exception ("Error: " . $p->get_errmsg());
    
    /* Start page 1 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    
    /* --- Load and output an RGB image without any ICC profile --- *
     * --- assigned to it                                       --- *
     */
    $imagefile = "nesrin.jpg";
    $y = 450;
    
    /* Load the RGB image. Just for our sample, ignore any ICC profile
     * which might be embedded into the image.
     */
    $image = $p->load_image("auto", $imagefile, "honoriccprofile=false");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
  
    /* Fit the image proportionally into a box */
    $p->fit_image($image, $x, $y, "boxsize={400 300} fitmethod=meet");
    $p->fit_textline("RGB image without any ICC profile assigned", $x, $y-= 30, 
	"font=" . $font . " fontsize=14");
    
    $p->close_image($image);
    
    
    /* --- Load and output an RGB image with the "sRGB" ICC profile --- *
     * --- assigned to it                                           --- *
     */
    
    /* Load the sRGB profile. sRGB is guaranteed to be always available */
    $icchandle = $p->load_iccprofile("sRGB", "usage=iccbased");
    if ($icchandle == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the RGB image, ignore any ICC profile which might be embedded
     * into the image, and assign the sRGB profile to it
     */
    $image = $p->load_image("auto", $imagefile, "iccprofile=" . $icchandle .
	" honoriccprofile=false");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
  
    /* Fit the image proportionally into a box */
    $p->fit_image($image, $x, $y-=350, "boxsize={400 300} fitmethod=meet");
    $p->fit_textline("RGB image with the \"sRGB\" ICC profile assigned",
	$x, $y-= 30, "font=" . $font . " fontsize=14");
    
    $p->close_image($image);
    
    $p->end_page_ext("");
    
    /* Start page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    

    /* --- Load and output a CMYK image without any ICC profile --- *
     * --- assigned to it                                       --- *
     */
    $imagefile = "nesrin_cmyk.jpg";
    $y = 450;
    
    /* Load the CMYK image. Just for our sample, ignore any ICC profile
     * which might be embedded into the image.
     */
    $image = $p->load_image("auto", $imagefile, "honoriccprofile=false");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
  
    /* Fit the image proportionally into a box */
    $p->fit_image($image, $x, $y, "boxsize={400 300} fitmethod=meet");
    $p->fit_textline("CMYK image without any ICC profile assigned", $x, $y-= 30, 
	"font=" . $font . " fontsize=14");
    
    $p->close_image($image);
    
    /* --- Load and output a CMYK image with the "ISOcoated" --- *
     * --- ICC profile assigned to it                        --- *
     */
    
    /* Load the ISOcoated profile */
    $icchandle = $p->load_iccprofile("ISOcoated", "usage=iccbased");
    if ($icchandle == 0)
	 throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the CMYK image, ignore any ICC profile which might be embedded
     * into the image, and assign the sRGB profile to it
     */
    $image = $p->load_image("auto", $imagefile, "iccprofile=" . $icchandle .
	" honoriccprofile=false");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
  
    /* Fit the image proportionally into a box */
    $p->fit_image($image, $x, $y-=350, "boxsize={400 300} fitmethod=meet");
    $p->fit_textline("CMYK image with the \"ISOcoated\" ICC profile assigned",
	$x, $y-= 30, "font=" . $font . " fontsize=14");
    
    $p->close_image($image);
    
    $p->end_page_ext("");
     
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=iccprofile_to_image.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;


?>
