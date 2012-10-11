<?php
/* $Id: opentype_features_for_cjk.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Starter sample for OpenType font features
 *
 * Demonstrate various typographic OpenType features after checking
 * whether a particular feature is supported in a font.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: Meiryo font from Windows Vista/7, or another
 * suitable font with OpenType CJK features
 * 
 * For better results you should replace the default font with a suitable
 * commercial font. Depending on the implementation of the features in
 * the font you may have to replace the sample text below in order to
 * see some effect of the features.
 * 
 * Note that even if a particular OpenType feature is present in the font
 * it may not necessarily process the characters provided in the test strings
 * below. In this case you must replace the test strings with content that
 * is suited to your font.
 *
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
/* Put the Meiryo font into the "extra_input" directory */
$extra_searchpath = dirname(dirname(dirname(__FILE__)))."/extra_input";

$outfile = "";

$i; $table; $font;
$llx = 50; $lly = 50; $urx = 800; $ury = 550;
$result;

$testfont = "meiryo:0";

$headers = array( "Description", "Option list",
    "Font name", "Raw input (feature disabled)", "Feature enabled" );

class testcase {
    function testcase($description, $optlist,
		$feature, $text) {
	$this->description = $description;
	$this->optlist = $optlist;
	$this->feature = $feature;
	$this->text = $text;
    }
}

