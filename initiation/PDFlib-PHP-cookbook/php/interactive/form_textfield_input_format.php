<?php
/* $Id: form_textfield_input_format.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form text field input format:
 * Format the data entered in a form field of type "textfield" according to
 * the rules defined.
 * 
 * Create a text field for displaying a date and format the input as 
 * "mmm dd yyyy.
 * Create a text field for displaying a price and format the input as
 * "x,xxx.xx" with the text " Euro" appended. 
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Text Field Input Format";


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
    
    /* ----------------------------------------------
     * Format a form text field for displaying a date
     * ----------------------------------------------
     *
     * Adjust the font size to match the field height. Then, descrease the
     * font size by e.g. 20% to leave some empty space from the field
     * borders.
     */
    $ascender = $p->info_font($monospaced_font, "ascender", "fontsize=" .
	$height);
    $descender = $p->info_font($monospaced_font, "descender", "fontsize=" .
	$height);
    $fontsize = ($ascender - $descender) * 0.8;
    
    /* For a correct formatting of the date, define an action for the date
     * to be formatted according to "mmm dd yyyy". 
     */
    $format_action = $p->create_action("JavaScript",
	"script={AFDate_FormatEx('mmm dd yyyy'); }");
  
    /* Create a form field of type "textfield" called "date" with the
     * JavaScript action supplied in the "action" option 
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
	"currentvalue={Oct 12 2007} maxchar=11 comb=true " .
	"scrollable=false tooltip={Enter a date} font=" . $monospaced_font .
	" fontsize=" . $fontsize . " action={format=" . $format_action . "}";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date", "textfield",
	$optlist);
    $lly-=40;
    
    $p->setfont($font, 12);
    $p->fit_textline("Change the date.", $llx, $lly, "");
    $p->fit_textline("After pressing \"Tab\" or \"Enter\", the date is " .
	"formatted as \"mmm dd yyy\".", $llx, $lly-=20, "");
    $lly-=100;
    
    
    /* -----------------------------------------------
     * Format a form text field for displaying a price
     * -----------------------------------------------
     *
     * Adjust the font size to match the field height. Then, descrease the
     * font size by e.g. 20% to leave some empty space from the field
     * borders.
     */
    $ascender = $p->info_font($monospaced_font, "ascender", "fontsize=" .
	$height);
    $descender = $p->info_font($monospaced_font, "descender", "fontsize=" .
	$height);
    $fontsize = ($ascender - $descender) * 0.8;
    
    /* For a correct formatting of the number, define an action which
     * formats the number as "x,xxx.xx", e.g. "1,200.50", and appends the
     * text " Euro". 
     */
    $format_action = $p->create_action("JavaScript",
	"script={AFNumber_Format(2, 0, 0, 0, \" Euro\", false); }");
	   
    /* Create a form field of type "textfield" called "price" with the
     * JavaScript action supplied in the "action" option 
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
	"maxchar=10 scrollable=false tooltip={Enter the price} " .
	"font=" . $font . " fontsize=" . $fontsize .
	" action={format=" . $format_action . "}";
    
    $width=250;
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "price",
	"textfield", $optlist);
    $lly-=40;
    
    $p->setfont($font, 12);
    $p->fit_textline("Enter a price, e.g. 150.95. A maximum of 10 digits " .
	"is allowed to be entered.", $llx, $lly, "");
    $p->fit_textline("After pressing \"Tab\" or \"Enter\", the price is " .
	"formatted as \"x,xxx.xx\" with the text \" Euro\" appended.", 
	$llx, $lly-=20, "");
    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_textfield_input_format.pdf");
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

