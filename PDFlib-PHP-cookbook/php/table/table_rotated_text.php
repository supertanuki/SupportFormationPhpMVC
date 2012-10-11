<?php
/* $Id: table_rotated_text.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Table rotated text:
 * Create a table containing rotated text
 * 
 * Create a table with rotated text lines and Textflow spanning several rows.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Rotated Text";

$tf=0; $tbl=0;

$pagewidth = 595; $pageheight = 842;
$fontsize = 12;
$capheight = 8.5;
$margin = 4;
$leading = "120%";

$c_table = 1; $c_numbers = 1; $c_description = 4;
$c_models = 2; $c_category = 1; $c_model = 2; $c_plane = 3;

$r_model = 4; $r_plane = 4; $r_description = 4;

/* The table coordinates are fixed */
$llx = 50; $urx = $pagewidth - $llx;
$lly = 30; $ury = $pageheight - $lly;

$yoffset = 10;
$ycontinued = $lly - $yoffset;

/* Row height */
$rowheight = 16;
       
/* Column widths */
$cwfirst = 50; $cwplane = 50; $cwdescription = 100;

$maxcols = 4;

/* Projects */
$models = array(
    "Rockets", "Gliders", "Arrows", "Darts"
);

$descriptions = array(
    "With the paper rockets you can send all your messages even when " .
    "sitting in a hall or in the cinema pretty near the back.",
    
    "Unbelievable sailplanes! They are amazingly robust and can even do " .
    "aerobatics. But they are best suited to gliding.",
    
    "The paper arrows can be thrown with big swing. We launched it from " .
    "the roof of a hotel. It stayed in the air a long time and covered " .
    "a considerable distance.",

    "The super darts can fly giant loops with a radius of 4 or 5 meters " .
    "and can cover long distances. Its heavy cone point is slightly " .
    "bowed upwards to get the lift required for loops"
);

