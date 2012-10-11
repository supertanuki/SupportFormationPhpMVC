<?php
/* $Id: link_annotations.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Link annotations:
 * On images and text lines, create links to open PDF files or Web pages or
 * execute a JavaScript.
 *
 * On an image, create a Link annotation to open a URL.
 * On a text line, create a Link annotation to open a URL.
 * On a text line, create a Link annotation to jump to another PDF file.
 * On a text line, create a Link annotation to execute a JavaScript which
 * brings up a message box.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Link Annotations";


/* PDF file to be referenced by the link */
$pdffile = "../input/PLOP-datasheet.pdf";

/* URL to be referenced by the link */
$url = "http://www.pdflib.com";

/* Image to load that will also get a link */
$imagefile = "websurfer.jpg";

$x = 20; $y = 400;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");


    /* --- On an image, create a link to open a URL --- */

    /* Place an image and determine the image dimensions via the
     * "image_matchbox" matchbox
     */
    $optlist =
	"boxsize={50 50} fitmethod=meet matchbox={name=image_matchbox}";

    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, $x, $y, $optlist);
    $p->close_image($image);

    /* Create a "URI" action for opening the URL */
    $optlist = "url={" . $url . "}";
    $action = $p->create_action("URI", $optlist);

    /* Using the matchbox "image_matchbox", create a Link annotation with
     * the "URI" action. 0 rectangle coordinates will be replaced with
     * matchbox coordinates.
     */
    $optlist = "action={activate " . $action . "} linewidth=0 " .
	      "usematchbox={image_matchbox}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);


    /* --- On a text line, create a link to open a URL --- */

    /* Place a text line and determine the text dimensions via the
     * "text_matchbox" matchbox */
    $optlist =
	"font=" . $font . " fontsize=20 " . 
	"matchbox={name=text_matchbox} " .
	"fillcolor={rgb 0 0.6 0.6} strokecolor={rgb 0 0.6 0.6} underline";
    $p->fit_textline("Go to " . $url, $x, $y-=60, $optlist);

    /* Create a "URI" action for opening the URL */
    $optlist = "url={" . $url . "}";
    $action = $p->create_action("URI", $optlist);

    /* Using the matchbox "text_matchbox", create a Link annotation with
     * the "URI" action. 0 rectangle coordinates will be replaced with
     * matchbox coordinates.
     */
    $optlist = "action={activate " . $action . "} linewidth=0 " .
	      "usematchbox={text_matchbox}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);


    /* --- On a text line, create a link to jump to another PDF file --- */

    /* Place a text line and determine the text dimensions via the
     * "goto_matchbox" matchbox
     */
    $optlist =
	"font=" . $font . " fontsize=20 " .
	"matchbox={name=goto_matchbox} " .
	"fillcolor={rgb 0.6 0.6 0} strokecolor={rgb 0.6 0.6 0} underline";
    $p->fit_textline("Jump to file \"" . $pdffile . "\"", $x, $y-=100, $optlist);

    /* Create a "GoToR" action for jumping to another PDF file */
    $optlist = "filename={" . $pdffile . "}" .
	" destination {page 1 type fitwindow} newwindow";
    $action = $p->create_action("GoToR", $optlist);

    /* Using the matchbox "text_matchbox", create a Link annotation with
     * the "GoToR" action. 0 rectangle coordinates will be replaced with
     * matchbox coordinates.
     */
    $optlist = "action={activate " . $action . "} linewidth=0 " .
	"usematchbox={goto_matchbox}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);


    /* --- On a text line, create a link to execute a --- *
     * --- JavaScript which brings up a message box   --- */

    /* Place a text line and determine the text dimensions via the
     * "js_matchbox" matchbox
     */
    $optlist = "font=" . $font . " fontsize=16 " .
	"matchbox={name=js_matchbox} " .
	"fillcolor={rgb 0.6 0 0.6} strokecolor={rgb 0.6 0 0.6} underline";
    $p->fit_textline("Launch JavaScript", $x, $y-=100, $optlist);

    /* Create a "JavaScript" action with a script which brings up
     * a message box.
     */
    $optlist = "script={app.alert(\"JavaScript works!\")}";
    $js_action = $p->create_action("JavaScript", $optlist);

    /* Using the matchbox "text_matchbox", create a Link annotation with the
     * "JavaScript" action. 0 rectangle coordinates will be replaced with
     * matchbox coordinates.
     */
    $optlist = "action={activate " . $js_action . "} linewidth=0 " .
	"usematchbox={js_matchbox}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=link_annotations.pdf");
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