$testcases = array(
    new testcase("Localized forms for simplified Chinese",
		"features={locl} script=hani language=ZHS", "locl",
		"&#x4E0B;&#x4E0E;&#x4E10;&#x4E11;&#x4E16;&#x4E17;" .
		"&#x4E4B;&#x4E4F;&#x4E55;&#x4E62;&#x4E76;&#x4E77;"),

    new testcase("Localized forms for traditional Chinese",
		"features={locl} script=hani language=ZHT", "locl",
		"&#x4E03;&#x4E0E;&#x4E10;&#x4E16;&#x4E31;&#x4E42;" .
		"&#x4E92;&#x4E9F;&#x4EA1;&#x4EA2;&#x4EA4;&#x4EA5;"),

    new testcase("Localized forms for Korean",
		"features={locl} script=hani language=KOR", "locl",
		"&#x4E42;&#x4E9E;&#x4EA8;&#x4EAB;&#x4EB6;&#x4ED7;" .
		"&#x50AC;&#x50C5;&#x50CA;&#x50CF;&#x50E7;&#x50ED;"),

    new testcase("Simplified forms",
		"features={smpl}", "smpl",
		"&#x6AAF; &#x81FA; &#x98B1;"),

    new testcase("Traditional forms",
		"features={trad}", "trad",
		"&#x53F0;&#x4E03;&#x4E0E;&#x4E31;&#x7D1B;&#x9912;&#x96B8;" .
	"&#x9698;&#x966A;&#x939A;"),

    new testcase("Expert forms",
		"features={expt}", "expt",
		"&#x5516;&#x82A6;&#x98F4;&#x9E78;&#x9D60;&#x6F23;&#x84EE;&#x7BDD;"),

    new testcase("Full widths",
		"features={fwid}", "fwid",
		"0123-456(789)=25"),

    new testcase("Horizontal kana alternates",
		"features={hkna}", "hkna",
		"&#x3041;&#x3042;&#x3043;&#x3044 ;&#x30A1;&#x30A2;&#x30A3;" .
	"&#x30A4;&#x30E5;&#x30E4;&#x30B4;&#x30A7;"),

    new testcase("Hangul",
		"features={hngl}", "hngl",
		"&#x4F3D;"),

    new testcase("Hojo kanji forms (JIS X 0212-1990)",
		"features={hojo}", "hojo",
		"&#x6FF9;&#x9B35;&#x8200;&#x9CE6;&#x9B2D;&#x9721;&#x884B;"),

    new testcase("Half widths",
		"features={hwid}", "hwid",
		"&#x53F0;&#x4E03;&#x4E0E;&#x4E31;&#x7D1B;&#x9912;&#x96B8;" .
		"0123-456(789)=25"),

    new testcase("Italics",
		"features={ital}", "ital",
		"ABCD abcd 1234"),

    new testcase("JIS2004 forms",
		"features={jp04}", "jp04",
		"&#x9022;&#x98F4;&#x6EA2;&#x8328;&#x9C2F;&#x6DEB;&#x8FC2;"),

    new testcase("JIS78 forms",
		"features={jp78}", "jp78",
		"&#x5516;&#x9C2F;&#x5026;&#x53F1;&#x63B4;&#x9019;&#x2FD4;&#x8000;"),

    new testcase("JIS83 forms",
		"features={jp83}", "jp83",
		"&#x9B58;&#x9BC6;&#x9BF1;&#x9D48;&#x9F08;&#x2F57;"),

    new testcase("JIS90 forms",
		"features={jp90}", "jp90",
		"&#x555E;"),

    new testcase("Alternate annotation forms",
		"features={nalt}", "nalt",
		"ABC123&#x002F;&#x30B4;&#x217B;&#x4E0B;&#x7981;&#x2F29;&#x4E2D;&#x2F42;"),

    new testcase("NLC kanji forms",
		"features={nlck}", "nlck",
		"&#x9022;&#x82A6;&#x98F4;&#x6EA2;&#x8328;&#xFA1F;&#x7149;"),

    new testcase("Proportional kana",
		"features={pkna}", "pkna",
		"&#x3041;&#x3042; &#x30A1;&#x30A2; &#xFF66; &#xFF67;"),

    new testcase("Proportional widths",
		"features={pwid}", "pwid",
		"&#x3041;&#x3042; &#x30A1;&#x30A2; &#xFF21;&#xFF23;&#xFF28;" .
		"&#xFF29;&#xFF2D; ABCD abcd 1234"),

    new testcase("Quarter widths",
		"features={qwid}", "qwid",
		"0123-456(789)=25"),

    new testcase("Ruby notation forms",
		"features={ruby}", "ruby",
		"&#x3042;&#x304A;&#x307D;&#x30AA;&#x30BC;&#x30F1;&#x25C9;&#x31F0;&#x31FF;"),

    new testcase("Traditional name forms",
		"features={tnam}", "tnam",
		"&#x4E9C;"),

    new testcase("Third widths",
		"features={twid}", "twid",
		"0123-456(789)=25"),

    new testcase("Vertical kana alternates",
		"features={vkna}", "vkna",
		"&#x3041;&#x3042;&#x3043;&#x3044 ;&#x31FF;&#x31F8;&#x30CB;&#x30BB;"),

    new testcase("Vertical alternates and rotation",
		"features={vrt2}", "vrt2",
		"[abc.]ABC- 012/"),
			
);

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("SearchPath", $extra_searchpath);
    $p->set_parameter("charref", "true");
    $p->set_parameter("escapesequence", "true");

    /*
     * This means that formatting and other errors will raise an
     * exception. This simplifies our sample code, but is not
     * recommended for production code.
     */
    $p->set_parameter("errorpolicy", "exception");
    $p->set_parameter("textformat", "utf8");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib starter sample");
    $p->set_info("Title", "opentype_features_for_cjk");

    $table = 0;

    /* Table header */
    for ($i = 0; $i < count($headers); $i++) {
	$col = $i + 1;

	$optlist =
	    "fittextline={fontname=Helvetica-Bold encoding=unicode fontsize=12} "
	    . "margin=4";
	$table = $p->add_table_cell($table, $col, 1, $headers[$i], $optlist);
    }

    /* Create a table with feature samples, one feature per table row */
    for ($i = 0; $i < count($testcases); $i += 1) {
	$testcase = $testcases[$i];
	$row = $i + 2;

	$col = 1;

	/* Common option list for columns 1-3 */
	$optlist =
	    "fittextline={fontname=Helvetica encoding=unicode fontsize=12} "
	    . "margin=4";

	/* Column 1: feature description */
	$table = $p->add_table_cell($table, $col++, $row,
		$testcase->description, $optlist);

	/* Column 2: option list */
	$table = $p->add_table_cell($table, $col++, $row, $testcase->optlist,
			$optlist);

	/* Column 3: font name */
	$table = $p->add_table_cell($table, $col++, $row, $testfont, $optlist);

	/* Column 4: raw input text with feature disabled */
	$optlist = "fittextline={fontname={" . $testfont
		. "} encoding=unicode fontsize=12 embedding} margin=4";
	$table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		$optlist);

	/*
	 * Column 5: text with enabled feature, or warning if the
	 * feature is not available in the font
	 */
	$font = $p->load_font($testfont, "unicode", "embedding");

	/* Check whether font contains the required feature table */
	$optlist = "name=" . $testcase->feature;
	if ($p->info_font($font, "feature", $optlist) == 1) {
	    /* feature is available: apply it to the text */
	    $optlist = "margin=4 fittextline={fontname={" . $testfont
		    . "} encoding=unicode fontsize=12 embedding "
		    . $testcase->optlist . "}";
	    $table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		    $optlist);
	}
	else {
	    /* feature is not available: emit a warning */
	    $optlist = "fittextline={fontname=Helvetica encoding=unicode "
		    . "fontsize=12 fillcolor=red} margin=4";
	    $table = $p->add_table_cell($table, $col++, $row,
		    "(feature not available in this font)", $optlist);
	}
    }

    /*
     * Loop until all of the table is placed; create new pages as long
     * as more table instances need to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

	$optlist = "header=1 fill={{area=rowodd fillcolor={gray 0.9}}} "
	    . "stroke={{line=other}} debugshow";

	/* Place the table instance */
	$result = $p->fit_table($table, $llx, $lly, $urx, $ury, $optlist);

	if ($result == "_error")
	    throw new Exception("Couldn't place table: "
		. $p->get_errmsg());

	$p->end_page_ext("");

    }
    while ($result == "_boxfull");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=opentype_features_for_cjk.pdf");
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
