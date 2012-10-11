<?php
/* $Id: import_pages_into_layers.php,v 1.2 2012/05/03 14:00:36 stm Exp $
 * Import pages into layers:
 * Import two pages and output them on two layers on the same page
 *
 * Import an English and a German PDF page and place them on an English and a
 * German layer on the same page.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Import Pages into Layers";

$fileEN = "PDFlib-real-world.pdf";
$fileDE = "PDFlib-real-world-D.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "openmode=layers") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Open the first page of the English input PDF */
    $indocEN = $p->open_pdi_document($fileEN, "");
    if ($indocEN == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $pageEN = $p->open_pdi_page($indocEN, 1, "");
    if ($pageEN == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page of the German input PDF */
    $indocDE = $p->open_pdi_document($fileDE, "");
    if ($indocDE == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $pageDE = $p->open_pdi_page($indocDE, 1, "");
    if ($pageDE == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start the page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Define the layer "English" and place some English text on it */
    $layerEN = $p->define_layer("English", "");
    $p->begin_layer($layerEN);

    /* Place the imported page on the English layer of the output page */
    $p->fit_pdi_page($pageEN, 0, 0, "");

    $p->close_pdi_page($pageEN);
    
    /* Define the layer "German" which is hidden when opening the document
     * or printing it
     */
    $layerDE = $p->define_layer("German", "initialviewstate=false " .
	"initialprintstate=false");
    $p->begin_layer($layerDE);
    
    /* Place the imported page on the German layer of the output page */
    $p->fit_pdi_page($pageDE, 0, 0, "");

    $p->close_pdi_page($pageDE);
    
    /* At most one of the "English" and "German" layers should be visible */
    $p->set_layer_dependency("Radiobtn", "group={" . $layerEN . " " .
	$layerDE . "}");
    
    /* Complete all layers */
    $p->end_layer();

    $p->end_page_ext("");
   
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=import_pages_into_layers.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
