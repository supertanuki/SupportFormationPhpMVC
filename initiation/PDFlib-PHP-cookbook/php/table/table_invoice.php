<?php
/* $Id: table_invoice.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Table invoice:
 * Create an invoice using the table feature
 * 
 * Create an invoice by importing a background PDF page with the company's
 * stationery header. Output the customer's address data as well as a table 
 * listing all items ordered including their quantities and prices. If the table
 * spans several pages output a subtotal after each table instance. To retrieve
 * the last row having been output in a table instance use info_table() with the
 * "lastbodyrow" option. In the last table row, output the total at the end of
 * the table. Output some final text directly after the table. Use info_table()
 * with the "height" option to retrieve the exact end position of the table. 
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Invoice";


$infile = "stationery.pdf";

$tf=0; $tbl=0;

$sum = 0; $total = 0; $subtotal = 0; $tabheight = 0;

$pagewidth = 595; $pageheight = 842;
$fontsize = 12;
$capheight = 8.5;
$rowheight = 16;
$margin = 4;
$leading = "120%";
$ystart = $pageheight - 170;
$yoffset = 15;
$ycontinued = 40;
$nfooters = 1; $nheaders = 1;

/* The table coordinates are fixed; only the height of the table may differ
 */
$llx = 55; $urx = 505; $lly = 80;
   
/* The widths of the individual columns is fixed */
$maxcol = 5;

$c1 = 30; $c2 = 200; $c3 = 70; $c4 = 70; $c5 = 80;

/* Get the current date */
setlocale(LC_TIME, "C");
date_default_timezone_set("Europe/Berlin");
$fulldate = date("F j, Y");

/* Text to output after the table */
$closingtext =
    "Terms of payment: 30 days net. " .
    "90 days warranty starting at the day of sale. " .
    "This warranty covers defects in workmanship only. " .
    "Kraxi Systems, Inc. will, at its option, repair or replace the " .
    "product under the warranty. This warranty is not transferable. " .
    "No returns or exchanges will be accepted for wet products.";

$items = array(
/*     Description,        Quantity, Price */
    array( "Long Distance Glider; price includes volume discount of 20% for " .
      "more than 10 items ordered",
				"11",    "15.96"),
    array( "Turbo Flyer",        "5",     "39.95"),
    array( "Giga Trash",         "1",     "179.95"),
    array( "Bare Bone Kit",      "3",     "49.95"),
    array( "Nitty Gritty",       "10",    "19.95"),
    array( "Pretty Dark Flyer",  "1",     "74.95"),
    array( "Free Gift",          "2",     "29.95"),
    array( "Giant Wing",         "2",     "29.95"),
    array( "Cone Head Rocket; price includes volume discount of 30% for " .
      "more than 20 items ordered",
				  "25",     "6.97"),

    array( "Super Dart",         "2",      "29.95"),
    array( "German Bi-Plane",    "6",      "9.95"),
    array( "Turbo Glider; price includes volume discount of 20% for " .
      "more than 10 items ordered",
				 "11",     "15.96"),
    array( "Red Baron",           "5",      "39.95"),
    array( "Mega Rocket",         "1",      "179.95"),
    array( "Kit the Kat",         "3",      "49.95"),
    array( "Red Wing",            "10",     "19.95"),
    array( "Dark Rider",          "1",      "74.95"),
    array( "Speedy Gift",         "2",      "29.95"),
    array( "Giant Bingo",         "2",      "29.95"),
    array( "Ready Rocket; price includes volume discount of 30% for " .
      "more than 20 items ordered",
				 "25",     "6.97"),
);

$address = array(
    "John Q. Doe", "255 Customer Lane", "Suite B",
    "12345 User Town", "Everland"
);

