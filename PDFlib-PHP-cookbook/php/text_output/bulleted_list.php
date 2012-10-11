<?php
/* $Id: bulleted_list.php,v 1.1 2012/04/03 10:29:12 rp Exp $
 * Bulleted list:
 * Output a bulleted list 
 * 
 * Use the "leftindent" option of add_textflow() to create a bulleted or 
 * numbered list, respectively. Use "leftindent=0" for adding the bullet or
 * number and "leftindent=22" for adding the list item.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$outfile = "";
$title = "Bulleted List";
$tf = 0; 
$llx=50; $lly=200; $urx=500; $ury=700;

$head_optlist =
    "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
    "fillcolor={cmyk 1 0.5 0.2 0}";

$items = array(
    "Long Distance Glider\nWith this paper rocket you can send all your " .
    "messages even when sitting in a hall or in the cinema pretty near " .
    "the back.",

    "Giant Wing\nAn unbelievable sailplane! It is amazingly robust and " .
    "can even do aerobatics. But it is best suited to gliding.",

    "Cone Head Rocket\nThis paper arrow can be thrown with big swing. " .
    "We launched it from the roof of a hotel. It stayed in the air a " .
    "long time and covered a considerable distance.",

    "Super Dart\nThe super dart can fly giant loops with a radius of 4 " .
    "or 5 meters and cover very long distances. Its heavy cone point is " .
    "slightly bowed upwards to get the lift required for loops."
);
    
try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    
    /* ----------------------
     * Create a bulleted list
     * ----------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create the Textflow. First, add a heading */
    $tf = $p->add_textflow($tf, "Page 1: Bulleted list\n\n\n", $head_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add list bullets and items. For the list bullets, use "leftindent=0"
     * and for the items, use "leftindent=22" to indent the item text.
     * For list bullets, use the "escapesequence" option to enable PDFlib to
     * resolve "\\xB7" as the hexadecimal code "0xB7" for the bullet in the
     * Symbol font. 
     */
    $num_optlist =
	"fontname=Symbol fontsize=12 encoding=builtin escapesequence " .
	"fillcolor={cmyk 1 0.5 0.2 0} leftindent=0 textformat=bytes";
    
    $item_optlist =
	"fontname=Helvetica fontsize=12 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify leading=140% leftindent=22 ";
    
    for ($i = 0; $i < 4; $i++) {
	$tf = $p->add_textflow($tf, "\\xB7", $num_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$tf = $p->add_textflow($tf, $items[$i] . "\n\n", $item_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    /* Place the Textflow */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    if (!$result == "_stop") {
	/* Check for further action */
    }
    
    $p->delete_textflow($tf);
    
    $p->end_page_ext("");
    
     
    /* ----------------------
     * Create a numbered list
     * ----------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create the Textflow. First, add a heading */
    $tf = 0;
    
    $tf = $p->add_textflow($tf, "Page 2: Numbered list\n\n\n", $head_optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add list numbers and list items. For the list numbers, use 
     * "leftindent=0" and for the items, use "leftindent=22" to indent the
     * item text.
     */
    $num_optlist =
	"fontname=Helvetica-Bold fontsize=12 encoding=unicode " .
	"fillcolor={cmyk 1 0.5 0.2 0} leftindent=0";
    
    $item_optlist =
	"fontname=Helvetica fontsize=12 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify leading=140% leftindent=22";
    
    for ($i = 0; $i < 4; $i++) {
	$tf = $p->add_textflow($tf, $i + 1, 
	    $num_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$tf = $p->add_textflow($tf, $items[$i] . "\n\n", $item_optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    /* Place the Textflow */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
    if (!$result == "_stop") {
	    /* Check for further action */
    }
    
    $p->delete_textflow($tf);
	    
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=bullet_list.pdf");
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
