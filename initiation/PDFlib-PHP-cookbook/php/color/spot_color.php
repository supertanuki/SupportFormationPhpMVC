<?php
/* $Id: spot_color.php,v 1.5 2012/05/03 14:00:39 stm Exp $
 * Spot color:
 * Define and use several spot colors
 * 
 * Define and use a PANTONE spot color.
 * Define and use a HKS spot color.
 * Define and use a custom spot color based on alternate CMYK values.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Spot Color";

$font;
$spot;
$y = 500; 
$x = 30;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "bytes");

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title . ' $Revision: 1.5 $');
    
    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start the page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
    
    
    /* ---- Define and use a PANTONE spot color ---- */
    
    /* Define the spot color "PANTONE 281 U" from the builtin color
     * library PANTONE */
    $spot = $p->makespotcolor("PANTONE 281 U");
    
    /* Set the spot color "PANTONE 281 U" with a tint value of 1 (=100%)
     * and output some text */
    $p->setcolor("fill", "spot", $spot, 1.0, 0.0, 0.0);
   
    $p->fit_textline("PANTONE 281 U spot color with a tint value of 100%",
	$x, $y -= 30, "font=" . $font . " fontsize=16");
    
    /* Set the spot color "PANTONE 281 U" with a tint value of 0.5 (=50%)
     * and output some text */
    $p->setcolor("fill", "spot", $spot, 0.5, 0.0, 0.0);
    
    $p->fit_textline("PANTONE 281 U spot color with a tint value of 50%",
	$x, $y -= 30, "font=" . $font . " fontsize=16");
    
    
    /* ---- Define and use a HKS spot color ---- */
    
    /* Define the spot color "HKS 39 E" from the builtin color library
     * HKS */
    $spot = $p->makespotcolor("HKS 39 E");
    
    /* Set the spot color "HKS 39 E" with a tint value of 1 (=100%)
     * and output some text */
    $p->setcolor("fill", "spot", $spot, 1.0, 0, 0);
    
    $p->fit_textline("HKS 39 E spot color with a tint value of 100%",
	$x, $y -= 50, "font=" . $font . " fontsize=16");
    
    /* Set the spot color "HKS 38 E" with a tint value of 0.7 (=70%)
     * and output some text */
    $p->setcolor("fill", "spot", $spot, 0.7, 0, 0);
    
    $p->fit_textline("HKS 39 E spot color with a tint value of 70%",
	$x, $y -= 30, "font=" . $font . " fontsize=16");
    
  
    /* ---- Define and use a custom spot color based on a CMYK ----
     * ---- alternate color                                    ---- */
     
    /* Set a CMYK color used as alternate CMYK color for the spot color */ 
    $p->setcolor("fill", "cmyk", 0, 0.2, 0.9, 0);
    
    /* Define a custom spot color called "CompanyLogo" with the alternate
     * CMYK values set above
     */
    $spot = $p->makespotcolor("CompanyLogo");
    
    /* Now set the spot color "CompanyLogo" with a tint value of 1 and
     * output some text */
    $p->setcolor("fill", "spot", $spot, 1, 0, 0);
    $p->fit_textline("CompanyLogo custom spot color with a tint value of " .
	"100%", $x, $y -= 50, "font=" . $font . " fontsize=16");

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=spot_color.pdf");
    print $buf;

    } catch (PDFlibException $e) {
	die("PDFlib exception occurred:\n".
	    "[" . $e->get_errnum() . "] " . $e->get_apiname() .
	    ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
	die($e->getMessage());
    } 
    $p = 0;
?>
