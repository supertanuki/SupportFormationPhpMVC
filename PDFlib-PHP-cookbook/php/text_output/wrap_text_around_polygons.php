<?php
/* $Id: wrap_text_around_polygons.php,v 1.2 2012/05/03 14:00:38 stm Exp $ 
 * Wrap text around polygons:
 * Use arbitrary polygons as wrapping shapes for text to wrap around
 * 
 * Use fit_textflow() with the "wrap" option and the "polygons" suboption to
 * wrap text around a triangle positioned relative to the fitbox of the
 * Textflow. Then wrap text around two hexagons positioned with absolute
 * coordinates on the page.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Wrap Text around Polygons";

$tf = 0;
$llx = 70; $lly = 50; $urx = 450; $ury = 820;


/* Repeat the dummy text to produce more contents */
$count = 6;

$optlist1 =
    "fontname=Helvetica fontsize=10.5 encoding=unicode " .
    "fillcolor={gray 0} alignment=justify";

$optlist2 =
    "fontname=Helvetica-Bold fontsize=11 encoding=unicode " .
    "fillcolor={rgb 0 0 1} charref";

/* Text which is repeatedly placed on the page. Soft hyphens are marked
 * with the character reference "&shy;" (character references are enabled
 * by the charref option).
 */
$text =
    "Our paper planes are the ideal way of passing the time. We offer " .
    "revolutionary new develop&shy;ments of the traditional common paper " .
    "planes. If your lesson, conference, or lecture turn out to be " .
    "deadly boring, you can have a wonderful time with our planes. " .
    "All our models are fol&shy;ded from one paper sheet. They are " .
    "exclu&shy;sively folded with&shy;out using any adhesive. Several " .
    "models are equipped with a folded landing gear enabling a safe " .
    "landing on the intended loca&shy;tion provided that you have aimed " .
    "well. Other models are able to fly loops or cover long distances. " .
    "Let them start from a vista point in the mountains and see where " .
    "they touch the ground. ";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    $p->set_parameter("charref", "true");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    
    /* ---------------------------
     * Wrap text around a triangle
     * ---------------------------
     */
    
    /* Create a heading */
    $heading = "Page 1: fit_textflow() with option\n" .
	"wrap={polygons={{50% 80%   20% 30%   80% 30%   50% 80%}}}\n\n";
    
    $tf = $p->add_textflow($tf, $heading, $optlist2);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create some amount of dummy text and feed it to a Textflow object
     * with alternating options
     */
    for ($i=1; $i<=$count; $i++) {
	$num = $i . " ";

	$tf = $p->add_textflow($tf, $num, $optlist2);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $text, $optlist1);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Loop until all of the text is placed; create new pages
     * as long as more text needs to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
  
	/* Place the text. Use the "wrap" option with the "polygons"
	 * suboption to define a triangle. In this case, each corner of the
	 * triangle is specified by a pair of coordinates defined as
	 * percentage of the fitbox width or height, respectively.
	 */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	    "verticalalign=justify linespreadlimit=120% " .
	    "wrap={ polygons={ {50% 80%   20% 30%   80% 30%   50% 80%} } }");

	$p->end_page_ext("");

	/* "_boxfull" means we must continue because there is more text;
	 * "_nextpage" is interpreted as "start new column"
	 */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if ($result != "_stop")
    {
	/* "_boxempty" happens if the box is very small and doesn't
	 * hold any text at all.
	 */
	if ($result ==  "_boxempty")
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
    
    
    /* -----------------------------
     * Wrap text around two hexagons
     * -----------------------------
     */
    $tf = 0;
    
    /* Create a heading */
    $heading = "Page 2: fit_textflow() with option " .
	"wrap={ polygons={\n{150 250   200 350   300 350   350 250   " .
	"300 150   200 150   150 250}\n{130 550   200 650   300 650   " .
	"370 550   300 450   200 450   130 550}}}\n\n";
    
    $tf = $p->add_textflow($tf, $heading, $optlist2);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Create some amount of dummy text and feed it to a Textflow object
     * with alternating options
     */
    for ($i=1; $i<=$count; $i++) {
	$num = $i . " ";

	$tf = $p->add_textflow($tf, $num, $optlist2);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $text, $optlist1);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Loop until all of the text is placed; create new pages
     * as long as more text needs to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
  
	/* Place the text. Use the "wrap" option with the "polygons"
	 * suboption to define two hexagons. In this case, each corner of
	 * the hexagons is specified by a pair of absolute coordinates on
	 * the page.
	 */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	    "verticalalign=justify linespreadlimit=120% wrap={ polygons={" .
"{150 250   200 350   300 350   350 250   300 150   200 150   150 250}   " .
"{130 550   200 650   300 650   370 550   300 450   200 450   130 550}} }");

	$p->end_page_ext("");

	/* "_boxfull" means we must continue because there is more text;
	 * "_nextpage" is interpreted as "start new column"
	 */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if ($result != "_stop")
    {
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
