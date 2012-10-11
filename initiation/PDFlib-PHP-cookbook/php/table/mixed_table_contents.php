<?php
/* $Id: mixed_table_contents.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Mixed table contents:
 * Demonstrate how to place various kinds of contents in table cells spanning
 * one or more columns or rows
 * 
 * Create a table with various kinds of contents. 
 * Place a text line spanning several columns and colorize the background of the
 * cell.
 * Place a text line and a Textflow in two neighbour cells of the same row and
 * vertically align them so that they have the same distance from the left and
 * upper cell borders.
 * Place an image in a cell spanning several rows and place a text line on top
 * of it in the same cell.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Mixed Table Contents";

$tf=0; $tbl=0;
$imagefile = "kraxi_logo.tif";

/* Width of the first, second, and third column of the table */
$c1 = 50; $c2 = 120; $c3 = 90;

/* Coordinates of the lower left and upper right corners of the table 
 * fitbox. The width of the fitbox matches the sum of the widths of the
 * three table columns. 
 */
$llx = 100; $lly = 500; $urx = 360; $ury = 600;

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
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    $normalfont = $p->load_font("Helvetica", "unicode", "");
    if ($normalfont == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	    
    
    /* ---------------------
     * Adding the first cell
     * ---------------------
     * 
     * The cell will be placed in the first column of the first row and will
     * span three columns. 
     * The first column has a width of 50 points.
     * The text line is centered vertically and horizontally, with a margin
     * of 4 points from all borders. 
     */
    $optlist = "fittextline={font=" . $boldfont . " fontsize=12" .
	" position=center} margin=4 colspan=3 colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, 1, "Our Paper Plane Models", $optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

    
    /* -------------------------------------------
     * Adding the second cell spanning two columns
     * -------------------------------------------
     *
     * The cell is placed in the first column of the second row and spans
     * two columns.
     * The first column has a width of 50 points.
     * The row height is 14 points.
     * The text line is horizontally positioned on the left and vertically
     * centered, with a margin of 4 points from all borders.
     * Since we want to vertically align text lines and a Textflow we would
     * like to have an exact font size of 8 but using the option 
     * "fontsize=8" does not exactly represent the letter height but adds
     * some space below and above. However, the height of an uppercase
     * letter is exactly represented by the capheight value of the font. For
     * this reason use "fontsize={capheight=6}" which will approximately
     * result in a font size of 8 points and (along with "margin=4"), will
     * sum up to an overall height of 14 points corresponding to the
     * "rowheight" option. 
     * Since the cell doesn't cover a complete row but only two of
     * three columns it cannot be filled with color using on of the
     * row-based shading options. We apply the Matchbox feature instead to
     * fill the rectangle covered by the cell with a gray background color. 
     */
    $optlist = "fittextline={position={left top} font=" . $boldfont . 
	" fontsize={capheight=6}} rowheight=14 colwidth=" . $c1 . 
	" margin=4 colspan=2 matchbox={fillcolor={gray .92}}";
    
    $tbl = $p->add_table_cell($tbl, 1, 2, "1  Giant Wing", $optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ---------------------------------
     * Adding three more text line cells
     * ---------------------------------
     *
     * Add two more text lines in the first column of the third row as well
     * as in the first column of the fourth row. For a description of the
     * options see above, 
     */
    $optlist = "fittextline={position={left top} font=" . $normalfont . 
	" fontsize={capheight=6}} rowheight=14 colwidth=" . $c1 .
	" margin=4";
    
    $tbl = $p->add_table_cell($tbl, 1, 3, "Material", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 4, "Benefit", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add another text line in the second column of the third row with a
     * column width of 120. For a description of the options see above.
     */
    $optlist = "fittextline={position={left top} font=" . $normalfont . 
	" fontsize={capheight=6}} rowheight=14 colwidth=" . $c2 .
	" margin=4";

    $tbl = $p->add_table_cell($tbl, 2, 3, "Offset print paper 220g/sqm",
	$optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ------------------------
     * Adding the Textflow cell
     * ------------------------
     * 
     */
    $tftext = 
	    "It is amazingly robust and can even do aerobatics. " .
	    "But it is best suited to gliding.";
    
    /* Prepare the option list for adding the Textflow
     * 
     * Use "fontsize={capheight=6}" which will approximately result in a
     * font size of 8 points and (along with "margin=4"), will sum up to an
     * overall height of 14 points as for the text lines above.
     */
    $optlist = "font=" . $normalfont . " fontsize={capheight=6} leading=110%"; 
    
    /* Add the Textflow to be placed in a table cell later */
    $tf = $p->add_textflow(0, $tftext, $optlist);
    if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    /* Prepare the option list for adding the Textflow table cell 
     * 
     * The first line of the Textflow should be aligned with the baseline of
     * the "Benefit" text line. At the same time, the "Benefit" text should
     * have the same distance from the top cell border as the "Material"
     * text. To avoid any space from the top add the Textflow cell using
     * "fittextflow={firstlinedist=capheight}". Then add a margin of 4
     * points, the same as for the text lines.
     */
    $optlist = "textflow=" . $tf . " fittextflow={firstlinedist=capheight} " .
	"colwidth=" . $c2 . " margin=4";

    $tbl = $p->add_table_cell($tbl, 2, 4, "", $optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* -----------------------------------
     * Add the image cell with a text line
     * -----------------------------------
     * 
     * The image is placed in a cell starting in the third column of the
     * second row and spans three rows.
     * The column width is 90 points. The cell margins are set to 4 points. 
     */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

    $optlist = "fittextline={font=" . $boldfont . " fontsize=9} image=" .
	$image .	" colwidth=" . $c3 . " rowspan=3 margin=4";

    $tbl = $p->add_table_cell($tbl, 3, 2, "Amazingly robust!", $optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* -------------
     * Fit the table
     * -------------
     * 
     * Using "header=1" the table header will include the first line.
     * The "fill" option and the suboptions "area=header" and
     * "fillcolor={rgb 0.8 0.8 0.87}" specify the header row(s) to be filled
     * with the supplied color. 
     * The "stroke" option and the suboptions "line=frame linewidth=0.8" 
     * define the ruling of the table frame with a line width of 0.8.
     * Using "line=other linewidth=0.3" the ruling of all cells is specified
     * with a line width of 0.3.
     */
    $optlist = "header=1 fill={{area=header fillcolor={rgb 0.8 0.8 0.87}}}" .
	" stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}";
    
    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);
    
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
    header("Content-Disposition: inline; filename=mixed_table_contents.pdf");
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
