<?php
/* $Id: image_as_text_fill_color.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Image as text fill color:
 * Create outline text and fill the interior of the glyphs with an image.
 * 
 * Define a pattern containing an image and use it as the fill color for the
 * text being filled and stroked.

 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Image as Text Fill Color";

$imagefile = "text_filling.tif";

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

    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Load the background image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Retrieve the image dimensions (related to its original 
     * resolution)
     */
    $imagewidth = $p->info_image($image, "imagewidth", "");
    $imageheight = $p->info_image($image, "imageheight", "");
    
    /* The image may have a resolution above 72 dpi. If we used the
     * returned dimensions (in points) for the pattern height and width
     * and placed our image into the pattern, it wouldn't match the
     * pattern size, and a white margin would be left. To make the pattern
     * the same size as the image we calculate the image size 
     * based on its resolution.
     * 
     * Retrieve the original image resolution
     */
    $resx = $p->info_image($image, "resx", "");
    $resy = $p->info_image($image, "resy", "");
    
    /* Calculate the image dimensions for 72 dpi */
    if ($resx > 0) {
	    $imagewidth = $imagewidth * 72 / $resx;
	    $imageheight = $imageheight * 72 / $resy;
    }

    /* Create a pattern using the retrieved image dimensions.
     * The painttype parameter must be set to 1 since a colorized
     * image is used (as opposed to an image mask).
     */
    $pattern = $p->begin_pattern($imagewidth, $imageheight, $imagewidth,
	$imageheight, 1);
    $p->fit_image($image, 0, 0, "");
    $p->end_pattern();
    $p->close_image($image);

    /* Start page */
    $p->begin_page_ext(595, 842, "");

    /* Set the pattern as the current fill color. Encapsulate the
     * setcolor() call with save() and restore() to be able to proceed with
     * the original colors.
     */
    $p->save();
    /* In addition to filling the text (which is the default), stroke the 
     * text by setting the textrendering mode to 2 (fill and stroke) and
     * defining a stroke color different from black.
     */
    $p->set_value("textrendering", 2);
    $p->setcolor("stroke", "rgb", 0.5, 0.2, 0.1, 0);
    $p->setcolor("fill", "pattern", $pattern, 0, 0, 0);
	  
    /* Output the text with the current fill color, i.e. the image in terms
     * of the pattern color
     */
    $p->setfont($font, 50);
    $p->show_xy("Hello World!", 50, 500);
    $p->continue_text("(says PDFlib GmbH)");
    
    $p->restore();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=image_as_text_fill_color.pdf");
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
