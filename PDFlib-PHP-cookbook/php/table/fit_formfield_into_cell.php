<?php
/*
 * $Id: fit_formfield_into_cell.php,v 1.1 2012/03/30 14:46:29 rp Exp $
 * 
 * Fit form field into cell
 *
 * Use add_table_cell() with the "fieldtype", "fieldname" and "fitfield" options
 * to define a table cell containing a push button form field.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

$outfile = "";
$title = "Fit Form Field into Cell";

$tbl = 0; $tf = 0;

$margin = 4;

/* URL to be referenced by the link */
$url = "http://www.pdflib.com/download/tet";

/*
 * Height of a table row which is the sum of a font size of 6 and the
 * upper and lower cell margin of 4 each
 */
$rowheight = 14;

/* Width of the three table columns */
$c1 = 180; $c2 = 120; $c3 = 60;

/* Coordinates of the lower left corner of the table fitbox */
$llx = 30; $lly = 100; $urx = 400; $ury = 400;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Load the font */
    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=a5.width height=a5.height");

    /*
     * Add a heading line spanning three columns 
     */
    $optlist = "fittextline={position={center top} font=" . $font
	. " fontsize={capheight=6}} rowheight=" . $rowheight
	. " margin=" . $margin . " colspan=3 " . "colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, 1, "Download the latest version",
	$optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding a Textflow cell in the first column of the second row
     */
    $license = "Try before you buy! We offer free downloads of "
	. "all our software packages which can be used "
	. "without a license key for testing and developing.";

    /*
     * The Textflow is centered vertically, with a margin from all
     * borders.
     */
    $optlist = "font=" . $font . " "
	. "fontsize={capheight=6} leading=110%";

    $tf = $p->add_textflow($tf, $license, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $optlist = "textflow=" . $tf
	. " fittextflow={firstlinedist=capheight} " . "margin="
	. $margin . " colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, 2, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create a "URI" action for opening the URL */
    $optlist = "url={" . $url . "}";
    $action = $p->create_action("URI", $optlist);
    
    /*
     * Adding a cell in the second column of the second row with
     * the form field containing the push button. Tie the action 
     * created above to the event that the mouse button is released
     * inside the push button.
     */
    $optlist = "colwidth=" . $c2 . " "
	    . "fieldtype=pushbutton fieldname={download} "
	    . "fitfield={action={up=". $action . "} linewidth=1 "
		. "bordercolor={rgb 0.25 0 0.95} "
		. "backgroundcolor={rgb 0.95 0.95 1} "
		. "caption={Download now} "
		. "fillcolor={rgb 0.25 0 0.95} "
		. "font=" . $font . " fontsize=14}";
    
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding a text line cell in the third column of the second row
     */
    $optlist = "fittextline={position={right center} font=" . $font
	. " fontsize={capheight=6}} rowheight=" . $rowheight
	. " margin=" . $margin . " colwidth=" . $c3;

    $tbl = $p->add_table_cell($tbl, 3, 2, "7.5 MB", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * ------------- Fit the table -------------
     * 
     * Using "header=1" the table header will include the first line.
     * Using "line=horother linewidth=0.3" the ruling is specified with
     * a line width of 0.3 for all horizontal lines.
     */
    $optlist = "header=1 stroke={ {line=horother linewidth=0.3}}";

    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);

    /* Check the $result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result == "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
	    /* Other return values require dedicated code to deal with */
	}
    }

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fit_formfield_into_cell.pdf");
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
