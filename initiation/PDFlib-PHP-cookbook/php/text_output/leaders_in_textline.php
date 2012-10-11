<?php
/* $Id: leaders_in_textline.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Leaders in text line:
 * Use dot leaders to fill the space between text and a page number such as in
 * a table of contents
 * 
 * Create a table of contents by placing text in a box with the space to the
 * right end of the box being filled with dot leaders. Place text representing a
 * page number after that text box.
 * Create another table of contents but with the page numbers right-aligned. 
 *  
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Leaders in Text Line";

$x = 20; $bw = 400; $y = 700; $yoff = 20;

$headopts = "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
    "fillcolor={cmyk 1 0.5 0.2 0}";
  
$items = array(
    array( "Long Distance Glider", "3" ),
    array( "Giant Wing", "7" ),
    array( "Cone Head Rocket", "12" ),
    array( "Super Dart", "18" )
);
    
try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Set an output path according to the name of the topic */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    /* Start Page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    
    /* -----------------
     * Table of contents
     * -----------------
     */
    
    /* Output a heading */
    $p->fit_textline("Table of contents", $x, $y, $headopts);
    
    /* Option list for the text entry. The text is placed in a box with a
     * width of 400 using boxsize={400 30}. The "leader" option is used to
     * define leaders to be used. The "alignment" suboption specifies the
     * alignment between text, leaders, and box. The default leader
     * character is ".". Use the "text" suboption to define one or more
     * other characters to be used as leaders.
     */ 
    $textopts = "fontname=Helvetica fontsize=14 encoding=unicode " .
	"boxsize={" . $bw . " 30} leader={alignment=right}";
    
    /* Option list for the page number */
    $numopts = "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
	"fillcolor={cmyk 1 0.5 0.2 0}";
    
    for ($i = 0; $i < 4; $i++) {
	/* Place the text line */
	$p->fit_textline($items[$i][0], $x, $y-=$yoff, $textopts);
	
	/* To the right of the text box place the page number with a 
	 * distance of 10
	 */
	$p->fit_textline($items[$i][1], $x+$bw+10, $y, $numopts);
    }
    
    
    /* ------------------------------------------------
     * Table of contents with the numbers right-aligned
     * ------------------------------------------------
     */
    
    /* Output a heading */
    $p->fit_textline("Table of contents with the numbers right-aligned",
	$x, $y-=2*$yoff, $headopts);
    
    /* In this case, the number is placed right-aligned with
     * "position={right bottom}" in a box with a width of 20 using
     * "boxsize={20 30}".
     */ 
    $numopts = "fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
	"boxsize={20 30} position={right bottom} " .
	"fillcolor={cmyk 1 0.5 0.2 0}";
    
    for ($i = 0; $i < 4; $i++) {
	/* Place the text line */
	$p->fit_textline($items[$i][0], $x, $y-=$yoff, $textopts);
	
	/* To the right of the text box place the page number right-aligned
	 * into a box with a width of 20 
	 */
	$p->fit_textline($items[$i][1], $x+$bw, $y, $numopts);
    }

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=leaders_in_textline.pdf");
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
