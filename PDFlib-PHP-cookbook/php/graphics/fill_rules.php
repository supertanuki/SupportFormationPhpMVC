<?php
/* $Id: fill_rules.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * Fill Rules:
 * Define some overlapping vector graphics and fill them using
 * various methods
 * 
 * Use the default "nonzero winding number" rule to fill overlapping objects and
 * create a ring, for example.
 * Use the "evenodd" fill rule to make overlapping objects being
 * filled alternately. 
 * 
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Fill Rules";


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

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    

    /* --------------------------------------------------------------------
     * Case I: The default "nonzero winding number" rule will be used. All
     * defined objects will be filled with one $p->fill() call. In this case
     * (without any coordinate transformation being performed), overlapping
     * objects which are drawn counterclockwise will be filled with the
     * current fill color while all objects drawn in clockwise direction
     * won't be filled. Note that $p->circle() and $p->rect() functions define
     * objects which will be drawn counterclockwise.
     * --------------------------------------------------------------------
     */
    
    /* Start Page 1 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Set the color to purple */
    $p->setcolor("fill", "rgb", 1.0, 0.0, 0.5, 0.0);
    
    /* Define a big arc segment of 360 degrees in counterclockwise 
     * direction. Move to the starting point of the arc segment first
     */
    $p->moveto(450, 600);
    $p->arc(300, 600, 150, 0, 360);
    
    /* Define a small arc segment of 360 degrees in counterclockwise
     * direction. Move to the starting point of the arc segment first
     */
    $p->moveto(400, 600);
    $p->arc(300, 600, 100, 0, 360);
    
    /* Define a big arc segment of 360 degrees in clockwise direction.
     * Move to the starting point of the arc segment first
     */
    $p->moveto(450, 200);
    $p->arcn(300, 200, 150, 360, 0);
    
    /* Define a small arc segment of 360 degrees in counterclockwise
     * direction. Move to the starting point of the arc segment first
     */
    $p->moveto(400, 200);
    $p->arc(300, 200, 100, 0, 360);
    
    $p->fill_stroke();
    
    /* Set the color to black */
    $p->setcolor("fill", "gray", 0, 0.0, 0, 0.0);
    
    $p->setfont($font, 14);
    
    $p->show_xy("With the default \"nonzero winding number\" rule:", 20, 790);
    $p->show_xy("Stroke a big and a small arc segment counterclockwise " .
	"and fill them with purple", 20, 770);
    
    $p->show_xy("With the default \"nonzero winding number\" rule:", 20, 400);
    $p->show_xy("Stroke a big arc segment clockwise and", 20, 380);
    $p->show_xy("a small arc segment counterclockwise and fill them with " .
	"purple", 20, 360);
    
    $p->end_page_ext("");
    
    
    /* -----------------------------------------------------------------
     * Case II: The fill rule of "evenodd" will be used. All defined
     * objects will be filled with one $p->fill() call. In this case, 
     * overlapping objects will be drawn according to the "evenodd" rule
     * and filled alternately.
     * -----------------------------------------------------------------
     */
    
    /* Start Page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $p->set_parameter("fillrule", "evenodd");
    
    /* Set the color to purple */
    $p->setcolor("fill", "rgb", 1.0, 0.0, 0.5, 0.0);
    
    /* Define a rectangle */
    $p->rect(100, 100, 400, 300);
    
    /* Define a big circle */
    $p->circle(300, 500, 200);
 
    /* Define an arc segment */
    $p->moveto(300, 750);
    $p->lineto(300, 500);
    $p->arc(300, 500, 250, 0, 90);
    
    /* Define a small circle */
    $p->circle(300, 600, 50);
    
    $p->fill_stroke();
    
    /* Set the color to black */
    $p->setcolor("fill", "gray", 0, 0.0, 0, 0.0);
    
    $p->setfont($font, 14);
    
    $p->show_xy("With the \"evenodd\" fill rule: Draw a rectangle, a big " .
	"circle,", 20, 810);
    $p->show_xy("an arc segment, and a small circle and fill them with " .
	"purple", 20, 790);
    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fill_rules.pdf");
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
