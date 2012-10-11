<?php
/* $Id: fill_converted_formfields.php,v 1.5 2012/05/03 14:00:41 stm Exp $
 * Fill converted form fields:
 * Output an imported PDF page with its blocks being filled with different 
 * personalized data
 * 
 * Import a PDF page whose form fields have been converted to blocks.
 * Fill some blocks representing text fields, checkboxes and radio buttons
 * appropriately.   
 * 
 * Required software: PPS 7
 * Required data: PDF document containing blocks
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Fill Converted Form Fields";

$infile = "form_with_blocks.pdf";

/* The following blocks are contained in the imported page:
 * "plane model" representing a text field
 * "quantity" representing a text field
 * "color" and "color_1" representing two radio buttons 
 * "perforation" and "glossy" representing two checkboxes
 */
try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    $p->set_parameter("escapesequence", "true");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Open a PDF containing blocks */
    $indoc = $p->open_pdi_document($infile, "");
    if ($indoc == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Open the first page */
    $inpage = $p->open_pdi_page($indoc, 1, "");
    if ($inpage == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Get the width and height of the imported page */
    $width = $p->pcos_get_number($indoc, "pages[0]/width");
    $height = $p->pcos_get_number($indoc, "pages[0]/height");
    
    /* Start the output page with the size given by the imported page */ 
    $p->begin_page_ext($width, $height, "");

    /* Place the imported page on the output page */
    $p->fit_pdi_page($inpage, 0, 0, "");
    
    /* Fill the "plane model" block with some text */
    if ($p->fill_textblock($inpage, "plane model", "Long Distance Glider", 
	"encoding=unicode") == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
    
    /* Fill the "quantity" text block with a quantity of 1 */
    if ($p->fill_textblock($inpage, "quantity", "1", "encoding=unicode") == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
    
    /* Fill the "color" block (representing a radiobutton) with a circle.
     * The circle is supplied as "l". The encoding "builtin" will retrieve
     * the circle symbol from the encoding which is integrated in the
     * ZapfDingbats font.
     * The following symbols are represented by the respective characters in
     * the ZapfDingbats font: Check=4, Circle=1, Cross=8, Diamond=u,
     * Square=n, Star=H 
     */
    $text_optlist = "fontname=ZapfDingbats encoding=builtin position=center";
    if ($p->fill_textblock($inpage, "color", "l", 
	$text_optlist) == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
    
    /* Fill the "color_1" block (for a radiobutton) with a space. Otherwise,
     * the border of the block representing the empty check box would not be
     * shown in the output document. 
     */
    $text_optlist = "fontname=ZapfDingbats encoding=builtin position=center";
    if ($p->fill_textblock($inpage, "color_1", " ", 
	$text_optlist) == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
    
    /* Fill the "perforation" block (for a checkbox) with a check mark
     * encoded in Unicode.
     */
    $text_optlist = "fontname=ZapfDingbats fontsize=12 encoding=unicode " .
	"position=center textformat=utf16";
    if ($p->fill_textblock($inpage, "perforation", "\x14\x27", 
	$text_optlist) == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
    
    /* Fill the "glossy" block (for a checkbox) with a space. Otherwise,
     * the border of the block representing the empty check box would not be
     * shown in the output document. 
     */
    $text_optlist = "fontname=ZapfDingbats fontsize=12 encoding=unicode " .
	"position=center";
    if ($p->fill_textblock($inpage, "glossy", " ", 
	$text_optlist) == 0)
	trigger_error("Warning: " . $p->get_errmsg() . "\n");
   
    $p->end_page_ext("");

    $p->close_pdi_page($inpage);

    $p->end_document("");
    $p->close_pdi_document($indoc);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=fill_converted_formfields.pdf");
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

