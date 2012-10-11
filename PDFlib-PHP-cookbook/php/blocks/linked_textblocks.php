<?php
/* $Id: linked_textblocks.php,v 1.3 2012/05/03 14:00:41 stm Exp $
 * Linked text blocks:
 * Link multiple Textflow blocks
 * 
 * Import a PDF page representing an advertisement template containing multiple
 * blocks for one product offer each. Several product offers represented in a 
 * Textflow are filled into the appropriate number of blocks depending on the
 * length of the Textflow.
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Linked Text Blocks";
$infile = "advertisement_blocks.pdf";

/* Number of Textflow blocks which can be filled */
$nblocks = 6;

/* Name prefix of the blocks contained on the imported page */
$blockname = "model_"; 

$models = 
    "Our paper planes are the ideal way of passing the time. We offer " .
    "revolutionary new developments of the traditional common paper " .
    "planes. If your lesson, conference, or lecture turn out to be " .
    "deadly boring, you can have a wonderful time with our planes. All " .
    "our models are folded from one paper sheet. They are exclusively " .
    "folded without using any adhesive. Several models are equipped with " .
    "a folded landing gear enabling a safe landing on the intended " .
    "location provided that you have aimed well. Other models are able " .
    "to fly loops or cover long distances. Let them start from a vista " .
    "point in the mountains and see where they touch the ground. Have a " .
    "look at our new paper plane models! With our Long Distance Glider " .
    "you can send all your messages even when sitting in a hall or in " .
    "the cinema pretty near the back. Try our Giant Wing, an " .
    "unbelievable sailplane! It is amazingly robust and can even do " .
    "aerobatics. ";

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
    
    /* Start the output page with the size given by the imported page */ 
    $p->begin_page_ext($width, $height, "");

    /* Place the imported page on the output page */
    $p->fit_pdi_page($inpage, 0, 0, "");
    
    /* Fill one or more blocks with the Textflow */ 
    $text = $models;
    
    $tf = 0;
     
    for ($i = 1; $i <= $nblocks; $i++)
    {
	/* Option list for text blocks; the font and encoding should be 
	 * defined and the Textflow handle supplied. In addition we slightly
	 * rotate the block rectangles by 3 degrees. Other options have
	 * already been set in the properties of the blocks contained in
	 * the input document, such as:
	 * "fitmethod=clip" to clip the text when it doesn't fit completely
	 * into the block while avoiding any text shrinking.
	 * "margin=4" to set some space between the text and the borders of
	 * the block rectangle.
	 * "fontsize=16" for a font size of 16.
	 * "backgroundcolor={gray 0.9}" to colorize the block rectangle with
	 * a light gray.
	 */
	$optlist =
	    "fontname=Helvetica encoding=unicode rotate=3 ".
	    "textflowhandle=" . $tf;
	
	$tf = $p->fill_textblock($inpage, $blockname.$i, $text, $optlist);
	    
	$text = null;
	    
	if ($tf == 0) {
	    trigger_error("Warning: " . $p->get_errmsg() . "\n");
	    break;
	}
	    
	/* Check result of most recent call to fit_textflow() */
	$reason = (int) $p->info_textflow($tf, "returnreason");
	$result = $p->get_parameter("string",  $reason);
	    
	/* End loop if all text was placed */
	if ($result == "_stop")
	{
	    $p->delete_textflow($tf);
	    break;
	}
    }

    $p->end_page_ext("");
       
    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=linked_textblocks.pdf");
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
