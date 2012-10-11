<?php
/*
 * $Id: repeat_cell_contents.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * 
 * Repeat cell contents:
 * Control the repetition of table cell contents in the next table instance if a
 * cell or row is split. 
 * 
 * Use the "repeatcontent" option of add_table_cell() to control if the cell
 * contents will be repeated in the next table instance if a cell or a row is
 * split.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Repeat Cell Contents";

$imagefile = "kraxi_logo.tif";
$pagewidth = 842; $pageheight = 595;

/*
 * Coordinates of the lower left and upper right corners of the table
 * fitbox. The width of the fitbox matches the sum of the widths of the
 * three table columns.
 */
$llx1 = 10; $lly1 = 300; $urx1 = 270; $ury1 = 500;
$llx2 = 280; $lly2 = 340; $urx2 = 540; $ury2 = 500;
$llx3 = 550; $lly3 = 340; $urx3 = 810; $ury3 = 500;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $normalfont = $p->load_font("Helvetica", "unicode", "");
    if ($normalfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext($pagewidth, $pageheight, "");

    /* Output some descriptive text */
    $p->setfont($boldfont, 10);
    $p->fit_textline("Table is placed in one table instance", $llx1,
	$ury1 + 35, "");

    /*
     * Output the first table. The supplied coordinates make up a fitbox
     * which is large enough for the table to be placed completely in
     * one table instance.
     */
    output_table($p, $llx1, $lly1, $urx1, $ury1, true);

    /* Output some descriptive text */
    $p->fit_textline("Table is placed in two table instances", $llx2,
	$ury2 + 35, "");
    $p->fit_textline("with some cell contents being repeated", $llx2,
	$ury2 + 25, "");

    /*
     * Output the second table. The supplied coordinates make up a
     * fitbox which is pretty small so that the table will be placed in
     * two table instances. The last parameter is supplied to enable the
     * repetition of cell contents if a cell or row is split.
     */
    output_table($p, $llx2, $lly2, $urx2, $ury2, true);

    /* Output some descriptive text */
    $p->fit_textline("Table is placed in two table instances", $llx3,
	$ury3 + 35, "");
    $p->fit_textline("with no cell contents being repeated", $llx3,
	$ury3 + 25, "");

    /*
     * Output the third table. The supplied coordinates make up a fitbox
     * which is pretty small so that the table will be placed in two
     * table instances. The last parameter is supplied to control the
     * "repeatcontent" option of add_table_cell(). The last parameter is
     * supplied to disable the repetition of cell contents if a cell or
     * row is split.
     */
    output_table($p, $llx3, $lly3, $urx3, $ury3, false);

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=frame_aroun_image.pdf");
    print $buf;

} catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n".
	"[" . $e->get_errnum() . "] " . $e->get_apiname() .
	": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}

$p=0;


