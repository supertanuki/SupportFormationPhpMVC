<?php
/* $Id: form_textfield_layout.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form text field layout:
 * Specify the layout of a form field of type "textfield" for displaying a date
 * 
 * Create a text field with equidistant subfields for storing a date.
 * The following field properties are defined: font and font size, border and
 * fill color, initial value, maximum characters allowed, scroll behaviour,
 * and tooti$p-> 
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Text Field Layout";


$width=140; $height=30; $llx = 10; $lly = 700;

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
    
    /* Adjust the font size to match the field height: Retrieve the
     * ascender and the descender (the latter is usually negative) of a
     * hypothetical font size equal to the field height and use the font
     * size resulting from the sum of ascender and (positive) descender as
     * a starting point. Then, decrease the font size by e.g. 20% to leave
     * some empty space from the field borders.
     */
    $ascender = $p->info_font($monospaced_font, "ascender", "fontsize=" .
	$height);
    $descender = $p->info_font($monospaced_font, "descender", "fontsize=" .
	$height);
    
    $fontsize = ($ascender - $descender) * 0.8;
    
    /* Create a form field of type "textfield" called "date" with a
     * background color of light blue and a black border.
     * Set an initial date (currentvalue={Sep 10 2007}) 
     * Allow a maximum of 11 characters to be entered (maxchar=11).
     * The characters are not moved out of the field if the field size is
     * reached (scrollable=false).
     * Display equidistant subfields for each character (comb=true).
     * A tooltip is displayed when the user moves in the field 
     * tooltip={Enter a date}.
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
    "currentvalue={Sep 10 2007} maxchar=11 scrollable=false comb=true " .
    "tooltip={Enter a date} font=" . $monospaced_font .
    " fontsize=" . $fontsize;

    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date", "textfield",
	$optlist);
    $lly-=40;

    $p->setfont($font, 12);
    $p->fit_textline("Form text field with an initial value set. The " .
	"characters are placed in equidistant subfields.", $llx, $lly, "");
    $p->fit_textline("A maximum of 11 characters is allowed. The text is " .
	"not allowed to scroll out of the window.", $llx, $lly-=20, "");
    $p->fit_textline("A tooltip is displayed when the mouse moves over the " .
	"field.", $llx, $lly-=20, "");
    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_textfield_layout.pdf");
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
