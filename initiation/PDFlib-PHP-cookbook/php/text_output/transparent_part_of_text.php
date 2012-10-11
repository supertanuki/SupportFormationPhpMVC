<?php
/* $Id: transparent_part_of_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Transparent part of text:
 * Create transparent text and highlight it with transparent rectangles
 * 
 * Use the "opacityfill" and "opacitystroke" options of create_gstate() to 
 * create an explicit graphics state with transparency settings. 
 * Place a transparent text line in two different ways. 
 * First use set_gstate() to set the explicit graphics state before placing the
 * text line. 
 * Second use the "gstate" option of fit_textline(). 
 * Output a Textflow with one word highlighted with a transparent rectangle
 * using the "gstate" suboption of the "matchbox" option of add_textflow().
 * Output a transparent Textflow with one word surrounded by the transparent
 * borders of a rectangle using the "gstate" suboption of the "matchbox" option
 * and the "gstate" option of add_textflow().
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

    /* Check whether the required minimum PDFlib version is available */
function required_pdflib_version_available( $p, $version){
    
    $major = $p->get_value("major", 0); 
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);
	   
    if ($major*100 + $minor*10 + $revision < $version) {
	return false;
    }
    else {
	return true;
    }
}

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Transparent Part of Text";

/* Required minimum PDFlib version */
$requiredversion = 800;
$requiredvstr = "8.0.0";

$imagefile = "nesrin.jpg";
$tf = 0;

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
    
    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page */
    $p->begin_page_ext(842, 595, "");
    
    /* Draw a yellow background rectangle */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->rect(0, 0, 842, 595);
    $p->fill(); 
    
    /* Output a background image */
    $p->fit_image($image, 50, 50, "");
    
    /* Save the current graphics state. The save/restore of the current
     * state is not necessarily required, but it will help you get back to
     * a graphics state without any transparency.
     */
    $p->save();
    
    /* Create an extended graphics state with transparency set to 50%, using
     * create_gstate() with the "opacityfill" option set to 0.5.
     */
    $gstate = $p->create_gstate("opacityfill=.5");
    
    /* Apply the extended graphics state */
    $p->set_gstate($gstate);
    
    /* Display some text which will be transparent according to the
     * graphics state set
     */
    $p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
    $p->fit_textline("Page 1: My favorite holiday photo", 100, 100, 
	"font=" . $font . " fontsize=40");
    
    /* Restore the current graphics state */
    $p->restore();
    
    $p->end_page_ext("");
    
    
