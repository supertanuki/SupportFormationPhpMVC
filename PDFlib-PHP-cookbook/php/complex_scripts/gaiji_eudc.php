<?php
/* $Id: gaiji_eudc.php,v 1.2 2012/05/03 14:00:42 stm Exp $
 * 
 * Demonstrate the use of SING and EUDC fonts for merging a
 * Gaiji character into an existing font 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: MS Mincho font, available at
 * http://www.pdflib.com/download/resources/japanese-resource-kit/
 * Please put the file "MS Mincho.ttf" into the "extra_input" directory. 
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
/* Extra search path for files not delivered with the Cookbook */
$extra_searchpath = dirname(dirname(dirname(__FILE__)))."/extra_input";
$outfile = "";
$title = "Gaiji and EUDC Fonts";

$llx = 50; $lly = 50; $urx = 800; $ury = 550;

$headers = array( "Use case",
    "Option list for the 'fallbackfonts' option", "Base font",
    "With fallback font" );

class testcase {
    function testcase($usecase, $fontname, $encoding, $fallbackoptions, $text) {
	$this->usecase = $usecase;
	$this->fontname = $fontname;
	$this->encoding = $encoding;
	$this->fallbackoptions = $fallbackoptions;
	$this->text = $text;
    }
}

$testcases = array(
    /*
     * Font with end-user defined character (EUDC) with Unicode value
     * U+E000. We map the Unicode range U+E000-U+E0FF to the EUDC
     * font.
     */
    new testcase("Gaiji with EUDC font", "MS Mincho",
	"unicode",
	"{fontname=EUDC encoding=unicode forcechars={U+E000-U+E0FF} "
	    . "fontsize=140% textrise=-20%}", "Gaiji: &#xE000;"),

    /*
     * SING fontlet containing a single gaiji character with Unicode
     * value U+E000. Usage of "forcechars=gaiji" allows to automatically
     * map the Unicode value of the character in the SING font, although
     * the Unicode value must also be explicitly known for making use of
     * the character.
     */
    new testcase("Gaiji with SING font", "MS Mincho",
	"unicode",
	"{fontname=PDFlibWing encoding=unicode forcechars=gaiji}",
	"Gaiji: &#xE000;"), );

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("SearchPath", $extra_searchpath);
    $p->set_parameter("charref", "true");
    $p->set_parameter("glyphcheck", "replace");
    $p->set_parameter("textformat", "utf8");

    /*
     * This means that formatting and other errors will raise an
     * exception. This simplifies our sample code, but is not
     * recommended for production code.
     */
    $p->set_parameter("errorpolicy", "exception");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start Page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    $table = 0;

    /* Table header */
    for ($i = 0; $i < count($headers); $i++) {
	$col = $i + 1;

	$optlist = "fittextline={fontname=Helvetica-Bold "
	    . "encoding=unicode fontsize=11} margin=4";
	$table = $p->add_table_cell($table, $col, 1, $headers[$i], $optlist);
    }

    /* Create fallback samples, one use case per row */
    for ($i = 0; $i < count($testcases); $i++) {
	$row = $i + 2;
	$testcase = $testcases[$i];
	$col = 1;

	/* Column 1: description of the use case */
	$optlist = "fittextline={fontname=Helvetica encoding=unicode "
		    . "fontsize=11} margin=4";
	$table = $p->add_table_cell($table, $col++, $row, $testcase->usecase,
		$optlist);

	/* Column 2: reproduce option list literally */
	$optlist = "fittextline={fontname=Helvetica encoding=unicode " .
			"fontsize=10} margin=4";
	$table = $p->add_table_cell($table, $col++, $row,
		$testcase->fallbackoptions, $optlist);

	/* Column 3: text with base font */
	$optlist = "fittextline={fontname={" . $testcase->fontname . "}"
		. " encoding=" . $testcase->encoding
		. " fontsize=11 } margin=4";
	$table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		$optlist);

	/* Column 4: text with base font and fallback fonts */
	$optlist = "fittextline={fontname={" . $testcase->fontname . "}"
		. " encoding=" . $testcase->encoding
		. " fontsize=11 fallbackfonts={"
		. $testcase->fallbackoptions . "}} margin=4";
	$table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		$optlist);
    }

    /* Place the table */
    $optlist = "header=1 fill={{area=rowodd "
	    . "fillcolor={gray 0.9}}} stroke={{line=other}} ";
    $result = $p->fit_table($table, $llx, $lly, $urx, $ury, $optlist);

    if ($result == "_error") {
	throw new Exception("Couldn't place table: " . $p->get_errmsg());
    }

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=nested_blocks.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in starter_block sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>


