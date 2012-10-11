<?php
/* $Id: vertical_text_alignment.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Vertical text alignment:
 * Demonstrate the vertical alignment of text lines and Textflows in a table
 * cell.
 * 
 * Create a simple table with some text lines and a Textflow which are
 * vertically centered in the table cell.
 * Then, create the same table but with all cell contents having the same
 * vertical distance from the cell borders regardless of whether they are
 * Textflows or text lines.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Vertical Text Alignment";

$tf=0; $tbl=0;

/* Define the column widths of the first and the second column */
$c1 = 80; $c2 = 120;

/* Define the lower left and upper right corners of the table instance.
 * The table width of 200 matches the sum of the widths of the two table
 * columns 80 + 120.
 */
$llx=100; $lly=500; $urx=300; $ury=600;

/* Text for filling a table cell with multi-line Textflow */
$tf_text = "It is amazingly robust and can even do aerobatics. " .
		 "But it is best suited to gliding.";

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
    
    /* Start the page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    
    /* ---------------------------------------------------------
     * Create a simple table with some text lines and a Textflow
     * ---------------------------------------------------------
     */
    
    /* Define the option list for three text line cells placed in the first
     * column. Define a fixed column width of 80 points. The position of the
     * text line with a font size of 8 is in the center on the left with a
     * margin of 4 points.
     */
    $optlist = "fittextline={position={left center} font=" . $font .
	" fontsize=8} colwidth=" . $c1 . " margin=4";

    /* Add a text line cell in column 1 row 1 */
    $tbl = $p->add_table_cell($tbl, 1, 1, "Our Paper Planes", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Add a text line cell in column 1 row 2 */
    $tbl = $p->add_table_cell($tbl, 1, 2, "Material", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Add a text line cell in column 1 row 3 */
    $tbl = $p->add_table_cell($tbl, 1, 3, "Benefit", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Define the option list for a text line placed in the second column.
     * It is similar to the options defined above except of the column width
     * being increased to 120.
     */
    $optlist = "fittextline={position={left center} font=" . $font .
	" fontsize=8} colwidth=" . $c2 . " margin=4";
    
    /* Add a text line cell in column 2 row 2 */
    $tbl = $p->add_table_cell($tbl, 2, 2, "Offset print paper 220g/sqm",
	$optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Add a Textflow to be placed in a table cell later */
    $optlist = "font=" . $font . " fontsize=8 leading=110%"; 
    $tf = $p->add_textflow(0, $tf_text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Define the option list for the Textflow cell using the handle
     * retrieved above. The column width for the cell is 120 with a margin 
     * of 4 points.
     */
    $optlist = "textflow=" . $tf . " margin=4 colwidth=" . $c2;

    /* Add the Textflow table cell in column 2 row 3 */
    $tbl = $p->add_table_cell($tbl, 2, 3, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Define the option list for fitting the table.
     * 
     * The "stroke" option specifies the table ruling. The 
     * "line=frame linewidth=0.8" suboptions define an outside ruling with a
     * line width of 0.8 and the "line=other linewidth=0.3" suboptions 
     * define a cell ruling with a line width of 0.3.
     */
    $optlist = "stroke={{line=frame linewidth=0.8} " .
	"{line=other linewidth=0.3}}";

    /* Place the table instance */
    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);

    /* Check the result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result == "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
		/* Other return values require dedicated code to deal with */
	}
    }

    /* Delete the table handle. This will also delete any Textflow handles
     * used in the table
     */
    $p->delete_table($tbl, "");
    
    
    /* -------------------------------------------------------------------
     * Create the same table but in this case we want all cell contents to
     * have the same vertical distance from the cell borders regardless of
     * whether they are Textflows or text lines.
     * -------------------------------------------------------------------
     */
    
    $tbl = 0;
    $tf = 0;
    
    /* Prepare the option list for the text line cells placed in the first
     * column.
     *  
     * Define a fixed row height of 14 points, and the position of the text
     * line to be on the top left with a margin of 4 points. 
     * The "fontsize=8" option which has been supplied above doesn't exactly
     * represent the letter height but adds some space below and above.
     * However, the height of an uppercase letter is exactly represented by
     * the capheight value of the font. For this reason use 
     * "fontsize={capheight=6}" which will approximately result in a font
     * size of 8 points and (along with "margin=4"), will sum up to an
     * overall height of 14 points corresponding to the "rowheight" option. 
    */
    $optlist = "fittextline={position={left top} font=" . $font . 
	" fontsize={capheight=6}} rowheight=14 colwidth=" . $c1 .
	" margin=4";
    
    /* Add the text line cells */
    $tbl = $p->add_table_cell($tbl, 1, 1, "Our Paper Planes", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 2, "Material", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tbl = $p->add_table_cell($tbl, 1, 3, "Benefit", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Define the option list for the text line placed in the second 
     * column. It is similar to the options defined above except of the 
     * column width increased to 120.
     */
    $optlist = "fittextline={position={left top} font=" . $font . 
	" fontsize={capheight=6}} rowheight=14 colwidth=" . $c2 .
	" margin=4";

    $tbl = $p->add_table_cell($tbl, 2, 2, "Offset print paper 220g/sqm",
	$optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Prepare the option list for adding the Textflow.
     * Use "fontsize={capheight=6}" which will approximately result in a
     * font size of 8 points and (along with "margin=4"), will sum up to an
     * overall height of 14 points as for the text lines above.
     */
    $optlist = "font=" . $font . " fontsize={capheight=6} leading=110%"; 
    
    /* Add the Textflow to be placed in a table cell later */
    $tf = $p->add_textflow(0, $tf_text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Prepare the option list for the Textflow cell 
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
    
    $tbl = $p->add_table_cell($tbl, 2, 3, "", $optlist);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

    /* Define the option list for fitting the table. The "stroke" option
     * specifies the table ruling. The "line=frame linewidth=0.8" suboptions
     * define an outside ruling with a line width of 0.8 and the 
     * "line=other linewidth=0.3" suboptions define a cell ruling with a
     * line width of 0.3.
     */
    $optlist = "stroke={{line=frame linewidth=0.8} " .
	"{line=other linewidth=0.3}}";
    
    /* Place the table instance */
    $result = $p->fit_table($tbl, $llx, $lly-150, $urx, $ury-150, $optlist);

    if ($result == "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=vertical_text_alignment.pdf");
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
