<?php
/* $Id: table_row_height.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Table row height:
 * Define the height of the rows in a table
 * 
 * Demonstrate how to use the "rowheight" option of add_table_cell() to 
 * define the height of the rows in a table.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Row Height";

$tbl = 0;

/* Image files and handles*/
$imagefile1 = "new.jpg";
$imagefile2 = "new.jpg";

/* Text handles */
$tf1 = 0; $tf2 = 0; 

/* x and y coordinates for the output of descriptive text */
$ystart = 800;
$ytext = $ystart;
$yoff = 15;
$x = 20;

/* Font handles and sizes */
$fontsize = 14;

/* Coordinates of the lower left and upper right corners of the table 
 * fitbox. The width of the fitbox matches the sum of the widths of the
 * three table columns. 
 */
$tab_llx = 100; $tab_lly = 200; $tab_urx = 450; $tab_ury = 600;

$rowheight1 = ($tab_ury - $tab_lly) / 2;
$rowheight2 = ($tab_ury - $tab_lly) / 4;

/* Textflows. Soft hyphens are marked with the character reference "&shy;"
 * (character references are enabled by the "charref" option). */
$tftext1 = 
    "Long Distance Glider: With this paper rocket you can send all your " .
    "messages even when sitting in a hall or in the cinema pretty near " .
    "the back.";

