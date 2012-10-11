<?php
/* $Id: overprinting_text.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Overprinting text:
 * Create text which will overprint other page contents instead of knocking
 * it out
 *
 * Create an extended graphics state with the options
 * "overprintfill=true overprintmode=1".
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Overprinting Text";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(400, 300, "");
    
    /* Draw a red background rectangle */
    $p->setcolor("fill", "cmyk", 0, 1, 1, 0);
    $p->rect(0, 0, 400, 300);
    $p->fill();
    
    /* Save the current graphics state */
    $p->save();
    
    /* Create an extended graphics state */
    $gstate = $p->create_gstate("overprintfill overprintmode=1");
    
    /* Apply the extended graphics state */
    $p->set_gstate($gstate);
    
    /* Show some text which will overprint other page contents according to
     * the graphics state set
     */
    $p->setfont($font, 36);
    $p->setcolor("fill", "cmyk", 0, 0, 0, 1);
    $p->set_text_pos(20, 200);
    $p->show("overprinting text");
    
    /* Restore the current graphics state */
    $p->restore();
	   
    /* Show some text. It will not be overprinting since the old graphics
     * state has been restored with no overprinting set.
     */
    $p->setfont($font, 36);
    $p->setcolor("fill", "cmyk", 0, 0, 0, 1);
    $p->set_text_pos(20, 100);
    $p->show("text not overprinting");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=overprinting_text.pdf");
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
