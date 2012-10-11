<?php
/* $Id: nested_bookmarks.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Nested bookmarks:
 * Create bookmarks which are nested in several levels
 * 
 * Create a title page with a top-level bookmark and provide the following
 * pages with bookmarks nested on the second level. Below each of those the
 * second level bookmarks create another bookmark which jumps to a Web site.  
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Nested Bookmarks";

$numpages = 5; $pagewidth=200; $pageheight=100; 
$x = 20; $y = 50;

$planes =array(
    "Giant Wing",
    "Long Distance Glider",
    "Cone Head Rocket",
    "Super Dart",
    "German Bi-Plane"
);

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Load a font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Create the title page with a top-level bookmark */
    $p->begin_page_ext($pagewidth, $pageheight, "");
    $p->fit_textline("Kraxi Paper Planes", $x, $y, "font= " . $font .
	" fontsize=14");
    $bm_planes = $p->create_bookmark("Kraxi Paper Planes", "");
    $p->end_page_ext("");

    /* Create further pages with a bookmark each which is nested below the
     * top-level bookmark created above */
    for ($i=0; $i < $numpages; $i++)
    {
    
	/* Start page */
	$p->begin_page_ext($pagewidth, $pageheight, "");
    
	/* Output some text on the page */
	$p->fit_textline($planes[$i], $x, $y, "font= " . $font . " fontsize=14");

	/* Create a "Plane" bookmark on the page which is nested under the 
	 * "Kraxi Paper Planes" bookmark */
	$bm_plane = $p->create_bookmark($planes[$i], "parent=" . $bm_planes);
    
	/* Create a "URI" action for opening a URL */
	$action = $p->create_action("URI", "url={http://www.kraxi.com}");
    
	/* Create a bookmark which jumps to be URL defined above. This
	 * bookmark is nested on level three under the "Plane" bookmark
	 * created above. 
	 */
	$p->create_bookmark("Jump to the Kraxi Website", 
	     "parent=" . $bm_plane . " action={activate=" . $action . "}");

	$p->end_page_ext("");
    }
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=nested_bookmarks.pdf");
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
