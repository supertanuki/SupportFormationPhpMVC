<?php
/* $Id: keep_lines_together.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Keep lines together:
 * Control the lines kept together on the page
 * 
 * Use the inline option "mark" of add/create_textflow() to mark certain 
 * positions in the Textflow. Use the "returnatmark" option fit_textflow() to
 * return prematurely at the text position where the inline option "mark" has
 * been defined with the specified number. The return reason string will be
 * "_mark#", where # is the number of the appropriate mark.
 * Use this feature to ensure that the text between two marks is always kept 
 * together in one fitbox. 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7.0.3
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Keep Lines Together";

$tf = 0;

/* Required minimum PDFlib version */
$requiredversion = 703;
$requiredvstr = "7.0.3";

$ystart = 700;
$boxwidth = 210; $boxheight = 110;
$offset = 10;
$xleft = 10;
$xright = $xleft + $boxwidth + 80;

$lastmark = 0;
$y = $ystart;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0); 
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);
	   
    if ($major*100 + $minor*10 + $revision < $requiredversion) 
	throw new Exception("Error: PDFlib " . $requiredvstr . 
	    " or above is required");
    
    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Text to be created. 
     * Soft hyphens are indicated with the character reference "&shy;"
     * (character references are enabled by the "charref" option).
     * The inline option "mark" is used to indicated text which should not
     * be divided, i.e. all text between an equal and an odd mark number
     * should be kept together in one fitbox.
     */
    $text =
	"<mark=0>Have a look at our new paper plane models!" .
	"<mark=1>" .
	"<mark=2><nextline fillcolor={rgb 0.5 0.0 0.0}>Long Distance Glider:<nextline>" .
	"With this paper rocket you can send all your messages even when " .
	"sitting in a hall or in the cinema pretty near the back." .
	"<mark=3>" .
	"<mark=4><nextline fillcolor={rgb 0.0 0.5 0.0}>Giant Wing:<nextline>" .
	"An unbelievable sailplane! It is ama&shy;zingly robust and can " .
	"even do aero&shy;batics. But it best suited to gliding." .
	"<mark=5>" .
	"<mark=6><nextline fillcolor={rgb 0.8 0.4 0.1}>Cone Head Rocket:<nextline>" .
	"This paper arrow can be thrown with big swing. We launched it " .
	"from the roof of a hotel. It stayed in the air a long time and " .
	"covered a considerable distance." .
	"<mark=7>" .
	"<mark=8><nextline fillcolor={rgb 0.0 0.5 0.5}>Super Dart:<nextline>" .
	"The super dart can fly giant loops with a radius of 4 or 5 " .
	"metres and cover very long distances. Its heavy cone point is " .
	"slightly bowed upwards to get the lift required for loops." .
	"<mark=9>" .
	"<mark=10><nextline fillcolor={rgb 0.0 0.0 0.5}>German Bi-Plane:<nextline>" .
	"Brand-new and ready for take-off. If you have lessons in the " .
	"history of aviation you can show your interest by letting it " .
	"land on your teacher's desk.<mark=11>";
    
    /* Maximum number of the mark defined in the Textflow */
    $maxmark = 11;
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Output some descriptive text */
    $topts = "fontname=Helvetica-Bold fontsize=14 encoding=unicode ";
	
    $p->fit_textline("Fit Textflow without considering", 
	$xleft, $y + 30, $topts);
    $p->fit_textline("marks", $xleft, $y + 15, $topts);
    $p->fit_textline("Fit Textflow using \"returnatmark\"", $xright, $y + 30,
	$topts);
    $p->fit_textline("to keep lines together", $xright, $y + 15, $topts);
    
    /* Option list to create the Textflow.
     * "avoidemptybegin" deletes empty lines at the beginning of a fitbox.
     * "charref" enables the substitution of numeric and character entity
     * or glyph name references, e.g. of the character reference "&shy;"
     * for a soft hyphen.
     */
    $optlist = "fontname=Helvetica fontsize=12 encoding=unicode " .
	"alignment=justify leading=120% charref avoidemptybegin";
	 
    /* ------------------------------------------------------------------
     * First, output the text without considering any marks, i.e. without
     * keeping lines together
     * ------------------------------------------------------------------
     */
    
    /* Create the Textflow */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    do
    {
	/* Set the line width used for the border around the fitbox of the 
	 * Textflow displayed with the "showborder" option.
	 */
	$p->setlinewidth(0.3);
	
	/* Fit the Textflow */
	$result = $p->fit_textflow($tf, $xleft, $y, $xleft + $boxwidth, 
	    $y - $boxheight, "showborder");
	
	$y -= $boxheight + $offset;
			      
    /* "_stop" means that the Textflow has been fit completely */
    } while ($result != "_stop" &&
	     $result != "_boxempty");
    
    /* -------------------------------------------------------------------
     * Now, output the text with keeping lines together by considering the 
     * marks
     * -------------------------------------------------------------------
     */
    
    /* Create the Textflow again */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the starting point for the first fitbox */
    $y = $ystart;
    
    /* Loop until all of the text is placed; create new fitboxes on the
     * page as long as more text needs to be placed.
     */
    do
    {
	/* Set the line width used for the border around the fitbox of the 
	 * Textflow displayed with the "showborder" option.
	 */
	$p->setlinewidth(0.3);
	
	/* Option list for fitting the Textflow.
	 * "showborder" is used to illustrate the fitbox borders for
	 * testing.
	 * "blind" is used to fit the Textflow in "blind" mode. All 
	 * calculations will be done but the Textflow will not actually be
	 * placed.
	 */
	$optlist = "showborder blind";
	
	/* Fit the Textflow in "blind" mode to find out up to which mark
	 * the text will fit into the fitbox.
	 */
	$result = $p->fit_textflow($tf, $xright, $y, $xright + $boxwidth, 
	    $y - $boxheight, $optlist);
	
	$lastmark = $p->info_textflow($tf, "lastmark");
	
	/* An even mark number indicates the start of a text section to be
	 * kept together. Reset it to the last odd mark number which 
	 * indicates the end of a text section.
	 */
	if ($lastmark%2 == 0) {
	    --$lastmark;
	}
		    
	/* Now actually fit the Textflow. To rewind the Textflow status
	 * to before the last call to fit_textflow() use "rewind=-1".
	 */
	$optlist = "showborder returnatmark=" . $lastmark . " rewind=-1";
	$result = $p->fit_textflow($tf, $xright, $y, $xright + $boxwidth, 
	    $y - $boxheight, $optlist);
	
	$y -= $boxheight + $offset;
			      
    /* _stop means that the Textflow has been fit completely.
     * _mark# with # equals "maxmark" defined above means that the maximum
     * mark number defined in the Textflow has been reached.
     */
    } while ($result != "_stop" &&
	     $result != "_boxempty" &&
	     $result != "_mark" . $maxmark);
    
    /* _boxempty" happens if the box is too small to hold any text */
    if ($result ==  "_boxempty") {
	throw new Exception ("Error: Textflow box too small");
    }
    
    $p->end_page_ext("");
	
    $p->delete_textflow($tf);
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=keep_lines_together.pdf");
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
