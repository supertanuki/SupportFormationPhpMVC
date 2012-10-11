<?php
/*
 * $Id: aligned_path_objects.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * 
 * Aligned path objects
 * 
 * Create a schematic street map from line and ring segments. The line segments
 * can have arbitrary length, the ring segments can have arbitrary radius and
 * angle. This is implemented with path objects. By using named points and
 * the "attachmentpoint" and "align" options the positioning of the segments
 * in a seamless fashion is very easy.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/**
 * The basic unit in points.
 */
define("BU", 20);

/**
 * Size of the circle for the reference point.
 */
define("REF_POINT_SIZE", BU / 20);

/**
 * The page width
 */
define("PG_WIDTH", 11 * BU);

/**
 * The page height
 */
define("PG_HEIGHT", 11 * BU);

/**
 * The street width in basic units.
 */
define("STREET_WIDTH", 0.15);

/**
 * The pdflib object
 */

interface segment {
    public function create_path();
}

class ring_segment implements segment {
    function ring_segment($radius, $phi) {
	$this->radius = $radius;
	$this->phi = $phi;
    }

    /**
     * Create a path for a ring segment using polar coordinates. A positive
     * angle means a turn to the left, a negative angle means a turn to the
     * right.
     */
    public function create_path(){
	global $p;
	$r1 = $this->radius * BU;
	$r2 = ($this->radius + STREET_WIDTH) * BU;
	$psi = 0;
	
	/*
	 * Ring with phi > 0 is constructed in the first quadrant.
	 * Ring with phi < 0 is constructed in the fourth quadrant: 
	 * Rotation through -180.
	 */
	if ($this->phi < 0) {
	    $rad = $r1;
	    $r1 = $r2;
	    $r2 = $rad;
	    $psi = -180;
	}
	
	$path = $p->add_path_point(0, $r1, $psi, "move", "polar name=pivot");
	$p->add_path_point($path, $r1, $psi + $this->phi / 2, "control", "polar");
	$p->add_path_point($path, $r1, $psi + $this->phi, "circular",
		"polar name=attach");
	$p->add_path_point($path, $r2, $psi + $this->phi, "line", "polar name=dir");
	$p->add_path_point($path, $r2, $psi + $this->phi / 2, "control", "polar");
	$p->add_path_point($path, $r2, $psi, "circular", "polar");

	return $path;
    }
}

class line_segment implements segment {
    function line_segment($length) {
	$this->length = $length;
    }

    /**
     * Create a path for a line segment.
     */
    public function create_path(){
	global $p;
	$l = $this->length * BU;
	$w = STREET_WIDTH * BU;

	$path = $p->add_path_point(0, 0, 0, "move", "name=pivot");
	$p->add_path_point($path, 0, $l, "line", "name=attach");
	$p->add_path_point($path, $w, $l, "line", "name=dir");
	$p->add_path_point($path, $w, 0, "line", "");

	return $path;
    }
}

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Aligned path objects";

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    $p->set_parameter("errorpolicy", "exception");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $p->begin_page_ext(PG_WIDTH, PG_HEIGHT, "");

    /*
     * Description
     */
    $optlist = "fontname=Helvetica encoding=unicode "
	. "fontsize=" . BU / 2 . " textformat=bytes";

    $p->fit_textline("Aligned path objects", 2 * BU, PG_HEIGHT - 2 * BU,
	$optlist);

    $segments = array( 
	new line_segment(4.5),
	new ring_segment(0.5, -70),
	new line_segment(3.5),
	new ring_segment(0.2, -135),
	new line_segment(0.5), 
	new ring_segment(0.1, 35),
	new ring_segment(7, 80),
	new ring_segment(0.1, -170), 
	new line_segment(3),
	new ring_segment(0.5, -60),
	new line_segment(2.5), 
	new ring_segment(0.1, 90),
	new ring_segment(1.2, 60),
	new ring_segment(0.3, -100),
	new line_segment(2.5),
	new ring_segment(0.3, -80),
	new line_segment(1.5),
	new ring_segment(0.15, 160),
	new line_segment(0.7),
	new ring_segment(0.15, 60),
	new ring_segment(0.15, -40),
	new line_segment(0.6),
	new ring_segment(0.15, -85),
	new line_segment(0.755),
	new ring_segment(0.15, -104),
	new line_segment(0.99),
    );

    /*
     * Initial direction
     */
    $dx = 0.469;
    $dy = 0.883;
    $align_opt = "align={" . $dx . " " . $dy . "}";

    /*
     * Start point
     */
    $startx = 4.7 * BU;
    $starty = 1 * BU;

    $x = $startx;
    $y = $starty;

    /*
     * Loop over segments and draw them one by one
     */
    $path = 0;
    for ($i = 0; $i < count($segments); $i += 1) {
	if ($path != 0) {
	    /*
	     * Compute the coordinates of the next pivot point
	     */
	    $xatt = $p->info_path($path, "px", $align_opt
		. " attachmentpoint=pivot name=attach");
	    $yatt = $p->info_path($path, "py", $align_opt
		. " attachmentpoint=pivot name=attach");

	    /*
	     * Compute the new alignment vector
	     */
	    $xdir = $p->info_path($path, "px", 
			$align_opt . " attachmentpoint=pivot name=dir");
	    $ydir = $p->info_path($path, "py", 
			$align_opt . " attachmentpoint=pivot name=dir");
	    $dx = $xdir - $xatt;
	    $dy = $ydir - $yatt;
	    $align_opt = "align={" . $dx . " " . $dy . "}";

	    /*
				 * New reference vector
				 */
	    $x += $xatt;
	    $y += $yatt;
	    
	    /*
	     * Get rid of previous path
	     */
	    $p->delete_path($path);
	}

	/*
	 * Create the path object and draw the path, taking the "pivot"
	 * point as attachment point and aligned to the alignment
	 * vector.
	 */
	$path = $segments[$i]->create_path();

	$p->draw_path($path, $x, $y,
	    $align_opt . " fillcolor=white attachmentpoint=pivot "
		. "close stroke fill linewidth=0.5");

	/*
	 * Mark the pivot point
	 */
	$p->setcolor("fill", "red", 0, 0, 0, 0);
	$p->circle($x, $y, REF_POINT_SIZE / 2);
	$p->fill();
    }

    /*
     * Delete last path object
     */
    if ($path != -1) {
	$p->delete_path($path);
    }

    /*
     * Redraw the first pivot point on top
     */
    $p->setcolor("fill", "red", 0, 0, 0, 0);
    $p->circle($startx, $starty, REF_POINT_SIZE / 2);
    $p->fill();

    $p->end_page_ext("");
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=aligned_path_objects.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
	"[" . $e->get_errnum() . "] " . $e->get_apiname() .
	": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}

?>
