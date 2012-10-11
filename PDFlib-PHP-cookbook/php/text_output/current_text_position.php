<?php
/* $Id: current_text_position.php,v 1.1 2012/04/03 14:04:12 rp Exp $
 * Current text position:
 * Demonstrate how the current text position can be used to output simple text,
 * text lines, or Textflows next to one another.
 * 
 * Use set_text_pos() to set the position for text output on the page. 
 * Output some simple text using show(). The text position will be implicitly
 * moved at the end of the output text. 
 * Use get_value() with "textx" and "texty" to retrieve the current text
 * position.
 * Based on these values output a text line using fit_textline(). 
 * Output a Textflow at the current text position below the text line. 
 * Retrieve the current text position at the end of the Textflow and output a Z
 * line there. 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$outfile = "";
$title = "Current Text Position";
$tf = 0;
$fsize = 12;
$llx=150; $lly=50; $urx=330; $ury=700;


/* Repeat the dummy text to produce more contents */
$count = 3;

$optlist1 =
    "fontname=Helvetica fontsize=" . $fsize . " encoding=unicode " .
    "fillcolor={gray 0} alignment=justify";

$optlist2 =
    "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
    "fillcolor={rgb 1 0 0} charref";

/* Dummy text for filling the columns. Soft hyphens are marked with the
 * character reference "&shy;" (character references are enabled by the
 * charref option).
 */
$text = 
    "Lorem ipsum dolor sit amet, consectetur adi&shy;pi&shy;sicing elit, " .
    "sed do eius&shy;mod tempor incidi&shy;dunt ut labore et dolore " .
    "magna ali&shy;qua. Ut enim ad minim ve&shy;niam, quis nostrud " .
    "exer&shy;citation ull&shy;amco la&shy;bo&shy;ris nisi ut " .
    "ali&shy;quip ex ea commodo con&shy;sequat. Duis aute irure dolor " .
    "in repre&shy;henderit in voluptate velit esse cillum dolore eu " .
    "fugiat nulla pari&shy;atur. Excep&shy;teur sint occae&shy;cat " .
    "cupi&shy;datat non proident, sunt in culpa qui officia " .
    "dese&shy;runt mollit anim id est laborum. ";

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Load font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $p->setfont($font, $fsize);
    $p->setcolor("fillstroke", "rgb", 1, 0, 0, 0);
    
    
    /* ------------------------------------------------------------------
     * Use simple text output functions for text output. The current text
     * position is moved to the end of the output text.
     * ------------------------------------------------------------------
     */
    
    /* Set the position for text output on the page */
    $p->set_text_pos($llx, $ury + 50);
    
    /* Output text at the current text position */
    $p->show("LORE IPSUM ");
    
    /* Output text at the end of the preceding text */
    $p->show("DOLOR SIT AMET,");
    
    /* Retrieve the current text position */
    $textx = $p->get_value("textx", 0);
    $texty = $p->get_value("texty", 0);
    
    /* Output text while setting the current text position to about one
     * line below
     */
    $p->show_xy("CONSECTETUR", $llx, $texty -= ($fsize*1.2));
    
    /* -----------------------------------------------------
     * Output a text line based on the current text position
     * ----------------------------------------------------- 
     */
    $p->fit_textline("ADIPISICING ELIT...", $llx, ($texty -= $fsize*1.2), "");
    
    
    /* -----------------------------------------------------------------
     * Output a Textflow and use the current text position at the end of
     * the Textflow to stroke some lines
     * -----------------------------------------------------------------
     */
    
    /* Create some amount of dummy text and feed it to a Textflow
     * object with alternating options.
     */
    for ($i=1; $i<=$count; $i++)
    {
	$num = $i . " ";

	$tf = $p->add_textflow($tf, $num, $optlist2);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $text, $optlist1);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    /* Retrieve the current text position */
    $textx = $p->get_value("textx", 0);
    $texty = $p->get_value("texty", 0);
    
    $texty -= 30;

    /* Loop until all of the text is placed. "_boxfull" means we must
     * continue because there is more text
     */
    do {
	$optlist = "verticalalign=justify linespreadlimit=120% ";
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $texty, $optlist);
    } while ($result == "_boxfull");
    
    /* Draw a Z line after the Textflow if the Textflow has been placed
     * correctly 
     */
    if ($result == "_stop") {
	/* Get the current text position at the end of the Textflow */
	$textx = $p->get_value("textx", 0);
	$texty = $p->get_value("texty", 0);
	
	/* Stroke Z line at the end of the Textflow */
	$p->setcolor("stroke", "rgb", 1, 0, 0, 0);
	$p->setlinewidth(2);
		  
	$texty += 4;
	$p->moveto($textx, $texty);
	$p->lineto($urx, $texty);
	$p->lineto($llx, $lly);
	$p->lineto($urx, $lly);
	$p->stroke();
    }

    /* Check for errors */
    else {
	/* "_boxempty" happens if the box is very small and doesn't
	 * hold any text at all.
	 */
	if ($result == "_boxempty")
	    throw new Exception ("Error: Textflow box too small");
	else
	{
	    /* Any other return value is a user exit caused by
	     * the "return" option; this requires dedicated code to
	     * deal with.
	     */
	    throw new Exception ("User return '" . $result .
		    "' found in Textflow");
	}
    }

    $p->delete_textflow($tf);
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=current_text_position.pdf");
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
