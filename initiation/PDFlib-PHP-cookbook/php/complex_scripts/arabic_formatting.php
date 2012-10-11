<?php
/* $Id: arabic_formatting.php,v 1.2 2012/05/03 14:00:42 stm Exp $
 * Starter sample for Arabic text formatting
 * Demonstarte various formatting topic specific for the Arabic script;
 * Additional aspects are demonstrated in the bidi_formatting Cookbook topic.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: Arabic fonts
 * 
 * The code below uses the ScheherazadeRegOT font (included in the Cookbook
 * package), the font "Tahoma" and the "Arial Unicode MS" font from Microsoft.
 * For using the latter two fonts, put the respective font files "tahoma.ttf"
 * and "arialuni.ttf" into the "extra_input" directory. Alternatively, if you 
 * have the fonts installed as host fonts, you can replace the lower-case names
 * with the host font names "Tahoma" and "Arial Unicode MS". Tahoma is available
 * by default on Windows systems.
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
/* Search path for extra resources, put "arialuni.ttf" and "tahoma.ttf"
 * here
 */
$extra_searchpath = dirname(dirname(dirname(__FILE__)))."/extra_input";
$outfile = "";

$llx = 50; $lly = 50; $urx = 800; $ury = 550;

$header = array( "Arabic formatting topic", "Raw input",
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
	"shaping script=arab",
	"Add vowels to base consonants",
	"&#x0643;&#x0650;&#x062A;&#x064E;&#x0627;&#x0628;"),
	
    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Isolated form of HEH could be confused with digit FIVE (wrong)",
	"&#x0661;&#x0663;&#x0666;&#x0665; &#x0647;"),
		
    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Force initial form of HEH with ZERO WIDTH JOINER",
	"&#x0661;&#x0663;&#x0666;&#x0665; &#x0647&ZWJ;"),

    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Persian plural with joined character (wrong)",
	"&#x0645;&#x0648;&#x0632;&#x0647;&#x0647;&#x0627;"),

    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Prevent character join with ZERO WIDTH NON-JOINER",
	"&#x0645;&#x0648;&#x0632;&#x0647&ZWNJ;&#x0647;&#x0627;"),
	
    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Abbreviation with character join (wrong)",
	"&#x0623;&#x064A;&#x0628;&#x064A;&#x0625;&#x0645;"),

    new shaping(
	"ScheherazadeRegOT", "shaping script=arab charref",
	"Prevent character join with ZERO WIDTH NON-JOINER",
	"&#x0623;&#x064A&ZWNJ;&#x0628;&#x064A&ZWNJ;&#x0625;&#x0645;"),
	    
// The "Allah" ligature in Tahoma works only with Arabic presentation forms!
    new shaping(
	"tahoma",
	"shaping script=arab",
	"Without optional ligature",
	"&#xFEDF;&#xFEE0;&#xFEEA;"),

    new shaping(
	"tahoma",
	"shaping script=arab features={liga}",
	"With optional ligature: features={liga}",
	"&#xFEDF;&#xFEE0;&#xFEEA;"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"European digits",
	"0123456789"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"Arabic-Indic digits",
	"&#x0660;&#x0661;&#x0662;&#x0663;&#x0664;&#x0665;&#x0666;&#x0667;&#x0668;&#x0669;"),

    new shaping(
	"arialuni",
	"shaping script=arab features={locl} language=URD",
	"Variant figures for Urdu: features={locl} language=URD",
	"&#x0660;&#x0661;&#x0662;&#x0663;&#x0664;&#x0665;&#x0666;&#x0667;&#x0668;&#x0669;"),
		
    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"Text without elongation",
	"&#x062D;&#x0642;&#x0648;&#x0642; " .
	"&#x0627;&#x0644;&#x0627;&#x0646;&#x0633;&#x0627;&#x0646;"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"Tatweel (kashida) elongation",
	"&#x062D;&#x0642;&#x0640;&#x0640;&#x0648;&#x0642; " .
	"&#x0627;&#x0644;&#x0627;&#x0646;&#x0633;&#x0640;&#x0640;&#x0627;&#x0646;"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"More tatweel elongation",
	"&#x062D;&#x0642;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0648;&#x0642; " .
	"&#x0627;&#x0644;&#x0627;&#x0646;&#x0633;" .
	"&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;" .
	"&#x0627;&#x0646;"),

    new shaping(
	"ScheherazadeRegOT",
	"shaping script=arab",
	"Even more tatweel elongation",
	"&#x062D;&#x0642;" .
	"&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;" .
	"&#x0648;&#x0642; " .
	"&#x0627;&#x0644;&#x0627;&#x0646;&#x0633;" .
	"&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;&#x0640;" .
	"&#x0627;&#x0646;"),
);

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("SearchPath", $extra_searchpath);

    /*
     * This means that formatting and other errors will raise an
     * exception. This simplifies our sample code, but is not
     * recommended for production code.
     */
    $p->set_parameter("errorpolicy", "exception");
    $p->set_parameter("textformat", "bytes");
    $p->set_parameter("charref", "true");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", "arabic_formatting");

    $table = 0;

    /* Create table header */
    for ($col = 0; $col < count($header); $col++) {
	$optlist =
		"margin=4 " .
	    "fittextline={fontname=Helvetica-Bold encoding=winansi "
	    . "fontsize=14} colwidth=" . ($col == 0 ? "50%" : "25%");
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
    header("Content-Disposition: inline; filename=arabic_formatting.pdf");
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

