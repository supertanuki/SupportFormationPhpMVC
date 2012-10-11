<?php
/* $Id: rotated_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Rotated text:
 * Create rotated text lines and Textflows which do not run horizontally,
 * but at some angle
 *
 * Three text lines are generated which are oriented to the west, east, or
 * south. A text line is rotated by 30 degrees. A Textflow is created which
 * is rotated by 30 degrees. A Textflow is generated with a rotation of 30
 * degrees and an orientation to the west.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Rotated Text";

$tf = 0;
$optlist =
    "fontname=Helvetica fontsize=14 encoding=unicode leading=120% charref";

$textflow =
    "To fold the famous rocket looper proceed as follows:\n" .
    "Take a DIN A4 sheet. Fold it lenghtwise in the middle. Then, fold " .
    "the upper corners down. Fold the long sides inwards that the points " .
    "A and B meet on the central fold. Fold the points C and D that the " .
    "upper corners meet with the central fold as well. Fold the plane in " .
    "the middle. Fold the wings down that they close with the lower " .
    "border of the plane.";

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

    /* Place a text line with an orientation of west, east, or south */
    $p->setfont($font, 24);
    $p->fit_textline("The famous rocket looper", 100, 500, "orientate=west");

    $p->setfont($font, 20);
    $p->fit_textline("The famous rocket looper", 200, 500, "orientate=east");

    $p->setfont($font, 16);
    $p->fit_textline("The famous rocket looper", 300, 500, "orientate=south");

    /* Rotate a text line by 30 degrees */
    $p->setfont($font, 16);
    $p->fit_textline("The famous rocket looper", 50, 350, "rotate=30");

    /* Place a Textflow with a rotation of 30 degrees */
    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 250, 50, 400, 400, "rotate=30");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }
    $p->delete_textflow($tf);

    $p->end_page_ext("");

    /* Page 2 */
    $p->begin_page_ext(595, 842, "");

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Set the stroke color to red */
    $p->setcolor("stroke", "rgb", 1, 0, 0, 0);

    /* Place a Textflow with a rotation of 30 degrees. Visualize the fitbox
     * of the Textflow with the "showborder" option */
    $tf = 0;
    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 150, 450, 400, 650, "showborder rotate=30");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }
    $p->delete_textflow($tf);

    $p->fit_textline("rotate=30", 300, 500, "font=" . $font . " fontsize=14");

    /* Now, place the Textflow with a rotation of 30 degrees and orientate
     * it to the west
     */
    $tf = 0;
    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 150, 100, 400, 300,
	"showborder rotate=30 orientate west");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }
    $p->delete_textflow($tf);

    $p->fit_textline("rotate=30  orientate=west", 300, 150, "font=" . $font .
	" fontsize=14");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=frame_around_image.pdf");
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
