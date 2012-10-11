<?php
/* $Id: tabstops_in_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Tab stops in Text:
 * Create a simple multi-column layout using tab stops
 * 
 * Create some lines of text which are divided into columns using tab stops.
 * For a more complex table layout, use the table feature.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Tabstops in Text";

$tf = 0;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Create an A4 Landscape page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Text containing tab stops to create a multi-column layout */
    $text = 
	"ITEM\tDESCRIPTION\tQUANTITY\tPRICE\tAMOUNT\n" .
	"1\tSuper Kite\t2\t20.00\t40.00\n" .
	"2\tTurbo Flyer\t5\t40.00\t200.00\n" .
	"3\tGiga Trash\t1\t180.00\t180.00\n\n" .
	"\t\t\tTOTAL\t420.00";
 
    /* Assemble option list. Use the "ruler" option to define the absolute
     * positions of four tab stops. With the "tabalignment" option define the
     * alignment of the four tab stops at their positions. Each tab stop is
     * defined by its position in the list. Use the "hortabmethod=ruler"
     * option to make the the tabs being considered. 
     */
    $optlist =
	"ruler ={60 200 300 400} tabalignment={left center right right} " .
	"hortabmethod=ruler leading=120% fontname=Helvetica fontsize=12 " .
	"encoding=unicode";
    
    /* Add the Textflow with the option list defined above */
    $tf = $p->add_textflow($tf, $text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the color for the border */
    $p->setcolor("stroke", "rgb", 0.85, 0.83, 0.85, 0);
    
    /* Place the Textflow in the fitbox */
    $result = $p->fit_textflow($tf, 100, 250, 500, 340, "showborder");
    
    if ($result != ("_stop"))
    {
	/* Check for errors */
    }
    $p->delete_textflow($tf);
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=tabstops_in_text.pdf");
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
