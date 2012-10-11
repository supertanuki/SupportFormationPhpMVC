<?php
/* $Id: form_textfield_fill_with_js.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form text field filling with JavaScript:
 * Fill a form text field with a value using JavaScript
 *
 * Create a text field for displaying a date. Using a JavaScript action display
 * the current date in the form field whenever the page is opened.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Text Field Filling with JavaScript";


$optlist;
$width=160; $height=30; $llx = 10; $lly = 700;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $monospaced_font = $p->load_font("Courier", "winansi", "");
    if ($monospaced_font == 0)
	throw new Exception("Error: " . $p->get_errmsg());


    /* --------------------------------------------------------------------
     * Define a JavaScript script for entering the current date in the form
     * field "date" and supply it as the open action when starting the page
     * --------------------------------------------------------------------
     *
     * First create an action of type JavaScript. With the "script" option
     * in the option list of the action define a JavaScript snippet which
     * displays the current date in the "date" text field in the format
     * "mm dd yyyy", e.g. "Sep 11 2007".
     */
    $optlist = "script={var d = util.printd('mmm dd yyyy', new Date());\n" .
	"var date = this.getField('date');\ndate.value = d;}";

    $show_date = $p->create_action("JavaScript", $optlist);

    /* In the second step create the page. In the option list supply
     * the "action" option which attaches the "show_date" action created
     * above to the page open event.
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height action={open " .
	$show_date . "}");

    /* ---------------------------------
     * Create the form text field "date"
     * ---------------------------------
     *
     * Create a form field of type "textfield" called "date" with a
     * background color of light blue and a black border.
     * Allow a maximum of 11 characters to be entered (maxchar=11).
     * The characters are not moved out of the field if the field size is
     * reached (scrollable=false).
     * Display equidistant subfields for each character (comb=true).
     */
    $optlist = "backgroundcolor={rgb 0.95 0.95 1} bordercolor={gray 0} " .
	"maxchar=11 scrollable=false comb=true font=" . $monospaced_font .
	" fontsize=18";

    $p->create_field($llx, $lly, $llx + $width, $lly + $height, "date", "textfield",
	$optlist);
    $lly-=40;

    $p->setfont($font, 12);
    $p->fit_textline("When opening the page the current date is " .
	"automatically displayed in the field.", $llx, $lly, "");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_textfield_fill_with_js.pdf");
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
