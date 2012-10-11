<?php
/* $Id: numbered_list.php,v 1.1 2012/04/03 14:57:00 rp Exp $
 * Numbered list:
 * Output numbered lists with the numbers left- or right-aligned
 * 
 * Create a numbered list with the numbers left-aligned. Use the "leftindent"
 * option of add_textflow() with "leftindent=0%" for adding the item number and
 * "leftindent=10%" for adding the item text.
 * 
 * Create a numbered list with the numbers right-aligned. 
 * Output the number using a right-aligned tabulator specified by the options
 * "hortabmethod=ruler", "ruler=3%", and "tabalignment=right". Use the
 * "leftindent" option of add_textflow() with "leftindent=10%" for adding the
 * item text.
 
 * Setting and resetting the indentation value is cumbersome, especially since
 * it is required for each paragraph. A more elegant solution creates a 
 * numbered list by defining a macro called "list" containing inline options
 * to be supplied inline in the Textflow.  
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7.0.2
 * Required data: none
 */
$outfile = "";
$title = "numbered_list";

/* Required minimum PDFlib version */
$requiredversion = 702;
$requiredvstr = "7.0.2";

$tf = 0; 
$llx=50; $lly=200; $urx=500; $ury=700;

$head_optlist =
    "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
    "fillcolor={cmyk 1 0.5 0.2 0}";

$numbers = array(
    "I", "II", "III", "IV"
);

$items = array(
    "Long Distance Glider\nWith this paper rocket you can send all your" .
    "messages even when sitting in a hall or in the cinema pretty near " .
    "the back.",

    "Giant Wing\nAn unbelievable sailplane! It is amazingly robust and " .
    "can even do aerobatics. But it best suited to gliding.",

    "Cone Head Rocket\nThis paper arrow can be thrown with big swing. " .
    "We launched it from the roof of a hotel. It stayed in the air a " .
    "long time and covered a considerable distance.",

    "Super Dart\nThe super dart can fly giant loops with a radius of 4 " .
    "or 5 meters and cover very long distances. Its heavy cone point is " .
    "slightly bowed upwards to get the lift required for loops."
);

try {
    $p = new pdflib();

    /* This means we must check return values of begin_document() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0); 
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);
	   
    if ($major*100 + $minor*10 + $revision < $requiredversion) 
	throw new Exception("Error: PDFlib " . $requiredvstr . 
	    " or above is required");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );


    /* ----------------------------------------------------
     * Create a numbered list with the numbers left-aligned
     * ----------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Create the Textflow. First, add a heading */
    $tf = $p->add_textflow($tf, "Page 1: Numbered list with the numbers " .
	"left-aligned", $head_optlist);

    /* Set general options for the following text */
    $tf = $p->add_textflow($tf, "",
	"fontname=Helvetica fontsize=12 encoding=unicode charref " .
	"alignment=justify leading=140% ");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Add list numbers and list items. Use the "leftindent=0%" and
     * "leftindent=10%" options to indent the item's  text by 0% or 10% of
     * the width of the Textflow's fitbox.
     */
    $num_optlist = "fillcolor={cmyk 1 0.5 0.2 0} leftindent=0%";

    $item_optlist = "fillcolor={gray 0} leftindent=10%";

    for ($i = 0; $i < 4; $i++) {
	$tf = $p->add_textflow($tf, "\n\n" . $numbers[$i], $num_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $items[$i], $item_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Place the Textflow */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    if ($result != "_stop") {
	/* Check for further action */
    }

    $p->delete_textflow($tf);

    $p->end_page_ext("");

    /* -----------------------------------------------------
     * Create a numbered list with the numbers right-aligned
     * -----------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $tf = 0;

    /* Create the Textflow. First, add a heading */
    $tf = $p->add_textflow($tf, "Page 2: Numbered list with the numbers " .
	"right-aligned", $head_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Set general text options.
     * 
     * Define the settings of a tabulator. Use "hortabmethod=ruler" and
     * "ruler=3%" to specify the tabulator's position as 3% of the width of
     * the Textflow's fitbox. Use "tabalignment=right" for the tabulator to
     * be right-aligned.
     */
    $tf = $p->add_textflow($tf, "",
	"fontname=Helvetica fontsize=12 encoding=unicode charref " .
	"alignment=justify leading=140% " .
	"hortabmethod=ruler ruler=3% tabalignment=right");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Set list number options */
    $num_optlist =
	"fillcolor={cmyk 1 0.5 0.2 0} leftindent=0%";

    /* Set list item options */
    $item_optlist =
	"fillcolor={gray 0} leftindent=10%";

    /* Add the list with its numbers and items to the Textflow */
    for ($i = 0; $i < 4; $i++) {
	$tf = $p->add_textflow($tf, "\n\n\t" . $numbers[$i], $num_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $items[$i], $item_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Place the Textflow */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    if ($result!= "_stop") {
	/* Check for further action */
    }

    $p->delete_textflow($tf);

    $p->end_page_ext("");
    
    
    /* -----------------------------------
     * Create a numbered list using macros
     * -----------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $tf = 0;
    
    /* Add a heading line */
    $p->fit_textline("Page 3: Numbered list using macros of inline options",
	$llx, $ury-17, $head_optlist);
  
    /* Setting and resetting the indentation value is cumbersome, especially
     * since it is required for each paragraph. A more elegant solution
     * defines a macro called "list". For convenience it defines a macro
     * "indent" which is used as a constant. 
     * The "leftindent" option specifies the distance from the left margin.
     * The "parindent" option, which is set to the negative of "leftindent",
     * cancels the indentation for the first line of each paragraph.
     * The options "hortabsize", "hortabmethod", and "ruler" specify a tab
     * stop which corresponds to "leftindent". It makes the text after the
     * number to be indented with the amount specified in "leftindent".
     * Furthermore, the Textflow contains the tab stops.
     */
    $text =
	"<macro " .
	"{indent {22} " .
	"list {parindent=-&indent leftindent=&indent hortabsize=&indent " .
	"hortabmethod=ruler ruler={&indent}}" .
	"}><&list>" .
	"1.\tLong Distance Glider<nextline>With this paper rocket you " .
	"can send all your messages even when sitting in a hall or in " .
	"the cinema pretty near the back.\n\n" .
	"2.\tGiant Wing<nextline>An unbelievable sailplane! It is " .
	"amazingly robust and can even do aerobatics. But it is best " .
	"suited to gliding.\n\n" .
	"3.\tCone Head Rocket<nextline>This paper arrow can be thrown " .
	"with big swing. We launched it from the roof of a hotel. It " .
	"stayed in the air a long time and covered a considerable " .
	"distance.\n\n" .
	"4.\tSuper Dart<nextline>The super dart can fly giant loops " .
	"with a radius of 4 or 5 meters and cover very long distances. " .
	"Its heavy cone point is slightly bowed upwards to get the lift " .
	"required for loops.";
    
    $optlist =
	"fontname=Helvetica fontsize=12 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify leading=140%";
    
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Place the Textflow */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury-34, "");
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
    header("Content-Disposition: inline; filename=numbered_list.pdf");
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
