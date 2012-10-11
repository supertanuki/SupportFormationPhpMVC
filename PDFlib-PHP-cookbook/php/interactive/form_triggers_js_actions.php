<?php
/* $Id: form_triggers_js_actions.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form triggers for JavaScript actions:
 * Demonstrate all possibilities to trigger a JavaScript action from a form
 * field. 
 * 
 * Trigger a JavaScript action by performing various actions on a
 * form text field. Use create_field() with the "action" option to trigger
 * actions by one of the following events: activate, keystroke, format, 
 * validate, calculate, enter, exit, down, up, focus, or blur.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Triggers for Javascript Actions";

$llx = 100; $lly = 780; $urx = 400; $ury = 800;
$tx = 50; $ty_off = 7;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start an A4 page */
    $p->begin_page_ext(595, 842, "");
    
    $p->setfont($font, 12);
    
    
    /* -------------------------------------------------------
     * Trigger a JavaScript action when the field is activated
     * -------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'activate' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {activate={" . $action . "} " . "} " .
	"currentvalue={Click in this field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly, $urx, $ury, "activate_field", "textfield",
	$optlist);
    $p->fit_textline("activate", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
    
    /* -----------------------------------------------------------------
     * Trigger a JavaScript action when the user types into a text field
     * -----------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'keystroke' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {keystroke={" . $action . "} " . "} " .
	"currentvalue={Press a key in this field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "keystroke_field", 
	"textfield", $optlist);
    $p->fit_textline("keystroke", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
    
    /* --------------------------------------------------------------------
     * Trigger a JavaScript action before the field is formatted to display
     * its current value. This allows the field's value to be modified
     * before formatting.
     * --------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'format' for the text " .
	"field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {format={" . $action . "} " . "} " .
	"currentvalue={Enter text and press Return} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "format_field", "textfield",
	$optlist);
    $p->fit_textline("format", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
    
    /* -------------------------------------------------------------------
     * Trigger a JavaScript action when the field's value is changed. This
     * allows the new value to be checked for validity.
     * -------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'validate' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {validate={" . $action . "} " . "} " .
	"currentvalue={Enter text and press Return} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "validate_field",
	"textfield", $optlist);
    $p->fit_textline("validate", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
    
    /* -------------------------------------------------------------------
     * Trigger a JavaScript action when the value of another field changes
     * in order to recalculate the value of this field
     * -------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'calculate' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {calculate={" . $action . "} " . "} " .
	"currentvalue={Enter text and press Return} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "calculate_field",
	"textfield", $optlist);
    $p->fit_textline("calculate", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");

	       
    /* -------------------------------------------------------------------
     * Trigger a JavaScript action when the mouse button is pressed inside
     * the field's area
     * -------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'down' for the text " .
	"field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {down={" . $action . "} " . "} " .
	"currentvalue={Press the mouse button in this field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "down_field", "textfield",
	$optlist);
    $p->fit_textline("down", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");

       
    /* --------------------------------------------------------------------
     * Trigger a JavaScript action when the mouse button is released inside
     * the field's area
     * --------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'up' for the text " .
	"field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {up={" . $action . "} " . "} " .
	"currentvalue={Press and then release the mouse button in this " .
	"field} backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "up_field", "textfield",
	$optlist);
    $p->fit_textline("up", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");

    
    /* ------------------------------------------------------------------
     * Trigger a JavaScript action when the text field receives the input
     * focus
     * ------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'focus' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {focus={" . $action . "} " . "} " .
	"currentvalue={Click this field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "focus_field", "textfield",
	$optlist);
    $p->fit_textline("focus", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");

    
    /* ---------------------------------------------------------------------
     * Trigger a JavaScript action when the text field loses the input focus
     * ---------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'blur' for the " .
	    "text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {blur={" . $action . "} " . "} " .
	"currentvalue={Click this and then another field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
		    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "blur_field", "textfield",
	$optlist);
    $p->fit_textline("blur", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
    
    /* ------------------------------------------------------------------
     * Trigger a JavaScript action when the mouse enters the field's area.
     * ------------------------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by the event 'enter' for the " .
	"text field\");}");
    
    /* Create a text field which triggers the action */
    $optlist = "action {enter={" . $action . "} " . "} " .
	"currentvalue={Move the mouse into this field} " .
	"backgroundcolor={rgb 0.95 0.95 0.7} font=" . $font;
    
    $p->create_field($llx, $lly-=50, $urx, $ury-=50, "enter_field", "textfield",
	$optlist);
    $p->fit_textline("enter", $tx, $lly + $ty_off,
	"fillcolor={rgb 0.47 0.47 0.34} fontsize=10");
    
   
    /* ---------------------------------------------------------------------
     * To trigger a JavaScript action when the mouse exits the field's area,
     * use "optlist = "action {exit={" + action ..."
     * ---------------------------------------------------------------------
     */
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_triggers_js_action.pdf");
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
