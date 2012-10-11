<?php
/* $Id: error_handling.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Error handling:
 * Demonstrate different strategies with respect to exception handling.
 * 
 * Example 1: Set the "errorpolicy" parameter to "return". This means that a 
 * PDFlib function call will return with -1 (in PHP: 0) when an error condition
 * is detected. Try to load a font which is not available so that load_font()
 * will return with -1 (in PHP: 0). Check for that return value and load another
 * available font instead.
 * 
 * Example 2: Set the "errorpolicy" parameter to "exception". This means that
 * an exception will be thrown whenever a PDFlib function call fails. Change the
 * error policy to "return" every time you want to check the return value of a
 * particular PDFlib function call. Then, set the "errorpolicy" parameter to
 * "return" to check the return value of load_image(). If the image to be loaded
 * does not exist output a replacement text. 
 * 
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Error Handling";

$imagefile = "unavailable.jpg";

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");
	    
    /* Use the "errorpolicy" parameter to define an appropriate error 
     * policy.
     * The default setting "errorpolicy=legacy" ensures compatibility to
     * earlier versions of PDFlib, where exceptions and error return values
     * are controlled by parameters and options such as fontwarning,
     * imagewarning, etc. This is only recommended for applications which
     * require source code compatibility with PDFlib 6.
     * 
     * With "errorpolicy=exception" an exception will be thrown when an
     * error condition is detected. However, the output document will be
     * unusable after an exception. This can be used for lazy programming
     * without any error conditionals at the expense of sacrificing the
     * output document even for problems which may be fixable by the
     * application.
     * 
     * With "errorpolicy=return" when an error condition is detected, the
     * respective function will return with a -1 (in PHP: 0) error value
     * regardless of any warning parameters or options. The application
     * developer must check the return value to identify problems, and must
     * react on the problem in whatever way is appropriate for the
     * application. This is the recommended approach since it allows a
     * unified approach to error handling.
     */
    
    /* ------------------------------------------------------------------
     * First example: 
     * Set the "errorpolicy" to "return" for all function calls.
     * This means we must check the return values of load_font() etc. for
     * -1 (in PHP: 0).
     * ------------------------------------------------------------------
     */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start page */
    $p->begin_page_ext(842, 595, "");

    /* Try to load a special font which might not be available.
     * 
     * For PDFlib Lite: change "unicode" to "winansi"
     */
    $font = $p->load_font("MyFontName", "unicode", "");

    /* Check the return value of load_font() */
    if ($font == 0) {
	/* font handle is invalid; find out what happened */
	$errmsg = $p->get_errmsg();
	
	/* Try a different font */
	$font = $p->load_font("Helvetica", "unicode", "");

	/* font handle is also invalid; give up */
	if ($font == 0) {
	    throw new Exception("Error: " . $p->get_errmsg());
	}
	
	/* Output the error message to the PDF. This is not pretty 
	 * realistic but it is intended to show how you can proceed after
	 * a failed function call without having damaged the PDF.
	 */
	$p->setfont($font, 20);
	$p->fit_textline($errmsg, 50, 400, "");
    }

    
    /* -------------------------------------------------------------------
     * Second example: 
     * Set the "errorpolicy" to "exception". Change it to "return" only if
     * you want to check the return value of a particular function call.
     * -------------------------------------------------------------------
     */
    $p->set_parameter("errorpolicy", "exception");
    
    /* Output some descriptive text */
    $p->setfont($font, 20);
    $p->fit_textline("Error policy can be legal, exception, or return.",
	50, 500, "");
    
    /* Set the "errorpolicy" parameter  to "return" to check the return
     * value of load_image(). Then set the parameter back to "exception"
     * again. If the image to be loaded does not exist a replacement text
     * will be output. 
     * 
     * Alternatively, the "errorpolicy=return" option of load_image() can be
     * used which changes the error policy just for the respective function
     * call. Note that this option is correctly supported only by 
     * PDFlib 7.0.3 and above.
     */
    $p->set_parameter("errorpolicy", "return");
      
    /* Load the image */
    $image = $p->load_image("auto", $imagefile, "");
    
    /* Set the "errorpolicy" parameter  to "exception" again */
    $p->set_parameter("errorpolicy", "exception");
    
    /* Output some replacement text if image is not available */
    if ($image == 0) {
	$p->setcolor("fillstroke", "rgb", 1, 0, 0, 0);
	$x=50; $y=200; $w=200; $h=100;
	$p->rect($x, $y, $w, $h);
	$p->stroke();
	$p->setfont($font, 10);
	$p->fit_textline("Replacement text for unavailable image", 
	    $x + 5, $y + 5, "boxsize={" . ($w - 10) . " " . ($h - 10) . "}");
    }
    else {
	/* Place the image */
	$p->fit_image($image, 20, 20, "scale=0.5");
    }
    
    $p->end_page_ext("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=error_handling.pdf");
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

