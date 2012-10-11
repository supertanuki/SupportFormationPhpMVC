<?php
/* $Id: opentype_feature_tester.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Test all supported OpenType features in a font
 *
 * Demonstrate all supported typographic OpenType features after checking
 * whether a particular feature is supported in a font. Only those features
 * will be shown which are
 * a) available in the font
 * b) are supported by PDFlib's "features" option
 * c) are not used automatically for shaping and layout features 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: suitable fonts with OpenType feature tables, by default
 * the program looks for the font file "arialuni.tff" for the "Arial Unicode MS"
 * font. Put the file "arialuni.ttf" into the "extra_input" directory.
 *
 * For better results you should replace the default font with a suitable
 * commercial font. Depending on the implementation of the features in
 * the font you may have to replace the sample text below in order to
 * see some effect of the features.
 *
 * Some ideas for suitable test fonts:
 * Palatino Linotype: standard Windows font with many OpenType features
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
/* Search path for extra resources, put "arialuni.ttf" here */
$extra_searchpath = dirname(dirname(dirname(__FILE__)))."/extra_input";
$outfile = "";
$title = "OpenType Feature Tester";

$optlist;
$i; $group; $table; $font;
$llx = 50; $lly = 50; $urx = 800; $ury = 550;
$result;

/* 
 * Name of the test font.
 * 
 * By default it looks for "Arial Unicode MS" font file "arialuni.ttf".
 * If you have "Arial Unicode MS" installed as a host font, you can
 * replace the string "arialuni" with "Arial Unicode MS".
 */
$testfont = "arialuni";

$headers = array(
    "Feature", "Description", "Feature disabled", "Feature enabled"
);

$groupdescriptions = array(
    "Supported OpenType features for Western typography",
    "Stylistic Sets",
    "Supported OpenType features for Chinese, Japanese, and Korean text"
);

class testcase {
    function testcase($feature,
		$additionaloptions,
		$description,
		$text) {
	$this->feature = $feature;
	$this->additionaloptions = $additionaloptions;
	$this->description = $description;
	$this->text = $text;
    }
}

/*
 * Features supported by PDFlib along with common test strings. Change
 * the test strings to match the features in the tested font.
 */
