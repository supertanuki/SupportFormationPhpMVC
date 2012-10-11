<?php
/* $Id: display_image_partially.php,v 1.2 2012/05/03 14:00:40 stm Exp $
 * Display image partially:
 * Display an image partially.
 *
 * Use the "matchbox" option with the "clipping" suboption to define a
 * rectangular part of the image.
 * Output the image part in its original size.
 * Output the image part by fitting it in a box of a given size.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Display Image Partially";

$imagefile = "nesrin.jpg";
$llx = 50; $lly = 550;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == -1)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start page 1 */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
    
    $p->setfont($font, 12);
    

    /* -------------------------------------------------------------
     * Output the complete image in its original size with a certain
     * transparency as a kind of background image
     * -------------------------------------------------------------
     */
    
    /* Output some descriptive text */
    $text = "Page 1:";
    $p->fit_textline($text, $llx, $lly, "");
    $text = "Place the full image in its original size, with a " .
	   "transparency of 50%";
    $p->fit_textline($text, $llx, $lly-=30, "");
    
    /* Save the current graphics state */
    $p->save();
    
    /* Create an extended graphics state with a transparency set to 50% */
    $gstate = $p->create_gstate("opacityfill=.4");
    
    /* Apply the extended graphics state */
    $p->set_gstate($gstate);
    
    /* Fit the image in its original size with the transparency set above */
    $p->fit_image($image, $llx, $lly-=500, "");
    
    /* Restore the original graphics state */
    $p->restore();
    
    
    /* ----------------------------------------------
     * Place a part of the image in its original size
     * ----------------------------------------------
     */
    
    /* Define a rectangular part of the image by using the "matchbox" option
     * with the "clipping" suboption to set the lower left and upper right
     * corners of the rectangle.
     */
    $c_llx = 350; $c_lly = 180; $c_urx = 550; $c_ury = 360;
    
    $optlist = "matchbox={clipping={" . 
	$c_llx . " " . $c_lly . " " . $c_urx . " " . $c_ury . "}}";
    
    /* Output some descriptive text */
    $text = "Place a part of the image in its original size at the";
    $p->fit_textline($text, $llx + $c_llx, 410, "");
    
    $text = "position as it would appear in the full image";
    $p->fit_textline($text, $llx + $c_llx, 395, "");
	      
    /* Display the image partially at the same position as it would appear
     * when displaying the complete image above.
     */
    $p->fit_image($image, $llx + $c_llx, $lly + $c_lly, $optlist);
    
    /* Output some descriptive text */
    $text = "Place a part of the image in its original size";
    $p->fit_textline($text, $llx, 230, "");
    
    $text = "at the lower left corner of the full image";
    $p->fit_textline($text, $llx, 215, "");
    
    /* Now, display the part of the image at the lower left corner of the
     * complete image above.
     */
    $p->fit_image($image, $llx, $lly, $optlist);
    
    $p->end_page_ext("");
	  
    /* Start Page 2 */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
    
    $p->setfont($font, 12);
    
	  
    /* ----------------------------------------------------
     * Display the image partially in a box of a given size
     * ----------------------------------------------------
     */
    $lly = 550;
    /* Output some descriptive text */
    $p->fit_textline("Page 2:", $llx, $lly, "");
    
    $text = "Output a part of the image in its original size:";
    $p->fit_textline($text, $llx, $lly-=30, "");
    
    $text = "\$p->fit_image(image, x, y, \"matchbox={clipping={35% 35% 75% " .
	   "75%}}\");";
    $p->fit_textline($text, $llx, $lly-=15, "");
		 
    /* Define a rectangular part of the image by using the "matchbox" option
     * with the "clipping" suboption. The lower left and upper right corners
     * of the rectangle are set as percentages of the original image size. 
     */
    $optlist = "matchbox={clipping={35% 35% 75% 75%}}";
    
    /* Place the part of the image defined */
    $p->fit_image($image, $llx, $lly-=220, $optlist);
    
    /* Output some descriptive text */
    $text = "Place a part of the image in a given box:";
    $p->fit_textline($text, $llx, $lly-=50, "");
    
    $text = "\$p->fit_image(image, x, y, \"matchbox={clipping={35% 35% 75% " .
	   "75%}} boxsize={300 200} fitmethod=meet showborder\");";
    $p->fit_textline($text, $llx, $lly-=15, "");
    
    /* Display the image partially in a box of the given size. Scale the
     * part of the image proportionally until it fits completely into the
     * box.
     */
    $optlist = "matchbox={clipping={35% 35% 75% 75%}} " .
	"boxsize={200 100} fitmethod=meet showborder";
    
    $p->fit_image($image, $llx, $lly-=120, $optlist);
    
    $p->end_page_ext("");
    
    $p->close_image($image);

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=display_image_partially.pdf");
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
