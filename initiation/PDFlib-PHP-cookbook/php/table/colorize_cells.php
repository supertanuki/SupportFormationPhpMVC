<?php
/* $Id: colorize_cells.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Colorize cells:
 * Provide some table cells with a colored background.
 * 
 * Create a table and use matchboxes to provide some table cells with a 
 * colored background.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Colorize cells";

$nrows = 4; 
$tbl = 0;

$margin = 3;
$fontsize = 12;

/* Height of a table row which is the sum of a font size of 12 and the upper
 * and lower cell margin of 3 each
 */
$rowheight = 18;

/* Width of the first and second column of the table */
$c1 = 120; $c2 = 120;

/* Coordinates of the lower left corner of the table fitbox */
$llx = 30; $lly = 400;

/* Color names */
$names = array( "Chocolate", "CornflowerBlue", "Gold" );

/* RGB color values used in PDFlib in the range 0...1. They are calculated
 * by dividing the RGB value by 255. For example, a PDFlib value of
 * (0.82 0.4 0.1) corresponds to an RGB value of (210 105 30) since
 * 0.82 = 210/255, 0.4 = 105/255, and 0.1 = 30/255.
 */
$pdflib =  array( "0.82 0.4 0.1", "0.4 0.58 0.93", "1 0.84 0" );

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

    $p->begin_page_ext(0, 0, "width=a5.width height=a5.height");
    
    
    /* ---------------------------------------
     * Add a heading line spanning two columns
     * ---------------------------------------
     */
    
    /* Set the current row */
    $row = 1;
    
    $optlist = "fittextline={font=" . $font . " fontsize=" . $fontsize .
	" position=center} rowheight=" . $rowheight . " margin=" . $margin .
	" colspan=2 " . "colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, $row, "Color Table", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ---------------------------------------------------------------
     * Adding two cells in the second, third, and fifth table row each
     * ---------------------------------------------------------------
     */
    
    for ($row = 2; $row <= 4; $row++)
    {
	/* Adding a cell in the first column of the current row.
	 * The text line is centered horizontally, with a margin
	 * from all borders. 
	 */
	$optlist = "fittextline={font=" . $font . " fontsize=" . $fontsize .
	    " position={left center}} rowheight=" . $rowheight . 
	    " margin=" . $margin . " colwidth=" . $c1;

	$tbl = $p->add_table_cell($tbl, 1, $row, $names[$row-2], $optlist);
	if ($tbl == 0)
		throw new Exception("Error: " . $p->get_errmsg());

    
	/* ----------------------------------
	 * Adding a cell in the second column
	 * ----------------------------------
	 *
	 * The cell is placed in the second column of the current row.
	 * Since the cell doesn't cover a complete row but only one column
	 * it cannot be filled with color using one of the row-based shading
	 * options. We apply the Matchbox feature instead to fill the
	 * rectangle covered by the cell with a gray background color. 
	 */
	$optlist = "colwidth=" . $c2 . " margin=" . $margin .
	    " matchbox={fillcolor={rgb " . $pdflib[$row-2] . "}}";
    
	$tbl = $p->add_table_cell($tbl, 2, $row, "", $optlist);
	if ($tbl == 0)
		throw new Exception("Error: " . $p->get_errmsg());
    } /* for */
    
    
    /* -------------
     * Fit the table
     * -------------
     * 
     * Using "header=1" the table header will include the first line.
     * Using "line=horother linewidth=0.3" the ruling is specified with a
     * line width of 0.3 for all horizontal lines.
     */
    $optlist = "header=1 stroke={ {line=horother linewidth=0.3}}";
    
    $result = $p->fit_table($tbl, $llx, $lly, $llx + $c1 + $c2, 
	$lly + $nrows * $rowheight, $optlist);
    
    /* Check the $result; "_stop" means all is ok */
    if (!$result == "_stop") {
	    if ($result ==  "_error")
		    throw new Exception("Error: " . $p->get_errmsg());
	    else {
		    /* Other return values require dedicated code to deal with */
	    }
    }
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=colorize_cells.pdf");
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
