<?php
/* $Id: insert_toc.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Insert table of contents:
 * Create some pages, assign labels to them and insert a table of contents at
 * the beginning of the document
 *
 * Pages are created in a different chronological order than the order in
 * which they appear in the document. Using page groups create several pages
 * and provide them with page labels. After creating the last page, go back
 * to the start of the document and insert the pages for the table of contents.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Insert Table of Contents";
$docsize=0;

/* Using the "groups" option, define several page groups for the
 * title (title), the table of contents (toc), the individual chapters
 * (body), and the index (index). List them in the order they will appear
 * in the document. Using the "labels" option, assign a page label to each
 * page grou$p->
 */
$optlist =
    "groups={title toc content index} " .
    "labels={{group=title prefix=title} " .
	    "{group=toc prefix={toc } start=1 style=r} " .
	    "{group=content start=1 style=D} " .
	    "{group=index prefix={index } start=1 style=r}}";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, $optlist) == 0)
	throw new Exception("Error: " + $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create a page in the page group "title" */
    $p->begin_page_ext(595, 842, "group title");
    $docsize++;
    $p->fit_textline("Title", 50, 700, "font=" . $font . " fontsize=36");
    $p->end_page_ext("");

    /* Loop over all pages in the page group "content" */
    for ($pageno = 1; $pageno <= 5; $pageno++)
    {
	$p->begin_page_ext(595, 842, "group content");
	$docsize++;
	$p->fit_textline("Chapter " . $pageno, 50, 700, "font=" . $font .
	    " fontsize=36");
	$p->end_page_ext("");
    }

    /* Insert two pages in the page group "index" */
    $p->begin_page_ext(595, 842, "group index");
    $docsize++;
    $p->fit_textline("Index I", 50, 700, "font=" . $font . " fontsize=36");
    $p->end_page_ext("");

    $p->begin_page_ext(595, 842, "group index");
    $docsize++;
    $p->fit_textline("Index II", 50, 700, "font=" . $font . " fontsize=36");
    $p->end_page_ext("");

    /* Insert two pages in the page group "toc" for a table of contents */
    $p->begin_page_ext(595, 842, "group toc");
    $docsize++;
    $p->fit_textline("Table of Contents I", 50, 700, "font=" . $font .
	" fontsize=36");
    $p->end_page_ext("");

    $p->begin_page_ext(595, 842, "group toc");
    $docsize++;
    $p->fit_textline("Table of Contents II", 50, 700, "font=" . $font .
	" fontsize=36");
    $p->fit_textline("for a Document of " . $docsize . " Pages.", 50, 600,
	"font=" . $font . " fontsize=24");
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=insert_toc.pdf");
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
