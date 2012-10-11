<?php
/* $Id: form_textfield_input_check.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form text field input check:
 * Check if the date entered in a form field of type "textfield" has been
 * formatted correctly.
 * 
 * Create a text field for displaying a date and check if the input has been
 * correctly formatted as "mmm dd yyyy, e.g. "Oct 12 2007". 
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Text Field Input Check";


$width=160; $height=30; $llx = 10; $lly = 700;

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
    
    $monospaced_font = $p->load_font("Courier", "winansi", "");
    if ($monospaced_font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    /* Adjust the font size to match the field height. Then, descrease the
     * font size by e.g. 20% to leave some empty space from the field
     * borders.
     */
    $ascender = $p->info_font($monospaced_font, "ascender", "fontsize=" .
	$height);
    $descender = $p->info_font($monospaced_font, "descender", "fontsize=" .
	$height);
    $fontsize = ($ascender - $descender) * 0.8;
    
    /* For a correct formatting of the date, define a keystroke and a
     * formatting action for the date to be checked for compliance to the
     * format "mmm dd yyyy". 
     */
    $keystroke_action = $p->create_action("JavaScript",
	"script={AFDate_KeystrokeEx('mmm dd yyyy'); }");

    /* Create a form field of type "textfield" called "date" with the
     * JavaScript action supplied in the "action" option
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
	"currentvalue={Sep 12 2007} maxchar=11 comb=true " .
	"scrollable=false tooltip={Enter a date} font=" . $monospaced_font . 
	" fontsize=" . $fontsize .
	" action={keystroke=" . $keystroke_action . "}";
	  
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date", "textfield",
	$optlist);
    $lly-=40;
    
    $p->setfont($font, 12);
    $p->fit_textline("Change the date.", $llx, $lly, "");
    $p->fit_textline("After pressing \"Enter\", the date is checked to be " .
	"correctly formatted as \"mmm dd yyy\".", $llx, $lly-=20, "");
    
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_textfield_input_check.pdf");
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

