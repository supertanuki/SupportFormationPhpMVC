<?php
/* $Id: escape_sequences.php,v 1.4 2012/05/03 14:00:38 stm Exp $
 * Escape sequences:
 * Use escape sequences in text lines to output octal or hexadecimal values 
 * 
 * Enable the resolution of escape sequences by setting the "escapesequence"
 * parameter to "true". 
 * Output simple text in octal as well as hexadecimal notation using
 * fit_textline().
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Escape Sequences";

$font;
$x=100; 
$xoff=200; 
$y=650; 
$yoff=35;
 
try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);
    
    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* For PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
	    
    /* Enable escape sequences to be resolved */
    $p->set_parameter("escapesequence", "true");
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Set the font and font size */
    $p->setfont($font, 18);
    
    /* Output some descriptive text, i.e. the header for a kind of
     * input/output table
     */
    $p->fit_textline("Input", $x, $y, "underline underlinewidth=1");
    $p->fit_textline("Output", $x+$xoff, $y, "underline underlinewidth=1");
    
     
    /* ---------------------------------------------------------
     * Output some text in octal notation using escape sequences
     * ---------------------------------------------------------
     */
  
    /* Show the input text. 
     * In PDFlib with PHP, four backslashes are needed to output just one
     * of them. First, the PHP interpreter resolves each pair of backslashes
     * to one backslash. Then, these two backslashes are resolved by PDFlib
     * to one backslash, e.g. "\\\\160" will result in the literal string
     * "\160" in the output.
     */
    $p->fit_textline("\\\\160", $x, $y-=$yoff, "");
    
    /* Output the character "p" by supplying it in octal notation
     * using the escape sequence "\\160". Two backslashes are needed since
     * the PHP interpreter will resolve "\\" to "\". Then, PDFlib will
     * interpret the "\" escape sequence as hexadecimal notation provided
     * that the "escapesequence" parameter has been set to "true" as done
     * above.  
     */
    $p->fit_textline("\\160", $x+$xoff, $y, "");
    
    
    /* ---------------------------------------------------------------
     * Output some German umlauts in hexadecimal notation using escape
     * sequences
     * ---------------------------------------------------------------
     */
	    
    /* Show the input text (for a description see above) */
    $p->fit_textline("\\\\xC4", $x, $y-=$yoff, "");
    
    /* Output the character "Ä" by supplying it in hexadecimal notation
     * using the escape sequence "\\xC4" (for a description see above).  
     */
    $p->fit_textline("\\xC4", $x+$xoff, $y, "");
    
    
    /* Show the input text (for a description see above) */
    $p->fit_textline("\\\\xD6", $x, $y-=$yoff, "");
    
    /* Output the character "Ö" by supplying it in hexadecimal notation
     * using the escape sequence "\\xD6" (for a description see above).  
     */
    $p->fit_textline("\\xD6", $x+$xoff, $y, "");
    
    
    /* Show the input text (for a description see above) */
    $p->fit_textline("\\\\xDC", $x, $y-=$yoff, "");
    
    /* Output the character "Ü" by supplying it in hexadecimal notation
     * using the escape sequence "\\xDC" (for a description see above).  
     */
    $p->fit_textline("\\xDC", $x+$xoff, $y, "");
    
    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=escape_sequences.pdf");
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
