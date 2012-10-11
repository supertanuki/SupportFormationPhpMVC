<?php
/* $Id: vertical_alignment_in_fitbox.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Vertical alignment in fitbox:
 * Control the vertical alignment of text in the fitbox
 * 
 * Use the "verticalalign" option of fit_textflow() to vertically align $text in
 * the fitbox in different ways.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Vertical Alignment in Fitbox";

$tf = 0; 
$ystart = 630;
$x = 20; $y = $ystart; $yoffset = 200;
$boxheight = 190; $boxwidth = 250;
$xtext = 300; $ytext = $y + $boxheight/2;

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
    $p->set_info("Title", $title);
    
    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create an A4 page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set the font with a font size of 14 */
    $p->setfont($font, 10);

    /* Text to be created */
   $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new developments of the traditional common " .
	"paper planes. If your lesson, conference, or lecture turn out " .
	"to be deadly boring, you can have a wonderful time with our " .
	"planes. All our models are folded from one paper sheet.";
    
    /* Option list to create the Textflow. It is similar in all cases. 
     * The leading is set to 120% and the alignment to justify.
     */
    $cr_optlist = "fontname=Helvetica fontsize=14 encoding=unicode " .
	"leading=120% alignment=justify";
	 
    /* -------------------------------------------
     * Case 1: Align the text on top of the fitbox 
     * -------------------------------------------
     */
    /* Output some descriptive text */
    $p->fit_textline("Case 1: Fit Textflow with default settings", 
	$xtext, $ytext, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * The "showborder" option illustrates the borders of the fitbox. No
     * further options are supplied so the default setting
     * "verticalalign=top" and "firstlinedist=leading" are used implicitly
     * and the text will be aligned on top of the fitbox with the baseline
     * of the first text line having a distance from the top of the fitbox 
     * corresponding to the leading value of the font size. 
     */
    $fit_optlist = "showborder";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    
    /* -------------------------------------------------------------------
     * Case 2: Align the text on top of the fitbox with a defined distance
     * from the top 
     * -------------------------------------------------------------------
     */
    $y -= $yoffset;
    $ytext = $y + $boxheight/2;
    
    /* Output some descriptive text */
    $p->fit_textline("Case 2: firstlinedist=capheight", 
	$xtext, $ytext, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * The default setting "verticalalign=top" is used implicitly.
     * "firstlinedist=capheight" defines the distance between the top of the 
     * fitbox and the baseline of the first text line as the capheight of 
     * the font size. 
     */
    $fit_optlist = "showborder firstlinedist=capheight";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);

    
    /* --------------------------------------------------
     * Case 3: Align the text at the bottom of the fitbox
     * --------------------------------------------------
     */
    $y -= $yoffset;
    $ytext = $y + $boxheight/2;
    
    /* Output some descriptive text */
    $p->fit_textline("Case 3: verticalalign=bottom", 
	$xtext, $ytext, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * "verticalalign=bottom" aligns the text at the bottom of the fitbox.
     */
    $fit_optlist = "showborder verticalalign=bottom";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    
    /* -----------------------------------------------------------------
     * Case 4: Align the text at the bottom of the fitbox with a defined
     * distance from the bottom
     * -----------------------------------------------------------------
     */
    $y -= $yoffset;
    $ytext = $y + $boxheight/2;
    
    /* Output some descriptive text */
    $p->fit_textline("Case 4: verticalalign=bottom lastlinedist=descender", 
	$xtext, $ytext, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * "verticalalign=bottom" aligns the text at the bottom of the fitbox.
     * "lastlinedist=descender" defines the distance between the baseline
     * of the last text line and the bottom of the fitbox as the descender
     * of the font size. 
     */
    $fit_optlist = "showborder verticalalign=bottom lastlinedist=descender";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    $p->end_page_ext("");
    
    
    /* Create an A4 page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set the font with a font size of 14 */
    $p->setfont($font, 10);
    
    $y = $ystart;
    $yoffset = 230;
    $ytext = $y + $boxheight/2;
    
    
    /* ------------------------------------------------
     * Case 5: Center the text in the fitbox vertically 
     * ------------------------------------------------
     */
    
    /* Output some descriptive text */
    $p->fit_textline("Case 5: ", 
	$xtext, $ytext, "");
    $p->fit_textline("verticalalign=center firstlinedist=capheight " .
	"lastlinedist=descender", $xtext, $ytext-15, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * The default setting "firstlinedist=leading" is used implicitly.
     * "verticalalign=center" aligns the text in the center of the fitbox.
     * "firstlinedist=capheight" defines the distance between the top of the 
     * fitbox and the baseline of the first text line as the capheight of 
     * the font size.  
     * "lastlinedist=descender" defines the distance between the baseline
     * of the last text line and the bottom of the fitbox as the descender
     * of the font size. 
     */
    $fit_optlist = "showborder verticalalign=center " .
	"firstlinedist=capheight lastlinedist=descender";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    
    /* --------------------------------------------------------------
     * Case 6: Fit the text by justifying it vertically in the fitbox
     * --------------------------------------------------------------
     */
    $y -= $yoffset;
    $ytext = $y + $boxheight/2;
    
    /* Output some descriptive text */
    $p->fit_textline("Case 6: verticalalign=justify linespreadlimit=200%", 
	$xtext, $ytext, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * "verticalalign=justify" justifies the text vertically in the fitbox.
     * "linespreadlimit=200% defines maximum distance between two text lines
     * as 200% of the font size. 
     */
    $fit_optlist = "showborder verticalalign=justify linespreadlimit=200%";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    
    /* --------------------------------------------------------------------
     * Case 7: Fit the text by justifying it vertically in the fitbox, with
     * a defined distance from the top and from the bottom
     * --------------------------------------------------------------------
     */
    $y -= $yoffset;
    $ytext = $y + $boxheight/2;
    
    /* Output some descriptive text */
    $p->fit_textline("Case 7:",
	    $xtext, $ytext+15, "");
    $p->fit_textline("verticalalign=justify linespreadlimit=200%",
	$xtext, $ytext, "");
    $p->fit_textline("firstlinedist=capheight lastlinedist=descender", 
	$xtext, $ytext-15, "");
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $cr_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow.
     * "showborder" illustrates the borders of the fitbox.
     * The default setting "firstlinedist=leading" is used implicitly.
     * "verticalalign=justify" justifies the text vertically in the fitbox.
     * "linespreadlimit=200% defines maximum distance between two text lines
     * as 200% of the font size.
     * "firstlinedist=capheight" defines the distance between the top of the 
     * fitbox and the baseline of the first text line as the capheight of 
     * the font size.  
     * "lastlinedist=descender" defines the distance between the baseline
     * of the last text line and the bottom of the fitbox as the descender
     * of the font size. 
     */
    $fit_optlist = "showborder verticalalign=justify linespreadlimit=200% " .
	"firstlinedist=capheight lastlinedist=descender";
    
    $result = $p->fit_textflow($tf, $x, $y, ($x + $boxwidth), ($y + $boxheight), 
	$fit_optlist);
    
    if (!$result == "_stop")
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=vertica_alignment_in_fitbox.pdf");
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
