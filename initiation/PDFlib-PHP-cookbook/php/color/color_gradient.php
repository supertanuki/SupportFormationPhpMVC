<?php
/* $Id: color_gradient.php,v 1.3 2012/05/03 14:00:39 stm Exp $
 * Color gradient:
 * Fill some area or text with a smooth transition from one color to another

 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Color Gradient";


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start Page */
    $p->begin_page_ext(595, 842, "");

    /* Fill the page completely with a gradient from green to black:
     * Set the first color for the gradient to green
     */
    $p->setcolor("fillstroke", "rgb", 0.0, 0.5, 0.5, 0.0);

    /* Set the second color for the gradient to black;
     * define an axial gradient with a size similar to the the page size
     */
    $sh = $p->shading("axial",  0, 0, 595, 842, 0.0, 0.0, 0.0, 0.0, "");

    /* Draw the gradient */
    $p->shfill($sh);

    /* Fill a rectangle with a gradient:
     * Save the current graphics state
     */
    $p->save();

    /* Set the first color for the gradient to orange */
    $p->setcolor("fill", "rgb", 1.0, 0.5, 0.1, 0.0);

    /* Set the second color for the gradient to a light orange;
     * define an axial gradient with a size similar to the size
     * of the rectangle to be filled
     */
    $sh = $p->shading("axial",  200, 200, 450, 450, 0.9, 0.8, 0.8, 0.0, "");

    /* Draw a rectangle and set the clipping path to
     * the shape of the rectangle
     */
    $p->rect(200, 200, 250, 250);
    $p->clip();

    /* Fill the clipping path with the gradient */
    $p->shfill($sh);

    /* Restore the current graphics state to reset the clipping path */
    $p->restore();

    /* Fill a circle with a gradient:
     * Save the current graphics state
     */
    $p->save();

    /* Set the first color for the gradient to white */
    $p->setcolor("fill", "rgb", 1.0, 1.0, 1.0, 0.0);

    /* Set the second color for the gradient to orange;
     * define a radial gradient with a size similar to the size
     * of the circle to be filled
     */
    $sh = $p->shading("radial", 400, 600, 400, 600, 1.0, 0.5, 0.1, 0.0,
	"r0 0 r1 60");

    /* Draw a circle and set the clipping path to
     * the shape of the circle
     */
    $p->circle(400, 600, 50);
    $p->clip();

    /* Fill the clipping path with the gradient */
    $p->shfill($sh);

    /* Restore the current graphics state to reset the clipping path */
    $p->restore();

    /* Fill a text with a gradient:
     * Load the font; for PDFlib Lite: change "unicode" to "winansi"
     */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 36);

    /* Set the first color for the gradient to white */
    $p->setcolor("fill", "rgb", 1, 1, 1, 0);

    /* Set the second color for the gradient to orange; define an axial
     * gradient with a size similar to the size of the text to be filled
     */
    $sh = $p->shading("axial", 50, 50, 50, 200, 1.0, 0.5, 0.1, 0.0, "");

    /* Create a shading pattern from the shading and set the
     * pattern as the current fill color
     */
    $pattern = $p->shading_pattern($sh, "");
    $p->setcolor("fill", "pattern", $pattern, 0, 0, 0);

    /* Output the text with the gradient as current fill color */
    $p->show_xy("Hello World!", 50, 100);
    $p->continue_text("(says PDFlib GmbH)");

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=color_gradient.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }


$p = 0;
?>
