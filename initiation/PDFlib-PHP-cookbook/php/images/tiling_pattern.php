<?php
/* $Id: tiling_pattern.php,v 1.4 2012/05/03 14:00:40 stm Exp $
 * Tiling pattern:
 * Define a tiling pattern containing an image and use it to cover the page
 * background with tiles
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Tiling Pattern";

$imagefile = "background_image.png";
$pagewidth = 595; $pageheight = 842;

try{
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

	$p->set_info("Creator", "PDFlib Cookbook");
	$p->set_info("Title", $title);

    /* Load the background image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Retrieve the image dimensions */
    $imagewidth = $p->info_image($image, "imagewidth", "");
    $imageheight = $p->info_image($image, "imageheight", "");
    
    /* The image may have a resolution above 72 dpi. If we used the
     * returned dimensions (in points) for the pattern height and width
     * and placed our image into the pattern, it wouldn't match the
     * pattern size, and a white margin would be left. To make the pattern
     * the same size as the image we calculate the image size 
     * based on its resolution. 
     * 
     * Retrieve the original image resolution:
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
    $p->begin_page_ext($pagewidth, $pageheight, "");
    
    /* Set a fill color and create and fill a rectangle with the dimensions
     * of the page. Encapsulate the setcolor() call with save() and 
     * restore() to be able to proceed with the original colors.
     */
    $p->save();
    $p->setcolor("fill", "pattern", $pattern, 0, 0, 0);
    $p->rect(0, 0, $pagewidth, $pageheight);
    $p->fill();
    $p->restore();

    /* Output some page contents */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->setfont($font, 14);
    $p->set_text_pos(20, 700);
    $p->show("The page background consists of a small image pattern");
    $p->continue_text("which will be output on the page repeatedly.");
    
    $p->end_page_ext("");
    
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=tiling_pattern.pdf");
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

