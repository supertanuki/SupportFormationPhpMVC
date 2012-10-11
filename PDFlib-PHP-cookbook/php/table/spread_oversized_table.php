<?php
/* $Id: spread_oversized_table.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Spread oversized table:
 * Output a table on 2 x m pages as it is needed to fit it completely.
 *
 * A table has a width which exceeds the width of the page. Therefore, output
 * the table on a pair of pages with one page acting as the left and one page 
 * acting as the right page. Add pairs of pages until the table has been placed
 * completely.
 * Output each table instance twice with an appropriate clipping rectangle 
 * applied to the "left" or the "right" part, respectively. Use the "rewind"
 * option of fit_table() to rewind the table to the state before the fit_table()
 * call used to place the first table instance. Otherwise the table instance
 * could not be placed a second time. 
 * 
 * In Acrobat, display the table as follows:
 * Disable "View, Page Display, Show Cover Page during Two-Up" if enabled.
 * Choose "View, Page Display, Two-Up" to display two pages next to each other.  
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Spread oversized Table";


$tbl = 0;

$pagewidth=421; $pageheight=595;

/* Number of rows and columns contained in the table */
$rowmax=50; $colmax=10;

/* Inside, outside, upper and lower page margins */
$inside=10; $outside=50; $top=20; $bottom=20;

/* Table width and height */
$tablewidth=2*($pagewidth-$outside-$inside);
$tableheight=$pageheight-$bottom-$top;

$lly=$bottom; $ury=$pageheight-$top;

$xmax = $pagewidth-$inside; 
$xright = $outside; 

