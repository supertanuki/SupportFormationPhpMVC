<?php
/*
 * $Id: recombine_color_channels.php,v 1.3 2012/05/07 14:07:01 stm Exp $
 * 
 * Recombine split color channels
 *
 * This sample code expects N grayscale images as input, colorizes each image
 * with Cyan, Magenta, Yellow, and Black, respectively, and places all
 * colorized images on top of each other with overprintfill=true. As a
 * result, the full recombined CMYK image is visible on the page.
 *
 * Using the parameters at the start of the code you can even recombine
 * more than four channels, or color channels other than C, M, Y, K.
 *
 * Caveats: Overprint Preview/Simulation is required for correct rendering!
 *
 * Screen display in Acrobat:
 * Edit, Preferences, [General], Page Display, Use Overprint Preview
 * If this is set to "Never", only the last (Black) channel will be visible.
 * We create PDF/X so that it works with Acrobat's default "Only for PDF/X
 * Files".
 *
 * Printing with Acrobat 10.1:
 * Print, Advanced, Output, Simulate Overprinting must be enabled!
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: TIFF image file, CMYK image file, ICC profile
 */
$title = "Channel Recombination";
$basename = "zebra"; /* zebra_C.tif etc. */
$suffix = "tif";
$channelsuffix = array( "_c", "_m", "_y", "_k" );
$MAXCHANNEL = count($channelsuffix);

$channelnames = array( "Cyan", "Magenta", "Yellow", "Black" );

/* CMYK "alternate" values for the process color channels */
$alt = array(
    array( 1, 0, 0, 0 ),
    array( 0, 1, 0, 0 ),
    array( 0, 0, 1, 0 ),
    array( 0, 0, 0, 1 )
);

/* This is where font/image/PDF input files live. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";

try {
    $p = new pdflib();

    if ($p->begin_document("", "pdfx=PDF/X-4") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_parameter("SearchPath", $searchpath);

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Set output intent ICC profile for PDF/X-4 */
    if ($p->load_iccprofile("ISOcoated.icc", "usage=outputintent") == 0) {
	throw new Exception("Error: " . $p->get_errmsg() . "\n" .
	    "Please install the ICC profile package from ". "\n" .
	    "www.pdflib.com");
    }

    /* Load split channel images and colorize with a suitable spot color */
    for ($channel = 0; $channel < $MAXCHANNEL; $channel++) {
	$p->setcolor("fill", "cmyk", $alt[$channel][0], $alt[$channel][1],
	    $alt[$channel][2], $alt[$channel][3]);
	$spot[$channel] = $p->makespotcolor($channelnames[$channel]);

	$filename = $basename . $channelsuffix[$channel] . "."
	    . $suffix;
	$optlist = "colorize=" . $spot[$channel];
	$image[$channel] = $p->load_image("auto", $filename, $optlist);

	if ($image[$channel] == 0) {
	    throw new Exception("Error: " . $p->get_errmsg());
	}
    }

    /* Enable overprint fill mode (applies to all images) */
    $gs = $p->create_gstate("overprintfill=true");

    /* dummy page size, will be adjusted by PDF_fit_image() */
    $p->begin_page_ext(10, 10, "");

    $p->set_gstate($gs);

    for ($channel = 0; $channel < $MAXCHANNEL; $channel++) {
	$p->fit_image($image[$channel], 0.0, 0.0, "adjustpage");
	$p->close_image($image[$channel]);
    }

    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=recombine_color_channels.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }
    $p = 0;
?>