$testcases = array(
    array(
	/* Western features */
	new testcase("afrc", "", "alternative fractions",
			"1/2 1/4 3/4"),
	new testcase("c2pc", "", "petite capitals from capitals",
			"ABCDEFG"),
	new testcase("c2sc", "", "small capitals from capitals",
			"ABCDEFG"),
	new testcase("case", "", "case-sensitive forms",
	    "(UN) [UN] (ISO693) &#x00BB;A-B+C&#x00AB;"),
	new testcase("dlig", "", "discretionary ligatures",
	    "st c/o TEL FAX"),
	new testcase("dnom", "", "denominators",
			"0123456789"),
	new testcase("frac", "", "fractions",
			"1/2 1/4 3/4"),
	new testcase("hist", "", "historical forms",
			"s"),
	new testcase("hlig", "", "historical ligatures",
	    "&.longs;b &.longs;t"),
	new testcase("liga", "", "standard ligatures",
			"ff fi fl ffi ffl"),
	new testcase("lnum", "", "lining figures",
			"0123456789"),
	new testcase("locl", "script=latn language=ROM",
			"localized forms",
			"&.Scedilla;&.Tcommaaccent;"),
	new testcase("mgrk", "", "mathematical Greek",
			"&#x03A3;"),
	new testcase("numr", "", "numerators",
			"0123456789"),
	new testcase("onum", "", "old-style figures",
			"0123456789"),
	new testcase("ordn", "", "ordinals",
			"No a o A O 1o 2a 3o"),
	new testcase("ornm", "", "ornaments",
			"&#x2022; abcqrstuvwABC"),
	new testcase("pcap", "", "petite capitals",
			"ABCDEFG"),
	new testcase("pnum", "", "proportional figures",
			"0123456789"),
	new testcase("salt", "", "stylistic alternates",
			"abcdefghij"),
	new testcase("sinf", "", "scientific inferiors",
			"0123456789"),
	new testcase("smcp", "", "small capitals",
			"PostScript"),
	new testcase("subs", "", "subscript ",
			"0123456789"),
	new testcase("sups", "", "superscript",
			"0123456789"),
	new testcase("swsh", "", "swash",
			"PQRSTpqrst &"),
	new testcase("titl", "", "titling",
			"ABCDEFG"),
	new testcase("tnum", "", "tabular figures",
			"0123456789"),
	new testcase("unic", "", "unicase",
			"Filosofia"),
	new testcase("zero", "", "slashed zero", "0"),
    ),
    array(
	/* Stylistic Sets */
	new testcase("ss01", "", "stylistic set 1",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss02", "", "stylistic set 2",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss03", "", "stylistic set 3",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss04", "", "stylistic set 4",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss05", "", "stylistic set 5",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss06", "", "stylistic set 6",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss07", "", "stylistic set 7",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss08", "", "stylistic set 8",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss09", "", "stylistic set 9",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss10", "", "stylistic set 10",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss11", "", "stylistic set 11",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss12", "", "stylistic set 12",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss13", "", "stylistic set 13",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss14", "", "stylistic set 14",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss15", "", "stylistic set 15",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss16", "", "stylistic set 16",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss17", "", "stylistic set 17",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss18", "", "stylistic set 18",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss19", "", "stylistic set 19",
	    "abcdefghijABCDEFGH0123"),
	new testcase("ss20", "", "stylistic set 20",
	    "abcdefghijABCDEFGH0123"),
    ),
    array(
	/* CJK features */
	new testcase("expt", "", "expert forms",
	    "&#x5516;&#x82A6;&#x98F4;&#x9E78;&#x9D60;&#x6F23;&#x84EE;"
		. "&#x7BDD;"),
	new testcase("fwid", "", "full widths",
			"0123-456(789)=25"),
	new testcase("hkna", "", "horizontal kana alternates",
	    "&#x3041;&#x3042;&#x3043;&#x3044; &#x30A1;&#x30A2;&#x30A3;"
		. "&#x30A4;&#x30E5;&#x30E4;&#x30B4;&#x30A7;"),
	new testcase("hngl", "", "hangul",
			"&#x4F3D;"),
	new testcase("hojo", "",
			"Hojo kanji forms (JIS X 0212-1990)",
	    "&#x6FF9;&#x9B35;&#x8200;&#x9CE6;&#x9B2D;&#x9721;&#x884B;"),
	new testcase("hwid", "", "half widths",
			"0123-456(789)=25"),
	new testcase("ital", "", "italics",
			"ABCD abcd 1234"),
	new testcase("jp04", "", "JIS2004 forms",
	    "&#x9022;&#x98F4;&#x6EA2;&#x8328;&#x9C2F;&#x6DEB;&#x8FC2;"),
	new testcase("jp78", "", "JIS78 forms",
	    "&#x5516;&#x9C2F;&#x5026;&#x53F1;&#x63B4;&#x9019;&#x2FD4;"
		. "&#x8000;"),
	new testcase("jp83", "", "JIS83 forms",
	    "&#x9B58;&#x9BC6;&#x9BF1;&#x9D48;&#x9F08;&#x2F57;"),
	new testcase("jp90", "", "JIS90 forms",
			"&#x555E;"),
	new testcase("locl", "script=hani language=ZHS",
			"localized forms",
			"&#x4E0B;&#x4E0E;&#x4E10;&#x4E11;&#x4E16;&#x4E17;"),
	new testcase("nalt", "", "alternate annotation forms",
	    "&#x002F;&#x30B4;&#x217B;&#x4E0B;&#x7981;&#x2F29;&#x4E2D;"
		. "&#x2F42;"),
	new testcase("nlck", "", "NLC kanji forms",
	    "&#x9022;&#x82A6;&#x98F4;&#x6EA2;&#x8328;&#xFA1F;&#x7149;"),
	new testcase("pkna", "", "proportional kana",
	    "&#x3041;&#x3042; &#x30A1;&#x30A2; &#xFF66; &#xFF67;"),
	new testcase("pwid", "", "proportional widths",
	    "&#x3041;&#x3042; &#x30A1;&#x30A2; &#xFF21;&#xFF23;&#xFF28;"
		. "&#xFF29;&#xFF2D; ABCD abcd 1234"),
	new testcase("qwid", "", "quarter widths",
			"0123-456(789)=25"),
	new testcase("ruby", "", "ruby notation forms",
	    "&#x3042;&#x304A;&#x307D;&#x30AA;&#x30BC;&#x30F1;&#x25C9;"
		. "&#x31F0;&#x31FF;"),
	new testcase("smpl", "", "simplified forms",
	    "&#x6AAF; &#x81FA; &#x98B1;"),
	new testcase("tnam", "", "traditional name forms",
			"&#x4E9C;"),
	new testcase("trad", "", "traditional forms",
	    "&#x53F0;&#x4E03;&#x4E0E;&#x4E31;&#x7D1B;&#x9912;&#x96B8;"
		. "&#x9698;&#x966A;&#x939A;"),
	new testcase("twid", "", "third widths",
			"0123-456(789)=25"),
	new testcase("vkna", "", "vertical kana alternates",
	    "&#x3041;&#x3042;&#x3043;&#x3044; &#x31FF;&#x31F8;&#x30CB;"
		. "&#x30BB;"),
	new testcase("vrt2", "",
		"vertical alternates and rotation",
	    "abcABC")
    )
);

