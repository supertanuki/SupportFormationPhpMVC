<?php
/* $Id: image_from_url.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Image from URL:
 * Read an image from an URL and place it in a PDF document
 * 
 * Read an image from the URL and store into a PDFlib virtual file (PVF). 
 * Then, load the image data from the PVF and place it on the page.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Image from URL";

$image_url = "http://www.pdflib.com/uploads/media/logo_01.gif";


try {
    $imageData = file_get_contents($image_url);
    if ($imageData == false){
	throw new Exception("Error: file_get_contents($image_url) failed");
    }
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
		
    /* Store the image in a PDFlib virtual file (PVF) called
     * "/pvf/image"
     */
    $p->create_pvf("/pvf/image", $imageData, "");

    /* Load the image from the PVF */
    $image = $p->load_image("auto", "/pvf/image" , "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start a page, place the image, and finish the page */
    $p->begin_page_ext(400, 200, "");
    $p->fit_image($image, 50, 100, "");
    $p->end_page_ext("");

    /* Delete the virtual file to free the allocated memory */
    $p->delete_pvf("/pvf/image");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=image_from_url.pdf");
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

