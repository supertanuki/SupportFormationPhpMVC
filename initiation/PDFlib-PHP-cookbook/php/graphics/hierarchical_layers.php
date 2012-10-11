<?php
/* $Id: hierarchical_layers.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Hierarchical layers:
 * Define a layer hierarchy using the parent and child options.
 *
 * Define the layer "Languages" with the layers "English" and "German" and use
 * set_layer_dependency() with the "parent" option to specify a hierarchy 
 * between them. Define the layer "Images" with the layers "RGB" and "Grayscale"
 * and use set_layer_dependency() with the "parent" option to specify a
 * hierarchy between them. Output images and text on the various layers and
 * open the document with the RGB images and English captions visible.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: grayscale and RGB images
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Hierarchical Layers";

$rgb = "nesrin.jpg";
$gray = "nesrin_gray.jpg";

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

    /* Load the Grayscale image */
    $imageGray = $p->load_image("auto", $gray, "");
    if ($imageGray == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the RGB image */
    $imageRGB = $p->load_image("auto", $rgb, "");
    if ($imageRGB == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Define all layers which will be used, and their relationships. This
     * should be done before the first page if the layers are used on more
     * than one page.
     */
    
    /* Define the layer "Images" */
    $layerImage = $p->define_layer("Images", "");
    
    /* Define the layer "RGB" */
    $layerRGB = $p->define_layer("RGB", "");
    
    /* Define the layer "Grayscale" which is hidden when opening the
     * document or printing it.
     */
    $layerGray = $p->define_layer("Grayscale", 
	"initialviewstate=false initialprintstate=false");
    
    /* At most one of the "Grayscale" and "RGB" layers will be visible */
    $p->set_layer_dependency("Radiobtn", 
	"group={" . $layerGray . " " . $layerRGB . "}");
    
    /* Make the "Images" layer to be the parent of the "RGB" and "Grayscale"
     * layers
     */
    $p->set_layer_dependency("Parent", "parent=" . $layerImage . 
	" children={" . $layerGray . " " . $layerRGB . "}");
	
    /* Define the  layer "Languages" */
    $layerLang = $p->define_layer("Languages", "");
    
    /* Define the layer "English" */
    $layerEN = $p->define_layer("English", "");
    
    /* Define the layer "German" which is hidden when opening the document
     * or printing it.
     */
    $layerDE = $p->define_layer("German",
	"initialviewstate=false initialprintstate=false");
    
    /* At most one of the "English" and "German" layers will be visible */
    $p->set_layer_dependency("Radiobtn", "group={" . $layerEN . " " .
	$layerDE . "}");
    
    /* Make the "Languages" layer to be the parent of the "German" and
     * "English" layers
     */
    $p->set_layer_dependency("Parent", "parent=" . $layerLang . 
	" children={" . $layerEN . " " . $layerDE . "}");

    /* Start the page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Place the RGB image on the "RGB" layer */
    $p->begin_layer($layerRGB);
    $p->fit_image($imageRGB, 100, 400, "boxsize={400 300} fitmethod=meet");
    
    /* Place the Grayscale image on the "Grayscale" layer */
    $p->begin_layer($layerGray);
    $p->fit_image($imageGray, 100, 400, "boxsize={400 300} fitmethod=meet");

    /* Place an English image caption on the "English" layer */
    $p->begin_layer($layerEN);
    $p->fit_textline("This is the Nesrin image.", 100, 370, "font=" . $font .
	" fontsize=20");

    /* Place a German image caption on the "German" layer */
    $p->begin_layer($layerDE);
    $p->fit_textline("Das ist das Nesrin-Bild.", 100, 370, "font=" . $font .
	" fontsize=20");

    $p->end_layer();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=hierarchical_layers.pdf");
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
