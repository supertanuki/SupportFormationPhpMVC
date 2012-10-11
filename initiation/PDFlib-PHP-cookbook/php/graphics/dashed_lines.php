<?php
/* $Id: dashed_lines.php,v 1.3 2012/05/03 14:00:37 stm Exp $
* Dashed lines:
* Create some dash patterns to be used as line styles
*
* Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
* Required data: none
*/

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Dashed Lines";

$startx = 10; $starty = 200; $x = 200; $y = 200;

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

    $p->begin_page_ext(250, 250, "");

    /* Set the drawing properties for the dash patterns */
    $p->setlinewidth(0.6);

    /* Set the first dash pattern with a dash and gap length of 3 */
    $p->setdashpattern("dasharray={3 3}");

    /* Stroke a line with that pattern */
    $p->moveto($startx, $starty);
    $p->lineto($x, $y);
    $p->stroke();

    /* Set the second dash pattern with a dash length of 3 and
     * a gap length of 6
     */
    $p->setdashpattern("dasharray={3 6}");

    /* Stroke a line with that pattern */
    $p->moveto($startx, $starty-20);
    $p->lineto($x, $y-20);
    $p->stroke();

    /* Set the third dash pattern with a dash length of 6 and
     * a gap length of 3
     */
    $p->setdashpattern("dasharray={6 3}");

    /* Stroke a line with that pattern */
    $p->moveto($startx, $starty-40);
    $p->lineto($x, $y-40);
    $p->stroke();

    /* Set the fourth dash pattern with a dash length of 5 and
     * a gap length of 3, then a dash of 0.5 and a gap of 3, then a dash
     * of 3 and a gap of 3, then a dash of 0.0 and a gap of 3 and so forth
     */
    $p->setdashpattern("dasharray={5 3 0.5 3 3 3 0.5 3}");
    /* Stroke a line with that pattern */
    $p->moveto($startx, $starty-60);
    $p->lineto($x, $y-60);
    $p->stroke();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=dashed_lines.pdf");
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
