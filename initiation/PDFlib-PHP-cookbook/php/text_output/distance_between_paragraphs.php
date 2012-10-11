<?php
/* $Id: distance_between_paragraphs.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Distance between paragraphs:
 * Control the distance between adjacent paragraphs
 * 
 * In many cases more distance between adjacent paragraphs is desired than
 * between the lines within a paragraph. To achieve this use the "nextline",
 * "leading", and "nextparagraph" options of add/create_textflow() to insert an
 * extra empty line with a suitable leading value and then start a new paragraph
 * with the initial leading. 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Distance between Paragraphs";

$tf = 0;

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
    $p->set_info("Title", $title );

    /* Create an A4 page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Text to be created. Gain some additional space between the first
     * and the second paragraph using the inline options
     * <nextline leading=150%>. This will create an empty line with its 
     * baseline having a distance of 150% from the last line of the previous
     * paragraph (where 100% equals the most recently set value of the font
     * size). 
     * With <nextparagraph leading=120%> a new line is started and the 
     * leading is reset to the initial value (defined in the option list
     * below).
     */
    $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new developments of the traditional common " .
	"paper planes. If your lesson, conference, or lecture turn out " .
	"to be deadly boring, you can have a wonderful time with our " .
	"planes. All our models are folded from one paper sheet. They " .
	"are exclusively folded without using any adhesive. Several " .
	"models are equipped with a folded landing gear enabling a safe " .
	"landing on the intended location provided that you have aimed " .
	"well. Other models are able to fly loops or cover long " .
	"distances. Let them start from a vista point in the mountains " .
	"and see where they touch the ground." .
	"<nextline leading=150%><nextparagraph leading=120%>" .
	"Have a look at our new paper plane models!" .
	"<nextline leading=80%><nextparagraph leading=120%>" .
	"Long Distance Glider: ".
	"With this paper rocket you can send all your messages even when " .
	"sitting in a hall or in the cinema pretty near the back. " .
	"<nextline leading=80%><nextparagraph leading=120%>" .
	"Giant Wing: " .
	"An unbelievable sailplane! It is amazingly robust and can even " .
	"do aerobatics. But it best suited to gliding." .
	"<nextline leading=80%><nextparagraph leading=120%>" .
	"Cone Head Rocket: " .
	"This paper arrow can be thrown with big swing. We launched it " .
	"from the roof of a hotel. It stayed in the air a long time and " .
	"covered a considerable distance. " .
	"<nextline leading=80%><nextparagraph leading=120%>" .
	"Super Dart: " .
	"The super dart can fly giant loops with a radius of 4 or 5 " .
	"metres and cover very long distances. Its heavy cone point is " .
	"slightly bowed upwards to get the lift required for loops." .
	"<nextline leading=80%><nextparagraph leading=120%>";
    
    /* Some more text to be added */
    $moretext =
	"German Bi-Plane: " .
	"Brand-new and ready for take-off. If you have lessons in the " .
	"history of aviation you can show your interest by letting it " .
	"land on your teacher's desk.";
    
    /* Option list to create the Textflow. 
     * The leading is initially set to 120%.
     */
    $optlist = "fontname=Helvetica fontsize=14 encoding=unicode " .
	"fillcolor={gray 0} leading=120% alignment=justify";
	 
    /* Create the Textflow with the option list defined above */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Option list to add some more text in violet color for distinction. 
     * The leading is initially set to 120%.
     * With "nextline leading=150%" an empty line is created with a leading
     * of 150%.
     * With "nextparagraph leading=120%" a new line is started and the
     * leading is reset to the initial value of 120%.
     */
    $optlist = "fontname=Helvetica fontsize=14 encoding=unicode " .
	"fillcolor={rgb 0.95 0.5 0.95} leading=120% alignment=justify " .
	"nextline leading=150% nextparagraph leading=120%";
    
    /* Add some more text using the option list defined above */
    $tf = $p->add_textflow($tf, $moretext, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Fit the Textflow */
    $result = $p->fit_textflow($tf, 100, 100, 500, 700,
	"verticalalign=justify linespreadlimit=120% ");
    
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
    header("Content-Disposition: inline; filename=frame_around_image.pdf");
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
