<?php
/* $Id: initial_view.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Initial view:
 * Define the initial viewing properties for a document, such as zoom, page
 * number, navigation tab, or title bar
 * 
 * Define to open the document on page 2 using a fixed window size with a zoom
 * of 300% and the page displayed with the coordinates 720,100 on the top left.
 * In addition, show the Bookmarks navigation pane and the document title in 
 * the title bar of Acrobat.
 * 
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Initial View";


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Open the document on page 2 using a fixed window size with
     * a zoom of 300% and the page displayed with the coordinates
     * 720,100 on the top left. In addition, show the "Title" document info
     * in Acrobat's title bar and show the "Bookmarks" navigation pane. 
     */
    $optlist = "destination={page=2 type=fixed zoom=3 top=720 left=100} " .
	      "viewerpreferences=displaydoctitle openmode=bookmarks ";

    if ($p->begin_document($outfile, $optlist) == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page 1 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create a bookmark for jumping on that page */
    $p->create_bookmark("Page 1", "");
    
    $p->fit_textline("This is page 1", 100, 700, "font=" . $font .
	" fontsize=20");
    
    $p->end_page_ext("");
    
    /* Start page 2 */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create a bookmark for jumping on that page */
    $p->create_bookmark("Page 2", "");
    
    $p->fit_textline("Page 2 is displayed with this text", 100, 700, "font=" .
	$font . " fontsize=18");
    $p->fit_textline("moved to the top left and a zoom", 100, 660, "font=" .
	$font . " fontsize=18");
    $p->fit_textline("of 300% when opening the document", 100, 620, "font=" .
	$font . " fontsize=18");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=initial_view.pdf");
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

