<?php
/* $Id: triggers_for_javascript_actions.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Triggers for JavaScript actions:
 * Demonstrate all possibilities to trigger a JavaScript action (except of form
 * fields).
 * 
 * Trigger JavaScript actions by clicking on a link: Use the "activate" option
 * in create_annotation().
 * Trigger JavaScript actions by clicking on a bookmark: Use the "activate"
 * option in create_bookmark().
 * Trigger JavaScript actions upon opening or closing a page: Use the "action"
 * option in begin_page_ext() or PDF_end_page_ext() with the triggers "open" and
 * "close". 
 * Trigger JavaScript actions upon opening, closing, printing, or saving the
 * document: Use the "action" option in PDF_end_document() with the triggers
 * "open", "didprint", "didsave", "willclose", "willprint", and "willsave".
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Triggers for Javascript Actions";


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start an A4 page */
    $p->begin_page_ext(595, 842, "");
    
    $p->setfont($font, 12);
    
    /* Output some descriptive text */
    $p->fit_textline("Click this link", 30, 750, "");
    $p->fit_textline("Click the bookmark on the left", 20, 700, "");
    $p->fit_textline("Print the document", 20, 670, "");
    $p->fit_textline("Save the document", 20, 640, "");
    $p->fit_textline("Close the document", 20, 610, "");
    
    
    /* -------------------------------------------------
     * Trigger a JavaScript action by clicking on a link
     * -------------------------------------------------
     */
    
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by a Link annotation\");}");

    /* Create the "Link" annotation which activates the action */
    $p->create_annotation(20, 740, 110, 765, "Link",
	"action {activate={" . $action . " " . "} }"); 
    
    
    /* -----------------------------------------------------
     * Trigger a JavaScript action by clicking on a bookmark
     * -----------------------------------------------------
     */
	   
    /* Create the JavaScript action */
    $action = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered by a bookmark\");}");
    
    /* Create the bookmark which activates the action */
    $p->create_bookmark("Click this bookmark", "action {activate={" .
	$action . "} }");

    
    /* ---------------------------------------------------------
     * Trigger JavaScript actions upon opening or closing a page
     * ---------------------------------------------------------
     */
	
    /* Create a JavaScript action to be triggered after opening the page */
    $page_open = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered after opening the page (open)\");}");
	
    /* Create a JavaScript action to be triggered after closing the page */
    $page_close = $p->create_action("JavaScript", "script {" .
	"app.alert(\"Action triggered after closing the page (close)\");}");
	     
    /* Close the page. For the page triggers "open" and "close", supply the
     * JavaScript page actions defined above.
     */
    $optlist = "action {open=" . $page_open . " close=" . $page_close . "}";
	
    $p->end_page_ext($optlist);
    
    
    /* --------------------------------------------------------------
     * Trigger JavaScript actions upon opening, closing, printing, or
     * saving the document.
     * --------------------------------------------------------------
     */
    
    /* Create a JavaScript action to be triggered upon opening the 
     * document.
     */
    $open = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered upon opening the document (open)\");}");
    
    /* Create a JavaScript action to be triggered upon closing the 
     * document.
     */
    $willclose = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered upon closing the document (willclose)\");}");
    
    /* Create a JavaScript action to be triggered before saving the 
     * document.
     */
    $willsave = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered before saving the document (willsave)\");}");
    
    /* Create a JavaScript action to be triggered after saving the 
     * document.
     */
    $didsave = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered after saving the document (didsave)\");}");
    
    /* Create a JavaScript action to be triggered before printing the 
     * document.
     */
    $willprint = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered before printing the document (willprint)\");}");
    
    /* Create a JavaScript action to be triggered after printing the 
     * document.
     */
    $didprint = $p->create_action("JavaScript", "script {app.alert(" .
	"\"Action triggered after printing the document (didprint)\");}");
    
    /* Define an option list for end_document(). All possible document
     * triggers will be supplied with the actions defined above.
     */
    $optlist = "action {open=" . $open . " willclose=" . $willclose . 
	" willprint=" . $willprint . " didprint=" . $didprint .
	" willsave=" . $willsave . " didsave=" . $didsave . "}";

    $p->end_document($optlist);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=triggers_for_javascript_actions.pdf");
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

