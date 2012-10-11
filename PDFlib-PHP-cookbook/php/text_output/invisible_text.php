<?php
/* $Id: invisible_text.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Invisible text:
 * Output invisible text on top of an image 
 *  
 * Place an image and create invisible text on top of it with the
 * "textrendering" parameter set to 3.  The most common scenario for this is
 * "scanned page with invisible OCR text (which has been retrieved from the
 * scanned page in an earlier step with OCR).

 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Invisible Text";

$imagefile = "multi_page.tif";

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
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the  image */
    $image = $p->load_image("auto", $imagefile, "page=1");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Place the image */
    $p->fit_image($image, 0, 0, "boxsize={595 842} fitmethod=meet");
    $p->close_image($image);
    
    /* Save the current graphics state */
    $p->save();
    
    /* Set the text rendering mode to "invisible text" */
    $p->set_value("textrendering", 3);
	  
    /* Output the text invisibly on top of the image with the rendering
     * mode set to "invisible text" above. (The text can be output
     * on top of the image or below the image.) The following text 
     * resembles text retrieved from the scanned page via OCR.
     */
    $p->setfont($font, 21);
    $p->show_xy("PDFlib GmbH München, Germany", 130, 750);
    $p->show_xy("www.pdflib.com", 215, 710);
    
    $p->setfont($font, 28);
    $p->show_xy("Tutorial for", 120, 480);
    $p->show_xy("PDFlib, PDI, and PPS", 120, 445);
    
    $p->setfont($font, 21);
    $p->show_xy("General Edition for", 195, 125);
    $p->show_xy("Cobol, C, C++, Java, Perl", 165, 95);
    $p->show_xy("PHP, Phyton, RPG, Ruby, and Tcl", 140, 70);
    
    /* Restore the current graphics state */
    $p->restore();
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=invisble_text.pdf");
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
