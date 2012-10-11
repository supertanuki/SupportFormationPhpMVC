<?php
/* $Id: footnotes_in_text.php,v 1.1 2012/04/03 14:18:02 rp Exp $
 * Footnotes in Text:
 * Create footnotes (superscript text) in a Textflow provided with links to
 * jump to the footnote text. 
 * 
 * Place a Textflow with some superscript numbers representing footnote 
 * references. Below the Textflow place a text line for each footnote (starting
 * with a superscript number). Provide the footnote references with links to
 * jump to the footnote texts.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$outfile = "";
$title = "Footnotes in Text";
$tf = 0; 
$llx= 50; $lly=50; $urx=500; $ury=800;

/* Define an option list including two macros to be used to start and 
 * finish a footnote
 */
$optlist =
    "fontname=Helvetica fontsize=14 encoding=unicode " .
    "fillcolor={gray 0} alignment=justify charref leading=140% " .
    "macro {ft_start {textrise=60% fontsize=10 fontname=Helvetica-Bold " .
    "encoding=unicode fillcolor={cmyk 1 0.5 0.2 0} " .
    "matchbox={name=footnote margin=-6} } ft_end {textrise=0 " .
    "fontname=Helvetica encoding=unicode fontsize=14 fillcolor={gray 0} " .
    "matchbox=end} }";

/* Text with some pieces of text included in the inline option "textrise"
 * which defines the percentage of the font size the text is shifted up.
 * To shift some text down use a negative textrise value.
 * The inline options matchbox and matchbox end are used to indicate the
 * footnote rectangles. They are used later to create hypertext links to
 * jump to the actual footnote text. 
 * Softhyphens are marked with the character reference "&shy;" (character
 * references are enabled by the charref option).
 */
$text=
    "Our paper planes<&ft_start>1<&ft_end> are the ideal way " .
    "of passing the time. We offer re&shy;volu&shy;tionary new " .
    "develop&shy;ments of the traditional common paper planes. If your " .
    "lesson, conference, or lecture turn out to be deadly boring, you " .
    "can have a wonderful time with our planes. All our models are " .
    "fol&shy;ded from one paper sheet. They are exclu&shy;sively folded " .
    "with&shy;out using any adhesive. Several models are equipped with a " .
    "folded landing gear enabling a safe landing<&ft_start>2<&ft_end> on " .
    "the intended loca&shy;tion provided that you have aimed well. Other " .
    "models are able to fly loops or cover long distances. Let them " .
    "start from a vista point in the mountains and see where they touch " .
    "the ground.";

$footnote1 = "For more information, see www.kraxi.com.";
$footnote2 = "For a safe landing strategy, see our How you " .
    "aim well paper.";
    
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
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $p->setfont($font, 14);
    
    /* Just some informational text */
    $p->fit_textline("Click on the superscripted blue footnote to zoom it " .
	"in!", $llx, $ury - 100, "fillcolor={cmyk 1 0.5 0.2 0}");
    
    /* Create and place a Textflow. Use the "textrise" inline option to
     * shift the footnote numbers upwards.
     */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $result = $p->fit_textflow($tf, $llx, $lly-400, $urx, $ury-200, "");
    if (!$result == "_stop") {
	    /* Check for further action */
    }
    
    /* Retrieve the y coordinate of the current text position after having
     * placed the Textflow. 
     */
    $lasty = $p->info_textflow($tf, "textendy");
    
    $p->delete_textflow($tf);
	    
    /* Create two text lines with the first shifted upwards. For subscript
     * text use a negative textrise value.
     */
    $p->fit_textline("1", $llx, $lasty - 30, "textrise=60% fontsize=8");
    $p->fit_textline($footnote1, $llx+6, $lasty - 30, "fontsize=10");
    
    /* Create two text lines with the first shifted upwards. (For subscript
     * text you would use a negative textrise value.)
     */
    $p->fit_textline("2", $llx, $lasty - 45, "textrise=60% fontsize=8");
    $p->fit_textline($footnote2, $llx+6, $lasty - 45, "fontsize=10");
    
    /* Create a "GoTo" action for jumping from the respective footnote 
     * reference to the footnote text. In addition, zoom-in the footnote
     * text and display it on the top left of the window.
     */
    $optlist = "destination={page=1 type=fixed left=0 top=" . $lasty .
	" zoom=150%}";
    $action = $p->create_action("GoTo", $optlist);

    /* Create a Link annotation with the "GoTo" action on all rectangles
     * defined by the matchbox "footnote". 0 rectangle coordinates will
     * be replaced with matchbox coordinates.
     */
    $optlist = "action={activate " . $action . "} linewidth=0 " .
	      "usematchbox={{footnote}}";
    $p->create_annotation(0, 0, 0, 0, "Link", $optlist);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=footnotes_in_text.pdf");
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

