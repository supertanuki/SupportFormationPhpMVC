<?php
/* $Id: continue_note_after_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * 
 * Continue note after text:
 * Insert a dot sequence or an arrow image at the end of a Textflow.
 *
 * Place a Textflow while inserting a dot sequence at the end of each fitbox.
 * Use the "width" key of info_textline() to retrieve the width of the dot 
 * sequence. Fit the Textflow with the "createlastindent" option set to the 
 * width of the dot sequence to specify appropriate empty space at the end of 
 * the fitbox. Then, fill the space between the last word and the right fitbox
 * border with the dot sequence using fit_textline(). For placing the dot 
 * sequence use the font and font size of the Textflow by retrieving it with
 * info_textflow() and the "lastfont" and "lastfontsize" options.
 * Similarly, place a Textflow but insert an arrow image at the end of each
 * fitbox.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/* Check whether the required minimum PDFlib version is available */

function required_pdflib_version_available($p, $version){

    $major = $p->get_value("major", 0);
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);

    if ($major * 100 + $minor * 10 + $revision < $version) {
	return false;
    }
    else {
	return true;
    }
}

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Continue Note after Text";

/* Required minimum PDFlib version */
$requiredversion = 800;
$requiredvstr = "8.0.0";

$imagefile = "arrow.jpg";

$tf = 0;

$imagewidth = 30;

$fontsize = 20;

$llx = 100; $urx = 400;
$lly_start = 630; $ury_start = 780;
$lly = $lly_start; $ury = $ury_start;
$yoffset = 190;

$dots = "...";

$tftext = 
    "Our paper planes are the ideal way of passing the time. We "
    . "offer revolutionary new developments of the traditional common "
    . "paper planes. If your lesson, conference, or lecture turn out "
    . "to be deadly boring, you can have a wonderful time with our "
    . "planes. All our models are folded from one paper sheet. They "
    . "are exclusively folded without using any adhesive. Several "
    . "mod&shy;els are equipped with a folded landing gear enabling a "
    . "safe land&shy;ing on the intended location pro&shy;vided that "
    . "you have aimed well. Other models are able to fly loops or "
    . "cover long distances. Let them start from a vista point in the "
    . "mountains and see where they touch the ground. "
    . "Have a look at our new paper plane models!";

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

    if (!required_pdflib_version_available($p, $requiredversion)) {
	throw new Exception("Error: PDFlib " . $requiredvstr
		. " or above is required");
    }

    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * ----------------------------------------------------------------
     * Place a Textflow in one ore more fitboxes while inserting
     * three dots at the end of each fitbox
     * ----------------------------------------------------------------
     */

    /* Start page */
    $p->begin_page_ext(595, 842, "");

    $p->setfont($font, 16);
    $p->fit_textline("Page 1: Three dots will be appended at the end of "
	    . "each fitbox", 20, 810, "");

    /* Add the Textflow */
    $optlist = "fontname=Helvetica fontsize=" . $fontsize
	    . " encoding=unicode "
	    . "leading=120% charref alignment=justify ";

    $tf = $p->add_textflow($tf, $tftext, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Retrieve the length of the text to be inserted at the end of the
     * Textflow
     */
    $optlist = "font=" . $font . " fontsize=" . $fontsize;
    $textwidth = $p->info_textline($dots, "width", $optlist);

    /* Initialize the fitbox rectangle */
    $lly = $lly_start;
    $ury = $ury_start;

    /*
     * Loop until all of the text is placed; create new fitboxes as long
     * as more text needs to be placed.
     */
    do {
	/*
	 * Prepare the option list with the "createlastindent" option 
	 * set to the text width retrieved for the dots above
	 */
	$p->setcolor("stroke", "rgb", 0.99, 0.44, 0.06, 0);

	$optlist = "verticalalign=justify linespreadlimit=120% "
		. "createlastindent={rightindent=" . $textwidth . "} "
		. "showborder";

	/* Place the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, $optlist);

	/* Check for errors */
	if ($result == "_boxempty")
	    throw new Exception("Error: Textflow box too small");

	if ($result != "_stop") {
	    /* Get the text position at the end of the Textflow */
	    $textendx = $p->info_textflow($tf, "textendx");
	    $textendy = $p->info_textflow($tf, "textendy");

	    /*
	     * Get the font and font size used in the last text line of
	     * the fitbox
	     */
	    $lastfont = $p->info_textflow($tf, "lastfont");
	    $lastfontsize = $p->info_textflow($tf, "lastfontsize");

	    /* Place the dots at the end of the Textflow */
	    $optlist = "font=" . $lastfont . " fontsize=" . $lastfontsize;
	    $p->fit_textline($dots, $textendx, $textendy, $optlist);

	    $lly -= $yoffset;
	    $ury -= $yoffset;
	}

	/* 
	 * "_boxfull" means we must continue because there is more text 
	 */
    }
    while ($result == "_boxfull");

    $p->delete_textflow($tf);

    $p->end_page_ext("");

    /*
     * -----------------------------------------------------------------
     * Place a Textflow in one ore more fitboxes while inserting an
     * image containing an arrow at the end of each fitbox
     * -----------------------------------------------------------------
     */

    /* Start page */
    $p->begin_page_ext(595, 842, "");

    $tf = 0;

    $p->setfont($font, 16);
    $p->fit_textline(
	    "Page 2: An arrow image will be appended at the end "
		    . "of each fitbox", 20, 810, "");

    /*
     * Retrieve the length of the text to be inserted at the end of the
     * Textflow
     */
    $optlist = "font=" . $font . " fontsize=" . $fontsize;

    /* Define the Textflow options */
    $optlist = "fontname=Helvetica fontsize=" . $fontsize
	    . " encoding=unicode "
	    . "leading=120% charref alignment=justify ";

    /* Add the Textflow */
    $tf = $p->add_textflow($tf, $tftext, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Initialize the fitbox rectangle */
    $lly = $lly_start;
    $ury = $ury_start;

    /*
     * Loop until all of the text is placed; create new fitboxes as long
     * as more text needs to be placed.
     */
    do {
	/*
	 * Prepare the option list with the "createlastindent" option 
	 * set to the text width retrieved for the dots above
	 */
	$p->setcolor("stroke", "rgb", 1, 0, 0, 0);

	$optlist = "verticalalign=justify linespreadlimit=120% "
		. "createlastindent={rightindent=" . $imagewidth . "}";

	/* Place the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, $optlist);

	/* Check for errors */
	if ($result == "_boxempty")
	    throw new Exception("Error: Textflow box too small");

	if ($result != "_stop") {
	    /* Get the text position at the end of the Textflow */
	    $textendx = (int) $p->info_textflow($tf, "textendx");
	    $textendy = (int) $p->info_textflow($tf, "textendy");

	    /* Place the image at the end of the Textflow */
	    $optlist = "boxsize={" . $imagewidth . " " . ($fontsize * 0.8)
		    . "} " . "fitmethod=meet position={right top}";

	    $p->fit_image($image, $textendx, $textendy, $optlist);

	    $lly -= $yoffset;
	    $ury -= $yoffset;
	}

	/* 
	 * "_boxfull" means we must continue because there is more text
	 */
    }
    while ($result == "_boxfull");

    $p->delete_textflow($tf);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=continue_note_after_text.pdf");
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
