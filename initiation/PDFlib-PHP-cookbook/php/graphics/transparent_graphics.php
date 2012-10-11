<?php
/* $Id: transparent_graphics.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Transparent graphics:
 * Create some transparent graphics objects
 *
 * Display a yellow rectangle and show some transparent graphics objects
 * overlapping with it. Create transparency by applying an extended graphics
 * state with the "opacityfill" option set to a value less than 1.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Transparent Graphics";

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
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Draw a yellow background rectangle */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->rect(50, 50, 500, 750);
    $p->fill();
    
    /* Display some descriptive red text */
    $p->setcolor("fill", "rgb", 0.73, 0.12, 0.30, 0);
    $p->fit_textline("Transparent vector graphics", 100, 100, "font=" . $font .
	" fontsize=20");
    
    /* Save the current graphics state. The save/restore of the current
     * state is not necessarily required, but it will help you get back to
     * a graphics state without any transparency.
     */
    $p->save();
    
    /* Create an extended graphics state with transparency set to 50%, using
     * create_gstate() with the "opacityfill" option set to 0.5.
     */
    $gstate = $p->create_gstate("opacityfill=.5");
    
    /* Apply the extended graphics state */
    $p->set_gstate($gstate);
    
    /* Draw a blue rectangle which will be transparent according to the
     * graphics state set
     */
    $p->setcolor("fill", "rgb", 0, 0.52, 0.64, 0);
    $p->rect(100, 500, 200, 600);
    $p->fill();
    
    /* Draw a red circle which will be transparent according to the
     * graphics state set
     */
    $p->setcolor("fill", "rgb", 0.73, 0.12, 0.30, 0);
    $p->arc(100, 100, 200, 0, 360);
    $p->fill();
    
    /* Restore the current graphics state */
    $p->restore();
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=transparent_graphics.pdf");
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