$headertext = "Table header (centered across all columns)";

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
    

    /* ------------------- 
     * Add the table cells
     * -------------------
     */

    /* In row 1 add the table header which spans all columns
     */
    $row = 1; $col = 1;
    $font = $p->load_font("Times-Bold", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $optlist = "fittextline={position=center font=" . $font .
	" fontsize=14} " . "margin=2 colspan=" . $colmax;

    $tbl = $p->add_table_cell($tbl, $col, $row, $headertext, $optlist);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());


    /* In row 2 and above add the respective column and row numbers */
    for ($row++; $row <= $rowmax; $row++) {
	for ($col = 1; $col <= $colmax; $col++)
	{

	    $num = "Col " . $col . "/Row " . $row;
	    $optlist = "fittextline={font=" . $font . " fontsize=10} " .
		"margin=2";
	    
	    $tbl = $p->add_table_cell($tbl, $col, $row, $num, $optlist);
	    if ($tbl == 0)
	    throw new Exception("Error adding cell: " .
		$p->get_errmsg());
	}
    }
    
    /* Option list for fitting the table: 
     * define the first line as the header line;
     * shade every other row; draw lines for all table cells
     */
    $optlist = "header=1 fill={{area=rowodd fillcolor={gray 0.9}}} " .
	"stroke={{line=other}} ";
    
	    
    /* --------------------------------------------------------------------
     * Check if the table width fits completely on the page or if the table
     * width has to distributed over two pages, i.e. the left and the right
     * page
     * --------------------------------------------------------------------
     */

    /* Place the first table instance over both pages in blind mode. All
     * calculations will be done but the table will not actually be placed.
     * Then, retrieve the number of the last column (i.e. the vertical line)
     * which still fits on the "left" page. 
     */
    $p->begin_page_ext($pagewidth, $pageheight, "");
    
    $llx = $outside;
    $urx = $llx+$tablewidth;
    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist . "blind");

    if ($result == "_error" || $result == "_boxempty")
	throw new Exception ("Couldn't place table: " . $p->get_errmsg());
    
    /* Query the x coordinate "xright" of the last column which fits in the
     * fitbox on the "left" page
     */
    for ($i = 1; $i <= $colmax; $i++)
    {
	/* Get the x coordinate of the vertical line with number #. 
	 * "xvertline0" is the left table border.
	 */ 
	$x = $p->info_table($tbl, "xvertline" . $i);  
	if ($x > $xmax) {
	    break;
	}
	$xright = $x;
    }

    /* -------------------------------------------------------------
     * Place the first half of the table instance on the "left" page
     * -------------------------------------------------------------
     */
    
    /* Since the table will be clipped on the right the inner margin of the
     * left page may be increased. Use the "xshift" value to shift the table
     * to the right so that the inner margin will become the default value
     * again.
    */
    $xshift = $pagewidth - $xright - $inside;
    
    /* Using the x coordinate of the last column fitting on the "left" page
     * set the clipping rectangle for the first columns of the table which 
     * will be placed on the left page.
     */
    $llx = $outside + $xshift;
    $widthleft = $xright - $outside;
    $p->rect($llx, $lly, $widthleft, $tableheight);
    $p->clip();
    
    /* Place the table instance on the "left" page. 
     * Use the "rewind" option to rewind the table to the state before
     * the last fit_table() call (which just happened in "blind" mode for 
     * calculation purposes).
     * The table instance will be placed with the complete table width but
     * it will be clipped according to the clipping rectangle to show only
     * the first "xright" columns.
     */
    $llx = $outside + $xshift;
    $urx = $llx + $tablewidth;
    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist . " rewind=-1");

    if ($result == "_error")
	throw new Exception ("Couldn't place table : " . $p->get_errmsg());
    
    $p->end_page_ext("");
    
    /* ---------------------------------------------------------------
     * Place the second half of the table instance on the "right" page
     * ---------------------------------------------------------------
     */
     
    $p->begin_page_ext($pagewidth, $pageheight, "");
    
    /* Set the clipping rectangle for all further columns of the table which
     * will be placed on the "right" page
     */
    $llx = $inside;
    $widthright = $tablewidth-$widthleft; 
    $p->rect($llx, $lly, $widthright, $tableheight);
    $p->clip();
    
    /* Place the table instance again on the "right" page. 
     * Use the "rewind" option to rewind the table to the state before
     * the last fit_table() call (which has been used to place the first 
     * table instance). Otherwise this instance could not be placed a second
     * time.
     * The fitbox of the table instance is shifted to the left "outside"
     * the page so that the last columns can be shown on the page.
     * The table instance will be placed with the complete table width but
     * it will be clipped according to the clipping rectangle to show only
     * the last columns. 
     */ 
    $llx = $llx-$widthleft;
    $urx = $inside+$widthright;
    $result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist . " rewind=-1");

    if ($result == "_error")
	throw new Exception ("Couldn't place table : " . $p->get_errmsg());

    $p->end_page_ext("");
    
    
    /* ---------------------------------------------------------------
     * Place all further table instances on a left and right page each
     * ---------------------------------------------------------------
     */ 
    
    /* Loop until all of the table is placed; create new pages as long as
     * more table instances need to be placed.
     */
    while ($result == "_boxfull")
    {
	$p->begin_page_ext($pagewidth, $pageheight, "");
	
	/* Set the clipping rectangle to place the first columns of the 
	 * table on the left page
	 */
	$llx = $outside + $xshift;
	$widthleft = $xright-$outside;
	$p->rect($llx, $lly, $widthleft, $tableheight);
	$p->clip();
	
	/* Place the table instance on the "left" page */ 
	$urx = $llx+$tablewidth;
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);

	if ($result == "_error")
	    throw new Exception ("Couldn't place table : " . $p->get_errmsg());
	
	$p->end_page_ext("");
	
	$p->begin_page_ext($pagewidth, $pageheight, "");
	
	/* Set the clipping rectangle to place all further columns of the 
	 * table on the right page
	 */
	$llx = $inside;
	$widthright = $tablewidth-$widthleft; 
	$p->rect($llx, $lly, $widthright, $tableheight);
	$p->clip();
	
	/* Place the table instance again on the "right" page */ 
	$llx = $llx-$widthleft;
	$urx = $inside+$widthright;
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, 
	    $optlist . " rewind=-1");

	if ($result == "_error")
	    throw new Exception ("Couldn't place table : " . $p->get_errmsg());
	
	$p->end_page_ext("");
    }
    
    /* Check the result; "_stop" means all is ok */
    if (!$result == "_stop") {
	if ($result ==  "_error")
	    throw new Exception ("Error when placing table: " .
		$p->get_errmsg());
	else
	    /* Any other return value is a user exit caused by the "return" 
	     * option; this requires dedicated code to deal with.
	     */
	    throw new Exception ("User return found in Textflow");
    }

    /* This will also delete Textflow handles used in the table */
    $p->delete_table($tbl, "");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=spread_oversized_table.pdf");
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
