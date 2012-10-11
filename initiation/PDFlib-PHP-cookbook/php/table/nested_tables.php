<?php
/* 
 * $Id: nested_tables.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Nested tables:
 * Place a sub-table in one table cell.
 *
 * Create a template which contains a table consisting of three rows and two 
 * columns. Create a table consisting of three rows and three columns. Add a
 * cell in the second column and the second row of the table so that the cell
 * contains the template, e.g. the contained table, as a sub-table.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Nested Tables";

/*
 * Option list for the template, i.e. the sub-table
 * Note "position={left bottom}" for being able to calculate the
 * size of the table for subsequent specification of the template size.
 */
$taboptlist1 = "stroke={{line=other linewidth=0.1 }} "
    . "fill={{area=table fillcolor={rgb 1 0.9 0.9}}} "
    . "position={left bottom}";

/*
 * Option list for the outer table
 */
$taboptlist2 = "stroke={{line=other linewidth=0.1 } "
    . "{line=frame linewidth=1.0 }} "
    . "fill={{area=table fillcolor={rgb 0.9 0.9 1}}} ";

$fontsize = 14.0;
$margin = 5.0;
$pagewidth = 595;
$pageheight = 500;
$x = 20; $y = 470; $yoff = 10;

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

    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Create a template which contains a table consisting of three
     * rows and two columns. Pass (0, 0) for the size of the template,
     * as we will calculate the size of the table and provide the
     * size to the template with end_template_ext().
     */
    $templ = $p->begin_template_ext(0, 0, "");

    /*
     * Add some cells for the template sub-table and fit them into the
     * template
     */
    $addoptlist1 = "fittextline={font=" . $font . " fontsize=" . $fontsize
	. "} margin=" . $margin;

    $tbl = $p->add_table_cell(0, 1, 1, "tab 1, cell A", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 2, 1, "tab 1, cell B", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 2, "tab 1, cell C", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 2, 2, "tab 1, cell D", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 3, "tab 1, cell E", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 2, 3, "tab 1, cell F", $addoptlist1);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_table($tbl, 0, 0, 1000, 1000, $taboptlist1);

    /* Retrieve the width and height of the template sub-table */
    $tabwidth1 = $p->info_table($tbl, "width");
    $tabheight1 = $p->info_table($tbl, "height");

    /* Finish the template while specifying the size of it */
    $p->end_template_ext($tabwidth1, $tabheight1);

    /* Start page */
    $p->begin_page_ext($pagewidth, $pageheight, "");

    /*
     * Place the sub-table. To illustrate the extent of the sub-table
     * first output it individually. "rewind=1" is only required here
     * for illustration purposes. It resets the table to the state
     * before last fit_table() call which has been used to place the
     * table into the template. Otherwise we would not be able to place
     * the table a second time.
     */
    
    /* Output some descriptive text */
    $textoptlist = "font=" . $font . " fontsize=" . $fontsize;
    $p->fit_textline("Inner table 1 (placed in a template):", $x, $y,
	$textoptlist);

    $y -= $tabheight1 + $yoff;

    $fitoptlist1 = $taboptlist1 . " rewind=1";
    $p->fit_table($tbl, $x + $tabwidth1, $y, $x + 2 * $tabwidth1, $y
	+ $tabheight1, $fitoptlist1);

    $p->delete_table($tbl, "");

    /*
     * Create the outer table consisting of three rows and three columns
     */

    /* Output some descriptive text */
    $y -= $yoff * 4;
    $textoptlist = "font=" . $font . " fontsize=" . $fontsize;
    $p->fit_textline("Outer table 2:", $x, $y, $textoptlist);

    /*
     * Add some cells to the outer table. The column width is set to the
     * table width of the sub-table which has been retrieved above. The
     * row height is set to the height of the sub-table which has been
     * retrieved above.
     */
    $addoptlist2 = "fittextline={font=" . $font . " fontsize=" . $fontsize
	. "} margin=" . $margin . " colwidth=" . $tabwidth1
	. " rowheight=" . $tabheight1;

    $tbl = $p->add_table_cell(0, 1, 1, "tab 2, cell 1", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 2, 1, "tab 2, cell 2", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 3, 1, "tab 2, cell 3", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 2, "tab 2, cell 4", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 3, 2, "tab 2, cell 5", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 3, "tab 2, cell 6", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 2, 3, "tab 2, cell 7", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 3, 3, "tab 2, cell 8", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Add another cell containing the template sub-table as well as
     * some gray text. The "image" option supplies the template. 
     */
    $addoptlist2 = "image=" . $templ . " fittextline={font=" . $font
	. " position={center 70} fontsize=26 fillcolor={gray 0.8}}";

    $tbl = $p->add_table_cell($tbl, 2, 2, "tab 2, cell 9", $addoptlist2);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Place the outer table */
    $y -= $yoff;
    $p->fit_table($tbl, $x, 20, $pagewidth - $x, $y, $taboptlist2);

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=nested_tables.pdf");
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
