<?php
/* $Id: form_pushbutton.php,v 1.3 2012/05/03 14:00:39 stm Exp $
 * Form pushbutton:
 * Create two form fields of type "pushbutton" for executing the "Print" and
 * "Save As" commands.
 * 
 * Create two form fields of type "pushbutton". Define two actions which
 * execute the Acrobat menu commands "File/Print" or "File/Save As",
 * respectively. Perform the Print or Save As action when the user clicks the
 * respective field. Represent the buttons using a caption or using an image
 * loaded as a template.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Pushbutton";

$p_imagefile = "fileprint.jpg";
$s_imagefile = "filesaveas.jpg"; 
$width=60; $height=30; $llx = 40; $lly = 600;

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
    
    /* Load the "Print" image as template */
    $pimg = $p->load_image("auto", $p_imagefile, "template=true");
    if ($pimg == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the "Save As" image as template */
    $simg = $p->load_image("auto", $s_imagefile, "template=true");
    if ($simg == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Create an action for executing the Acrobat command File/Print */
    $pact = $p->create_action("Named", "menuname=Print");
    
    /* Create an action for executing the Acrobat command File/Save As */
    $sact = $p->create_action("Named", "menuname=SaveAs");
    
    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    $p->setfont($font, 14);
    
    
    /* ------------------------------------------------------------
     * Create two pushbutton form fields for executing Acrobat menu 
     * commands and represent them using a caption
     * ------------------------------------------------------------
     */
    
    /* Output some descriptive text */
    $p->fit_textline("Two buttons executing Acrobat menu commands and " .
	"represented by a caption:", $llx, $lly, "");
    $lly-=60;
   
    /* Create the "print" and "save" form fields of type "pushbutton".
     * Provide the buttons with a light blue background
     * (backgroundcolor={rgb 0.95 0.95 1}) and 
     * a blue border (bordercolor={rgb 0.25 0 0.95}) with a default line
     * width of 1.
     * Supply a blue caption describing the menu command
     * (caption={Print} fillcolor={rgb 0.25 0 0.95}).
     * Define an individual tooltip for each button.
     * Supply the action defined above to be performed when the user
     * releases the mouse button inside the field's area
     * (action={up <actionhandle>}).
     * 
     */
    $optlist = "bordercolor={rgb 0.25 0 0.95} " .
	"backgroundcolor={rgb 0.95 0.95 1} " .
	"fillcolor={rgb 0.25 0 0.95} font=" . $font . " fontsize=14";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "print",
	"pushbutton", $optlist . " caption={Print} action={up " . $pact . 
	"} tooltip={Print the document}");
    
    $llx+=100;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "save",
	    "pushbutton", $optlist . " caption={Save As} action={up " . $sact . 
	"} tooltip={Save the document}");
    
     
    /* ------------------------------------------------------------
     * Create two pushbutton form fields for executing Acrobat menu 
     * commands and represent them using images
     * ------------------------------------------------------------
     */
    $llx=40;
    $lly=400;
	
    /* Output some descriptive text */
    $p->fit_textline("Two buttons executing Acrobat menu commands and " .
	"represented by an image:", $llx, $lly, "");
    $lly-=60;
    
    /* Get the width and height of the "Print" image */
    $pwidth = $p->info_image($pimg, "imagewidth", "");
    $pheight = $p->info_image($pimg, "imageheight", "");
       
    /* Get the width and height of the "Save" image */
    $swidth = $p->info_image($simg, "imagewidth", "");
    $sheight = $p->info_image($simg, "imageheight", "");
    
    /* Create a "printicon" and a "saveicon" form field of type
     * "pushbutton".
     * Calculate the button width based on a fixed button
     * height as well as the image proportions.
     * Supply the action defined above to be performed when the user
     * releases the mouse button inside the field's area
     * (action={up <actionhandle>}).
     * Provide the buttons with an image (icon=<imagehandle>).
     * Define an individual tooltip for each button.
     * Fit the image completely into the button rectangle: because of 
     * a particular behaviour of Acrobat we need to define a border color
     * and set the line width to zero. 
     * .
     */
    $pbuttonwidth = $pwidth/$pheight*$height;
    
    $p->create_field($llx, $lly, $llx + $pbuttonwidth, $lly + $height, "printicon",
	"pushbutton", "action={up " . $pact . "} icon=" . $pimg .
	" tooltip={Print the document} bordercolor={gray 0} linewidth=0" .
	" font=" . $font);
    
    $llx+=100;
    
    $sbuttonwidth = $swidth/$sheight*$height;
    
    $p->create_field($llx, $lly, $llx + $sbuttonwidth, $lly + $height, "saveicon",
	"pushbutton", "action={up " . $sact . "} icon=" . $simg .
	" tooltip={Save the document} bordercolor={gray 0} linewidth=0" .
	" font=" . $font);
    
    $p->close_image($pimg);
    $p->close_image($simg);
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_pushbutton.pdf");
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

