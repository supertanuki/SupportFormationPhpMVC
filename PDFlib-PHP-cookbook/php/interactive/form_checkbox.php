<?php
/* $Id: form_checkbox.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form checkbox:
 * Create four form fields of type "checkbox".
 * 
 * Define four checkboxes for choosing some extras for the paper plane to order.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Checkbox";


$width=15; $height=15; $llx = 50; $lly = 600;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    $p->setfont($font, 12);
    
    /* Output a title text for the checkboxes */
    
    $p->fit_textline("Choose paper plane extras:", $llx, $lly, "");
    $lly-=40;
    
    /* Create several form fields of type "checkbox" which appear as
     * blue crosses (buttonstyle=cross fillcolor={rgb 0.25 0 0.95})
     * on a light blue background (backgroundcolor={rgb 0.95 0.95 1})
     * with a black border (bordercolor={gray 0}). 
     * Provide a toolti$p->
     * Activate the first checkbox (currentvalue={On}).
     */
    $optlist = "buttonstyle=cross bordercolor={gray 0} " .
	"backgroundcolor={rgb 0.95 0.95 1} fillcolor={rgb 0.25 0 0.95}";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "paper", "checkbox", 
	$optlist . " currentvalue={On}");
    
    /* Output the checkbox label */
    $p->fit_textline("Glossy paper", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=30;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "color", "checkbox",
	$optlist);
    
    $p->fit_textline("Rainbow colors", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=30;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "perforation",
	"checkbox", $optlist);
    
    $p->fit_textline("Perforation", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=30;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "stability",
	"checkbox", $optlist);
    
    $p->fit_textline("High stability", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
   
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_checkbox.pdf");
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