function output_table($p, $llx, $lly, $urx, $ury, $repeat) {
    global $font, $boldfont, $fontsize, $normalfont, $image;

    $tf = 0; $tbl = 0;
    $rowheight = 14;
    $yoffset = 180;

    /* Width of the first, second, and third column of the table */
    $c1 = 50; $c2 = 120; $c3 = 90;

    $tftext = "Our paper planes are the ideal way of passing the time. We "
	. "offer revolutionary new developments of the traditional common "
	. "paper planes. If your lesson, conference, or lecture turn out "
	. "to be deadly boring, you can have a wonderful time with our "
	. "planes. All our mod&shy;els are folded from one paper sheet. "
	. "They are exclusively folded without using any adhesive. Several "
	. "models are equipped with a folded landing gear enabling a safe "
	. "landing on the intended location provided that you have aimed "
	. "well. Other models are able to fly loops or cover long "
	. "distances. Let them start from a vista point in the mountains "
	. "and see where they touch the ground.";

    $tftext2 = "It is amazingly robust and can even do aerobatics. "
	. "But it is best suited to gliding.";

    /*
     * Adding a text line cell containing a header
     * 
     * The cell will be placed in the first column of the first row and will
     * span three columns. The first column has a width of 50 points. The
     * text line is centered vertically and horizontally, with a margin of 4
     * points from all borders.
     */
    $optlist = "fittextline={font=" . $boldfont . " fontsize=12"
	. " position=center} margin=4 colspan=3 colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, 1, "Our Paper Plane Models", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding a Textflow cell containing an introduction
     * 
     * Add the Textflow to be placed in the table cell. Use
     * "fontsize={capheight=6}" which will approximately result in a font
     * size of 8 points and (along with "margin=4"), will sum up to an
     * overall height of 14 points as for the text lines above. "charref"
     * enables the substitution of character references.
     */
    $optlist = "font=" . $normalfont . " fontsize={capheight=6} "
	. "leading=110% charref";

    $tf = $p->add_textflow(0, $tftext, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Add the Textflow table cell. The cell is placed in column 1, row 2
     * and spans three columns. To avoid any space from the top add the
     * Textflow cell using "fittextflow={firstlinedist=capheight}". Then add
     * a margin of 4 points, the same as for the text lines.
     */
    $optlist = "textflow=" . $tf . " fittextflow={firstlinedist=capheight} "
	. "colwidth=" . $c2 . " colspan=3 margin=4 rowheight=" . $rowheight;

    $tbl = $p->add_table_cell($tbl, 1, 2, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding a text line cell containing the name of the plane
     * 
     * The cell is placed in column 1, row 3 and spans two columns. The
     * first column has a width of 50 points. The row height is 14 points.
     * The text line is horizontally positioned on the left and vertically
     * centered, with a margin of 4 points from all borders. We use
     * "fontsize={capheight=6}" which will approximately result in a font
     * size of 8 points and (along with "margin=4"), will sum up to an
     * overall height of 14 points corresponding to the "rowheight" option.
     * We apply the Matchbox feature to fill the cell with a gray background
     * color.
     */
    $optlist = "fittextline={position={left top} font=" . $boldfont
	. " fontsize={capheight=6}} rowheight=" . $rowheight . " colwidth="
	. $c1 . " margin=4 colspan=2" . " matchbox={fillcolor={gray .92}}";

    $tbl = $p->add_table_cell($tbl, 1, 3, "1  Giant Wing", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding three more text line cells containing plane data
     * 
     * For a description of the options see above.
     */
    $optlist = "fittextline={position={left top} font=" . $normalfont
	. " fontsize={capheight=6}} rowheight=14 colwidth=" . $c1
	. " margin=4";

    $tbl = $p->add_table_cell($tbl, 1, 4, "Material", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 5, "Benefit", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $optlist = "fittextline={position={left top} font=" . $normalfont
	. " fontsize={capheight=6}} rowheight=14 colwidth=" . $c2
	. " margin=4";

    $tbl = $p->add_table_cell($tbl, 2, 4, "Offset print paper 220g/sqm",
	$optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Adding a Textflow cell containing the plane description
     * 
     * Add the Textflow
     */
    $optlist = "font=" . $normalfont . " fontsize={capheight=6} leading=110%";

    $tf = $p->add_textflow(0, $tftext2, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Add the Textflow table cell */
    $optlist = "textflow=" . $tf . " fittextflow={firstlinedist=capheight} "
	. "colwidth=" . $c2 . " margin=4";

    $tbl = $p->add_table_cell($tbl, 2, 5, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Add the image cell with a text line
     * 
     * The image is placed in a cell starting in column 3 and row 3 and
     * spans three rows. The column width is 90 points. The cell margins are
     * set to 4 points.
     */
    $optlist = "fittextline={font=" . $boldfont . " fontsize=9} image="
	. $image . " colwidth=" . $c3 . " rowspan=3 margin=4";

    /*
     * Additional options for marking the potentially repeating cell
     */
    $mark_optlist = " matchbox={innerbox borderwidth=1 strokecolor=red "
	. "linecap=projecting}";

    if (!$repeat)
	$optlist .= " repeatcontent=false";

    $tbl = $p->add_table_cell($tbl, 3, 3, "Amazingly robust!", $optlist
	. $mark_optlist);

    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * ------------- Fit the table -------------
     * 
     * Using "header=1" the table header will include the first line. The
     * "fill" option and the suboptions "area=header" and
     * "fillcolor={rgb 0.8 0.8 0.87}" specify the header row(s) to be filled
     * with the supplied color. The "stroke" option and the suboptions
     * "line=frame linewidth=0.8" define the ruling of the table frame with
     * a line width of 0.8. Using "line=other linewidth=0.3" the ruling of
     * all cells is specified with a line width of 0.3.
     */
    $optlist = "header=1 fill={{area=header fillcolor={rgb 0.8 0.8 0.87}}}"
	. " stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}";

    do {
	/* Place the table instance */
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);

	if ($result == "_error" || $result == "_boxempty")
	    throw new Exception("Couldn't place table : " . $p->get_errmsg());

	$lly -= $yoffset;
	$ury -= $yoffset;
    }
    while ($result == "_boxfull");

    /* Check the $result; "_stop" means all is ok */
    if (!$result == "_stop") {
	throw new Exception("Error when placing table: " . $p->get_errmsg());
    }
}
?>
