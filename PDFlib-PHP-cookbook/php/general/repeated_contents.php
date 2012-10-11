<?php
/* $Id: repeated_contents.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Repeated contents:
 * Create contents which are used identically on multiple pages, such as fixed
 * headers or footers.
 * 
 * Create a PDFlib template (which will result in a PDF Form XObject) for
 * a business letter header which holds an image and some text. Then place
 * the template. When placing the template on multiple pages (or multiply on
 * the same page), the actual PDF operators for constructing the template are
 * only included once in the PDF file, thereby saving PDF output file size. 
 * 
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Repeated Contents";

$imagefile = "kraxi_header.tif";
$x = 30; $y;
$pagewidth=595; $pageheight=842;
$addresses =array(
    array( "Mr Bee", "3, Rose Gardens", "London" ),
    array( "Miss Hopper", "26, Shakespeare Road", "Hopperfield" ),
    array( "Mr Duck", "128, Chapel Hill", "Bournemouth" ),
    array( "Miss Haley", "50, Virginia Street", "Southport" )
);

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

    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    /* Load the image to be contained in the template */
    $image = $p->load_image("auto", $imagefile, "");

    if ($image == 0)
    throw new Exception("Error: " . $p->get_errmsg());
    
    /* Define a template with a business letter header containing the 
     * image loaded and some text for the sender's address
     */
    $template = $p->begin_template_ext($pagewidth, $pageheight, "");
    
    /* Place the image into the template */        
    $p->fit_image($image, $pagewidth-230, $pageheight-140, 
	"boxsize={200 100} fitmethod=meet");
    
    /* Place the text into the template */
    $p->fit_textline("Kraxi Systems, Inc., 17, Aviation Road, Paperfield",
	30, $pageheight-160, "font=" . $font . " fontsize=8");
    
    /* Finish the template */
    $p->end_template();
    
    /* Close the image */
    $p->close_image($image);
    
    /* Create four letters consisting of one page each: On each page, place
     * the template with the sender's address information, the individual
     * customer's address as well as some informative text
     */
    for ($i = 0; $i < 4; $i++) {
	$p->begin_page_ext($pagewidth, $pageheight, "");
	$y = $pageheight - 165;
    
	/* Place the template on the page, just like using an image */
	$p->fit_image($template,  0.0,  0.0, "");
	
	/* Place the customer address */
	for ($j = 0; $j < 3; $j++) {
	     $p->fit_textline($addresses[$i][$j], $x, $y-=15,
		     "font=" . $font . " fontsize=12");     
	}
    
	/* Place the actual page contents on the page */
	$i++;
	$p->fit_textline("Dear customer, this is the actual page contents " .
	    "of page " . $i . ".", $x, $y-=80, "font=" . $font .
	    " fontsize=12");
	$i--;
	$p->fit_textline("The image and the sender's address above are " .
	    "part of a template.", $x, $y -=20, "font=" . $font .
	    " fontsize=12");

	$p->end_page_ext("");
    }
    
    /* If required, place the template on further pages. Then close it. */
    $p->close_image($template);

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=repeated_contents.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() .
        ": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;
?>
