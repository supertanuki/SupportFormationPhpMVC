<?php
/*
 * $Id: alpha_channel.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 *
 * Demonstrate the use of an integrated alpha channel (aka soft masks aka 
 * transparency) in images. This works with TIFF and PNG images.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Add Image With Alpha Channel";

$imagefile = "magnolia.png";
$pg_width = 595;
$pg_height = 842;

$bg_text = 
    "This text is visible through the transparent image.";
$repeat_text = 40;


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == -1)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Define size and position of image box. */
    $box_width = $pg_height / 2;
    $box_height = $pg_width / 2;
    $img_llx = ($pg_width - $box_width) / 2;
    $img_lly = ($pg_height - $box_height) / 2;
    
    /* Position and size of headline box */
    $headline_displacement = 40;
    $headline_llx = $img_llx;
    $headline_lly = $img_lly + $box_height + $headline_displacement;
    $headline_width = $box_width;
    $headline_height = $headline_displacement;
    
    /* Start page */
    $p->begin_page_ext($pg_width, $pg_height, "");

    /* Create a headline */
    $p->fit_textline(
	"Place an image with an alpha channel over a background", 
	$headline_llx, $headline_lly, 
	"boxsize={" . $headline_width . " " . $headline_height . "} "
	. "font=" . $font . " fontsize=18 position=center");
    
    /*
     * Put a text in the background, covering the same area as the
     * image.
     */
    $tf = 0;
    for ($i = 0; $i < $repeat_text; $i += 1) {
	$tf = $p->add_textflow($tf, $bg_text, "font=" . $font 
		. " fontsize=12 alignment=justify");
    }

    /* Place the textflow */
    $p->fit_textflow($tf, $img_llx, $img_lly, 
	$img_llx + $box_width, $img_lly + $box_height, "");
    $p->delete_textflow($tf);
    
    /* Place the image over the textflow */
    $p->fit_image($image, $img_llx, $img_lly, 
	"boxsize={" . $box_width . " " . $box_height . "} "
	. "fitmethod=meet showborder position=center");
    $p->close_image($image); 

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=alpha_channel.pdf");
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
