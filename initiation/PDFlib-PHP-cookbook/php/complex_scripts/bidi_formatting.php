<?php
/* $Id: bidi_formatting.php,v 1.2 2012/05/03 14:00:42 stm Exp $
 * Starter sample for bidirectional text formatting
 * 
 * Demonstrate formatting of mixed left-to-right and right-to-left
 * (bidirectional) text with default settings and with user-supplied
 * Directional Formatting Codes as defined in Unicode.
 *  
 * Required software: PDFlib/PDFlib+PDI/PPS 8.0.0p3
 * "script=_auto" is not supported in the p1 and p2 version of PDFlib 8.0.0
 * Required data: Arabic font
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";

$llx = 50; $lly = 50; $urx = 800; $ury = 550;

$header = array( "Bidi formatting topic", "Raw input",
    "Reordered and shaped output" );

class shaping {
    function shaping($fontname, $optlist, $language, $text) {
	$this->fontname = $fontname;
	$this->optlist = $optlist;
	$this->language = $language;
	$this->text = $text;
    }
}

$shapingsamples = array(
    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto charref",
	"Mixed part number with default settings (wrong)",
	"&#x0631;&#x0642;&#x0645;:  XY &#x0661;&#x0662;&#x0663; A"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto charref",
	"Mixed part number forced as LTR sequence",
	"&#x0631;&#x0642;&#x0645;:  &LRO;XY &#x0661;&#x0662;&#x0663; A&PDF;"),
		
    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto charref",
	"Mixed text with default settings (wrong order in RTL context)",
	"He said '&#x0645;&#x0631;&#x062D;&#x0628;&#x0627;!' (Hello!) to me"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto charref",
	"Mixed text with initial RLM (wrong parentheses)",
	"&RLM;He said '&#x0645;&#x0631;&#x062D;&#x0628;&#x0627;!' " .
	"(Hello!) to me"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto charref",
	"Mixed text with initial RLM and LRM after punctuation",
	"&RLM;He said '&#x0645;&#x0631;&#x062D;&#x0628;&#x0627;!' " .
	"&LRM;(Hello!) to me"),
		
    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto",
	"Symmetrical swapping of mirrored glyphs",
	"[&#x0646;&#x0644;&#x0627;&#x062D;&#x0638;] 3<4"),
		
    new shaping(
	"ScheherazadeRegOT",
	"shaping script=_auto leader={alignment=left text=.}",
	"Dot leaders: leader={alignment=left text=.}",
	"&#x0645;&#x0631;&#x062D;&#x0628;&#x0627;"),
	  
);

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /*
     * This means that formatting and other errors will raise an
     * exception. This simplifies our sample code, but is not
     * recommended for production code.
     */
    $p->set_parameter("errorpolicy", "exception");
    $p->set_parameter("textformat", "utf8");
    $p->set_parameter("charref", "true");

    /* Set an output path according to the name of the topic.
     * "direction=r2l" instructs Acrobat to treat the document as
     * right-to-left document.
     */
    if ($p->begin_document($outfile, "viewerpreferences={direction=r2l}") == -1) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", "bidi_formatting ");

    $table = 0;

    /* Create table header */
    for ($col = 0; $col < count($header); $col++) {
	$optlist =
	    "fittextline={fontname=Helvetica-Bold encoding=winansi "
	    . "fontsize=14} colwidth=" . ($col == 0 ? "40%" : "30%");
	$table = $p->add_table_cell($table, $col + 1, 1, $header[$col],
		$optlist);
    }

    /* Create shaping samples */
    for ($i = 0; $i < count($shapingsamples); $i++) {
	$sample = $shapingsamples[$i];

	$col = 1;
	$row = $i + 2;

	/* Column 1: description */
	$optlist = "margin=4 fittextline={fontname=Helvetica "
		. "encoding=unicode fontsize=12 position={left center}}";
	$table = $p->add_table_cell($table, $col++, $row, $sample->language,
		$optlist);

	/* Column 2: raw text */
	$optlist = "margin=4 fittextline={fontname={" . $sample->fontname
		. "} encoding=unicode fontsize=18 position={left center}}";
	$table = $p->add_table_cell($table, $col++, $row, $sample->text,
			$optlist);
	
	/* Column 3: shaped and reordered text */
	$optlist =
	    "margin=4 fittextline={fontname={" . $sample->fontname
	    . "} encoding=unicode fontsize=18 "
	    . $sample->optlist . " position={right center}}";
	$table = $p->add_table_cell($table, $col++, $row, $sample->text,
		$optlist);
    }

    /* ---------- Place the table on one or more pages ---------- */
    /*
     * Loop until all of the table is placed; create new pages as long
     * as more table instances need to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

	/* Shade every other row; draw lines for all table cells. */
	$optlist = "header=1 fill={{area=rowodd "
		. "fillcolor={gray 0.9}}} stroke={{line=other}} ";

	/* Place the table instance */
	$result = $p->fit_table($table, $llx, $lly, $urx, $ury, $optlist);

	if ($result == "_error") {
	    throw new Exception("Couldn't place table: "
		    . $p->get_errmsg());
	}

	$p->end_page_ext("");

    }
    while ($result == "_boxfull");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=bidi_formatting.pdf");
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

