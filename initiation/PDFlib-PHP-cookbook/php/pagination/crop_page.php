<?php
/* $Id: crop_page.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Crop page:
 * Crop an A4 page to an A5 page
 *
 * Create a page with A4 format and Portrait orientation and crop it to an A5 page.
 * Open the A5 page with the same zoom as chosen for the A4 page to fit completely
 * into the window.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Crop Page";


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

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    

    /* ----------------------------------
     * Create the first page in A4 format
     * ----------------------------------
     */
    $p->begin_page_ext(595, 842, "");
    
    /* Draw a violet rectangle in A5 format */
    $p->setcolor("fill", "rgb", 0.7, 0, 0.6, 0);
    $p->rect(87, 123, 421, 595);
    $p->fill();
    
    /* Output some explanatory text */
    $p->fit_textline("A4 page uncropped", 50, 50, 
	"font=" . $font . " fontsize=20  fillcolor={gray 0}");
    
    /* Create a "GoTo" action for opening the current page (page=0) with
     * a zoom to completely fit it into the window (type=fitwindow) 
     */
    $optlist = "destination={page=0 type=fitwindow}";
    $action = $p->create_action("GoTo", $optlist);
    
    /* Finish the page with the "GoTo" action being applied. When the page
     * is opened in the viewer it will completely fit into the window.
     */
    $p->end_page_ext("action={open=" . $action . "}");
    
    
    /* -------------------------------------------------------------
    /* Create the second page in A4 format and crop it to an A5 page
     * -------------------------------------------------------------
     */
    $p->begin_page_ext(595, 842, "");
    
    /* Draw a violet rectangle in A5 format */
    $p->setcolor("fill", "rgb", 0.7, 0, 0.6, 0);
    $p->rect(87, 123, 421, 595);
    $p->fill();
    
    /* Output some explanatory text */
    $p->fit_textline("A4 page cropped to A5", 95, 140, 
	"font=" . $font . " fontsize=20 fillcolor={gray 0}");
    
    /* Create a "GoTo" action for opening the current page with the same
     * zoom as chosen for the A4 page. This is accomplished by supplying
     * the size of the A4 page as the rectangle to completely fit into
     * the window.
     */
    $optlist = "destination={page=0 type=fitrect left=0 bottom=0 " .
	"right=595 top=842}";
    $action = $p->create_action("GoTo", $optlist);
    
    /* Finish the page and crop it to the A5 format. In addition, the
     * "GoTo" action is applied. When the page is opened in the viewer it
     * will be displayed with the same zoom as the A4 page.
     */
    $p->end_page_ext("cropbox={87 123 508 718} action={open=" . $action . "}");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=crop_page.pdf");
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