/* Used to format the prices to a maximum of to fraction digits */

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Open the PDF document */
    $stationery = $p->open_pdi_document($infile, "");
    if ($stationery == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page of the PDF */
    $page = $p->open_pdi_page($stationery, 1, "");
    if ($page == 0)
	throw new Exception("Error: " . $p->get_errmsg());
  
    /* Load the bold and regular styles of a font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $regularfont = $p->load_font("Helvetica", "unicode", "");
    if ($regularfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start the output page */
    $p->begin_page_ext($pagewidth, $pageheight, "");

    /* Fit and close the imported PDF page */
    $p->fit_pdi_page($page, 0, 0, "");
    $p->close_pdi_page($page);

    /* Output the customer's address */
    $y = $ystart;
    
    $p->setfont($regularfont, $fontsize);

    for ($i = 0; $i < count($address); $i++) {
	$p->fit_textline($address[$i], $llx, $y, "");
	$y -= $yoffset;
    }
    
    /* Print the header and the date */
    $y -= 3 * $yoffset;
    
    $p->setfont($boldfont, $fontsize);
    
    $p->fit_textline("INVOICE", $llx, $y, "position {left top}");
    $p->fit_textline($fulldate, $urx, $y, "position {right top}");
    
    $y -= 3 * $yoffset;
    
    /* Initialize the maximum number of fraction digits to two */
    //form.setMaximumFractionDigits(2);
    //form.setMinimumFractionDigits(2);


    /* ----------------------------------------------------
     * Add the first table row containing the heading cells
     * ----------------------------------------------------
     */
    
    /* Prepare the general option list for adding text line cells of the 
     * table header:
     * Define a fixed row height, and the position of the text line to be on
     * the top left with a margin of 4, for example. 
     * The text will be aligned on the top right or on the top left, 
     * respectively.
     * For an exact vertical alignment of the text line and the Textflow 
     * which will be added later note the following:
     * The height of an uppercase letter is exactly represented by the
     * capheight value of the font. For this reason use the capheight in the
     * font size specification. For example, a capheight of 8.5 will
     * approximately result in a font size of 12 points and (along with
     * "margin=4"), will sum up to an overall height of 16 points. 
    */
    $head_opts_right = "fittextline={position={right top} " .
	" font=" . $boldfont . " fontsize={capheight=" . $capheight . "}} " .
	" rowheight=" . $rowheight . " margin=" . $margin;
    
    $head_opts_left = "fittextline={position={left top} " .
	" font=" . $boldfont . " fontsize={capheight=" . $capheight . "}} " .
	" rowheight=" . $rowheight . " margin=" . $margin;
	 
    $col = 1; $row = 1;
	  
    /* Add each heading cell with the option list defined above; 
     * in addition, supply a fixed column width
     */
    $tbl = $p->add_table_cell($tbl, $col++, $row, "ITEM", 
	$head_opts_right . " colwidth=" . $c1);
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    $tbl = $p->add_table_cell($tbl, $col++, $row, "DESCRIPTION", 
	$head_opts_left . " colwidth=" . $c1);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
    
    $tbl = $p->add_table_cell($tbl, $col++, $row, "QUANTITY", 
	$head_opts_right . " colwidth=" . $c3);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
    $tbl = $p->add_table_cell($tbl, $col++, $row, "PRICE", 
	$head_opts_right . " colwidth=" . $c4);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
    $tbl = $p->add_table_cell($tbl, $col++, $row, "SUM", 
	    $head_opts_right . " colwidth=" . $c5);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
    $row++;
    
    
    /* -------------------------------------------
     * Add the body cells in subsequent table rows
     * -------------------------------------------
     */
    
    /* Prepare the general option list for adding text line cells of the 
     * table body; it is similar to the option list defined for header cells
     * but the font is set to a regular font instead
     */
    $body_opts = "fittextline={position={right top} " .
	" font=" . $regularfont . 
	" fontsize={capheight=" . $capheight . "}} " .
	" rowheight=" . $rowheight . " margin=" . $margin;
    
    for ($itemno = 1; $itemno <= count($items); $itemno++, $row++) {
	$col = 1;
	
	/* ---------------------------------------------------------------
	 * Add the text line cell containing the Item in the first column,
	 * with the options defined for table body cells above
	 * ---------------------------------------------------------------
	 */
	$tbl = $p->add_table_cell($tbl, $col++, $row, $itemno, $body_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	
	/* --------------------------------------------------------------
	 * Add the Textflow cell containing the Description in the second
	 * column
	 * --------------------------------------------------------------
	 */
		  
	/* Prepare the option list for adding the Textflow.
	 * For an exact vertical alignment of the Textflow and the text
	 * lines added as well note the following:
	 * The height of an uppercase letter is exactly represented by the
	 * capheight value of the font. For this reason use the capheight in
	 * the font size specification. For example, a capheight of 8.5 will
	 * approximately result in a font size of 12 points and (along with
	 * "margin=4"), will sum up to an overall height of 16 points. 
	 */
	$tf_opts = "font=" . $regularfont . 
	    " fontsize={capheight=" . $capheight . "} leading=" . $leading; 
	
	/* Prepare the option list for adding the Textflow cell 
	 * 
	 * The first line of the Textflow should be aligned with the
	 * baseline of the text lines. At the same time, the text lines 
	 * should have the same distance from the top cell border as the 
	 * Textflow. To avoid any space from the top add the Textflow cell
	 * using "fittextflow={firstlinedist=capheight}". Then add a margin
	 * of 4 points, the same as for the text lines.
	 */
	$bodytf_opts = "fittextflow={firstlinedist=capheight}" . 
	    " colwidth=" . $c2 . " margin=" . $margin;
	
	/* Add the Textflow with the options defined above */
	$tf = $p->add_textflow(0, $items[$itemno-1][0], $tf_opts);
	
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	    
	/* Add the Textflow table cell with the options defined above */
	$tbl = $p->add_table_cell($tbl, $col++, $row, "", 
	    $bodytf_opts . " textflow=" . $tf);
	    
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	$tf = 0;
	
		    
	/* -----------------------------------------------------------
	 * Add the text line cell containing the Quantity in the third
	 * column, with the options defined for table body cells above
	 * -----------------------------------------------------------
	 */
	$tbl = $p->add_table_cell($tbl, $col++, $row, $items[$itemno-1][1], 
	    $body_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	
	/* -----------------------------------------------------------
	 * Add the text line cell containing the Price in the third
	 * column, with the options defined for table body cells above
	 * -----------------------------------------------------------
	 */
	$tbl = $p->add_table_cell($tbl, $col++, $row, $items[$itemno-1][2], 
	    $body_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	
	/* ---------------------------------------------------------------
	 * Add the text line cell containing the sum with the options 
	 * defined for table body cells above. Format them to a maximum of 
	 * two fraction digits.
	 * ---------------------------------------------------------------
	 */
	$sum = $items[$itemno-1][1] * $items[$itemno-1][2];
	
	$roundedValue = sprintf("%.2f", $sum);
		       
	$tbl = $p->add_table_cell($tbl, $col, $row, 
			       $roundedValue, $body_opts);
	
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	    
	/* Calculate the overall sum */
	$total += $sum;
    } /* for */
    
    /* Add an empty footer row containing a matchbox called "subtotal".
     * It will be filled with the subtotal or total later. The matchbox 
     * starts in the column before last and spans two columns.
     */
    $footer_opts = 
	"rowheight=" . $rowheight . " colspan=2 margin =" . $margin .
	" matchbox={name=subtotal}";
    
    $tbl = $p->add_table_cell($tbl, $maxcol-1, $row, "", $footer_opts . "");
	      
    if ($tbl == 0)
	throw new Exception("Error adding cell: " . $p->get_errmsg());

	
    /* ------------------------------------
     * Place the table on one or more pages
     * ------------------------------------
     */

    /* Loop until all of the table is placed; create new pages as long as
     * more table instances need to be placed
     */
    do {
	/* The first row is the header row which will be repeated on each
	 * new page. The last row is the footer and will be repeated on each
	 * new page. The header row is filled with a light blue, and the
	 * footer row is filled with a light orange. Each odd row is filled
	 * with a light gray.
	 */
	$fit_opts = 
	    "header=" . $nheaders . " footer=" . $nfooters . 
	    " fill={{area=rowodd fillcolor={gray 0.9}} " .
	    "{area=header fillcolor={rgb 0.90 0.90 0.98}} " .
	    "{area=footer fillcolor={rgb 0.98 0.92 0.84}}}";

	/* Place the table instance */
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $y, $fit_opts);
	
	/* An error occurred or the table's fitbox is too small to keep any
	 * contents 
	 */
	if ($result == "_error" || $result == "_boxempty")
	    throw new Exception ("Couldn't place table : " .
		$p->get_errmsg());
	
	/* If all rows have been placed output the total in the matchbox
	 * defined for the footer row. Since the matchbox cannot be supplied
	 * directly to fit_textline(), we retrieve the matchbox coordinates
	 * and fit the text accordingly.
	 */
	if ($result != "_boxfull") {
	    /* Format the total to a maximum of two fraction digits */
	    $roundedValue = sprintf("%.2f", $total);
	    $contents = "total:   " . $roundedValue;
	    
	    /* Retrieve the coordinates of the third (upper right) corner of
	     * the "subtotal" matchbox. The parameter "1" indicates the 
	     * first instance of the matchbox.
	     */
	    $x3 = 0; $y3 = 0;
			 
	    if ($p->info_matchbox("subtotal", 1, "exists") == 1) {
		$x3 = $p->info_matchbox("subtotal", 1, "x3");
		$y3 = $p->info_matchbox("subtotal", 1, "y3");
	    }
	    else {
		throw new Exception("Error: " . $p->get_errmsg());
	    }
	    
	    /* Start the text line at the corner coordinates retrieved
	     * (x2, y2) with a small margin. Right-align the text.
	     */
	    $p->setfont($boldfont, $fontsize);
	    $p->fit_textline($contents, $x3 - $margin, $y3 - $margin,
		"position={right top}");
	}
	
	/* Print the subtotal for all rows in the table instance on the
	 * current page below the last table column before we place the 
	 * remaining rows on the next page 
	 */
	else if ($result == "_boxfull") {
	    /* Get the last body row output in the table instance */
	    $lastrow = $p->info_table($tbl, "lastbodyrow");
	    
	    /* Calculate the subtotal */
	    $subtotal = 0;
	    for ($i = 0 ; $i < $lastrow - $nfooters; $i++) {
		$subtotal += $items[$i][1] * $items[$i][2];
	    }
	   
	    /* Output the subtotal in the matchbox defined for the footer 
	     * row. Since the matchbox cannot be directly referenced we 
	     * retrieve the matchbox coordinates and fit the text
	     *  accordingly.
	     */
	    
	    /* Format the subtotal to a maximum of two fraction digits*/
	    $roundedValue = sprintf("%.2f", $subtotal);
	    
	    $contents = "subtotal:   " . $roundedValue;
	    
	    /* Retrieve the coordinates of the third (upper right) corner of
	     * the "subtotal" matchbox. The parameter "1" indicates the 
	     * first instance of the matchbox.
	     */
	    $x3 = 0; $y3 = 0;
	    
	    if ($p->info_matchbox("subtotal", 1, "exists") == 1) {
		$x3 = $p->info_matchbox("subtotal", 1, "x3");
		$y3 = $p->info_matchbox("subtotal", 1, "y3");
	    }
	    else {
		throw new Exception("Error: " . $p->get_errmsg());
	    }
	    
	    /* Start the text line at the corner coordinates retrieved in
	     * (x3, y3) with a small margin. Right-align the text.
	     */
	    $p->setfont($boldfont, $fontsize);
	    $p->fit_textline($contents, $x3 - $margin, $y3 - $margin,
		"position={right top}");
    
	    /* Output the "Continued" remark */               
	    $p->setfont($regularfont, $fontsize);
	    $p->fit_textline("-- Continued --", $urx, $ycontinued, 
		"position {right top}");
	    
	    $p->end_page_ext("");
	    $p->begin_page_ext($pagewidth, $pageheight, "");
	    $y = $ystart;
	}
    } while ($result == "_boxfull");
    
    
    /* -----------------------------------------------
     * Place the closing text directly after the table
     * -----------------------------------------------
     */
    
    /* Get the table height of the current table instance */
    $tabheight = $p->info_table($tbl, "height");
    
    $y = $y - (int) $tabheight - $yoffset;
	    
    /* Add the closing Textflow to be placed after the table */
    $tf_opts = "font=" . $regularfont . " fontsize=" . $fontsize .
	" leading=" . $leading . " alignment=justify";
    
    $tf = $p->add_textflow(0, $closingtext, $tf_opts);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Loop until all text has been fit which is indicated by the "_stop"
     * return value of fit_textflow()
     */
    do {
	/* Place the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $y, "");
	
	if ($result == "_error")
	    throw new Exception ("Couldn't place table : " .
		$p->get_errmsg());
	
	if ($result == "_boxfull" || $result == "_boxempty") {
	    $p->setfont($regularfont, $fontsize);
	    $p->fit_textline("-- Continued --", $urx, $ycontinued,
		"position {right top}");
	    
	    $p->end_page_ext("");
	    $p->begin_page_ext($pagewidth, $pageheight, "");
	    $y = $ystart;
	}
    } while (!$result == "_stop");
    
    $p->end_page_ext("");
   
    $p->end_document("");
    $p->close_pdi_document($stationery);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=table_invoice.php.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() .
        ": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;
?>

