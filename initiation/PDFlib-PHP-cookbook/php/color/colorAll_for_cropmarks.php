<?php
/* $Id: colorAll_for_cropmarks.php,v 1.3 2012/05/03 14:00:39 stm Exp $
* Color space for crop marks:
* Create crop marks which will be visible on all color separations, using the
* special color "All".
*
* Create a CMYK document by loading a CMYK image and create the special color
* "All" with make_spotcolor(). Draw some crop marks with standard vector
* graphics and output some info text outside of the actual page area (i.e.
* outside of the CropBox).
* Test in Acrobat 7 or 8:
* Show the MediaBox with Document, Crop Pages, Set to Zero.
* Choose Advanced, (Print Production), Output Preview:
* All elements with color "All" will be visible in all combinations of
* separation previews.
*
* Required software: PDFlib/PDFlib+PDI/PPS 7
* Required data: CMYK image
*/

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Color All for Crop Marks";
$imagefile = "lionel.jpg";
$info = "Lionel watching the big blue lion  Page 1  Wednesday, " . 
    "August 01, 2007  8:52 PM";

$pagewidth=842;
$pageheight=595;

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start the page with a crop box defined with 40 units less than the
     * page size in all directions for placing crop marks and text info
     */
    $p->begin_page_ext($pagewidth, $pageheight, "cropbox={40 40 802 555}");

    /* Load and fit a CMYK image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->fit_image($image, 0, 0,
	"boxsize={" . $pagewidth . " " . $pageheight . "} position=center");

    /* Create the special color "All" to be used for crop marks
     * and info text which will be visible on all color separations.
     */
    $color = $p->makespotcolor("All");
    $p->setcolor("fillstroke", "spot", $color, 1, 0, 0);

    /* Draw crop marks within the CropBox */

    /* Draw crop marks at the bottom left */
    $p->moveto(0, 20);
    $p->lineto(15, 20);
    $p->moveto(20, 0);
    $p->lineto(20, 15);

    /* Draw crop marks on the top left */
    $p->moveto(0, $pageheight - 20);
    $p->lineto(15, $pageheight - 20);
    $p->moveto(20, $pageheight);
    $p->lineto(20, $pageheight - 15);
    $p->stroke();

    /* Draw crop marks on the top right */
    $p->moveto($pagewidth - 20, $pageheight);
    $p->lineto($pagewidth - 20, $pageheight - 15);
    $p->moveto($pagewidth, $pageheight - 20);
    $p->lineto($pagewidth - 15, $pageheight - 20);

    /* Draw crop marks at the bottom right */
    $p->moveto($pagewidth - 20, 0);
    $p->lineto($pagewidth - 20, 15);
    $p->moveto($pagewidth, 20);
    $p->lineto($pagewidth - 15, 20);

    $p->stroke();

    /* Load the font */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Output some info text outside of the CropBox */
    $p->fit_textline($info, 30, 8, "font=" . $font . " fontsize=10");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=colorAll_for_cropmarks.pdf");
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
