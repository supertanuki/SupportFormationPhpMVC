<?php
/* $Id: center_image_on_card.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Center image on card:
 * Place an image on an imported PDF card
 *
 * The image will be placed in the center on the imported PDF page and scaled
 * to 80% of the page size. The height of the image will be scaled accordingly
 * so that the picture will stay in ratio.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document, image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Center Image on Card";

$imagefile = "nesrin.jpg";
$cardfile = "card.pdf";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");

    if ($image == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Load the card file */
    $card = $p->open_pdi_document($cardfile, "");
    if ($card == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the first page of the card file */
    $cardpage = $p->open_pdi_page($card, 1, "");
    if ($cardpage == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Query the width and height of the first page of the card */
    $cardwidth = $p->pcos_get_number($card, "pages[0]/width");
    $cardheight = $p->pcos_get_number($card, "pages[0]/height");

    /* Create a page with the width and height of the card */
    $p->begin_page_ext(0, 0, "width=" . $cardwidth . " height=" . $cardheight);

    /* Place the card page */
    $p->fit_pdi_page($cardpage, 0, 0, "");

    /* Place the image in the center of a box which covers 80% of the card
     * size and starts from 10 percent of the card width and height. Fit
     * the image proportionally into the box so that it will cover 80% of
     * the card size as well.
     */
    $p->fit_image($image, $cardwidth * 0.1, $cardheight * 0.1,
	    "boxsize={" . $cardwidth * 0.8 . " " . $cardheight * 0.8 .
	    "} position=center fitmethod=meet");

    $p->end_page_ext("");
    $p->close_image($image);
    $p->close_pdi_page($cardpage);
    $p->close_pdi_document($card);
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=center_images_on_card.pdf");
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

