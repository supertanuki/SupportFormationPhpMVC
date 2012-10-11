<?php
/* $Id: form_tab_order.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Form tab order:
 * Define the position of each form field in the tab order, i.e. when the user
 * presses the "Tab" key. 
 * 
 * By default the tab order of fields will be implicitly defined according to
 * their creation order. Using the "taborder" option another tab order can
 * be defined. Create a matrix of text fields row by row but define the tab
 * order column by column.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Form Tab Order";


$width=130; $height=30; $llx = 10; $lly = 600;

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /* Start page */
    $p->begin_page_ext(0, 0, " width=a4.width height=a4.height");
    
    $p->setfont($font, 14);
    $p->fit_textline("Press \"Tab\" to move from one field to the next in " .
	"the tab order defined.", $llx, $lly, "");
    
    /* Create a matrix of text fields row by row but define the tab order
     * column by column (taborder=...). Each text fields show its position
     * in the tab order (currentvalue={...}).
     */
    $optlist = "backgroundcolor={rgb 0.95 1 0.95} bordercolor={gray 0} " .
	"alignment=center font=" . $font . " fontsize=18";
    
    $lly-= 50;
    
    for ($i = 1; $i <= 3; $i++) {
	for ($j = 1, $tab=$i; $j <= 3; $j++, $tab+=3) {
	    $p->create_field($llx, $lly, $llx + $width, $lly + $height, 
		"field" . $tab, "textfield", $optlist . " taborder=" . $tab .
		" currentvalue={tab position " . $tab . "}");
	    $llx+=$width + 20;
	}
	$llx = 10;
	$lly -= $height + 30;
    }

    $p->end_page_ext("");
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=form_tab_order.pdf");
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