$tftext2 =
    "Giant Wing: An unbeliev&shy;able sailplane! It is amaz&shy;ingly " .
    "robust and can even do aerobatics.";


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
    
    /* Load two images */
    $image1 = $p->load_image("auto", $imagefile1, "");
    if ($image1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $image2 = $p->load_image("auto", $imagefile2, "");
    if ($image2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    $normalfont = $p->load_font("Helvetica", "unicode", "");
    if ($normalfont == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    $tfoptlist = "font=" . $normalfont . " fontsize=" . 
	$fontsize . " leading=110% charref";

    
    /* ---------------------------------------------------------------------
     * First case:
     * Create and fit a table containing two rows. The table width and 
     * height are given but no row height is defined. The row height will
     * be calculated automatically as a half, a third, a quarter etc. of the
     * table height, depending of the number of rows available. This means
     * that each row will always have the same size.
     * ---------------------------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* In the first column of the first row, add a table cell containing a
     * Textflow
     */
    
    /* In the first column of the first row, add an image cell */
    $imgoptlist = "image=" . $image1;
    $tbl = $p->add_table_cell($tbl, 1, 1, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf1 = $p->add_textflow(0, $tftext1, $tfoptlist);
    if ($tf1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the first row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf1;
    $tbl = $p->add_table_cell($tbl, 2, 1, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the first column of the second row, add an image cell */
    $imgoptlist = "image=" . $image2;
    $tbl = $p->add_table_cell($tbl, 1, 2, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf2 = $p->add_textflow(0, $tftext2, $tfoptlist);
    if ($tf2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the second row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf2;
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the stroke color which is used for testing to illustrate the
     * borders of the table fitbox with the "showborder" option below
     */
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 0.0, 0.0);
    
    /* Fit the table
     * The "stroke" option and the suboptions "line=frame linewidth=0.8" 
     * define the ruling of the table frame with a line width of 0.8.
     * Using "line=other linewidth=0.3" the ruling of all cells is specified
     * with a line width of 0.3.
     * "showborder" is only used for testing to illustrate the borders of
     * the table fitbox.
     */
    $taboptlist = 
	"stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}" .
	" showborder";
    
    $result = $p->fit_table($tbl, $tab_llx, $tab_lly, $tab_urx, $tab_ury, 
	$taboptlist);
    
    /* Check the result; "_stop" means all is ok */
    if (!$result == "_stop") {
	    if ($result == "_error")
		    throw new Exception("Error: " . $p->get_errmsg());
	    else {
		    /* Other return values require dedicated code to deal with */
	    }
    }
    
    /* Output some descriptive text */
    $p->setfont($boldfont, 14);
    $p->fit_textline("Table size is defined as " . ($tab_urx - $tab_llx) .  
	" x " . ($tab_ury - $tab_lly), $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("No row height is defined", $x, $ytext, "");
    
    $ytext -= 2 * $yoff;
    $p->fit_textline("Result:", $x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("Row height will be calculated automatically",
	$x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("With two rows available it will be half of the table " .
	"height", $x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("(with three rows it will be a third of the table " .
	"height, etc.)", $x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("As a result each row will have the same height", 
	$x, $ytext, "");
    
    $p->end_page_ext("");
    
    
    /* ----------------------------------------------------------------
     * Second case:
     * Create and fit a table containing two rows. The table width and 
     * height are given. The row height of each row is set to a minimum
     * height of 1. In this case, the row height is calculated as it is 
     * needed to fit the object completely. 
     * ----------------------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $ytext = $ystart;
    $tf1 = 0;
    $tf2 = 0;
    $tbl = 0;
    
    /* In the first column of the first row, add an image cell */
    $imgoptlist = "image=" . $image1;
    $tbl = $p->add_table_cell($tbl, 1, 1, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf1 = $p->add_textflow(0, $tftext1, $tfoptlist);
    if ($tf1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the first row, add a Textflow table cell.
     * Use the "rowheight" option to set the minimum row height to 1. The
     * row height will be increased automatically as the object needs more
     * space to be fit completely.
     */
    $celloptlist = "textflow=" . $tf1 . " rowheight=1";
    $tbl = $p->add_table_cell($tbl, 2, 1, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the first column of the second row, add an image cell */
    $imgoptlist = "image=" . $image2;
    $tbl = $p->add_table_cell($tbl, 1, 2, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf2 = $p->add_textflow(0, $tftext2, $tfoptlist);
    if ($tf2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the second row, add a Textflow table cell.
     * Use the "rowheight" option to set the minimum row height to 1. The
     * row height will be increased automatically as the object needs more
     * space to be fit completely.
     */
    $celloptlist = "textflow=" . $tf2 . " rowheight=1";
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the stroke color which is used for testing to illustrate the
     * borders of the table fitbos with the "showborder" option below
     */
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 0.0, 0.0);
    
    /* Fit the table
     * The "stroke" option and the suboptions "line=frame linewidth=0.8" 
     * define the ruling of the table frame with a line width of 0.8.
     * Using "line=other linewidth=0.3" the ruling of all cells is specified
     * with a line width of 0.3.
     * "showborder" is only used for testing to illustrate the borders of
     * the table fitbox.
     */
    $taboptlist = 
	"stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}" .
	" showborder";
    
    $result = $p->fit_table($tbl, $tab_llx, $tab_lly, $tab_urx, $tab_ury, 
	$taboptlist);
    
    /* Check the result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result  == "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
	    /* Other return values require dedicated code to deal with */
	}
    }
    
    /* Output some descriptive text */
    $p->setfont($boldfont, 14);
    $p->fit_textline("Table size is defined as " . ($tab_urx - $tab_llx) .
	" x " . ($tab_ury - $tab_lly), $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("For the first row rowheight=1 is defined", 
	$x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("For the second row rowheight=1 is defined",
	$x, $ytext, "");
    
    $ytext -= 2 * $yoff;
    $p->fit_textline("Result:", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("rowheight=1 defines the minimum height of the row. " .
	"The height will ", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("be increased automatically as the object needs more " .
	"space to be fit", $x, $ytext, "");
	  
    $p->end_page_ext("");
    
    
    /* ---------------------------------------------------------------------
     * Third case:
     * Create and fit a table containing two rows. The table width and 
     * height are given. The row height of each row is defined as percentage 
     * of the overall table height.
     * ---------------------------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $ytext = $ystart;
    $tf1 = 0;
    $tf2 = 0;
    $tbl = 0;
    
    /* In the first column of the first row, add an image cell */
    $imgoptlist = "image=" . $image1;
    $tbl = $p->add_table_cell($tbl, 1, 1, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf1 = $p->add_textflow(0, $tftext1, $tfoptlist);
    if ($tf1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the first row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf1 . " rowheight=70%";
    $tbl = $p->add_table_cell($tbl, 2, 1, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the first column of the second row, add an image cell */
    $imgoptlist = "image=" . $image2;
    $tbl = $p->add_table_cell($tbl, 1, 2, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf2 = $p->add_textflow(0, $tftext2, $tfoptlist);
    if ($tf2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the second row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf2 . " rowheight=30%";
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the stroke color which is used for testing to illustrate the
     * borders of the table fitbos with the "showborder" option below
     */
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 0.0, 0.0);
    
    /* Fit the table
     * The "stroke" option and the suboptions "line=frame linewidth=0.8" 
     * define the ruling of the table frame with a line width of 0.8.
     * Using "line=other linewidth=0.3" the ruling of all cells is specified
     * with a line width of 0.3.
     * "showborder" is only used for testing to illustrate the borders of
     * the table fitbox.
     */
    $taboptlist = 
	"stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}" .
	" showborder";
    
    $result = $p->fit_table($tbl, $tab_llx, $tab_lly, $tab_urx, $tab_ury, 
	$taboptlist);
    
    /* Check the result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result ==  "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
	    /* Other return values require dedicated code to deal with */
	}
    }
    
    /* Output some descriptive text */
    $p->setfont($boldfont, 14);
    $p->fit_textline("Table size is defined as " . ($tab_urx - $tab_llx) .
	" x " . ($tab_ury - $tab_lly), $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("For the first row rowheight=70% is defined",
	$x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("For the second row rowheight=30% is defined",
	$x, $ytext, "");
    
    $ytext -= 2 * $yoff;
    $p->fit_textline("Result:", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("The height of the first row will be 70% of " .
	"the table height", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("The height of the second row will be 30% of the table" .
	" height", $x, $ytext, "");
	  
    $p->end_page_ext("");
    
    
    /* -------------------------------------------------------------------
     * Fourth case:
     * Create and fit a table containing two rows. The table width and 
     * height are given. The row height of each row is defined as a fixed 
     * value.
     * -------------------------------------------------------------------
     */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $ytext = $ystart;
    $tf1 = 0;
    $tf2 = 0;
    $tbl = 0;
    
    /* In the first column of the first row, add an image cell */
    $imgoptlist = "image=" . $image1;
    $tbl = $p->add_table_cell($tbl, 1, 1, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf1 = $p->add_textflow(0, $tftext1, $tfoptlist);
    if ($tf1 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the first row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf1 . " rowheight=" . $rowheight1;
    $tbl = $p->add_table_cell($tbl, 2, 1, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the first column of the second row, add an image cell */
    $imgoptlist = "image=" . $image2;
    $tbl = $p->add_table_cell($tbl, 1, 2, "", $imgoptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the Textflow to be placed in the table cell */
    $tf2 = $p->add_textflow(0, $tftext2, $tfoptlist);
    if ($tf2 == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* In the second column of the second row, add a Textflow table cell */
    $celloptlist = "textflow=" . $tf2 . " rowheight=" . $rowheight2;
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $celloptlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the stroke color which is used for testing to illustrate the
     * borders of the table fitbos with the "showborder" option below
     */
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 0.0, 0.0);
    
    /* Fit the table
     * The "stroke" option and the suboptions "line=frame linewidth=0.8" 
     * define the ruling of the table frame with a line width of 0.8.
     * Using "line=other linewidth=0.3" the ruling of all cells is specified
     * with a line width of 0.3.
     * "showborder" is only used for testing to illustrate the borders of
     * the table fitbox.
     */
    $taboptlist = 
	"stroke={{line=frame linewidth=0.8} {line=other linewidth=0.3}}" .
	" showborder";
    
    $result = $p->fit_table($tbl, $tab_llx, $tab_lly, $tab_urx, $tab_ury, 
	$taboptlist);
    
    /* Check the $result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result ==  "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
	    /* Other return values require dedicated code to deal with */
	}
    }
    
    /* Output some descriptive text */
    $p->setfont($boldfont, 14);
    $p->fit_textline("Table size is given as " . ($tab_urx - $tab_llx) . 
	" x " . ($tab_ury - $tab_lly), $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("For the first row rowheight=" . $rowheight1 . 
	" is defined", $x, $ytext, "");
    $ytext -= $yoff;
    $p->fit_textline("For the second row rowheight="  . $rowheight2 .
	" is defined", $x, $ytext, "");
    
    $ytext -= 2 * $yoff;
    $p->fit_textline("Result:", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("rowheight defines the minimum row height. Since the " .
	"objects need less space it ", $x, $ytext, "");
    
    $ytext -= $yoff;
    $p->fit_textline("does not have to be increased. Parts of the table " .
	"fitbox will be left empty", $x, $ytext, "");
	  
    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=table_row_height.pdf");
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
