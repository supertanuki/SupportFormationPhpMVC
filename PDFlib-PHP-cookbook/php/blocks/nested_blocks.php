<?php
/* $Id: nested_blocks.php,v 1.2 2012/05/03 14:00:41 stm Exp $
 * Nested_blocks:
 * Nested Block processing is used to implement both imposition and 
 * personalization with Blocks:
 *
 * - The first-level Block container page contains several large PDF Blocks
 *   which indicate the major areas on the paper to be printed on. The
 *   arrangement of PDF Blocks reflects the intended postprocessing of the
 *   paper (e.g. folding).
 * - Each of the first-level PDF Blocks is then filled with a second-level
 *   container PDF page which contains Text, Image, or PDF Blocks to be filled
 *   with variable text for personalization.
 *
 * Required software: PPS 8
 * Required data:
 * - first-level Block container with PDF Blocks named "Block_<nr>"
 * - second-level Block container with arbitrary Blocks
 * - variable data for filling second-level Blocks (provided in an inline array)
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Nested Blocks";

$infilename1 = "A3_imposition_2x2.pdf";
$infilename2 = "businesscard_blocks.pdf";


/* Names of the blocks contained in the imported page */
$blocknames = array( "name", "business_title",
    "business_address_line1", "business_address_city",
    "business_telephone_voice", "business_telephone_fax",
    "business_email", "business_homepage" );

$nblocks = count($blocknames); // number of blocks to be filled

/* Data related to various persons used for personalization */
$persons = array(
    array( "Victor Kraxi", "Chief Paper Officer", "17, Aviation Road",
	"Paperfield", "7079-4301", "7079-4391", "victor@kraxi.com",
	"www.kraxi.com" ),
    array( "Paula Kraxi", "Chief Paper Pilot", "17, Aviation Road",
	"Paperfield", "7079-4302", "7079-4392", "paula@kraxi.com",
	"www.kraxi.com" ),
    array( "Serge Kraxi", "Chief Paper Folder", "17, Aviation Road",
	"Paperfield", "7079-4303", "7079-4393", "serge@kraxi.com",
	"www.kraxi.com" ),
    array( "Lena Kraxi", "Chief Financial Officer", "17, Aviation Road",
	"Paperfield", "7079-4304", "7079-4394", "lena@kraxi.com",
	"www.kraxi.com" ),
    array( "Dana Kraxi", "Auxiliary Paper Folder", "17, Aviation Road",
	"Paperfield", "7079-4305", "7079-4395", "dana@kraxi.com",
	"www.kraxi.com" ),
    array( "Anna Kraxi", "Accounting Assistant", "17, Aviation Road",
	"Paperfield", "7079-4306", "7079-4396", "anna@kraxi.com",
	"www.kraxi.com" ) );

$recordcount = count($persons); // number of personalization records

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Open the first-level Block container with imposition Blocks */
    $indoc1 = $p->open_pdi_document($infilename1, "");
    if ($indoc1 == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    /* Open the first page and prepare the page boxes for cloning */
    $inpage1 = $p->open_pdi_page($indoc1, 1, "cloneboxes");
    if ($inpage1 == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    /* Query the number of Blocks to keep the code flexible */
    $pagespersheet = (int) $p->pcos_get_number($indoc1,
	    "length:pages[0]/blocks");

    /*
     * Open the second-level Block container with personalization Blocks
     */
    $indoc2 = $p->open_pdi_document($infilename2, "");
    if ($indoc1 == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    /* Open the first page */
    $inpage2 = $p->open_pdi_page($indoc2, 1, "");
    if ($inpage2 == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    for ($record = 0; $record < $recordcount; $record++) {
	/* Start a new output page if required */
	if ($record % $pagespersheet == 0) {
	    /* The dummy size will be adjusted with "cloneboxes" */
	    $p->begin_page_ext(10, 10, "");

	    /*
	     * Place the first-level Block container page on the output
	     * page
	     */
	    $p->fit_pdi_page($inpage1, 0, 0, "cloneboxes");
	}

	/*
	 * Imposition: fill first-level PDF Blocks with second-level
	 * container page
	 */
	if ($p->fill_pdfblock($inpage1, "Block_" . $record % $pagespersheet,
		$inpage2, "") == 0) {
	    print("Warning: " . $p->get_errmsg());
	    continue;
	}

	/* 
	 * Personalization: fill second-level Blocks with variable data
	 **/
	for ($block = 0; $block < $nblocks; $block++) {

	    if ($p->fill_textblock($inpage2, $blocknames[$block],
		    $persons[$record][$block],
		    "encoding=unicode embedding") == 0) {
		print("Warning: " . $p->get_errmsg());
	    }
	}

	/*
	 * Finish the page if no more space, or no more records
	 * available
	 */
	if ($record % $pagespersheet == $pagespersheet - 1
		|| $record == $recordcount - 1) {
	    $p->end_page_ext("");
	}
    }

    $p->close_pdi_page($inpage1);
    $p->close_pdi_page($inpage2);

    $p->end_document("");
    $p->close_pdi_document($indoc1);
    $p->close_pdi_document($indoc2);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=nested_blocks.pdf");
    print $buf;


}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in starter_block sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

?>
