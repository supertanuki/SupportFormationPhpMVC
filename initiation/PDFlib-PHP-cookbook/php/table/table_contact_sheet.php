<?php
/* $Id: table_contact_sheet.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Table contact sheet:
 * Create a contact sheet with photos and their labels
 * 
 * Create a table and place a number of photos in it. For each photo, an image
 * is placed in one cell, and a text label is placed in the cell below. 
 *  
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Table Contact Sheet";

$tbl=0; $image=0;
$fontsize = 12;
$pagewidth = 842; $pageheight = 595;

/* table coordinates */
$llx = 50; $urx = $pagewidth - $llx;
$lly = 80; $ury = $pageheight - $lly;

$yheading = $ury + 2 * 15;

/* fixed number of table columns, variable number of rows */
$nocols = 5;

/* column width for all columns */
$cw = 100;

/* row height for image rows and text rows */
$img_rowheight = 100;
$txt_rowheight = 30;

/* margins for image and text cells */
$img_margin = 6; $txt_margin = 6;
  
$imagefiles = array(
    "cambodia_angkor_thom.jpg",
    "cambodia_angkorwat1.jpg",
    "cambodia_angkorwat2.jpg",
    "cambodia_banteay_samre.jpg",
    "cambodia_bayon1.jpg",
    "cambodia_bayon2.jpg",
    "cambodia_bayon3.jpg",
    "cambodia_neak_pean.jpg",
    "cambodia_preah_khan1.jpg",
    "cambodia_preah_khan2.jpg",
    "cambodia_preah_khan3.jpg",
    "cambodia_preah_khan4.jpg",
    "cambodia_pre_rup1.jpg",
    "cambodia_pre_rup2.jpg",
    "cambodia_woman.jpg",
    "cambodia_bayon1.jpg",
    "cambodia_bayon2.jpg",
    "cambodia_bayon3.jpg",
    "cambodia_neak_pean.jpg",
    "cambodia_preah_khan1.jpg",
    "cambodia_preah_khan2.jpg",
    "cambodia_preah_khan3.jpg",
    "cambodia_preah_khan4.jpg",
    "cambodia_pre_rup1.jpg",
    "cambodia_pre_rup2.jpg",
    "cambodia_woman.jpg",
    "cambodia_angkorwat2.jpg",
    "cambodia_banteay_samre.jpg",
    "cambodia_bayon1.jpg",
    "cambodia_bayon2.jpg",
    "cambodia_bayon3.jpg",
    "cambodia_neak_pean.jpg",
    "cambodia_preah_khan1.jpg",
    "cambodia_preah_khan2.jpg",
    "cambodia_preah_khan3.jpg",
    "cambodia_preah_khan4.jpg",
    "cambodia_pre_rup1.jpg"
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
    $p->fit_textline("Contact Sheets", $llx, $yheading, "");

    
    /* ------------------------------------------------------------------
     * For each photo add a cell containing an image and a text line with
     * the image file name
     * ------------------------------------------------------------------
     */
    
    /* Loop over all photos */
    $col = 1; $row = 1;
	   
    for ($i = 0; $i < count($imagefiles); $i++) {
	$image = 0;
	
	/* Load the photo */
	$image = $p->load_image("auto", $imagefiles[$i], "");
	if ($image == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Add the cell containing the image.
	 * 
	 * "fitmethod=auto" scales the image so that it entirely fits into
	 * the cell while preserving its aspect ratio.
	 * "rowjoingroup" keeps image and text together on the same page. 
	 */
	$img_opts = 
	    " image=" . $image . " fitimage={fitmethod=auto}" .
	    " colwidth=" . $cw . " rowheight=" . $img_rowheight .
	    " margin=" . $img_margin . 
	    " rowjoingroup=group" . $i;
	
	$tbl = $p->add_table_cell($tbl, $col, $row, "", $img_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	/* Add the cell containing the text with the image file name.
	 * 
	 * "fitmethod=auto" scales the text so that it entirely fits into
	 * the cell. Note that if this option is not used the cell width 
	 * will be increased until the text fits completely into the cell.
	 * "rowjoingroup" keeps image and text together on the same page.
	 */
	$txt_opts = 
	    "fittextline={font=" . $regularfont . " fitmethod=auto" .
	    " fontsize=9 fillcolor={gray 1} position={center}}" .
	    " colwidth=" . $cw . " rowheight=" . $txt_rowheight .
	    " margin=" . $txt_margin .
	    " rowjoingroup=group" . $i;
	
	$tbl = $p->add_table_cell($tbl, $col, $row + 1, $imagefiles[$i], $txt_opts);
	if ($tbl == 0)
	    throw new Exception("Error adding cell: " . $p->get_errmsg());
	
	if ($col < $nocols) {
	    $col++;
	}
	else { 
	    $col = 1; $row += 2;
	}
    } /* for */
	    
	 
    /* ------------------------------------
     * Place the table on one or more pages
     * ------------------------------------
     */
    
    /* Prepare the option list for fitting the table.
     * The "stroke" option will stroke every vertical line and every
     * second horizontal line in white with a line width of 0.3.
     * The "fill" option fills the complete table with a dark gray.
     */
    $stroke_opts = 
	"stroke={{line=vertother strokecolor={gray 1} linewidth=0.3}";
    
    for ($i = 0, $j = 2; $i < count($imagefiles); $i += $nocols, $j+=2) {
	$stroke_opts .=
	    " {line=hor" . $j . " strokecolor={gray 1} linewidth=0.3}";
    }
    $stroke_opts .= "} ";
    
    $fill_opts = "fill={{area=table fillcolor={rgb 0.1 0.1 0.1}}}";
    
    $fittab_opts = $stroke_opts . $fill_opts; 
	      
    /* Loop until all of the table is placed; create new pages as long as
     * more table instances need to be placed
     */
    do {
	/* Place the table instance */
	$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $fittab_opts);

	if ($result == "_error" || $result == "_boxempty")
	    throw new Exception ("Couldn't place table: " .
		$p->get_errmsg());
	
	/* Start a new page */
	if ($result == "_boxfull") {
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
    header("Content-Disposition: inline; filename=table_contact_sheet.pdf");
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
