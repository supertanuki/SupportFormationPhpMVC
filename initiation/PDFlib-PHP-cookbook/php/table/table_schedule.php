<?php
/* $Id: table_schedule.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Table schedule:
 * Create a weekly booking plan for the meeting rooms of a company
 * 
 * For the weekly bookings of the meeting rooms in a company, create a table
 * with each cell representing a period of time on a day of the week for a
 * certain meeting room. If a meeting room is booked for a certain period of 
 * time on a certain day of the week the corresponding cells will be colorized
 * and provided with some booking text. 
 * Use the "colwidth" and "rowheight" options of add_table_cell() to create the
 * table as a kind of grid with unique and fixed column width and row height. 
 * Use the "fitmethod=auto" option to decrease the font size if the booking text
 * is too large to fit completely into the cell.
 * Use the "matchbox" option of add_table_cell() to colorize cells.
 * 
 * The table is to be spread over two pages with a defined row being the last
 * one on the first page. Use the "return" option of add_table_cell() to force
 * fit_table() to a break after having placed the last row containing the cell. 
 *  
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Schedule";

$tf=0; $tbl=0;

$pagewidth = 842; $pageheight = 595;
$fontsize = 12;
$capheight = 8.5;
$margin = 4;
$rowheight1 = 16; $rowheight2 = 32;
$leading = "120%";

$dayspan = 5; $timespan=3; $mroomspan = 2; $croomspan=3;
$tstart = 5; $tend = 22; $tbreak = 12;

/* The table coordinates are fixed */
$llx = 50; $urx = $pagewidth - $llx;
$lly = 120; $ury = $pageheight - $lly;

$yoffset = 15;
$yheading = $ury + 2 * $yoffset;
$ycontinued = $lly - $yoffset;
       
/* The widths of the first and the other columns is fixed */
$cwfirst = 50; $cwother = 30;

$maxdays = 6;

$days = array(
    "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
);

$maxrooms = 5;

$rooms = array(
    "M1", "M2", "C1", "C2", "C3"
);

$idxDay = 0; $idxStart = 1; $idxEnd = 2; $idxRoom = 3;
$idxText = 4; $idxColor = 5;

$maxbookings = 6;

