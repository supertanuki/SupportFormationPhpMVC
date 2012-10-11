<?php
/* $Id: transparent_images.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Transparent images:
 * Create transparent images
 *
 * Display a colored background rectangle. Output two transparent background
 * images over that rectangle, then output a non-transparent image in the
 * foreground. Create transparency by applying an extended graphics
 * state with the "opacityfill" option set to a value less than 1.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Transparent Images";

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
    $p->set_info("Title", $title);
    
    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Start page 1 */
    $p->begin_page_ext(842, 595, "");
    
    /* Draw a yellow background rectangle */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->rect(0, 0, 421, 595);
    $p->fill();
    
    /* Display some descriptive text */
    $p->setcolor("fill", "gray", 0, 0, 0, 0);
    $p->fit_textline("Two transparent background images with a " .
	"non-transparent image placed in the foreground", 50, 530, "font=" .
	$font . " fontsize=18");
    
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
    
    /* Display two images which will be transparent according to the
     * graphics state set.
     */
    $p->fit_image($image, 0, 0, "boxsize={842 595} position={center bottom}");
    $p->fit_image($image, 0, 0, "boxsize={842 595} position={center bottom} " .
	"scale=0.7");
      
    /* Restore the current graphics state */
    $p->restore();
    
    /* Display the image again. It will not be transparent since the old
     * graphics state has been restored with no transparency set.
     */ 
    $p->fit_image($image, 0, 0, "boxsize={842 595} position={center bottom} " .
	"scale=0.35");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=transparent_images.pdf");
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

