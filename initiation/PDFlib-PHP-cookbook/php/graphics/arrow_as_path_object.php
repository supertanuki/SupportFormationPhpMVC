<?php
/* $Id: arrow_as_path_object.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * 
 * Arrows: Create arrows using path objects
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/**
 * Draw a path on the page in ROTATION_STEPS rotated variations.
 * 
 * @param p
 *            The pdflib object
 * @path_number The number of the path, used to position the path vertically
 * @param current_path
 *            The path object to draw
 * 
 * @throws PDFlibException
 */


/**
 * Describe an add_path_point operation
 */

class point {
    function point($x, $y, $type, $optlist) {
	$this->x = $x;
	$this->y = $y;
	$this->type = $type;
	$this->optlist = $optlist;
    }
};

/**
 * Describe a path with the draw operation
 */
class path {
    function path($ops, $optlist) {
	$this->ops = $ops;
	$this->optlist = $optlist;
    }
};

$paths = array(
    /* black arrow with rounded corners */
    new path(array(
	new point(-25.0, 0.0, "move", "round=10"),
	new point(-75.0, 0.0, "line", ""),
	new point(0.0, 100.0, "line", ""),
	new point(75.0, 0.0, "line", ""),
	new point(25.0, 0.0, "line", ""),
	new point(25.0, -200.0, "line", ""),
	new point(0.0, -200.0, "line", ""),
	new point(-25.0, -200.0, "line", ""),
	new point(-25.0, 0.0, "line", ""),
    ),
    "fill"),

    /* white arrow in blue circle */
    new path( array(
	new point(0, 200.0, "move", "fillcolor=blue"),
	new point(0, -200.0, "control", ""),
	new point(0, 200.0, "circular", ""),
	new point(-25.0, 50.0, "move", "fillcolor=white"),
	new point(-75.0, 50.0, "line", ""),
	new point(0.0, 150.0, "line", ""),
	new point(75.0, 50.0, "line", ""),
	new point(25.0, 50.0, "line", ""),
	new point(25.0, -150.0, "line", ""),
	new point(0.0, -150.0, "line", ""),
	new point(-25.0, -150.0, "line", ""),
	new point(-25.0, 50.0, "line", ""),
    ),
    "fill"),
    
    /* black arrow in white circle with black border */
    new path(array(
	new point(0, 210.0, "move", "fillcolor=black"),
	new point(0, -210.0, "control", ""),
	new point(0, 210.0, "circular", ""),
	new point(0, 200.0, "move", "fillcolor=white"),
	new point(0, -200.0, "control", ""),
	new point(0, 200.0, "circular", ""),
	new point(-50.0, 0, "move", "fillcolor=black"),
	new point(-150.0, 0, "line", ""),
	new point(0.0, 150.0, "line", ""),
	new point(150.0, 0, "line", ""),
	new point(50.0, 0, "line", ""),
	new point(50.0, -150.0, "line", ""),
	new point(0.0, -150.0, "line", ""),
	new point(-50.0, -150.0, "line", ""),
	new point(-50.0, 50.0, "line", ""),
    ),
    "fill"),
    
    /* black triangle inside grey square with black rounded border */
    new path(array(
	new point(-48.0, -48.0, "move", "round=20 fillcolor=black"),
	new point(-48.0,  48.0, "line", ""),
	new point( 48.0,  48.0, "line", ""),
	new point( 48.0, -48.0, "line", ""),

	new point(-42.0, -42.0, "move", "round=16 fillcolor=silver"),
	new point(-42.0,  42.0, "line", ""),
	new point( 42.0,  42.0, "line", ""),
	new point( 42.0, -42.0, "line", ""),

	new point(-22.0, -27.0, "move", "fillcolor=black"),
	new point(-22.0,  27.0, "line", ""),
	new point( 24.765, 0.0, "line", ""),
	new point(-22.0, -27.0, "line", ""),
    ),
    "fill close")
);

define("PG_WIDTH", 500);
define("PG_HEIGHT", 500);
define("ROTATION_STEPS", 4);
define("ROTATION_LIMIT", 360);
define("ROTATION_STEPS_DEG", ROTATION_LIMIT / ROTATION_STEPS);

/* Dimensions for the box for a single arrow variation */
define("BOX_WIDTH", PG_WIDTH / ROTATION_STEPS);
define("BOX_HEIGHT", BOX_WIDTH);

/* Leave some space around the individual arrows */
define("BOX_MARGIN", 0.1);
define("INNER_BOX_WIDTH", (1 - (2 * BOX_MARGIN)) * BOX_WIDTH);
define("INNER_BOX_HEIGHT", (1 - (2 * BOX_MARGIN)) * BOX_HEIGHT);

/* Distribute the variations evenly across the page */
define("Y_DISPLACEMENT", 
    (PG_HEIGHT - (count($paths) * BOX_HEIGHT)) / (count($paths) + 1));

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Arrows as path objects";


try {
    $p = new pdflib();
    
    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $p->begin_page_ext(PG_WIDTH, PG_HEIGHT, "");

    for ($i = 0; $i < count($paths); $i += 1) {
	draw_path($p, $i, $paths[$i]);
    }

    $p->end_page_ext("");
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=arrow_as_path_object.pdf");
    print $buf;


} catch (PDFlibException $e){
    die("PDFlib exception occurred:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() .
        ": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}
$p=0;

/**
 * Draw a path on the page in ROTATION_STEPS rotated variations.
 * 
 * @param p
 *            The pdflib object
 * @path_number The number of the path, used to position the path vertically
 * @param current_path
 *            The path object to draw
 * 
 * @throws PDFlibException
 */
function draw_path($p, $path_number, $current_path){
    $path = 0;
    
    /* Construct path according to path operations */
    for ($i = 0; $i < count($current_path->ops); $i += 1) {
	$pt = $current_path->ops[$i];
	$path = $p->add_path_point($path,
	    $pt->x, $pt->y, $pt->type, $pt->optlist);
    }
    
    /* Draw the rotated variations of the path */
    $ypos =  ($path_number + 1) * Y_DISPLACEMENT 
			+ ($path_number + BOX_MARGIN) * BOX_HEIGHT;
    
    for ($i = 0; $i < ROTATION_STEPS; $i += 1) {
	$rotation = $i * ROTATION_STEPS_DEG;
	$xpos = $i * BOX_WIDTH + BOX_MARGIN * BOX_WIDTH;
	
	/* Draw path */
	$placement_opts = 
	    " boxsize={" . INNER_BOX_WIDTH . " " . INNER_BOX_HEIGHT 
		. "} orientate=" . $rotation . " fitmethod=meet position=center";
	$p->draw_path($path, $xpos, $ypos, $current_path->optlist . $placement_opts);
    }
    
    $p->delete_path($path);
}

?>




