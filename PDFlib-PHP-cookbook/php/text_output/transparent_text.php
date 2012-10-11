<?php
/* $Id: transparent_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Transparent text:
 * Create some transparent text
 *
 * Display a background image and show transparent text in the foreground.
 * Create transparency by applying an extended graphics state with the
 * "opacityfill" option set to a value less than 1.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Transparent Text";

$imagefile = "nesrin.jpg";

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
    
    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(842, 595, "");
    
    
    /* Draw a yellow background rectangle */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->rect(0, 0, 842, 595);
    $p->fill(); 
    
    /* Output a background image */
    $p->fit_image($image, 50, 50, "");
    
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
    
    /* Display some red text which will be transparent according to the
     * graphics state set
     */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->fit_textline("My favorite holiday photo", 100, 100, "font=" . $font .
	" fontsize=50");
    
    /* Restore the current graphics state */
    $p->restore();
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=transparent_part_of_text.pdf");
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
