<?php
/* $Id: form_radiobutton.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form radio button:
 * Create a field group with three form fields of type "radiobutton".
 * 
 * Create a field group and three radio buttons.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Radiobutton";


$width=20; $height=20; $llx = 50; $lly = 600;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " + $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " + $p->get_errmsg());
    
    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    $p->setfont($font, 12);
    
    /* First, create a form field group called "colors" */
    $p->create_fieldgroup("colors", "fieldtype=radiobutton");
    

    $p->fit_textline("Choose the paper plane color:", $llx, $lly, "");
    $lly-=40;
    
    /* Then, create several form fields of type "radiobutton" which appear
     * as gray circles (buttonstyle=circle bordercolor={gray 0.8}). 
     * All fields belong to the "colors" field grou$p-> Indicate this
     * relationship by providing each radiobutton field name with the
     * prefix "colors.".
     * Activate the first radio button (currentvalue={On}).
     * Provide a toolti$p-> The tooltip is always shared by the grou$p->
     */
    $optlist = "buttonstyle=circle bordercolor={gray 0.8}";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "colors.standard",
	"radiobutton", $optlist . " currentvalue={On} " .
	"tooltip={Select a color for the paper plane}");
    
    /* Output the button label */
    $p->fit_textline("Standard", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=40;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, 
	"colors.yellow", "radiobutton", $optlist);
    
    $p->fit_textline("Yellow", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=40;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "colors.blue",
	"radiobutton", $optlist);
    
    $p->fit_textline("Blue", $llx + 30, $lly, "boxsize={0 " . $height .
	"} position={left center}");
    $lly-=40;

    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_radiobutton.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() .
        ": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;
?>

