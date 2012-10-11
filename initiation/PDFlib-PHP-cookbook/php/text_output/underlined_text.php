<?php
/* $Id: underlined_text.php,v 1.1 2012/04/12 15:10:54 rp Exp $
 * Underlined Text:
 * Create underlined text
 * 
 * Place an underlined text line with standard and individual underline width
 * and position. Place a Textflow with several words underlined.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$outfile = "";
$title = "Underlined Text";
$tf = 0;
$llx= 50; $lly=50; $urx=500; $ury=800;
$optlist =
    "fontname=Helvetica fontsize=14 encoding=unicode " .
    "fillcolor={gray 0} alignment=justify charref";

/* Dummy text for filling the columns. Soft hyphens are marked with the
 * character reference "&shy;" (character references are enabled by the
 * charref option).
 */
$text=
     "<underline>Our paper planes<underline=false> are the <underline>" .
     "ideal<underline=false> way of passing the time. We offer " .
     "<underline=true>re&shy;volu&shy;tionary<underline=false> new " .
     "develop&shy;ments of the traditional common paper planes. If your " .
     "lesson, conference, or lecture turn out to be deadly boring, you " .
     "can have a wonderful time with our planes. All our models are " .
     "fol&shy;ded from one paper sheet. They are exclu&shy;sively folded " .
     "with&shy;out using any adhesive. Several models are equipped with " .
     "a folded landing gear enabling a <underline>safe landing" .
     "<underline=false> on the intended loca&shy;tion provided that you " .
     "have aimed well. Other models are able to fly loops or cover long " .
     "distances. Let them start from a vista point in the mountains " .
     "and see where they touch the ground. ";

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $p->setfont($font, 20);
	    
    /* Create a text line with standard underline settings */
    $p->fit_textline("Our paper planes are the ideal way of passing the time",
	$llx, $ury-50, "underline");
    
    /* Create a text line with individual underline settings */
    $p->fit_textline("Our paper planes are the ideal way of passing the time",
	$llx, $ury-100, "underline underlinewidth=3 underlineposition=-40%");
    
    /* Create a Textflow with standard underline settings */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, $llx, $lly-200, $urx, $ury-200, "");
    if ($result != "_stop") {
	    /* Check for further action */
    }
    $p->delete_textflow($tf);
    
    /* Create a Textflow with individual underline settings */
    $tf = $p->create_textflow($text, $optlist . " leading=160% " .
	"underlinewidth=2 underlineposition=-30%");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());        
    
    $result = $p->fit_textflow($tf, $llx, $lly-400, $urx, $ury-400, "");
    if ($result != "_stop") {
	    /* Check for further action */
    }
    $p->delete_textflow($tf);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=underline_text.pdf");
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
