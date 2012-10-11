<?php
/* $Id: text_on_color.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Text on colored background:
 * Place a text line and a Textflow on a colored background
 *
 * Place a text line on a colored background using info_textline() to get the
 * dimensions of the text line. Place a text line using the "matchbox" option
 * which provides a more versatile way for text calculations.
 * Fit a Textflow on a colored background. Use fit_textflow() in "blind" mode
 * and info_textflow() to calculate the dimensions of the Textflow without any
 * output generated. Use fit_textflow() with the "rewind" option to actually
 * fit the Textflow.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Text on colored Background";

$tf = 0; $offset = 30; $x = 50;

$textline = "Giant Wing Paper Plane";

$textflow =
    "To fold the famous rocket looper proceed as follows:\nTake a A4 " .
    "sheet. Fold it lenghtwise in the middle. Then, fold the upper " .
    "corners down. Fold the long sides inwards that the points A and B " .
    "meet on the central fold. Fold the points C and D that the upper " .
    "corners meet with the central fold as well. Fold the plane in the " .
    "middle. Fold the wings down that they close with the lower border " .
    "of the plane.";

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

    /* Start Page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Load font */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* --------- Text line: Method I -------- */

    /* Place a text line with a colored background using info_textline()
     * for retrieving the width and height of the default box for the
     * text. The height will be the capheight of the font by default. To
     * use another font property use the "matchbox" option as shown below.
     */
    $optlist = "font=" . $font . " fontsize=40 " . "fillcolor={gray 1}";
    $width = $p->info_textline($textline, "width", $optlist);
    $height = $p->info_textline($textline, "height", $optlist);

    /* Draw a rectangle with exactly the retrieved width and height */
    $p->setcolor("fill", "rgb", 0.0, 0.8, 0.8, 0);
    $p->rect($x, 700, $width, $height);
    $p->fill();

    /* Place the text line on the rectangle */
    $p->fit_textline($textline, $x, 700, $optlist);

    /* --------- Text line: Method II -------- */

    /* Place a text line with a colored background in a single
     * fit_textline() call. The "matchbox" option is used to specify the
     * rectangle to be colored. The "boxheight" suboption defines the height
     * of the matchbox while "offsetbottom" and "offsettop" adds some space
     * at the top and bottom. The width of the matchbox is specified by the
     * text width plus the space defined by "offsetleft" and "offsetright".
     */
    $optlist =
	"font=" . $font . " fontsize=40 " . "fillcolor={gray 1} " .
	"matchbox={fillcolor={rgb 0 0.8 0.8} " .
	"boxheight={ascender descender}}";

    /* Place the text line */
    $p->fit_textline($textline, $x, 600, $optlist);

    /* To increase the matchbox beyond the text, use "offsetbottom" and
     * "offsettop" to add some space at the top and bottom. Use "offsetleft"
     * and "offsetright" to add some space to the left and right.
     */
    $optlist =
	"font=" . $font . " fontsize=40 " . "fillcolor={gray 1} " .
	"matchbox={fillcolor={rgb 0 0.8 0.8} " .
	"boxheight={ascender descender} " .
	"offsetleft=-8 offsetright=8 offsettop=8 offsetbottom=-8}";

    /* Place the text line */
    $p->fit_textline($textline, $x, 500, $optlist);

    /* --------- Textflow on a colored background -------- */

    /* Place a white Textflow on a green background. To get the dimensions
     * of the background rectangle, first fit the Textflow in "blind" mode.
     * All calculations will be done but no Textflow will be placed in the
     * output file.
     * Then, retrieve the width and height of the Textflow and use them to
     * draw the background rectangle. Finally, rewind the Textflow state to
     * before the last (blind mode) call to fit_textflow() and fit the
     * Textflow.
     */
    $optlist = "font=" . $font .  " fontsize=20 fillcolor={gray 1}";

    $tf = $p->add_textflow($tf, $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Fit the Textflow in "blind" mode. All calculations will be done but
     * the textflow will not actually be placed.
     */
    $result = $p->fit_textflow($tf, 0, 0, 500, 500, "blind");
    if (!$result == "_stop")
    {
	/* Check for errors or more text to be placed */
    }

    /* Get the width and height of the Textflow placed */
    $width = $p->info_textflow($tf, "textwidth");
    $height = $p->info_textflow($tf, "textheight");

    /* Draw a rectangle with the retrieved width and height */
    $y = 270;
    $p->setcolor("fill", "rgb", 0.0, 0.8, 0.8, 0.0);
    $p->rect($x, $y, $width, $height);
    $p->fill();

    /* Now actually fit the textflow. To rewind the Textflow
     * status to before the last call to fit_textflow() use "rewind=-1".
     */
    $result = $p->fit_textflow($tf, $x, $y, $x+$width, $y+$height, "rewind=-1");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }

    /* Draw a rectangle with the retrieved dimensions plus a defined
     * offset for some empty space around the text.
     */
    $y = 50;
    $p->setcolor("fill", "rgb", 0.0, 0.8, 0.8, 0.0);
    $p->rect($x-$offset/2, $y-$offset/2, $width+$offset, $height+$offset);
    $p->fill();

    /* Fit the Textflow a second time while considering the offset. To
     * rewind the Textflow status to before the last call to
     * fit_textflow() use "rewind=-1".
     */
    $result = $p->fit_textflow($tf, $x, $y, $x+$width, $y+$height, "rewind=-1");
    if ($result != "_stop")
    {
	/* Check for errors or more text to be placed */
    }

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=text_on_color.pdf");
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
