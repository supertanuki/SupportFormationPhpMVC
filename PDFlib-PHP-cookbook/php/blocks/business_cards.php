<?php
/* $Id: business_cards.php,v 1.3 2012/05/03 14:00:41 stm Exp $
 * Business cards:
 * Output an imported PDF page several times, with its blocks being filled with
 * different personalized data
 * 
 * Import a PDF page representing a business card template. To create business
 * cards for various persons, output the page several times with the contained
 * blocks being filled with personalized data related to the respective person. 
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Business Cards";

$infile = "businesscard_blocks.pdf";

$nblocks = 8; // number of blocks to be filled

/* Names of the blocks contained in the imported page */
$blocknames = array(
    "name", "business_title", "business_address_line1",
    "business_address_city", "business_telephone_voice",
    "business_telephone_fax", "business_email", "business_homepage"
);

$npersons = 3; // number of persons

/* Data related to various persons used for personalization */
$persons = array( 
    array("Victor Kraxi", "Chief Paper Officer", "17, Aviation Road",
     "Paperfield", "7079-4301",
     "7079-4302", "victor@kraxi.com", "www.kraxi.com"),
    array("Paula Kraxi", "Chief Paper Pilot", "17, Aviation Road",
     "Paperfield", "7079-4301",
     "7079-4302", "paula@kraxi.com", "www.kraxi.com"),
    array("Serge Kraxi", "Chief Paper Folder", "17, Aviation Road",
     "Paperfield", "7079-4301",
     "7079-4302", "serge@kraxi.com", "www.kraxi.com")
);

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Open a PDF containing blocks */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page */
    $inpage = $p->open_pdi_page($indoc, 1, "");
    if ($inpage == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Get the width and height of the imported page */
    $width = $p->pcos_get_number($indoc, "pages[0]/width");
    $height = $p->pcos_get_number($indoc, "pages[0]/height");
    
    /* Based on the imported page output several pages with the blocks
     * being filled with data related to different persons
     */ 
    for ($i = 0; $i < $npersons; $i++)
    {
	/* Start the output page with the size given by the imported page */ 
	$p->begin_page_ext($width, $height, "");

	/* Place the imported page on the output page */
	$p->fit_pdi_page($inpage, 0, 0, "");

	/* Loop over all blocks on the page */
	for ($j = 0; $j <  $nblocks; $j++) {
	    /* Fill the j-th block with the corresponding entry of the
	     * persons array. The "bordercolor" and "linewidth" options are
	     * only used to illustrate the block rectangles
	     */
	    $optlist = "encoding=unicode " .
		"bordercolor={gray 0.5} linewidth=0.25";

	    if ($p->fill_textblock($inpage, $blocknames[$j], $persons[$i][$j],
		    $optlist) == 0)
		trigger_error("Warning: " . $p->get_errmsg() ."\n");
	}
	$p->end_page_ext("");
    }

    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=business_cards.pdf");
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

