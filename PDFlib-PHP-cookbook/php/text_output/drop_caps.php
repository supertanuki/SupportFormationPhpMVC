<?php
/* $Id: drop_caps.php,v 1.2 2012/05/03 14:00:38 stm Exp $ 
 * Drop caps:
 * Create an initial drop cap at the beginning of some text
 * 
 * Set the initial character of a Textflow in larger type and drop it down
 * a bit into the text.
 * Place the initial character which covers several lines of text at a certain
 * text position within a Textflow. The "matchbox" and "matchbox end" inline
 * options indicate the rectangle of the character's fitbox. The "textrise" 
 * option with an appropriate negative value will drop the character some lines
 * down. The "createwrapbox" option indicates that the matchbox will be inserted
 * as wrap box for further text to wrap around.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Drop Caps";

$tf = 0;
$optlist = "";
    
$llx = 100; $lly = 50; $urx = 450; $ury = 800;
$t_fontsize = 16;      // font size of the text
$t_leading = 20;       // leading of the text
$c_num = 3;            // no. of lines for the drop cap to cover

$c_textrise = -(($c_num - 1) * $t_leading); // text rise of the drop cap
$c_fontsize = -($c_textrise * 1.8);     // font size of the drop cap

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Option list for the output of the initial drop character defining 
     * the two macros "cap_start" and "cap_end" for starting and ending the
     * drop ca$p->
     * The "matchbox" and "matchbox end" inline options indicate the 
     * rectangle of the character's fitbox. The "textrise" option with an
     * appropriate negative value will drop the character some lines down.
     * The "createwrapbox" option indicates that the matchbox will be 
     * inserted as wrap box for further text to wrap around.
     */
    $optlist = "fontname=Helvetica encoding=unicode alignment=justify " .
	"charref " .
	"macro " .
	"{cap_start {fontsize=" . $c_fontsize . " leading=" . $t_leading .
	"            textrise=" . $c_textrise .
	" matchbox={createwrapbox boxheight={leading textrise}}} " .
	"cap_end {matchbox=end fontsize=" . $t_fontsize . " textrise=0}}";
     
    /* Text to be placed on the page. Soft hyphens are marked with the 
     * character reference "&shy;" (character references are enabled by the
     * "charref" option).
     */
    $text =
	"<&cap_start>O<&cap_end>ur Paper Planes are the ideal way of " .
	"passing the time. We offer revolutionary " .
	"new develop&shy;ments of the traditional com&shy;mon " .
	"paper planes. If your lesson, conference, or lecture " .
	"turn out to be deadly boring, you can have a wonderful time " .
	"with our planes. All our models are fol&shy;ded from one paper " .
	"sheet. They are exclu&shy;sively folded with&shy;out using any " .
	"adhesive. Several models are equipped with a folded landing " .
	"gear enabling a safe landing on the intended loca&shy;tion " .
	"provided that you have aimed well. Other models are able to fly " .
	"loops or cover long distances. Let them start from a vista " .
	"point in the mountains and see where they touch the ground. ";
    

    /* Create the Textflow using the optlist defined above */
    $tf = $p->create_textflow($text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    do
    {
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
  
    /* Fit the Textflow. It will wrap around the matchbox defined for the
     * initial drop ca$p->
     */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");

    $p->end_page_ext("");

    /* "_boxfull" means we must continue because there is more text;
     * "_nextpage" is interpreted as "start new column"
     */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if (!$result == "_stop")
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
    header("Content-Disposition: inline; filename=drop_caps.pdf");
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
