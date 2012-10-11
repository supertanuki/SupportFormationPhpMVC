<?php
/* $Id: tagged_pdf_with_textflow.php,v 1.3 2010/01/26 07:58:46 stm Exp $
 * Tagged PDF with Textflow:
 * Create a Tagged PDF containing a Textflow with appropriate structure elements
 * over several pages
 *
 * Using begin_item(), activate_item() and end_item(), define a nested Tagged
 * PDF structure with a single structure element containing a Textflow spread
 * over several pages.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7.0.3
 * The basic code also works with PDFlib/PDFlib+PDI/PPS 7.0.0-7.0.2, but these
 * versions require the "lang" option in begin_document() or end_document().
 * Required data: none
 */

$outfile = "";
$title = "Tagged PDF with Textflow";


/* Required minimum PDFlib version */
$requiredversion = 703;
$requiredvstr = "7.0.3";

$tf1 = 0;
$tf2 = 0; 
$llx=50; $lly=50; $urx=400; $ury=400;
$width = 450; $height=450;

$optlist =
    "fontname=Helvetica fontsize=14 encoding=unicode " .
    "fillcolor={gray 0} charref alignment=justify";

/* Text placed on one or more pages. Soft hyphens are marked
 * with the character reference "&shy;" (character references are enabled
 * by the charref option).
 */
$text1=
    "Our paper planes are the ideal way of passing the time. We offer " .
    "revolutionary new develop&shy;ments of the traditional common paper " .
    "planes. If your lesson, conference, or lecture turn out to be " .
    "deadly boring, you can have a wonderful time with our planes.\n\n" .
    "All our models are fol&shy;ded from one paper sheet. They are " .
    "exclu&shy;sively folded with&shy;out using any adhesive. Several " .
    "models are equipped with a folded landing gear enabling a safe " .
    "landing on the intended loca&shy;tion provided that you have aimed " .
    "well. Other models are able to fly loops or cover long distances. " .
    "Let them start from a vista point in the mountains and see where " .
    "they touch the ground.\n\n" .
    "Have a look at our new paper plane models!\n\n" .
    "Long Distance Glider\nWith this paper rocket you can send all your " .
    "messages even when sitting in a hall or in the cinema pretty near " .
    "the back.\n\nGiant Wing\nAn unbelievable sailplane! It is amazingly " .
    "robust and can even do aerobatics. But it best suited to gliding." .
    "\n\nCone Head Rocket\nThis paper arrow can be thrown with big " .
    "swing. We launched it from the roof of a hotel. It stayed in the " .
    "air a long time and covered a considerable distance.\n\n" .
    "Super Dart\nThe super dart can fly giant loops with a radius of 4 or" .
    " 5 meters and cover very long distances. Its heavy cone point is " .
    "slightly bowed upwards to get the lift required for loops.\n\n";

$text2=
    "To fold the famous rocket looper proceed as follows:\n\n" .
    "Take an A4 sheet. Fold it lengthwise in the middle. Then, fold the " .
    "upper corners down. Fold the long sides inwards that the points A " .
    "and B meet on the central fold. Fold the points C and D that the " .
    "upper corners meet with the central fold as well. Fold the plane in " .
    "the middle. Fold the wings down that they close with the lower " .
    "border of the plane.";

try {
    $p = new PDFlib();

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

    /* Open the document.
     * "tagged=true" opens the document in Tagged PDF mode.
     * "lang=en" indicated the predominant document language as English.
     * "openmode=tagged"
     */
    if ($p->begin_document($outfile, "tagged=true lang=EN") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Force automatic word breaks */
    $p->set_parameter("autospace", "true");

    /* Feed the first text to the first Textflow object */
    $tf1 = $p->add_textflow($tf1, $text1, $optlist);
    if ($tf1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Feed the second text to the second Textflow object */
    $tf2 = $p->add_textflow($tf2, $text2, $optlist);
    if ($tf2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Open a structure element of type "Article" */
    $id_art = $p->begin_item("Art", "Title = {Flyer}");

    /* Start the first page */
    $p->begin_page_ext($width, $height, "");

    /* Open a structure element of type "Section" for all text contents to
     * be included.
     */
    $id_sect = $p->begin_item("Sect", "Title = {Topic}");

    /* Open a structure element of type "H1" */
    $id_h1 = $p->begin_item("H1", "Title = {Intro}");
    $p->setfont($font, 20);
    $p->fit_textline("Our Paper Planes", $llx, $ury + 20, "");

    /* Close the structure element of type "H1" */
    $p->end_item($id_h1);

    /* Open a structure element of type "P" for the first Textflow to be
     * included. All parts of the Textflow, i.e. all calls to
     * PDF_fit_textflow() with the specific Textflow handle, should be
     * contained in a single structure element.
     * */
	$id_p = $p->begin_item("P", "Title = {Description}");

	    /* Loop until all of the text is placed; create new pages
     * as long as more text needs to be placed.
     */
	do
    {
	    $result = $p->fit_textflow($tf1, $llx, $lly, $urx, $ury,
		"linespreadlimit=140%");

	    $p->end_page_ext("");

	/* Start a new page */
	    $p->begin_page_ext($width, $height, "");

    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Fit the second Textflow */
	$result = $p->fit_textflow($tf2, $llx, $lly, $urx, $ury,
	    "linespreadlimit=140%");

    /* Close the structure element of type "P" */
    $p->end_item($id_p);

    $p->setfont($font, 20);
    $p->fit_textline("Read more ...", $llx, $lly - 20, "");

    $p->end_page_ext("");

    /* Close the structure element of type "Section" */
    $p->end_item($id_sect);

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

    $p->delete_textflow($tf1);
    $p->delete_textflow($tf2);

    /* Close the structure element of type "Article" */
    $p->end_item($id_art);

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=tagged_pdf_with_textflow.pdf");
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

