<?php
/* $Id: control_nexpress_trays.php,v 1.4 2012/05/03 14:00:41 stm Exp $
 * Control NexPress trays:
 * For NexPress digital color printing machines, create some special kind of
 * annotations to control the input tray.
 * 
 * Use the "custom" option of create_annotation() to create a "Stamp" annotation
 * with NexPress-specific custom extensions.   
 * The "P" in "PDF" means "Portable", which excludes the use of device-specific
 * parameters such as tray control. This technique is not really in the spirit
 * of portable PDF documents, but NexPress digital printing machines expect it
 * this way nevertheless.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Control NexPress trays";

$font;

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Start the document */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set document info entries */
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Load font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set font */
    $p->setfont($font, 12);
    
    /* Output some descriptive text */
    $p->fit_textline("Use the \"custom\" option of create_annotation() to " .
	 "create a \"Stamp\" annotation with", 20, 600, "");
    $p->fit_textline("NexPress-specific custom extensions in the document.",
	20, 580, "");
    
    /* Create a "Stamp" annotation with NexPress-specific custom 
     * extensions. 
     * Some names which can be used in the "Name" key for tray selection:
     * SubstrateTypeCover, SubstrateTypeInsert,
     * SubstrateTypeInsert1 ... SubstrateTypeInsert9.
     * More details are available in the printer-specific documentation.
     */
    $optlist =
	"custom={{key=Open type=boolean value=false} " .
	"{key=Name type=name value=SubstrateTypeCover} " .
	"{key=Subj type=string value={Cover}}} " .
	"contents={Stamp Annotation for tray selection}";

    $p->create_annotation(50, 500, 150, 550, "Stamp", $optlist);
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=control_nexpress_trays.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in starter_pdfx sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e->getMessage());
}

$p = 0;
?>

