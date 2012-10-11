<?php
/* $Id: form_textfield_height.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form text field height:
 * Determine the height of a form field of type "textfield" with respect to
 * the font size and vice versa 
 * 
 * Create a text field with a defined field height and no font size specified.
 * Create a text field with a defined field height and calculate the
 * appropriate font size from the field height. Create a text field with a
 * defined font size and calculate the appropriate field height from the font
 * size.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Textfield Height";


$width=160; $height=30;
$llx = 10; $lly = 700;
$text = "Enter text here";

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

    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    
    /* ------------------------------------------
     * Field height given. No font size specified
     * ------------------------------------------
     *
     * Create a form field of type "textfield" called "date". 
     * Provide it with a light blue background and a black border.
     * Allow a maximum of 25 characters to be entered (maxchar=20).
     * If we don't supply any further options the font size will be
     * automatically adjusted to ensure the text being completely fit into
     * the text field. This means that the text get smaller when entering
     * more text. 
     */   
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
	"maxchar=25 currentvalue={" . $text . "} font=" . $font;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date", "textfield",
	$optlist);
    $lly-=40;
    
    $p->fit_textline("Field height given, no font size specified; a " .
	"maximum of 25 characters is allowed.", $llx, $lly, "font=" . $font . 
	" fontsize=12");
    $p->fit_textline("Acrobat automatically decreases the font size when " .
	"more text is entered.", $llx, $lly-=20, "font=" . $font .
	" fontsize=12");
    
    $lly-=100;
    
    
    /* ---------------------------------------------------
     * Field height given. Calculate appropriate font size
     * ---------------------------------------------------
     */
    
    /* Create another text field "date2" of a given size. Adjust the font
     * size to match the field height: Retrieve the ascender and the
     * descender (the latter is usually negative) of a hypothetical font
     * size equal to the field height and use the font size resulting from
     * the sum of ascender and (positive) descender as a starting point.
     * Then, descrease the font size by e.g. 20% to leave some empty space
     * from the field borders.
     */
    $ascender = $p->info_font($font, "ascender", "fontsize=" . $height);
    $descender = $p->info_font($font, "descender", "fontsize=" . $height);
    
    $fontsize = ($ascender - $descender) * 0.8;
    
    $optlist = "backgroundcolor={rgb 0.95 0.95 1}  bordercolor={gray 0} " .
    "currentvalue={" . $text . "} font=" . $font . " fontsize=" . $fontsize;
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date2",
	"textfield", $optlist);
    $lly-=40;
    $p->fit_textline("Field height of 30 is given. Acrobat uses an " .
	"appropriate font size.", $llx, $lly, "font=" . $font .
	" fontsize=12");
    
    $lly-=100;
    
    
    /* --------------------------------------------------------
     * Font size given. Calculate appropriate field height size
     * --------------------------------------------------------
     */
    
    /* Create another text field "date3". In this case, the font size is
     * given as a fixed value of 24 and the field height should be chosen
     * appropriately: Retrieve the ascender and descender (which is usually
     * negative) of the font size 24. The resulting value will be a good
     * starting point for determining the field height, for example.
     * 
     * Since the behaviour of Acrobat regarding the chosen font and 
     * baseline is not obvious, the field height will be too small in many
     * cases. To avoid that we add a margin of an appropriate percentage of
     * the field height, e.g. 50%.
     */
    $ascender = $p->info_font($font, "ascender", "fontsize=24");
    $descender = $p->info_font($font, "descender", "fontsize=24");
    
    $height = ($ascender - $descender) * 1.5;
    
    $optlist = "backgroundcolor={rgb 0.95 0.95 1}  bordercolor={gray 0} " .
    "currentvalue={" . $text . "} font=" . $font . " fontsize=24";
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date3",
	"textfield", $optlist);
    $lly-=40;
    
    $p->fit_textline("Font size of 24 is given. We calculate the field " .
	    "height based on the font's ascender and descender.", $llx, $lly,
	"font=" . $font . " fontsize=12");
     
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_textfield_height.pdf");
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


