<?php
/* $Id: simulated_fontstyles.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * Simulated font styles:
 * Create simulated italic or bold text output
 * 
 * Output text lines and Textflows with simulated obliqued or bold font, which
 * is useful if a real italic or bold variant of the font is not available.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Simulated Font Styles";


$textline = "Our Paper Planes";

$textflow =
    "Our paper planes are the ideal way of passing the time. We offer " .
    "revolutionary new developments of the traditional common paper " .
    "planes. If your lesson, conference, or lecture turn out to be " .
    "deadly boring, you can have a wonderful time with our planes.";

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

    /* Start Page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Load the font */
    $font = $p->load_font("GenR102", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    

    /* Place a text line with a simulated italic angle of -12. The
     * characters will be slanted by 12 degrees to the right.
     */
    $optlist = "font=" . $font . " fontsize=20 " . "italicangle=-12";
    $p->fit_textline($textline . " (italicangle=-12)", 100, 750, $optlist);
    
    
    /* Place a text line with a simulated bold font style */
    $optlist = "font=" . $font . " fontsize=20 " . "fakebold=true";
    $p->fit_textline($textline . " (fakebold=true)", 100, 650, $optlist);

       
    /* Place a Textflow with a simulated italic angle of -12. The
     * characters will be slanted by 12 degrees to the right.  
     */
    $optlist = "font=" . $font .  " fontsize=20  italicangle=-12";

    $tf = $p->add_textflow(0, "(italicangle=-12)\n" . $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 100, 350, 450, 550, "");
    if (!$result == "_stop")
    {
	/* Check for errors or more text to be placed */
    }
    $p->delete_textflow($tf);
    
    
    /* Place a Textflow with a simulated bold font style */  
    $optlist = "font=" . $font .  " fontsize=20  fakebold=true";
    
    $tf = $p->add_textflow(0, "(fakebold=true)\n" . $textflow, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $result = $p->fit_textflow($tf, 100, 100, 450, 300, "");
    if (!$result == "_stop")
    {
	/* Check for errors or more text to be placed */
    }
    $p->delete_textflow($tf);
    
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=simulated_fontstyles.pdf");
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