$bookings =array(
/*   day, start, end,  room, text,                  color */
    array("1", "8",   "12", "1",  "Company Meeting",     "rgb 0.8 0.36 0.36"),
    array("2", "11",  "20", "4",  "Technical Workgroup", "rgb 1.0 0.84 0.0"),
    array("4", "8",   "16", "2",  "QM Meeting",          "rgb 0.0 0.8 0.82"),
    array("5", "10",  "12", "4",  "Admin Training",      "rgb 0.6 0.8 0.92"),
    array("5", "14",  "18", "4",  "Admin Training",      "rgb 0.6 0.8 0.92"),
    array("6", "14",  "22", "5",  "Admin Training",      "rgb 0.6 0.8 0.92")
 );


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

    /* Load the bold and regular styles of a font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $regularfont = $p->load_font("Helvetica", "unicode", "");
    if ($regularfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start the output page */
    $p->begin_page_ext($pagewidth, $pageheight, "");

    /* Output the heading */
    $p->setfont($boldfont, $fontsize);
    $p->fit_textline("Booking Schedule", $llx, $yheading, "");
	  
    /* Prepare the general option list for adding a Textflow.
     * For an exact vertical alignment of Textflow and text lines note the
     * following:
     * The height of an uppercase letter is exactly represented by the
     * capheight value of the font. For this reason use the capheight in the
     * font size specification. For example, a capheight of 8.5 will
     * approximately result in a font size of 12 points. 
     * "alignment=center" centers the text.
     * "leading" specifies the distance between to text lines.
     */
    $tf_opts = "font=" . $regularfont . " alignment=center" . 
	" fontsize={capheight=" . $capheight . "} leading=" . $leading; 


    /* -------------------------------------------------------------------
     * Add the Textflow cell containing the time heading which spans three
     * rows
     * -------------------------------------------------------------------
     */
    $col = 1; $row = 1;
    
    /* Add the Textflow to be placed in the heading cell */
    $tf = $p->add_textflow(0, "Time\nfrom\nto", $tf_opts);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Prepare the option list for adding the Textflow cell 
     * 
     * The first line of the Textflow should be aligned with the baseline of
     * the text lines. At the same time, the text lines should have the same
     * distance from the top cell border as the Textflow. To avoid any space
     * from the top add the Textflow cell using
     * "fittextflow={firstlinedist=capheight}".
     * "colwidth" defines the width of the first column the cell is spanned.
     * "rowheight" defines the row height.
     * "margin" adds some empty space between the text and the cell borders.
     * "colspan" defines the number of columns the cell is spanned.
     */
    $tfcell_opts = 
	"textflow=" . $tf . 
	" fittextflow={firstlinedist=capheight fitmethod=auto}" . 
	" colwidth=" . $cwfirst .
	" rowheight=" . $rowheight1 .
	" margin=" . $margin .
	" rowspan=" . $timespan;
    
    $tbl = $p->add_table_cell($tbl, $col, $row, "", $tfcell_opts);
    
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
	 
    /* -------------------------------------------------------------------
     * Add the text line cells containing the days of the week headings in
     * the first row, spanning several columns each
     * -------------------------------------------------------------------
     */
    $col = 2; $row = 1;
    
    /* Prepare the option list:
     * "position={center top}" positions the text on the top left. 
     * "fitmethod=auto" decreases the font size of the text if it is too
     * large to fit completely into the cell instead of increasing the row 
     * height.
     * The height of an uppercase letter is exactly represented by the
     * capheight value of the font. For this reason use the capheight in the
     * font size specification. For example, to match a row height of 16,
     * you could use a capheight of 8.5 and a margin of 4.
     * "fitmethod=auto" will decrease the font size, if necessary, until the
     * text line fits completely into the cell.
     * "colwidth" defines the width of the first column the cell is spanned.
     * "rowheight" defines the row height.
     * "margin" adds some empty space between the text and the cell borders.
     * "colspan" defines the number of columns the cell is spanned.
     */
    $tlcell_opts = "fittextline={position={center top} fitmethod=auto" .
    " font=" . $boldfont . " fontsize={capheight=" . $capheight . "}} " .
    " colwidth=" . $cwother .
    " rowheight=" . $rowheight1 .
    " margin=" . $margin .
    " colspan=" . $dayspan;
		 
    /* Add the table cells containing the days of the week */
    for ($i = 0; $i < $maxdays; $i++)
    {
	$tbl = $p->add_table_cell($tbl, $col, $row, $days[$i], $tlcell_opts);
    
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    
	$col += $dayspan;
    }
    
    
    /* --------------------------------------------------------------------
     * In the second row below each day of the week, add the "Meeting Room"
     * and "Conference Room" Textflow heading cells. The two cells together
     * span the same number of columns as spanned by the day of the week. 
     * --------------------------------------------------------------------
     */
    $col = 2; $row = 2;
    
    /* Loop over the number of days of the week */
    for ($i = 0; $i < $maxdays; $i++)
    {
	/* Add the "Meeting Room" Textflow */
	$tf = $p->add_textflow(0, "Meeting Room", $tf_opts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
	/* Add the "Meeting Room" Textflow cell */
	$tfcell_opts = "textflow=" . $tf . 
	    " fittextflow={firstlinedist=capheight fitmethod=auto}" . 
	    " colwidth=" . $cwother .
	    " rowheight=" . $rowheight2 .
	    " margin=" . $margin .
	    " colspan=" . $mroomspan;
	       
	$tbl = $p->add_table_cell($tbl, $col, $row, "", $tfcell_opts);
    
	if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$col += $mroomspan;
	
	/* Add the "Conference Room" Textflow */
	$tf = $p->add_textflow(0, "Conference Room", $tf_opts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
	/* Add the "Conference Room" Textflow cell */
	$tfcell_opts = "textflow=" . $tf .
	" fittextflow={firstlinedist=capheight fitmethod=auto}" . 
	" colwidth=" . $cwother . 
	" rowheight=" . $rowheight2 .
	" margin=" . $margin .
	" colspan=" . $croomspan;
    
	$tbl = $p->add_table_cell($tbl, $col, $row, "", $tfcell_opts);
    
	if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	$col += $croomspan;
    }
    
    
    /* ---------------------------------------------------------------------
     * In the third row below the "Meeting Room" and "Conference Room" cells
     * for each day of the week add five text line heading cells containing
     * the names of the meeting rooms. 
     * ---------------------------------------------------------------------
     */
    $col = 2; $row = 3;
    
    /* Prepare the option list for adding the cells */
    $tlcell_opts = "fittextline={position={center top} fitmethod=auto" .
	" font=" . $regularfont . " fontsize={capheight=" . $capheight . "}}".
	" colwidth=" . $cwother .
	" rowheight=" . $rowheight1 .
	" margin=" . $margin;
 
    /* Loop over the number of days of the week */
    for ($i = 0; $i < $maxdays; $i++)
    {
	/* Add the cells with the names of the room */
	for ($j = 0; $j < $maxrooms; $j++) {
	    $tbl = $p->add_table_cell($tbl, $col++, $row, $rooms[$j], $tlcell_opts);
	    
	    if ($tbl == 0)
		throw new Exception("Error adding cell: " . $p->get_errmsg());
	}
    }
    
    
    /* --------------------------------------------------------------
     * In the first column add the Textflow cells containing the time 
     * intervals, one cell for each hour
     * --------------------------------------------------------------
     */
    $col = 1; $row = 4;
    
    /* For outputting the time of the day, initialize the maximum number
     * of fraction digits to two
     */
    /* Loop over all time intervals */

    for ($i = 1, $t = $tstart; $i <= $tend - $tstart; $i++, $t++)
    {
	/* Format the current time interval to a maximum of two fraction
	 * digits
	 */
	$value = sprintf("%.2f", $t);
	
	$text = $value;
	
	/* Add the time interval Textflow */
	$tf = $p->add_textflow(0, $text, $tf_opts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
	/* Prepare the option list for adding the time interval headings */
	$tfcell_opts = "textflow=" . $tf . 
	" fittextflow={firstlinedist=capheight  fitmethod=auto}" . 
	" colwidth=" . $cwfirst .
	" rowheight=" . $rowheight2 .
	" margin=" . $margin;
	
	/* We want to spread the table over two pages. The last row placed 
	 * on the first page should be the one representing a defined time
	 * interval, e.g. of 12-13. To accomplish this use the "return" 
	 * option of add_table_cell() when adding the respective cell. This
	 * signals to fit_table() to return after having placed the
	 * corresponding table row, and we can fit the following table rows
	 * in a subsequent call on the second page.    
	 */
	if ($t == $tbreak)
	    $tfcell_opts .= " return break";
    
	/* Add the time interval Textflow cell */
	$tbl = $p->add_table_cell($tbl, $col, $row++, "", $tfcell_opts);
	       
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    }
    
    
    /* -----------------------------------------------------------------
     * For each booking item add a text line cell containing the booking
     * text and colorize it. Place the cell according to the day of the
     * week, time interval and meeting room affected.
     * -----------------------------------------------------------------
     */
    
    /* Loop over all bookings */
    for ($i = 0; $i < $maxbookings; $i++) {
	/* Read the attributes of the current booking */
	$start = $bookings[$i][$idxStart];
	$end = $bookings[$i][$idxEnd];
	$day = $bookings[$i][$idxDay];
	$room = $bookings[$i][$idxRoom];
	$text = $bookings[$i][$idxText];
	
	/* Get the column and row as well as the number of rows spanned by
	 * the cell
	 */
	$rowspan = 1;
	
	$col = 2 + (($day - 1) * $dayspan) + ($room - 1);
	$row = 4 + ($start - $tstart);
	
	if ($end != $start)
	    $rowspan = $end - $start;
	
	/* Prepare the option list for adding the booking item cell */
	$opts = "fittextline={position={center} " .
	    " fitmethod=auto font=" . $regularfont . " orientate=west" . 
	    " fontsize={capheight=" . $capheight . "}} " .
	    " colwidth=" . $cwother . 
	    " rowheight=" . $rowheight2 . 
	    " margin=" . $margin . 
	    " rowspan=" . $rowspan . 
	    " matchbox={fillcolor={" . $bookings[$i][$idxColor] . "}}";
	
	$tbl = $p->add_table_cell($tbl, $col, $row, $text, $opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());    
    }
	    
	 
    /* ------------------------------------
     * Place the table on one or more pages
     * ------------------------------------
     */

    /* Prepare the option list for fitting the table.
     * "header=3" will repeat the first three rows at the beginning of
     * each new page. The "stroke" option will stroke lines with two 
     * different line widths. The table frame as well as each vertical
     * line to the right of a day-of-the-week cell is stroked with a
     * line width of 1, all other lines are stroked with a line width of
     * 0.3.
     */
    $fittab_opts = "header=3 stroke={" .
	"{line=frame linewidth=1} {line=other linewidth=0.3} ";
    
    for ($i = 0; $i < $maxdays; $i++)
	$fittab_opts .= "{line=vert" . (1+$i*$maxrooms) . " linewidth=1} ";     
		  
    $fittab_opts .= "}";
    
    /* Loop until all of the table is placed; create new pages as long as
     * more table instances need to be placed
     */
    do {
	/* Place the table instance */
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $fittab_opts);

	if ($result == "_error")
	    throw new Exception ("Couldn't place table: " .
		$p->get_errmsg());
	
	/* A return value of "break" has been explicitly specified in 
	 * add_table_cell() when adding the cell for a certain time interval
	 * after which a new page shall be started. 
	 */
	if ($result == "_boxfull" || $result == "break") {
	    $p->setfont($regularfont, $fontsize);
	    $p->fit_textline("-- Continued --", $urx, $ycontinued, 
		"position {right top}");
	    
	    $p->end_page_ext("");
	    $p->begin_page_ext($pagewidth, $pageheight, "");
	}
    } while ($result == "_boxfull" || $result == "break");
    
    $p->end_page_ext("");
   
    $p->end_document("");
  
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=table_schedule.pdf");
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
