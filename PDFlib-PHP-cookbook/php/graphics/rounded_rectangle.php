<?php
/* $Id: rounded_rectangle.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Rounded rectangles:
 * Create some rectangle with the corners being rounded
 *
 * Create two simple rectangles using the linejoin parameter for rounded
 * corners. Define a path for a rectangle with corners rounded by a given
 * radius. Create three rhombuses using the linejoin and linecap parameters for
 * rounded corners.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Rounded Rectangle";

$x = 50; $y = 100; $radius = 50; $width = 400; $height = 300;

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

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Start page 1 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    $p->setlinewidth(15.0);

    /* Red rectangle */
    $p->setcolor("stroke", "rgb", 1.0, 0.0, 0.0, 0.0);
    $p->rect(100, 700, 100, 100);
    $p->stroke();
    $p->fit_textline("Simple rectangle", 250, 750, "font=" . $font .
	" fontsize=16");

    /* Red rectangle with the "linejoin" parameter set */
    $p->setlinejoin(1);
    $p->rect(100, 500, 100, 100);
    $p->stroke();
    $p->fit_textline("Simple rectangle with linejoin=1", 250, 550,
	"font=" . $font . " fontsize=16");

    /* Define a path for a rectangle with corners rounded by a given radius.
     * Start from the lower left corner and proceed counterclockwise.
     */
    $p->moveto($x + $radius, $y);
    /* Start of the arc segment in the lower right corner */
    $p->lineto($x + $width - $radius, $y);
    /* Arc segment in the lower right corner */
    $p->arc($x + $width - $radius, $y + $radius, $radius, 270, 360);
    /* Start of the arc segment in the upper right corner */
    $p->lineto($x + $width, $y + $height - $radius );
    /* Arc segment in the upper right corner */
    $p->arc($x + $width - $radius, $y + $height - $radius, $radius, 0, 90);
    /* Start of the arc segment in the upper left corner */
    $p->lineto($x + $radius, $y + $height);
    /* Arc segment in the upper left corner */
    $p->arc($x + $radius, $y + $height - $radius, $radius, 90, 180);
    /* Start of the arc segment in the lower left corner */
    $p->lineto($x , $y + $radius);
    /* Arc segment in the lower left corner */
    $p->arc($x + $radius, $y + $radius, $radius, 180, 270);

    $p->stroke();

    $p->fit_textline("Rectangle with corners rounded by a defined radius",
	70, 60, "font=" . $font . " fontsize=16");

    /* Reset the linejoin parameter */
    $p->setlinejoin(0);

    $p->end_page_ext("");

    /* Start page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    $p->setlinewidth(15.0);

    /* Blue rhombus without the "linejoin" parameter set */
    $p->setcolor("stroke", "rgb", 0.0, 0.0, 1.0, 0.0);
    $p->moveto(100, 700);     // left corner
    $p->lineto(200, 800);     // upper corner
    $p->lineto(300, 700);     // right corner
    $p->lineto(200, 600);     // lower corner
    $p->lineto(100, 700);     // left corner
    $p->stroke();
    $p->fit_textline("Rhombus", 320, 695, "font=" . $font . " fontsize=16");

    /* Blue rhombus with the "linejoin" parameter set */
    $p->setlinejoin(1);
    $p->moveto(100, 450);     // left corner
    $p->lineto(200, 550);     // upper corner
    $p->lineto(300, 450);     // right corner
    $p->lineto(200, 350);     // lower corner
    $p->lineto(100, 450);     // left corner
    $p->stroke();
    $p->fit_textline("Rhombus with linejoin=1", 320, 445, "font=" .
	$font . " fontsize=16");

    /* blue rhombus with linejoin and linecap parameter */
    $p->setlinecap(1);
    $p->moveto(100, 200);     // left corner
    $p->lineto(200, 300);     // upper corner
    $p->lineto(300, 200);     // right corner
    $p->lineto(200, 100);     // lower corner
    $p->lineto(100, 200);     // left corner
    $p->stroke();
    $p->fit_textline("Rhombus with linejoin=1", 320, 195, "font=" . $font .
	" fontsize=16");
    $p->fit_textline("and linecap=1", 320, 175, "font=" . $font .
	" fontsize=16");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=rounded_rectangle.pdf");
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
