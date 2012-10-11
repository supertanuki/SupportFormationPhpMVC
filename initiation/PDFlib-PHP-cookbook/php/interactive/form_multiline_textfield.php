<?php
/* $Id: form_multiline_textfield.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form multiline text field:
 * Create a form field of type "textfield" for entering multiline text
 * 
 * Create the "comment" text field for entering multiline text which is not
 * allowed to scroll out of the window. Define the following field properties: 
 * default text, border and fill color, tooltip, tab order position.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Multiline Textfield";

$width=200; $height=80;
$llx = 30; $lly = 500;

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
    
    /* Create a multiline text field called "comment" (multiline=true).
     * Don't allow the text to be scrolled out of the field
     * (scrollable=false).
     * Define a tooltip (tooltip={Enter a comment}).
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1}  bordercolor={gray 0} " .
	"currentvalue={Enter a multiline comment} multiline=true " .
	"scrollable=false tooltip={Enter a comment} font =" . $font .
	" fontsize=14";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "comment",
	"textfield", $optlist);

    $p->setfont($font, 12);
    $p->fit_textline("Multiline text field; a tooltip is provided.",
	$llx, $lly-=30, "");
    $p->fit_textline("Text is not allowed to scroll out of the window.",
	$llx, $lly-=20, "");
    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_multiline_textfield.pdf");
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
