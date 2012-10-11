<?php
/* $Id: table_timesheet.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Table time:
 * Create a monthly time sheet for the working hours of an employee
 * 
 * Create a table with one row for each day of the month. Each row has several
 * columns representing various projects. In a cell the working hours for one or
 * more tasks are added, related to the respective day and project.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Time Sheet";

$tf=0; $tbl=0;

$pagewidth = 842; $pageheight = 595;
$fontsize = 12;
$capheight = 8.5;
$margin = 4;
$rwfirst = 16; $rwother = 32;
$leading = "120%";

$dbreak = 15;

/* The table coordinates are fixed */
$llx = 50; $urx = $pagewidth - $llx;
$lly = 30; $ury = $pageheight - $lly;

$tablewidth = $urx - $llx;
$cwsum = 0;

$yoffset = 10;
$yheading = $ury + $yoffset;
$ycontinued = $lly - $yoffset;
       
/* The widths of the first two columns */
$cwfirst = 50;

$maxdays = 31;

/* Projects */
$projects = array(
    "Project 1", "Project 2", "Project 3", "Project 4", "Project 5"
);

/* For the first five days of the month entries are made in the timesheet
 * for each of the projects defined above 
 */
$times = array(
    /* Project 1, Project 2, Project 3, Project 4, Project 5 */
    array( "5h task-001", "2h task-033", "", "", "1h task-024" ),
    array( "2h task-354, 2h task-099, 2h task-045, 1h task-001, 1h task-270",
      "", "", "", "" ),
    array( "", "2h task-354", "4h task-033", "2h task-001", "" ),
    array( "8h task-001", "", "", "", "" ),
    array( "", "2h task-033", "5h task-045, 2h task-024, 0.5h task-033", "", "" )
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
    $p->set_info("Title", $title);

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
    $p->fit_textline("Time Sheet", $llx, $yheading, "");
	  
  
    /* -----------------------------------------------------------------
     * In the first row, add the text line cells containing the headings
     * -----------------------------------------------------------------
     */
    $col = 1; $row = 1;
    
    /* Prepare the option list:
     * "colwidth" defines a fixed column width.
     * "rowheight" defines a fixed row height.
     * "margin" adds some empty space between the text and the cell borders.
     */
    $tlcell_opts = "fittextline={font=" . $boldfont . 
	" fontsize={capheight=" . $capheight . "} position={left top}} " .
	" colwidth=" . $cwfirst . " rowheight=" . $rwfirst . 
	" margin=" . $margin;
		      
    /* Add the "Day" heading */
    $tbl = $p->add_table_cell($tbl, $col++, $row, "Day", $tlcell_opts);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    $cwsum += $cwfirst;
    
    /* Add the "hours" heading */
    $tbl = $p->add_table_cell($tbl, $col++, $row, "Hours", $tlcell_opts);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    $cwsum += $cwfirst;
    
    /* Add the project headings. Those columns evenly share the remaining 
     * width of the table's fitbox.
     */
    for ($i = 0; $i < count($projects); $i++) {
	$tlcell_opts = "fittextline={font=" . $boldfont . 
	    " fontsize={capheight=" . $capheight . "} position={left top}}" .
	    " rowheight=" . $rwfirst . 
	    " colwidth=" . (($tablewidth - $cwsum) / count($projects)) .
	    " margin=" . $margin;
    
	$tbl = $p->add_table_cell($tbl, $col++, $row, $projects[$i], $tlcell_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    }
    
	   
    /* --------------------------------------------------------------
     * In the first column add the text line cells containing the day
     * --------------------------------------------------------------
     */
    $col = 1; $row = 2;
    
    /* Loop over all days */
    for ($i = 1; $i <= $maxdays; $i++)
    {
	/* Prepare the option list for adding the text line cell */
	$tlcell_opts = "fittextline={font=" . $boldfont . 
	" fontsize={capheight=" . $capheight . "} position={left top}} " .
	" colwidth=" . $cwfirst . " rowheight=" . $rwother . 
	" margin=" . $margin;
	
	/* We want to spread the table over two pages. The last row placed 
	 * on the first page should be the one representing a defined day,
	 * e.g. the 15th. To accomplish this use the "return" 
	 * option of add_table_cell() when adding the respective cell. This
	 * signals to fit_table() to return after having placed the
	 * corresponding table row, and we can fit the following table rows
	 * in a subsequent call on the second page.    
	 */
	if ($i == $dbreak)
	    $tlcell_opts .= " return break";
    
	/* Add the text line cell */
	$tbl = $p->add_table_cell($tbl, $col, $row++, $i . ".", $tlcell_opts);
	       
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    }
    
    
    /* -------------------------------------------------------------------
     * Add the Textflow cell containing the individual times spent for the
     * different projects
     * -------------------------------------------------------------------
     */
    $col = 3; $row = 2;
    
    /* Prepare the option list for adding a Textflow.
     * "leading" specifies the distance between to text lines.
     */
    $tf_opts = "font=" . $regularfont . 
	" fontsize={capheight=" . $capheight . "} leading=" . $leading;
    
    for ($i = 0; $i < count($times); $i++) {
	for ($j = 0; $j < count($times[$i]); $j++) {
	    /* Add the Textflow */
	    $tf = $p->add_textflow(0, $times[$i][$j], $tf_opts);
	    if ($tf == 0)
		throw new Exception("Error: " . $p->get_errmsg());
	    
	    /* Prepare the option list for adding the Textflow cell 
	     * The first line of the Textflow should be aligned with the
	     * baseline of the text lines. At the same time, the text lines
	     * should have the same distance from the top cell border as the
	     * Textflow. To avoid any space from the top add the Textflow 
	     * cell using "fittextflow={firstlinedist=capheight}".
	     * "fitmethod=auto" scales the text until it entirely fits into
	     * the cell. (You can use the "minfontsize" option to define a
	     * lower limit when scaling the text, if required.)
	     * Note that if "fitmethod=auto" is not used the row height will
	     * be increased until the text fits completely into the cell.
	     * "verticalalign=top" will place the text at the top of the 
	     * cell.
	     * "rowheight" defines the row height.
	     * "margin" adds some empty space between the text and the cell 
	     * borders.
	     */
	    $tfcell_opts = 
		"textflow=" . $tf . 
		" fittextflow={firstlinedist=capheight verticalalign=top" .
		" fitmethod=auto}" . 
		" rowheight=" . $rwother . " margin=" . $margin;
    
	    /* Add the table cell */
	    $tbl = $p->add_table_cell($tbl, $col + $j, $row + $i, "", $tfcell_opts);
    
	    if ($tbl == 0)
		throw new Exception("Error: " . $p->get_errmsg());
	}
    }
     
		
    /* ------------------------------------
     * Place the table on one or more pages
     * ------------------------------------
     */

    /* Prepare the option list for fitting the table.
     * "header=1" will repeat the first row at the beginning of each new 
     * page. The "stroke" option will stroke lines with two different line
     * widths. The table frame as well as each vertical line to the right of
     * a day-of-the-week cell is stroked with a line width of 1, all other
     * lines are stroked with a line width of 0.3.
     */
    $fittab_opts = "header=1 stroke={" .
	"{line=frame linewidth=1} {line=other linewidth=0.3} " .
	"{line=vert2 linewidth=1}}";
			 
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
    header("Content-Disposition: inline; filename=table_timesheet.pdf");
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
