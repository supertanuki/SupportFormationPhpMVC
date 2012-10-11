<?php
/* $Id: form_and_layers.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Forms and Layers:
 * Define two layers for English or German contents and hide or show
 * them via form field buttons 
 * 
 * Define two layers for displaying some text and a "combobox" form field
 * together with its list items in English or German. Create the two form field
 * buttons "English" and "Deutsch" which show the English or the German layer
 * when the user presses the respective button.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form and Layers";

$width=100; $height=18; $llx = 100; $lly = 600;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    
    /* Open the document with the "Layers" navigation tab visible */
    if ($p->begin_document($outfile, "openmode=layers") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Load the font */
    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0) {
	throw new Exception("Error: " . $p->get_errmsg());	
    }
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
	  
    /* ------------------------------------------------------------------
     * Define the layer "English" and place some English text as well as 
     * a form field of type "combobox" with some English list items on it
     * ------------------------------------------------------------------
     */
    $layerEN = $p->define_layer("English", "");
    $p->begin_layer($layerEN);
   
    /* Output an English combobox title */
    $p->setfont($font, 12);
    $p->fit_textline("Choose a color from the list or enter an individual " .
	"color:", $llx, $lly, "");
    $lly-=30;
    
    /* Create the "color" form field of type "combobox" with some English
     * list items and a height similar to the height of one list item
     */
    $optlist = "font=" . $font . " fontsize=14 backgroundcolor={gray 0.9} " .
	"bordercolor={gray 0.7} itemnamelist={0 1 2 3 4} currentvalue=4 " .
	"itemtextlist={yellow green blue red white} editable=true layer=" .
	$layerEN;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "color", "combobox",
	$optlist);
    
	   
    /* -----------------------------------------------------------------
     * Define the layer "German" and place some German text as well as 
     * a form field of type "combobox" with some German list items on it
     * -----------------------------------------------------------------
     */
    $layerDE = $p->define_layer("German", "initialviewstate=false " .
	"initialprintstate=false");
    $p->begin_layer($layerDE);
    
    $lly=600;
    $p->fit_textline("Wählen Sie eine Farbe aus der Liste oder geben Sie " .
	"eine eigene Farbe ein:", $llx, $lly, "");
    $lly-=30;
    
    /* Create the "farbe" form field of type "combobox" with some German
     * list items and a height similar to the height of one list item
     */
  
    $optlist = "font=" . $font . " fontsize=14 backgroundcolor={gray 0.9} " .
	"bordercolor={gray 0.7} itemnamelist={0 1 2 3 4} currentvalue=4 " .
	"itemtextlist={gelb grün blau rot weiß} editable=true layer=" .
	$layerDE;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "farbe", "combobox",
	$optlist);
   
    /* At most one of the "English" and "German" layers should be visible */
    $p->set_layer_dependency("Radiobtn", "group={" . $layerEN . " " .
	$layerDE . "}");
    
    /* Create a "SetOCGState" action which shows the English layer. Since
     * at most one layer may be visible we don't need to explicitly hide
     * the German layer.
     */
    $en_act = $p->create_action("SetOCGState", "layerstate={on " .
	$layerEN . "}");

    /* Create a "SetOCGState" action which shows the German layer. Since
     * at most one layer may be visible we don't need to explicitly hide
     * the English layer. */
    $de_act = $p->create_action("SetOCGState", "layerstate={on " .
	$layerDE . "}"); 
    
    /* Create the "english" and "german" form fields of type "pushbutton".
     * Using the action defined above switch to the other layer when the
     * user releases the mouse button inside the field's area
     * (action={up <actionhandle>}).
     */
    $lly = 680;
    $optlist =
	"bordercolor={rgb 0.25 0 0.95} backgroundcolor={rgb 0.95 0.95 1} " .
	"fillcolor={rgb 0.25 0 0.95} font=" . $font . " fontsize=14";
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "english",
	"pushbutton", $optlist . " caption={English} action={up " .
	$en_act . "}");
    
    $llx+=150;
    
    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "german",
	"pushbutton", $optlist . " caption={Deutsch} action={up " . 
	$de_act . "}");
  
    /* Complete all layers */
    $p->end_layer();
   
    $p->end_page_ext("");
	    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_and_layers.pdf");
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

