<?php
/* $Id: form_combobox.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form combobox:
 * Create a form field of type "combobox" for choosing an item from a list or
 * changing an existing item.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Combobox";


$width=100; $height=18; $llx = 100; $lly = 600;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    /* Output a combobox title */
    $p->setfont($font, 14);
    $p->fit_textline("Choose a size from the list or enter an individual " .
	"value:", $llx, $lly, "");
    $lly-=30;
    
    /* Create a form fields of type "combobox".
     * Provide the box with a light gray background
     * (backgroundcolor={gray 0.9}) and a gray border
     * (bordercolor={gray 0.7}).
     * Set the values for the combobox items (itemnamelist={0 1 2 3 4}).
     * Set the labels for the combobox items (itemtextlist={...}).
     * Set the focus on the last item (currentvalue=4).
     * Allow the user to change an item (editable=true)
     */
    $optlist = "font=" . $font . " fontsize=14 backgroundcolor={gray 0.9} " .
	"bordercolor={gray 0.7} itemnamelist={0 1 2 3 4} currentvalue=4 " .
	"itemtextlist={S M L XL XXL} editable=true";
    
    /* Create the field with a height similar to the height of one list
     * item
     */ 
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "size", "combobox",
	$optlist);
    
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_combobox.pdf");
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

