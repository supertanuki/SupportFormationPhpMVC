<?php
/* $Id: avoid_linebreaking.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Avoid line breaking:
 * Create a Textflow and define various options for line breaking
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Avoid Line Breaking";

$tf = 0; $tf_avoid = 0;

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

    /* Create an A4 Landscape page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->setfont($font, 12);
    
    /* ---------------------------------------------------------------------
     * Output text which does not contain any options to avoid line breaking
     * ---------------------------------------------------------------------
     */
    $text = "For more information about the Giant Wing Paper Plane see " .
	"our Web site <underline=true>www.kraxi-systems.com" .
	"<underline=false>.<nextline>Alternatively, contact us by email " .
	"via <underline=true>questions@kraxi-systems.com" .
	"<underline=false>. You'll get all information about how to fly " .
	"the Giant Wing in a thunderstorm as soon as possible.";
 
    $p->fit_textline("Text without any options to avoid line breaking", 
	50, 430, "");

    /* Create the Textflow from the $text */
    $optlist = "fontname=Helvetica fontsize=20 encoding=unicode " .
	"leading=140%";
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $result = $p->fit_textflow($tf, 50, 100, 300, 400,
	"fitmethod=auto showborder");
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    
    /* --------------------------------------------------------------
     * Output the same $text but with additional options to avoid line
     * breaking
     * --------------------------------------------------------------
     */
    
    /* Text containing options to avoid any line breaking in the Web 
     * address with "<avoidbreak>...<noavoidbreak>" and to avoid any line
     * breaking after the "-" and "." characters in the email address with
     * "charclass={letter {- .}}>...<charclass=default>".
     */ 
    $text_avoid = "For more information about the Giant Wing Paper Plane " .
	"see our Web site <underline=true avoidbreak>" .
	"www.kraxi-systems.com<underline=false noavoidbreak>.<nextline>" .
	"Alternatively, contact us by email via <underline=true " .
	"charclass={letter {- .}}>questions@kraxi-systems.com" .
	"<charclass={default {- .}} underline=false>. You'll get all " .
	"information about how to fly the Giant Wing in a thunderstorm " .
	"as soon as possible.";
    
    $p->fit_textline("Text with \"charclass\" and \"avoidbreak\" options",
	450, 430, "");
    
    /* Create the Textflow from the $text */
    $tf_avoid = $p->create_textflow($text_avoid, $optlist);
    if ($tf_avoid == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf_avoid, 450, 100, 700, 400,
	"fitmethod=auto showborder");
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf_avoid);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=avoid_linebreaking.pdf");
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
