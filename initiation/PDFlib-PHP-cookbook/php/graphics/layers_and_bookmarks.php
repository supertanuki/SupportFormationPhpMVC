<?php
/* $Id: layers_and_bookmarks.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Layers and bookmarks:
 * Define two layers and hide or show them via bookmarks 
 * 
 * Define two layers for English or German text. Create two bookmarks which
 * show the English or the German layer, respectively.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Layers and Bookmarks";


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Open the document with the "Bookmarks" navigation tab visible */
    if ($p->begin_document($outfile, "openmode=bookmarks") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0) {
	throw new Exception("Error: " . $p->get_errmsg());	
    }
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Define the layer "English" and place some English text on it */
    $layerEN = $p->define_layer("English", "");
    $p->begin_layer($layerEN);
    $p->fit_textline("Our paper planes are the ideal way of passing the " .
	"time.", 30, 600, "font=" . $font . " fontsize=20");
	   
    /* Define the layer "German" which is hidden when opening the document
     * or printing it. Place a German image caption on that layer.
     */
    $layerDE = $p->define_layer("German", "initialviewstate=false " .
	"initialprintstate=false");
    $p->begin_layer($layerDE);
    $p->fit_textline("Unsere Papierflieger sind ein idealer Zeitvertreib.",
	30, 600, "font=" . $font . " fontsize=20");
	 
    /* At most one of the "English" and "German" layers should be visible */
    $p->set_layer_dependency("Radiobtn", "group={" . $layerEN . " " .
	$layerDE . "}");
    
    /* Create a "SetOCGState" action which shows the English layer. Since
     * at most one layer may be visible we don't need to explicitly hide
     * the German layer.
     */
    $action = $p->create_action("SetOCGState", "layerstate={on " .
	$layerEN . "}");

    /* Create a bookmark which activate the action above to shows the layer
     * with the English text
     */
    $p->create_bookmark("Show English", " action={activate=" . $action . "}");
    
    /* Create a "SetOCGState" action which shows the German layer. Since
     * at most one layer may be visible we don't need to explicitly hide
     * the English layer. */
    $action = $p->create_action("SetOCGState", "layerstate={on " .
	$layerDE . "}");

    /* Create a bookmark which activate the action above to shows the layer
     * with the German text
     */
    $p->create_bookmark("Show German", " action={activate=" . $action . "}");
    
    /* Complete all layers */
    $p->end_layer();
   
    $p->end_page_ext("");
	    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=layer_and_bookmarks.pdf");
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
