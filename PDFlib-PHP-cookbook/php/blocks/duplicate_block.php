<?php
/* $Id: duplicate_block.php,v 1.2 2012/05/03 14:00:41 stm Exp $
 * Duplicate block:
 * Duplicate a PDFlib block to any number of pages
 * 
 * Import a PDF page containing a block and place it with fit_pdi_page() on
 * the first page of the output document. Fill the contained text block with the 
 * number of the current page.
 * On subsequent pages, place the imported PDF page with the "blind" option 
 * supplied so that the actual page contents will not be placed, but the block
 * can be filled nevertheless. Fill the text block with the number of the
 * respective page.
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Duplicate Block";

$infile = "announcement_blocks.pdf";


/* Name of the block contained on the imported page */
$blockname = "page"; 
 
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

    /* Open a PDF containing a block to store the page number */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page of the imported document */
    $inpage = $p->open_pdi_page($indoc, 1, "");
    if ($inpage == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Get the width and height of the imported page */
    $width = $p->pcos_get_number($indoc, "pages[0]/width");
    $height = $p->pcos_get_number($indoc, "pages[0]/height");
    
    /* Start the output page with the size given by the imported page */ 
    $p->begin_page_ext($width, $height, "");

    /* Place the imported page */
    $p->fit_pdi_page($inpage, 0, 0, "");
    
    /* Fill the text block with the number of the first page */
    $p->fill_textblock($inpage, $blockname, "1",
	"encoding=unicode");
    
    /* Finish page */
    $p->end_page_ext("");
    
	    
    /* -----------------------------------------------
     * Duplicate the block on several subsequent pages
     * -----------------------------------------------
     */
    for ($i = 2; $i <= 5; $i++)
    {
	/* Start the output page with the size given by the imported page */ 
	$p->begin_page_ext($width, $height, "");
	
	/* Place the imported page on the output page but supply the "blind"
	 * option to suppress the actual page contents. The block is
	 * available to be filled nevertheless.
	 */
	$p->fit_pdi_page($inpage, 0, 0, "blind");
	
	/* Fill the text block with the number of the current page */
	$p->fill_textblock($inpage, $blockname, $i,
	    "encoding=unicode");
	
	/* Output some descriptive text */
	$optlist = "fontname=Helvetica fontsize=16 encoding=unicode";
	$text = "Page " . $i . ": The page number is filled " .
	    "in the block duplicated from page 1";
	
	$p->fit_textline($text, 30, 400, $optlist);
	
	/* Finish page */
	$p->end_page_ext("");
    }
	   
    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=dublicate_block.pdf");
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