/* Three planes belong to each model */
$planes = array(
    array( "Long Distance Glider", "Giant Wing", "Spider Glider" ),
    array( "Cone Head Rocket", "Turbo Flyer", "Red Baron" ),
    array( "Free Gift", "Bare Bone Kit", "Witty Kitty" ),
    array( "Super Dart", "Giga Trash", "Cool Carve" )
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

    /* Prepare the general option list for horizontal and vertical text line
     * cells:
     * The height of an uppercase letter is exactly represented by the
     * capheight value of the font. For this reason use the capheight in the
     * font size specification. For example, to match a row height of 16,
     * you could use a capheight of 8.5 and a margin of 4.
     * "colwidth" defines a fixed column width.
     * "rowheight" defines a fixed row height.
     * "margin" adds some empty space between the text and the cell borders.
     */
    $htlcell_opts = "fittextline={font=" . $boldfont . 
	" fontsize={capheight=" . $capheight . "} position={left top}} " .
	" colwidth=" . $cwfirst . " rowheight=" . $rowheight . 
	" margin=" . $margin;
    
    $vtlcell_opts = "fittextline={font=" . $regularfont . 
	" fontsize={capheight=" . $capheight . "} position={center}" .
	" orientate=west} " .
	" colwidth=" . $cwfirst . " rowheight=" . $rowheight . 
	" margin=" . $margin;
		      
	
    /* --------------------------------------------------------------
     * In the first four rows, add the text line cells containing the
     * headings
     * --------------------------------------------------------------
     */
    $row = 1;
  
    /* Add the "Plane Table" heading spanning all columns */
    $tbl = $p->add_table_cell($tbl, $c_table, $row++, "Plane Table", 
	$htlcell_opts . " colspan=" . $maxcols);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    /* Add the column number headings */
    for ($i = $c_numbers; $i <= $maxcols; $i++) {
	$tbl = $p->add_table_cell($tbl, $i, $row, $i, 
	    $htlcell_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    }
    
    $row++;
    
    /* Add the "Description" heading */
    $tbl = $p->add_table_cell($tbl, $c_description, $row, "Description", 
	$htlcell_opts);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    /* Add the "Models" heading */
    $tbl = $p->add_table_cell($tbl, $c_models, $row++, "Models", 
	$htlcell_opts . " colspan=2");
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    
    /* ---------------------------------------------------------------------
     * In the first column add a heading as text line cell orientated to the
     * west and spanning as much rows as planes are available
     * ---------------------------------------------------------------------
     */
    $nplanes = count($planes) * count($planes[0]);
    
    $tbl = $p->add_table_cell($tbl, $c_category, $row, 
	"Paper Planes Standard Collection", 
	$vtlcell_opts . " rowspan=" . $nplanes);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
	
    /* -------------------------------------------------------------------
     * In the second column add the individual model headings as text line
     * cells orientated to the west and spanning three rows each
     * -------------------------------------------------------------------
     */
    $row = $r_model;
	
    for ($i = 1; $i <= count($models); $i++) {
	$tbl = $p->add_table_cell($tbl, $c_model, $row, $models[$i-1],
	    $vtlcell_opts . " rowspan=3");
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	$row += 3;           
    }
    
	   
    /* --------------------------------------------------------------------
     * In the third column add the individual planes as Textflow cells with
     * all cells having the same height 
     * --------------------------------------------------------------------
     */
    $row = $r_plane;
    
    /* Prepare the option list for adding a Textflow.
     * "leading" specifies the distance between to text lines.
     */
    $tf_opts = "font=" . $regularfont . 
	" fontsize={capheight=" . $capheight . "} leading=" . $leading;
    
    for ($i = 0; $i < count($planes); $i++) {
	for ($j = 0; $j < count($planes[$i]); $j++) {
	    /* Add the Textflow */
	    $tf = $p->add_textflow(0, $planes[$i][$j], $tf_opts);
	    if ($tf == 0)
		throw new Exception("Error: " . $p->get_errmsg());
	    
	    /* Prepare the option list for adding the Textflow cell 
	     * To avoid any space from the top add the Textflow cell using 
	     * "fittextflow={firstlinedist=capheight}".
	     * "verticalalign=top" will place the text at the top of the 
	     * cell.
	     * "rowheight" defines the row height, and "colwidth" specifies
	     * the column width.
	     * "margin" adds some empty space between the text and the cell 
	     * borders.
	     * "rowscalegroup=myscaling" assigns the group "myscaling" to
	     * each of the cells to ensure that all cells will be scaled to
	     * have the same height. 
	     */
	    $tfcell_opts = 
		"textflow=" . $tf . 
		" fittextflow={firstlinedist=capheight verticalalign=top}" . 
		" rowheight=" . $rowheight .
		" colwidth=" . $cwplane .
		" margin=" . $margin .
		" rowscalegroup=myscaling";
    
	    /* Add the table cell */
	    $tbl = $p->add_table_cell($tbl, $c_plane, $row++, "",
		$tfcell_opts);
    
	    if ($tbl == 0)
		throw new Exception("Error: " . $p->get_errmsg());
	}
    }
    
    
    /* -----------------------------------------------------------------
     * Add the descriptions as Textflow cells orientated to the west and
     * spanning three rows each
     * -----------------------------------------------------------------
     */
    $row = $r_description;
    
    /* Prepare the option list for adding a Textflow. For a description see 
     * above.
     */
    $tf_opts = "font=" . $regularfont . 
	" fontsize={capheight=" . $capheight . "} leading=" . $leading;
    
    for ($i = 0; $i < count($descriptions); $i++) {
	/* Add the Textflow */
	$tf = $p->add_textflow(0, $descriptions[$i], $tf_opts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	    
	/* Prepare the option list for adding the Textflow cell. 
	 * "orientate=west" orientates the Textflow to the west.
	 * "rowheight" and "colwidth" specify the height of the row and the 
	 * width of the column.
	 * "margin" adds some empty space between the text and the cell 
	 * borders.
	 * With "rowspan=3" the cell will span three rows.
	 */
	$tfcell_opts = 
	    "textflow=" . $tf . 
	    " fittextflow={firstlinedist=$capheight verticalalign=top" .
	    " orientate=west}" . 
	    " rowheight=" . $rowheight .
	    " colwidth=" . $cwdescription . 
	    " margin=" . $margin .
	    " rowspan=3";
    
	/* Add the table cell */
	$tbl = $p->add_table_cell($tbl, $c_description, $row, "", $tfcell_opts);
	if ($tbl == 0)
		throw new Exception("Error: " . $p->get_errmsg());
	
	$row += 3;
    }
     
		
    /* ------------------------------------
     * Place the table on one or more pages
     * ------------------------------------
     */

    /* Prepare the option list for fitting the table.
     * "header=4" will repeat the first four rows at the beginning of
     * each new page. The "stroke" option will stroke lines with two 
     * different line widths. The table frame is stroked with a line width 
     * of 1, all other lines are stroked with a line width of 0.3.
     */
    $fittab_opts = "header=3 stroke={" .
	"{line=frame linewidth=1} {line=other linewidth=0.3} }";
			 
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
    header("Content-Disposition: inline; filename=table_rotated_text.pdf");
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

