<?php
/* $Id: text_as_clipping_path.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Text as clipping path:
 * Output text filled with an image 
 *  
 * Use various text rendering modes to place an image clipped by some text
 * outlines.
 * Use the "textrendering=7" parameter to add text to the clipping path. Then
 * place an image clipped by that path.
 * Use "textrendering=5" to stroke text and add it to the clipping path. Then
 * place an image clipped by that path.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Text as Clipping Path";

$imagefile = "nesrin.jpg";

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

    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start an A4 landscape page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
    
    /* Save the current graphics state */
    $p->save();
    
    /* Set the text rendering mode to "add text to the clipping path" */
    $p->set_value("textrendering", 7);
	   
    /* Output some text. The text outlines will not actually be output but 
     * be added to the clipping path instead. Note that with a text
     * rendering mode of 7, just the simple text output functions can be
     * used. 
     */ 
    $p->setfont($font, 250);
    $p->set_text_pos(30, 250);
    $p->show("Hello!");
    
    /* Place the image */
    $p->fit_image($image, 0.0, 0.0, "boxsize={842 595} fitmethod=entire");
	    
     
    /* Restore the current graphics state */
    $p->restore();
    
    /* Save the current graphics state */
    $p->save();
    
    
    /* Set the text rendering mode to "stroke text and add it to the
     * clipping path"
     */
    $p->set_value("textrendering", 5);
	   
    /* Output some text. The text outlines will not actually be output but 
     * be added to the clipping path instead. Note that with a text
     * rendering mode of 5, just the simple text output functions can be
     * used. 
     */ 
    $p->setfont($font, 200);
    
    $p->setcolor("stroke", "rgb", 0.1, 0.1, 0.1, 0);
    $p->setlinewidth(8);
    
    $p->set_text_pos(100, 50);
    $p->show("Hello!");
    
    /* Place the image */
    $p->fit_image($image, 0.0, 0.0, "boxsize={842 595} fitmethod=entire");
	    
    $p->close_image($image);
	  
    /* Restore the current graphics state */
    $p->restore();
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=text_as_clipping_path.pdf");
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
