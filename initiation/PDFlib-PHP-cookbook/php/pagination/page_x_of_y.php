<?php
/* $Id: page_x_of_y.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Create page X of Y:
 * Create a running footer "Page x of y" on each page of the document
 *
 * On each page a running header or footer "Page x of y" is required where x is
 * the current page number and y is the total number of pages in the document.
 * Since y is only known after creating all pages, the number y must be added
 * to each page after creating the bulk of the document.
 *
 * This topic demonstrates the use of suspend/resume_page().
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Create Page X of Y";

$x = 500; 
$y = 20; 
$pagecount = 0;

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
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create some pages while counting the total number of pages.
     * Suspend the creation of each page to be able to add the total number
     * of pages later. Resume the page creation to add the total number of
     * pages in a footer like "Page X of Y".
     *
     * Create page 1
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    $pagecount++;
    $p->setcolor("fill", "rgb", 0, 0.4, 0.4, 0);
    $p->setfont($font, 12);
    $p->show_xy("Page 1", $x, $y);

    /* Suspend page 1 to resume it later */
    $p->suspend_page("");

    /* Create page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $pagecount++;
    $p->setcolor("fill", "rgb", 0, 0.4, 0.4, 0);
    $p->setfont($font, 12);
    $p->show_xy("Page 2", $x, $y);

    /* Suspend page 2 to resume it later */
    $p->suspend_page("");

    /* Create page 3 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $pagecount++;
    $p->setcolor("fill", "rgb", 0, 0.4, 0.4, 0);
    $p->setfont($font, 12);
    $p->show_xy("Page 3", $x, $y);

    /* Suspend page 3 to resume it later */
    $p->suspend_page("");

    /* Revisit page 1 */
    $p->resume_page("pagenumber 1");

    /* Add the total number of pages */
    $p->show(" of " . $pagecount);
    $p->end_page_ext("");

    /* Revisit page 2 */
    $p->resume_page("pagenumber 2");

    /* Add the total number of pages */
    $p->show(" of " . $pagecount);
    $p->end_page_ext("");

    /* Revisit page 3 */
    $p->resume_page("pagenumber 3");

    /* Add the total number of pages */
    $p->show(" of " . $pagecount);
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=page_x_of_y.pdf");
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
