<?php
/* $Id: arrows.php,v 1.4 2012/05/03 14:00:37 stm Exp $
 * Arrows:
 * Create an arrow using different methods
 *
 * Method I: Draw a simple horizontal arrow.
 * Method II: Draw an arrow with the aid of its unit vector respresentation.
 * Method III: Draw an arrow using coordinate system translation and rotation.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Arrows";

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
    
    /* Load the font; for PDFlib Lite: change "unicode" to "winansi" */
    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=500 height=500");

    /* Method I:
     * Draw a horizontal green arrow from left to right. Start at the given
     * start point located in the middle of the arrow shaft.
     * The following values are given:
     */
    $startx = 100;       /* x coordinate of the starting point */
    $starty = 100;       /* y coordinate of the starting point */
    $stopx = 400;        /* x coordinate of the end point */
    $stopy = 100;        /* y coordinate of the end point */
    $ahl = 40;           /* arrow head length */
    $ahw = 10;           /* arrow head width */
    $sw = 20;            /* shaft width */
    $l = $stopx - $startx; /* length of the arrow */

    /* Set the drawing properties */
    $p->setlinewidth(5.0);
    $p->setcolor("stroke", "rgb", 0.0, 0.5, 0.5, 0.0);
    $p->setcolor("fill", "rgb", 1, 1, 1, 0.0);
    $p->setlinejoin(1);
    $p->setlinecap(1);

    /* Start drawing the arrow */
    $p->moveto($startx, $starty);

    $x = $startx;
    $y = $starty + $sw/2;
    $p->lineto($x, $y);

    $x = $x + ($l - $ahl);
    $p->lineto($x, $y);

    $y = $y + $ahw;
    $p->lineto($x, $y);

    $p->lineto($stopx, $stopy);

    $y = $y - (2*$ahw + $sw);
    $p->lineto($x, $y);

    $y = $y + $ahw;
    $p->lineto($x, $y);

    $x = $x - ($l - $ahl);
    $p->lineto($x, $y);

    $y = $starty + $sw/2;
    $p->lineto($x, $y);
    $p->fill_stroke();

    /* Method II:
     * Draw a non-horizontal pink arrow from left to right. Start at the
     * given start point located in the middle of the arrow shaft.
     * The following values are given:
     */
    $startx = 100;       /* x coordinate of the starting point */
    $starty = 200;       /* y coordinate of the starting point */
    $stopx = 400;        /* x coodingate of the end point */
    $stopy = 300;        /* y coordinate of the end point */
    $ahl = 40;           /* arrow head length */
    $ahw = 20;           /* arrow head width */
    $sw = 20;            /* shaft width */

    /* Calculate the unit vector ($ux, $uy) and its perpendicular
     * ($pux, $puy)
     */
    $dx = $stopx - $startx;
    $dy = $stopy - $starty;
    $l = sqrt($dx*$dx + $dy*$dy);
    $ux = $dx/$l;
    $uy = $dy/$l;
    $pux = $uy;
    $puy = -$ux;

    /* Set the drawing properties */
    $p->setlinewidth(5.0);
    $p->setcolor("stroke", "rgb", 1.0, 0.5, 1.0, 0.0);
    $p->setcolor("fill", "rgb", 0.9, 0.8, 0.8, 0.0);
    $p->setlinejoin(1);
    $p->setlinecap(1);

    /* Start at the given start point located in the middle of the arrow
     * shaft
     */
    $p->moveto($startx, $starty);

    $x = $startx + $sw/2 * $pux;
    $y = $starty + $sw/2 * $puy;
    $p->lineto($x, $y);

    $x = $x + ($l - $ahl) * $ux;
    $y = $y + ($l - $ahl) * $uy;
    $p->lineto($x, $y);

    $x = $x + $ahw * $pux;
    $y = $y + $ahw * $puy;
    $p->lineto($x, $y);

    $p->lineto($stopx, $stopy);

    $x = $x - (2*$ahw + $sw) * $pux;
    $y = $y - (2*$ahw + $sw) * $puy;
    $p->lineto($x, $y);

    $x = $x + $ahw * $pux;
    $y = $y + $ahw * $puy;
    $p->lineto($x, $y);

    $x = $x - ($l - $ahl) * $ux;
    $y = $y - ($l - $ahl) * $uy;
    $p->lineto($x, $y);

    $x = $startx + $sw/2 * $pux;
    $y = $starty + $sw/2 * $puy;
    $p->lineto($x, $y);

    $p->fill_stroke();

    /* Method III:
     * Draw a non-horizontal black arrow from left to right. Start at the
     * given start point located in the middle of the arrow shaft.
     * The following values are given:
     */
    $startx = 100;       /* x coordinate of the starting point */
    $starty = 300;       /* y coordinate of the starting point */
    $angle = 40;         /* Rotation angle in degrees */
    $l = 200;            /* length of the arrow */
    $ahl = 30;           /* arrow head length */
    $ahw = 10;           /* arrow head width */
    $sw = 4;             /* shaft width */

    /* Set the drawing properties */
    $p->setlinewidth(3.0);
    $p->setcolor("stroke", "rgb", 0.0, 0.0, 0.0, 0.0);
    $p->setcolor("fill", "rgb", 0.0, 0.0, 0.0, 0.0);
    $p->setlinejoin(1);
    $p->setlinecap(1);

    /* Rotate and translate the coordinate system */
    $p->translate($startx, $starty);
    $p->rotate($angle);

    /* Start drawing the arrow */
    $p->moveto(0, 0);

    $x = 0;
    $y = $sw/2;
    $p->lineto($x, $y);

    $x = $x + ($l - $ahl);
    $p->lineto($x, $y);

    $y = $y + $ahw;
    $p->lineto($x, $y);

    $x = $x + $ahl;
    $y = $y - ($ahw + $sw/2);
    $p->lineto($x, $y);

    $x = $x - $ahl;
    $y = $y - ($ahw + $sw/2);
    $p->lineto($x, $y);

    $y = $y + $ahw;
    $p->lineto($x, $y);

    $x = $x - ($l - $ahl);
    $p->lineto($x, $y);

    $y = $y + $sw/2;
    $p->lineto($x, $y);
    $p->fill_stroke();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=arrows.pdf");
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
