<?php
/* $Id: spread_text_over_cells.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Spread text over cells:
 * Spread a Textflow over several cells.
 * 
 * Create a simple table with a Textflow which is spread over several cells. Use
 * the "continuetextflow" option of add_table_cell() for each Textflow cell the
 * Textflow is to be spread over. 
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Spread Text over Cells";

$tf = 0; $tbl = 0; 
$rowheight = 60;
$imagefile = "kraxi_logo.tif";

/* Page width and height */
$pagewidth = 595; $pageheight = 421;

/* Define the column widths of the first and the second column */
$c1 = 80; $c2 = 120;

/* Define the lower left and upper right corners of the table instance.
 * The table width of 200 matches the sum of the widths of the two table
 * columns 80 + 120.
 */
$llx = 100; $lly = 100; $urx = 300; $ury = 350;

$tf_text =
    "Our paper planes are the ideal way of passing the time. We " .
    "offer revolution&shy;ary new develop&shy;ments of the " .
    "trad&shy;itional common paper planes. If your les&shy;son, " .
    "conference, or lect&shy;ure turn out to be deadly boring, you can " .
    "have a wonderful time with our planes. All our models are folded " .
    "from one pap&shy;er sheet. They are exclu&shy;sively folded without " .
    "the use of any adhes&shy;ive. Se&shy;veral mod&shy;els are " .
    "equip&shy;ped with a folded landing gear.";

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

    /* Load the normal font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load bold font */
    $boldfont = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($boldfont == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start the page */
    $p->begin_page_ext($pagewidth, $pageheight, "");
    
    /* Output some descriptive text */
    $p->setfont($boldfont, 11);
    $p->fit_textline("Use the \"textflow\" option of add_table_cell() ",
	$llx, $ury + 30, "");
    $p->fit_textline("to spread a Textflow over several cells", 
	$llx, $ury + 15, ""); 
    
    
    /* -----------------
     * Add an image cell
     * -----------------
     * 
     * The image is placed in a cell starting in column 1 row 2. The column
     * width is 90 points. The cell margins are set to 4 points. 
     */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $optlist = "image=" . $image . " colwidth=" . $c1 . " margin=4";

    $tbl = $p->add_table_cell($tbl, 1, 2, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* Add the Textflow */
    $optlist = "font=" . $font . " fontsize=8 leading=110% charref"; 
    
    $tf = $p->add_textflow(0, $tf_text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Prepare the option list for the Textflow cell
     * 
     * Use the "continuetextflow" option to continue the Textflow in further
     * cells.
     * To avoid any space from the top add the Textflow cell use 
     * "fittextflow={firstlinedist=capheight}". Then add a margin of 4 
     * points.
     */
    $optlist = "textflow=" . $tf . " fittextflow={firstlinedist=capheight} " .
	"colwidth=" . $c2 . " rowheight=" . $rowheight . 
	" margin=4 continuetextflow";
    
    /* Add the Textflow table cell in column 1 row 1 */
    $tbl = $p->add_table_cell($tbl, 1, 1, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Continue the Textflow table cell in column 2 row 2 */
    $tbl = $p->add_table_cell($tbl, 2, 2, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Continue the Textflow table cell in column 1 row 3 */
    $tbl = $p->add_table_cell($tbl, 1, 3, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Colorize the table cell in column 2 row 1 and column 2 row 3 using a
     * matchbox
     */
    $optlist = "matchbox={fillcolor={rgb 0.8 0.8 0.87}}";
    $tbl = $p->add_table_cell($tbl, 2, 1, "", $optlist);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
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
	if ($result ==  "_error")
	    throw new Exception("Error: " . $p->get_errmsg());
	else {
		/* Other return values require dedicated code to deal with */
	}
    }

    /* Delete the table handle. This will also delete any Textflow handles
     * used in the table
     */
    $p->delete_table($tbl, "");
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=spread_text_over_cells.pdf");
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

