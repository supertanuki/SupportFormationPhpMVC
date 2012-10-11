<?php
/* $Id: shadowed_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Shadowed text:
 * Create a shadowed text line
 * 
 * To create a shadowed text line use the "shadow" option of fit_textline() and
 * the suboptions "offset" for setting the position, "fillcolor" for defining 
 * the color, and "gstate" for applying advanced graphics settings for the
 * shadow.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
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
$title = "Shadowed Text";

/* Required minimum PDFlib version */
$requiredversion = 800;
$requiredvstr = "8.0.0";

$imagefile = "cambodia_bayon3.jpg";
$text = "the faces of Bayon";

$x1 = 20; $x2 = 360; $yoff = 60;
$y = 550;

$bigfontsize = 35; $smallfontsize = 15;

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

    $normalfont = $p->load_font("Helvetica", "unicode", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(842, 595, "");

    /* Define general options for small and big fonts */
    $smallfont_opts = "font=" . $normalfont . " fontsize="
	. $smallfontsize . " ";
    $bigfont_opts = "font=" . $boldfont . " fontsize=" . $bigfontsize
	. " ";

    /* Ouput a heading */
    $p->fit_textline("Output of fit_textline()", $x1, $y, $smallfont_opts);
    $p->fit_textline("Options of fit_textline()", $x2, $y, $smallfont_opts);

    /*
     * ------------------------------------------------------ Output
     * shadowed text with default shadow option values
     * ------------------------------------------------------
     */
    $shadow_opts = "shadow={} ";
    $p->fit_textline($text, $x1, $y -= $yoff, $bigfont_opts . $shadow_opts);

    /* Output a description of the shadow options */
    $p->fit_textline($shadow_opts, $x2, $y + $bigfontsize / 4,
	    $smallfont_opts);

    /*
     * -------------------------------------------------------------
     * Output shadowed text with varied offset (Default is {5% -5%})
     * -------------------------------------------------------------
     */
    $shadow_opts = "shadow={offset={10% 8%}} ";
    $p->fit_textline($text, $x1, $y -= $yoff, $bigfont_opts . $shadow_opts);

    /* Output a description of the shadow options */
    $p->fit_textline($shadow_opts, $x2, $y + $bigfontsize / 4,
	    $smallfont_opts);

    /* Output shadowed text with default shadow option values */
    $shadow_opts = "shadow={offset={-10% 8%}} ";
    $p->fit_textline($text, $x1, $y -= $yoff, $bigfont_opts . $shadow_opts);

    /* Output a description of the shadow options */
    $p->fit_textline($shadow_opts, $x2, $y + $bigfontsize / 4,
	    $smallfont_opts);

    /* Output shadowed text with default shadow option values */
    $shadow_opts = "shadow={offset={-10% -8%}} ";
    $p->fit_textline($text, $x1, $y -= $yoff, $bigfont_opts . $shadow_opts);

    /* Output a description of the shadow options */
    $p->fit_textline($shadow_opts, $x2, $y + $bigfontsize / 4,
	    $smallfont_opts);

    /*
     * ------------------------------------------- Output shadowed text
     * with varied fill color
     * -------------------------------------------
     */
    $shadow_opts = "shadow={fillcolor={rgb 0.28 0.81 0.8} offset={10% -10%}} ";

    $color_opts = "fillcolor={rgb 0 0.5 0.52}";
    $p->fit_textline($text, $x1, $y -= $yoff, $bigfont_opts . $shadow_opts
	. $color_opts);

    /* Output the shadow options */
    $p->fit_textline($shadow_opts, $x2, $y + $bigfontsize / 4,
	    $smallfont_opts);

    /*
     * Save the current graphics state. The save/restore of the current
     * state is not necessarily required, but it will help you get back
     * to a graphics state without any transparency.
     */
    $p->save();

    /* Output a background image */
    $p->fit_image($image, $x1, 50, "boxsize={170 170} fitmethod=meet");

    /*
     * Create an extended graphics state with transparency set to 50%,
     * using create_gstate() with the "opacityfill" option set to 0.5.
     */
    $gstate = $p->create_gstate("opacityfill=.5");

    /*
     * Display some white shadowed text which will be transparent
     * according to the graphics state supplied
     */
    $shadow_opts = "shadow={fillcolor={rgb 0.87 0.72 0.52} "
	. "offset={8% -8%}} gstate=" . $gstate;

    $color_opts = " fillcolor={rgb 1 1 1}";

    $p->fit_textline("the", $x1, 185, $bigfont_opts . $shadow_opts
	. $color_opts);
    $p->fit_textline("faces", $x1, 145, $bigfont_opts . $shadow_opts
	. $color_opts);
    $p->fit_textline("of", $x1, 105, $bigfont_opts . $shadow_opts
	. $color_opts);
    $p->fit_textline("Bayon", $x1, 65, $bigfont_opts . $shadow_opts
	. $color_opts);

    /* Restore the current graphics state */
    $p->restore();

    /* Output the shadow options */
    $p->fit_textline($shadow_opts, $x2, 145, $smallfont_opts);
    $p->fit_textline("(gstate is created with \"opacityfill=.5\")", $x2,
	115, $smallfont_opts);

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=shadow_text.pdf");
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