try {
    $p = new PDFlib();
    $row=0; $col=0;

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("SearchPath", $extra_searchpath);
    $p->set_parameter("charref", "true");
    $p->set_parameter("textformat", "utf8");

    /*
     * This means that formatting and other errors will raise an
     * exception. This simplifies our sample code, but is not
     * recommended for production code.
     */
    $p->set_parameter("errorpolicy", "exception");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0) {
	throw new Exception("Error: " . $p->get_apiname() . ": "
	    . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $table = 0;
    $row = 1;
    $col = 1;

    /* Table header */
    $optlist = "fittextline={fontname=Helvetica-Bold encoding=unicode "
	. "fontsize=12} margin=4";
    $table = $p->add_table_cell($table, $col, $row++,
	"Supported OpenType features in font " . $testfont, $optlist
	    . " colspan=4");

    for ($i = 0; $i < count($headers); $i++) {
	$col = $i + 1;
	$table = $p->add_table_cell($table, $col, $row, $headers[$i], $optlist);
    }
    $row++;

    for ($group = 0; $group < count($testcases); $group += 1) {
	$featurecount = 0;

	$col = 1;

	$optlist = "fittextline={fontname=Helvetica-BoldOblique "
	    . "encoding=unicode fontsize=12} "
	    . "margin=4 colspan=4 rowjoingroup= " . $group;
	$table = $p->add_table_cell($table, $col, $row++,
	    $groupdescriptions[$group], $optlist);

	/*
	 * Create a table with feature samples, one feature per table
	 * row
	 */
	for ($i = 0; $i < count($testcases[$group]); $i += 1) {
	    $testcase = $testcases[$group][$i];
	    $col = 1;

	    /* skip unavailable features */
	    $font = $p->load_font($testfont, "unicode", "embedding");
	    $optlist = "name=" . $testcase->feature;
	    if ($p->info_font($font, "feature", $optlist) != 1)
		continue;

	    $featurecount++;

	    /* Common option list for columns 1-3 */
	    $optlist = "fittextline={fontname=Helvetica "
		. "encoding=unicode fontsize=12} margin=4";

	    /* Column 1: feature name */
	    $table = $p->add_table_cell($table, $col++, $row,
		$testcase->feature . " " . $testcase->additionaloptions,
		$optlist);

	    /* Column 2: feature description */
	    $table = $p->add_table_cell($table, $col++, $row,
		$testcase->description, $optlist);

	    /* Column 3: raw input text with feature disabled */
	    $optlist = "fittextline={fontname={" . $testfont
		. "} encoding=unicode fontsize=12 embedding} margin=4";
	    $table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		$optlist);

	    /* Column 4: text with enabled feature */
	    $optlist = "fittextline={fontname={" . $testfont
		. "} encoding=unicode fontsize=12 embedding "
		. "features={" . $testcase->feature .  "} "
		. $testcase->additionaloptions . "} margin=4";
	    $table = $p->add_table_cell($table, $col++, $row, $testcase->text,
		$optlist);

	    $row++;
	}

	if ($featurecount == 0) {
	    $col = 1;

	    $optlist = "fittextline={fontname=Helvetica "
		. "encoding=unicode fontsize=12} "
		. "margin=4 colspan=4 rowjoingroup= " . $group;
	    $table = $p->add_table_cell($table, $col, $row++, "(none)",
		$optlist);
	}
    }

    /*
     * Loop until all of the table is placed; create new pages as long
     * as more table instances need to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

	$optlist = "header=2 fill={{area=rowodd fillcolor={gray 0.9}}} "
	    . "stroke={{line=other}} ";

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
    header("Content-Disposition: inline; filename=opentype_feature_tester.pdf");
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
