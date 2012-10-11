<?php
/* $Id: fill_pattern.php,v 1.3 2012/05/03 14:00:37 stm Exp $
 * Fill pattern:
 * Define some hatching patterns and use them to fill arbitrary shapes.
 * 
 * Create a green pattern with a hatching of rising lines and fill a rectangle
 * with it. Create pattern with a hatching of falling lines and fill a
 * rectangle with it using the colors currently being set. Create a colored pie
 * chart using the two hatchings and various stroke colors. Create a more
 * complex hatching pattern and fill a circle with it.
 * 
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Fill Pattern";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    
    /* -----------------------------------------------------------------
     * Define Pattern I, a green pattern with a hatching of rising lines
     * -----------------------------------------------------------------
     * 
     * Define a pattern with a width of w and a height of h. When
     * repeatedly placing the pattern an x-offset of w and an y-offset
     * of h is used. The stroke color is defined within the pattern
     * (painttype=1).
     */
    $w = 10;
    $h = 10;
    $pattern1 = $p->begin_pattern($w, $h, $w, $h, 1);

    /* Set the stroke color for the pattern to green.
     * Draw three lines with an angle of sin(h/w). For a smooth transition
     * from one line segment to the adjacent when output the pattern 
     * repeatedly: First, set the line cap to beyond the line end. Second, 
     * stroke not one but three line elements with a distance of w between
     * each other (while having a pattern's x-offset of w).
     */
    $p->setlinewidth(0.5);
    $p->setlinecap(2);
    $p->setcolor("stroke", "rgb", 0.0, 0.5, 0.5, 0);
    
    $p->moveto(0, 0);
    $p->lineto($w, $h);
    
    $p->moveto($w, 0);
    $p->lineto(2*$w, $h);
    
    $p->moveto(-$w, 0);
    $p->lineto(0, $h);
    
    $p->stroke();

    $p->end_pattern();
    
    
    /* --------------------------------------------------
     * Define Pattern II with a hatching of falling lines
     * --------------------------------------------------
     * 
     * Define a pattern with a width of w and a height of h. When
     * repeatedly placing the pattern an x-offset of w and an y-offset
     * of h is used. The pattern will have no color on its own but will
     * be colorized later by the fill color which is set at the time the 
     * pattern will be used (painttype=2).
     */
    $w = 10;
    $h = 10;
    $pattern2 = $p->begin_pattern($w, $h, $w, $h, 2);

    /* Draw three lines with an angle of -sin(h/w). For a smooth transition
     * from one line segment to the adjacent when output the pattern 
     * repeatedly: First, set the line cap to beyond the line end. Second, 
     * stroke not one but three line elements with a distance of w between
     * each other (while having a pattern's x-offset of w).
     */
    
    /* Set the stroke line width for the pattern */
    $p->setlinewidth(0.5);
    
    /* Set the line cap beyond the line ends */
    $p->setlinecap(2);
    
    /* Define and stroke the path for the pattern */
    $p->moveto(0, $h);
    $p->lineto($w, 0);
    
    $p->moveto($w, $h);
    $p->lineto(2*$w, 0);
    
    $p->moveto(-$w, $h);
    $p->lineto(0, 0);
    
    $p->stroke();

    $p->end_pattern();
    
    
    /* ---------------------------------------------------------------
     * Define Pattern III, a more complex light green hatching pattern
     * ---------------------------------------------------------------
     * 
     * The stroke color is defined within the pattern (painttype=1) as a
     * shade of green.
     */
    $w = 5;
    $h = 10;
    $pattern3 = $p->begin_pattern($w, $h, $w, $h, 1);

    /* Define a custom color for the pattern */
    $p->setcolor("stroke", "rgb", 0.4, 0.5, 0.2, 0);
    $p->setlinewidth(0.5);
    
    /* Set the line cap beyond the line end */
    $p->setlinecap(2);
    
    /* Define and stroke the path for the pattern to be used */
    $p->moveto(0, 0);
    $p->lineto($w, $h / 2);
    $p->lineto(0, $h);
    $p->stroke();

    $p->moveto(0, $h / 2);
    $p->lineto($w / 2, $h / 4);
    $p->stroke();

    $p->moveto($w, $h);
    $p->lineto($w / 2, 3 * $h / 4);
    $p->stroke();

    $p->end_pattern();
    
    $p->begin_page_ext(500, 500, "");
    
    
    /* ------------------------------
     * Output graphics with pattern I
     * ------------------------------
     * 
     * Set the stroke color to black.
     * Set the green pattern I as the fill color.
     */
    $p->setcolor("stroke", "gray", 0.0, 0.0, 0.0, 0);
    $p->setcolor("fill", "pattern", $pattern1, 0, 0, 0);
    
    /* Draw a rectangle with the current fill color, i.e. filled with 
     * pattern I repeatedly applied. The rectangle will have a border
     * colored with the current stroke color which as been set to black 
     * above.
     */
    $p->rect(50, 300, 150, 100);
    $p->fill_stroke();
   
    /* -------------------------------
     * Output graphics with pattern II
     * -------------------------------
     * 
     * Set the fill and stroke color to light red. 
     * Set pattern II as the fill color. Since pattern II has no inherent 
     * stroke color it will use the stroke color currently being set. 
     */
    $p->setcolor("fillstroke", "rgb", 1.0, 0.5, 0.5, 0);
    $p->setcolor("fill", "pattern", $pattern2, 0, 0, 0);
    
    /* Draw a rectangle with the current fill color, i.e. filled with 
     * pattern II repeatedly applied. Set the line width of the rectangle
     * borders to the line width of the pattern
     */
    $p->setlinewidth(0.5);
    $p->rect(250, 300, 150, 100);
    $p->fill_stroke();
    
    /* Set the current stroke color to green. Draw an arc segment
     * as part of a pie chart. The arc will be filled with the green 
     * pattern II set as current fill color above. The borders will be
     * stroked with the stroke color currently being set to green.
     */
    $p->setlinewidth(1);
    $p->setcolor("stroke", "rgb", 0.0, 0.5, 0.5, 0);
    $p->moveto(100, 150);
    $p->lineto(100, 200);
    $p->arcn(100, 150, 50, 90, 120);
    $p->closepath_fill_stroke();
    
    /* Set pattern I as the current fill color */
    $p->setcolor("fill", "pattern", $pattern1, 0, 0, 0);
    
    /* Set the current stroke color to light red */
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 0.5, 0);
    
    /* Draw another smaller arc segment as part of the pie chart */
    $p->moveto(100, 150);
    $p->lineto(100, 200);
    $p->arc(100, 150, 50, 90, 120);
    $p->closepath_fill_stroke();

 
    /* --------------------------------
     * Output graphics with pattern III
     * --------------------------------
     * 
     * Set pattern III as the fill color
     */
    $p->setcolor("fill", "pattern", $pattern3, 0, 0, 0);
    
    /* Draw a circle with the current fill color, i.e. filled with 
     * the light green pattern III repeatedly being applied
     */
    $p->circle(350, 150, 100);
    $p->fill();
    
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fill_pattern.pdf");
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
