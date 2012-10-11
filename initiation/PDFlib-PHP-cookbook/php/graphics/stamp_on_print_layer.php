<?php
/* $Id: stamp_on_print_layer.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Stamp on print layer:
 * Place a stamp on a layer which is only visible upon printing
 * 
 * Create a layer "stamp" which contains the stam$p-> The layer properties are set
 * to print, but not display the layer contents on screen.
 * Create some standard text which is displayed and printed by default.  
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Stamp on Print Layer";

$x = 50; $y = 50;
$tf = 0; 

$textflow =
    "To fold the famous rocket looper proceed as follows:\n" .
    "Take a DIN A4 sheet.\nFold it lenghtwise in the middle.\nThen, fold " .
    "the upper corners down.\nFold the long sides inwards that the " .
    "points A and B meet on the central fold.\nFold the points C and D " .
    "that the upper corners meet with the central fold as well." .
    "\nFold the plane in the middle. Fold the wings down that they close " .
    "with the lower border of the plane.";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Open the document with the "Layers" navigation tab visible */
    if ($p->begin_document($outfile, "openmode=layers") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    

    /* ---------------------------------------------------------------
     * Define the layer "stamp" to be hidden when opening the document
     * (initialviewstate=false) and place the stamp on it.
     * ---------------------------------------------------------------
     */
    $stamplayer = $p->define_layer("stamp", 
	"initialviewstate=false initialprintstate=true");
    $p->begin_layer($stamplayer);
    
    /* Fit a text line as outlines like a stamp in the specified box. The
     * stamp will be placed diagonally from the upper left to the lower
     * right (stamp=ul2lr).
     */
    $p->fit_textline("The Famous Rocket Looper", $x, $y, "font=" . $font .
	" strokecolor={rgb 1 0 0} textrendering=1 boxsize={500 750} " .
	"strokecolor={rgb 0.5 0 1} stamp=ul2lr");
    
    /* End all layers */
    $p->end_layer();

    /* ------------------------------------
     * Place some standard text on the page
     * ------------------------------------
     */
    $optlist = "fontname=Helvetica fontsize=24 encoding=unicode " .
	"leading=120% charref";
    
    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 100, 100, 400, 700, "");
    if ($result.=! "_stop")
    {
	/* Check for errors or more text to be placed */
    }

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=stamp_on_print_layer.pdf");
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
