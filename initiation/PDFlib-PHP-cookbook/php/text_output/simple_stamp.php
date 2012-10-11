<?php
/* $Id: simple_stamp.php,v 1.2 2012/05/03 14:00:38 stm Exp $p->java,v 1.16 2007/10/30 16:16:33 katja Exp $
 * Simple stamp:
 * Create a stamp across the page which runs diagonally from one edge to the
 * other
 *
 * Place a text line as outlines like a stamp in the background of a Textflow.
 * Place a text line like a stamp with a certain opacity on an image.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Simple Stamp";

$imagefile = "nesrin.jpg";
$tf = 0;
$llx=50; $lly=50;

$optlist =
    "fontname=Helvetica fontsize=24 encoding=unicode leading=120% charref";

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

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Page 1 */
    $p->begin_page_ext(595, 842, "");

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Fit the text line as outlines like a stamp in the specified box. The
     * stamp will be placed diagonally from the upper left to the lower
     * right (stamp=ul2lr).
     */
    $p->fit_textline("The Famous Rocket Looper", $llx, $lly, "font=" . $font .
	" strokecolor={rgb 1 0 0} textrendering=1 boxsize={500 750} " .
	"strokecolor={rgb 0.5 0 1} stamp=ul2lr");

    /* Place the Textflow on top of the stamp */
    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 100, 100, 400, 700, "");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }

    $p->end_page_ext("");

    /* Page 2 */
    $p->begin_page_ext(350, 220, "");

    /* Load and place the image first to have it in the background
    */
    $image = $p->load_image("auto", $imagefile, "");

    if ($image == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, 0, 0, "scale=0.5");

    /* Save the current graphics state */
    $p->save();

    /* Set the opacity in the current graphics state,
     * for the stamp to appear slightly transparent
     */
    $gstate = $p->create_gstate("opacityfill=0.5");
    $p->set_gstate($gstate);

    /* Fit the text line as outlines like a stamp in the specified box.
     * The stamp will be place diagonally from the lower left to the upper
     * right (stamp=ll2ur). It will be drawn in a transparent white 
     * according to the opacity set for the current graphics state.
     */
    $p->fit_textline("Our Test Image", 30, 10, "font=" . $font .
	" fillcolor={rgb 1 1 1} boxsize={300 200} fontsize=1 stamp=ll2ur");

    $p->restore();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=simple_stamp.pdf");
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