/* -----------------
* The following code will only be executed if the minimum required PDFlib
* version is available
* -----------------
*/
    /* If the minimum required version is not available output a page with
     * an appropriate comment
     */
    if (!required_pdflib_version_available($p, $requiredversion)) {
	$p->begin_page_ext(842, 595, "");
	$optlist = "font=" . $font . " fontsize=14 ";
	$p->fit_textline("Page 2: PDFlib " . $requiredvstr . " specific " .
	    "output is omitted since a lower PDFlib version has been used",
	    50, 470, $optlist);
	$p->end_page_ext("");
    }
    /* If the minimum required version is available proceed as desired */
    else {
	/* Start page */
	$p->begin_page_ext(842, 595, "");
	
	/* Draw a yellow background rectangle */
	$p->setcolor("fill", "rgb", 1, 0.9, 0.58, 0);
	$p->rect(0, 0, 842, 595);
	$p->fill(); 
	
	/* Output a background image */
	$p->fit_image($image, 50, 50, "");
	
	
	/* ------------------------------
	 * Output a transparent text line
	 * ------------------------------
	 */
	
	/* Create an explicit graphics state with the transparency for fill 
	 * operations set to 40%
	 */
	$gstate = $p->create_gstate("opacityfill=0.4");
	
	/* Display a text line with the explicit graphics state applied. The
	 * text will appear transparent. 
	 */
	$optlist = 
	    "font=" . $font . " fontsize=40 " . 
	    "fillcolor={rgb 1 0.9 0.58} " .
	    "gstate=" . $gstate;
	
	$p->fit_textline("Page 2: My favorite holiday photo", 100, 470, 
	    $optlist);
	
	
	/* --------------------------------------------------------
	 * Output a Textflow with the word "favorite" filled with a 
	 * transparent rectangle
	 * --------------------------------------------------------
	 */
	
	/* Create an explicit graphics state with the transparency for fill
	 * operations set to 40%
	 */
	$gstate = $p->create_gstate("opacityfill=0.4");
	
	/* Define the general Textflow options */
	$optlist =
	    "fontname=Helvetica-Bold fontsize=30 encoding=unicode " .
	    "leading=120% charref fillcolor={rgb 1 0.9 0.58} ";
	
	/* Add parts of the Textflow */
	$text = "Here you can see my ";  
	$tf = $p->add_textflow($tf, $text, $optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Use the "matchbox" option to define a matchbox rectangle to be 
	 * filled on parts of the Textflow. To fill the rectangle 
	 * transparently the matchbox is provided with the graphics state
	 * created above.
	 */
	$mopts = "matchbox={name=mymatchbox boxheight={ascender descender}" .
	    " fillcolor={rgb 1 0.9 0.58} gstate=" . $gstate .
	    " offsetleft=-4 offsetright=4 offsettop=4} ";
	
	/* Use the "gstate" option to apply the explicit graphics state
	 * defined above
	 */
	$gopts = "gstate=" . $gstate;
	
	/* Add a part of the Textflow with the "matchbox" and "gstate" 
	 * options. That part of the text will be filled with a transparent
	 * rectangle and will appear transparent.
	 */
	$text = "favorite";   
	$tf = $p->add_textflow($tf, $text, $optlist . $mopts . $gopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Define the "matchbox=end" option to end the matchbox */
	$mopts = "matchbox=end ";
	
	/* Create another explicit graphics state with no transparency */
	$gstate = $p->create_gstate("opacityfill=1");
	
	/* Use the "gstate" option to apply the explicit graphics state
	 * defined above
	 */
	$gopts = "gstate=" . $gstate;
	
	/* Add parts of the text with the "matchbox=end" option applied.
	 * The matchbox rectangle will end before the text to be added.
	 * The text will not appear transparent any more.
	 */
	$text = " holiday photo.";
	$tf = $p->add_textflow($tf, $text, $optlist . $mopts . $gopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$result = $p->fit_textflow($tf, 100, 200, 300, 400, "");
	if ($result == "_stop")
	{
	    /* Check for errors or more text to be placed */
	}
	$p->delete_textflow($tf);
	
	
	/* -----------------------------------------------------------------
	 * Output a transparent Textflow with the word "favorite" surrounded
	 * by the transparent borders of a rectangle.
	 * -----------------------------------------------------------------
	 */
	$tf = 0;
	
	/* Create an explicit graphics state with transparency for stroking
	 * and filling set to 40%
	 */
	$gstate = $p->create_gstate("opacitystroke=0.4 opacityfill=0.4");
	
	/* Define the general Textflow options including the graphics state
	 * defined above
	 */
	$optlist =
	    "fontname=Helvetica-Bold fontsize=30 encoding=unicode " .
	    "leading=120% charref fillcolor={rgb 1 0.9 0.58} " .
	    "gstate=" . $gstate;
	
	/* Add parts of the Textflow */
	$text = "This is my ";  
	$tf = $p->add_textflow($tf, $text, $optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Use the "matchbox" option for defining a matchbox rectangle to be 
	 * stroked around parts of the Textflow. To stroke the rectangle 
	 * transparently the matchbox is provided with the graphics state 
	 * created above.
	 */
	$mopts = " matchbox={name=mymatchbox gstate=" . $gstate . 
	    " boxheight={ascender descender} linecap=projecting" .
	    " borderwidth=3 strokecolor={rgb 1 0.9 0.58}" .
	    " offsetleft=-4 offsetright=4 offsettop=4}";
	
	/* Add a part of the Textflow with the matchbox option */
	$text = "favorite";   
	$tf = $p->add_textflow($tf, $text, $optlist . $mopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Define the "matchbox=end" option to end the matchbox */
	$mopts = " matchbox=end";
	
	/* Add parts of the text with the "matchbox=end" option.
	 * The matchbox rectangle will end before the text to be added.
	 */
	$text = " holiday photo.";
	$tf = $p->add_textflow($tf, $text, $optlist . $mopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$result = $p->fit_textflow($tf, 650, 100, 780, 280, "");
	if ($result != "_stop")
	{
	    /* Check for errors or more text to be placed */
	}
	$p->delete_textflow($tf);
 
	$p->end_page_ext("");
    }
/* ---------------
* The previous code has only been executed if the minimum required PDFlib 
* version is available
* ---------------
*/

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=transparent_part_of_text.pdf");
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
