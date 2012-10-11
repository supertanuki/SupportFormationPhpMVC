<?php
/* $Id: weblink_in_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Weblink in Text:
 * Create a Textflow and integrate colorized Web links in the text
 *
 * Using the inline options "matchbox" and "matchbox end" define
 * matchboxes which are used to indicate the pieces of text on which the
 * Web links are to be placed.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Weblink in Text";

$tf = 0;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Create page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Create and fit a Textflow with two Web links. Using the inline
     * options "matchbox" and "matchbox end" define the two matchboxes
     * "kraxiweb" and "kraximail" which are used to indicate the pieces of
     * text on which the Web links are to be placed. Provide the matchboxes
     * with particular text color and decoration.
     */
    $text =
	"For more information about the Giant Wing Paper Plane see the " .
	"Web site of <underline=true fillcolor={rgb 0 0.5 0.5} " .
	"strokecolor={rgb 0 0.5 0.5} " .
	"matchbox={name=kraxiweb boxheight={fontsize descender}}>Kraxi " .
	"Systems, Inc.<matchbox=end underline=false fillcolor={rgb 0 0 0}" .
	" strokecolor={rgb 0 0 0}> or contact us by email via " .
	"<matchbox={name=kraximail fillcolor={rgb 0 0.8 0.8} " .
	"boxheight={ascender descender}}>questions@kraxi.com" .
	"<matchbox=end fillcolor={rgb 0 0 0}>. You'll get all " .
	"information about how to fly the Giant Wing in a thunderstorm " .
	"as soon as possible.";

    $optlist =
	"fontname=Helvetica fontsize=20 encoding=unicode leading=140%";

    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 100, 200, 500, 500, "fitmethod=auto");
    if ($result != "_stop")
    {
	/* Check for errors */
    }

    /* Create URI action */
    $optlist = "url={http://www.kraxi.com}";
    $act = $p->create_action("URI", $optlist);

    /* Create Link annotation on matchbox "kraxiweb" */
    $optlist = "action={activate " . $act . 
	"} linewidth=0 usematchbox={kraxiweb}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);

    /* Create URI action */
    $optlist = "url={mailto:questions@kraxi.com}";
    $act = $p->create_action("URI", $optlist);

    /* Create Link annotation on matchbox "kraximail" */
    $optlist = "action={activate " . $act . 
	"} linewidth=0 usematchbox={kraximail}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=weblink_in_text.pdf");
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
