<?php
/* $Id: frame_around_image.php,v 1.3 2012/05/03 14:00:40 stm Exp $
 * Frame around image:
 * Draw a frame around an image
 *
 * Place an image and draw a thick border around it using the "matchbox" option
 * and its "borderwidth" and "offset" suboptions.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Frame around Image";

$imagefile = "nesrin.jpg";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Define the option list for fit_image():
     * Place the image in the center of a box using the "boxsize" and 
     * "position" options. Maintain its proportions using "fitmethod=meet".
     * Use the "matchbox" option with the "borderwidth" suboption to draw a
     * thick rectangle around the image. The "strokecolor" suboption
     * determines the border color, and the "linecap" and "linejoin"
     * suboptions are used to round the corners. The matchbox is always
     * drawn before the image which means it would be hidden by the image.
     * To avoid this use the "offset" suboptions with 50 percent of the
     * border width to enlarge the frame beyond the area covered by the
     * image.
     */
    
    $optlist =
	    "boxsize={400 300} position={center} fitmethod=meet " .
	"matchbox={borderwidth=10 offsetleft=-5 offsetright=5 " .
	"offsetbottom=-5 offsettop=5 linecap=round linejoin=round " .
	"strokecolor {rgb 0.0 0.3 0.3}}";
    
    /* Fit the image using the option list defined above */
    $p->fit_image($image, 200, 150, $optlist);
    $p->close_image($image);

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=frame_around_image.pdf");
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
