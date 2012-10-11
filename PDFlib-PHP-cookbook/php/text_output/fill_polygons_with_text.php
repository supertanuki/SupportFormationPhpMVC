<?php
/* $Id: fill_polygons_with_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $ 
 * Fill polygons with text:
 * Define arbitrary polygons to be filled with text
 * 
 * Use fit_textflow() with the "wrap" option and the "addfitbox" and "polygons"
 * suboptions to define a rhombus to be filled with text. Then define a hexagon
 * and a rectangle positioned within the hexagon. In this case the overlapping
 * area will be left blank when fitting the text using the options above.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Fill Polygons with Text";

$tf = 0;
$llx = 50; $lly = 200; $urx = 450; $ury = 700;


$textopts =
    "fontname=Helvetica fontsize=10.5 encoding=unicode " .
    "fillcolor={gray 0} alignment=justify charref";

/* Text which is placed on the page. Soft hyphens are marked with the
 * character reference "&shy;" (character references are enabled by the
 * charref option).
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
    "Let them start from a vista point in the mountains and see " .
    "where they touch the ground. ";

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
    
    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ------------------------
     * Fill a rhombus with text
     * ------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Create a heading */
    $p->setfont($font, 11);
    
    $p->fit_textline("Page 1:", $llx, $ury+60, "fillcolor={rgb 0 0 1}");
    $p->fit_textline("fit_textflow() with option", $llx, $ury+40,
	"fillcolor={rgb 0 0 1}");
    $p->fit_textline("wrap={addfitbox polygons={{50% 80%   20% 50%   " .
	"50% 20%   80% 50%   50% 80%}}}", $llx, $ury+20, 
	"fillcolor={rgb 0 0 1}");

    /* Create some text and feed it to a Textflow object */
    $tf = $p->add_textflow($tf, $text, $textopts);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Place the text. Use the "wrap" option with the "addfitbox" and
     * "polygons" suboptions to add a rhombus to the area being filled.
     * In this case, each corner of the rhombus is specified by a pair
     * of coordinates defined as percentage of the fitbox width or
     * height, respectively.
     * "showborder" is used to illustrate the fitbox borders. 
     */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	"verticalalign=justify linespreadlimit=120% showborder " .
	"wrap={ addfitbox polygons={" .
	"{50% 80%   20% 50%   50% 20%   80% 50%   50% 80%} } }");

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
    
    $p->end_page_ext("");
    
    
    /* ------------------------------------------------------------------
     * Fill two overlapping shapes with text. 
     * In this case, the first shape will be filled while the area of the
     * second shape overlapping with the first shape will be left blank.  
     * ------------------------------------------------------------------
     */
    $tf = 0;
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create a heading */
    $p->setfont($font, 11);
    
    $p->fit_textline("Page 2:", $llx, $ury+80, "fillcolor={rgb 0 0 1}");
    $p->fit_textline("fit_textflow() with option wrap={addfitbox polygons={",
	$llx, $ury+60, "fillcolor={rgb 0 0 1}");
    $p->fit_textline("{20% 10%   80% 10%   100% 50%   80% 90%   20% 90%   " .
	"0% 50%   20% 10%}", $llx, $ury+40, "fillcolor={rgb 0 0 1}");
    $p->fit_textline("{35% 35%   65% 35%   65% 65%   35% 65%   35% 35%}}}",
	$llx, $ury+20, "fillcolor={rgb 0 0 1}");
    
    /* Create some text and feed it to a Textflow object */
    for ($i = 0; $i < 3; $i++) {
	$tf = $p->add_textflow($tf, $text, $textopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    /* Place the text. Use the "wrap" option with the "addfitbox" and
     * "polygons" suboptions to add a hexagon to the area being filled and
     * a rectangle to the area being left empty. Each corner is specified by
     * a pair of coordinates defined as percentage of the fitbox width or
     * height, respectively.
     * "showborder" is used to illustrate the fitbox borders. 
     */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	"verticalalign=justify linespreadlimit=120% showborder " .
	"wrap={ addfitbox polygons={" .
  "{20% 10%   80% 10%   100% 50%   80% 90%   20% 90%   0% 50%   20% 10%} " .
  "{35% 35%   65% 35%   65% 65%   35% 65%   35% 35%} } }");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fill_polygons_with_text.pdf");
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
