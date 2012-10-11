<?php
/* $Id: metric_topdown_coordinates.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Metric topdown coordinates:
 * Output text using metric coordinates in a topdown coordinate system
 * 
 * Use topdown coordinates starting from the top left instead from the bottom
 * left, scale the coordinate system to use metric coordinates, and place some
 * horizontal lines 1 cm from each other.
 *  
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Metric Topdown Coordinates";


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
    
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Use topdown coordinates */
    $p->set_parameter("topdown", "true");
    
    /* Start an A4 page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Scale the coordinate system to use metric coordinates.
     * By default, PDFlib will expect incoming coordinates in DTP points 
     * which are defined as: 1 pt = 1/72 inch = 25.4/72 mm = 0.3528 mm.
     * In order to supply the coordinates in centimeters instead of points
     * we have to calculate the scaling factor as follows:
     * (72 points/inch) / (2.54 cm/inch) = 28.3465 points/cm.
     */
    $p->scale(28.3465, 28.3465);
    
    /* Now, PDFlib will interpret all coordinates (except for interactive
     * features, see below) in centimeters. The scaling is only in effect
     * for the current page, and must be repeated on each page (if so
     * desired).
     */
    
    /* Interpret all coordinates for hypertext elements in the coordinates
     * defined above as well
     */
    $p->set_parameter("usercoordinates", "true");
    
    /* Place some horizontal lines 1 cm from each other. 
     * With the third line, draw a rectangle.
     */
    $p->setlinewidth(0.01);
 
    for ($i = 1; $i < 29; $i++) {
	$p->moveto(0, $i);
	$p->lineto(1, $i);
	if ($i == 3) {
	    $p->rect(0, $i, 1, 1);
	}
	    $p->fill_stroke();
    }
    
    /* Set the font size in cm */
    $p->setfont($font, 0.5);
    
    $p->fit_textline("Centimeters from the top left instead of points from " .
	"the bottom left:", 2, 5, "");
    $p->fit_textline("The small lines on the left are placed 1 cm from " .
	"each other.", 2, 6, "");
    $p->fit_textline("This text line is displayed at 7 cm from top and 2 " .
	"cm from the left.", 2, 7, "");
    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=metric_topdown_coordinates.pdf");
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
