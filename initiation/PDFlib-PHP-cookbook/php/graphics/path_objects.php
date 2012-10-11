<?php
/* $Id: path_objects.php,v 1.2 2012/05/03 14:00:37 stm Exp $
 * 
 * Use path objects:
 * Create various shapes with path objects
 * 
 * Build a table that shows the shapes together with the corresponding 
 * add_path_point() and draw_point() calls.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 */

/**
 * Margin to add around the path in a table cell
 */
define("PATH_BOX_MARGIN",  0.1);

/**
 * Interface for the different use cases.
 */
interface use_case {
    function create_example_path_desc($p);
    function use_case_description();
}

/**
 * Class for describing path construction operations.
 */
class add_path_point_op {
    function add_path_point_op($x, $y, $keyword, $options) {
	$this->x = $x;
	$this->y = $y;
	$this->keyword = $keyword;
	$this->options = $options;
    }
}

/**
 * Class that encapsulates a path handle and the operations that it
 * created.
 */
class path_desc {
    function path_desc($p, $path_point_ops, $draw_options){
	$this->path_point_ops = $path_point_ops;
	$this->draw_path_options = $draw_options;
	//$path_desc_iterator = $path_point_ops->iterator();
	
	$tf_options = "fontname=Helvetica encoding=unicode fontsize=10";

	$this->path = 0;
	$this->textflow = 0;
	//while ($path_desc_iterator->hasNext()) 
	foreach($path_point_ops as $op ){
	    //$op = $path_desc_iterator->next();
	    $this->path = $p->add_path_point($this->path, $op->x, $op->y, $op->keyword, $op->options);
	    
	    $op_text = 
		sprintf("add_path_point(path, %.2f, %.2f, \"%s\", \"%s\")\n",
		$op->x, $op->y, $op->keyword, $op->options);
	    $this->textflow = $p->add_textflow($this->textflow, $op_text, $tf_options);
	}
	$this->textflow = $p->add_textflow($this->textflow, 
		    "draw_path(path, x, y, \"" . $draw_options . "\")\n",
		    $tf_options);
    }
    
}

class UseCase1 implements use_case{
    function use_case_description() {
	return "Circle";
    }

    function create_example_path_desc($p) {
	    $radius = 50;
	    $x = 50; $y = 50;
	    
	$ops = array(
	new add_path_point_op($x - $radius, $y, "move", ""),
	new add_path_point_op($x + $radius, $y, "control", ""),
	new add_path_point_op($x - $radius, $y, "circular", ""));
	
	return new path_desc($p, $ops, "stroke");
    }
}
    
class UseCase2 implements use_case{
    function use_case_description() {
	return "Rectangle";
    }

    function create_example_path_desc($p) {
	$rect_height = 50; $rect_width = 100;
	
	$ops = array(
	/*
	 * Build the rectangle. It implicitly starts at (0, 0).
	 */
	new add_path_point_op(0, $rect_height, "line", ""),
	new add_path_point_op($rect_width, $rect_height, "line", ""),
	new add_path_point_op($rect_width, 0, "line", ""));
	
	return new path_desc($p, $ops, "stroke close");
    }
}

    
class UseCase3 implements use_case{
    function use_case_description() {
	return "Rectangle With Inbound Rounded Corners";
    }

    function create_example_path_desc($p) {
	$rect_height = 50; 
	$rect_width = 100;
	$line_width = 2;
	$round_radius = -5;
	
	$ops = array(
	/*
	 * Build the rectangle. It implicitly starts at (0, 0).
	 */
	new add_path_point_op(0, $rect_height, "line", ""),
	new add_path_point_op($rect_width, $rect_height, "line", ""),
	new add_path_point_op($rect_width, 0, "line", ""));
	
	return new path_desc($p, $ops, 
		    "stroke close"
		    . " round=" . $round_radius
		    . " linewidth=" . $line_width);
    }
}

class UseCase4 implements use_case{
    function use_case_description() {
	return "Triangle";
    }

    function create_example_path_desc($p) {
	$edge_length = 100;
	$height =
	    $edge_length * sin(deg2rad(60));
	
	$ops = array(
	/*
	 * Build the rectangle. It implicitly starts at (0, 0).
	 */
	new add_path_point_op($edge_length / 2, $height, "line", ""),
	new add_path_point_op($edge_length, 0, "line", ""));
	
	return new path_desc($p, $ops, "stroke close");
    }
}

class UseCase5 implements use_case{
    function use_case_description() {
	return "Triangle With Rounded Corners";
    }

    function create_example_path_desc($p) {
	$edge_length = 100;
	$height = $edge_length * sin(deg2rad(60));
	$line_width = 10;
	$round_radius = 10;
	
	$ops = array(
	/*
	 * Build the rectangle. It implicitly starts at (0, 0).
	 */
	new add_path_point_op($edge_length / 2, $height, "line", ""),
	new add_path_point_op($edge_length, 0, "line", ""));
	
	return new path_desc($p, $ops, 
		    "stroke close strokecolor=red"
		    . " round=" . $round_radius 
		    . " linewidth=" . $line_width);
    }
}

class UseCase6 implements use_case{
    function use_case_description() {
	return "B\xe9zier Segment With Four Control Points";
    }

    function create_example_path_desc($p) {
	$ops = array(
	/*
	 * Specify the control points. The first control point 
	 * (0, 0) is implicit.
	 */
	new add_path_point_op(100, 50, "control", ""),
	new add_path_point_op(100, 100, "control", ""),
	new add_path_point_op(0, 100, "curve", ""));
	
	return new path_desc($p, $ops, "stroke");
    }
}

class UseCase7 implements use_case{
    function use_case_description() {
	return "Two B\xe9zier Segments Joined Automatically";
    }

    function create_example_path_desc($p) {
	$y_ctrl_delta = 10;
	$x_ctrl_width = 100;
	
	$ops = array(
	/*
	 * Specify the control points. The first control point 
	 * (0, 0) is implicit.
	 */
	new add_path_point_op(
		    $x_ctrl_width, 1 * $y_ctrl_delta, "control", ""),
	new add_path_point_op(
		    $x_ctrl_width, 2 * $y_ctrl_delta, "control", ""),
	new add_path_point_op(
		    $x_ctrl_width / 2, 3 * $y_ctrl_delta, "curve", ""),
	new add_path_point_op(
		    0, 4 * $y_ctrl_delta, "control", ""),
	new add_path_point_op(
		    0, 5 * $y_ctrl_delta, "control", ""),
	new add_path_point_op(
		    $x_ctrl_width, 6 * $y_ctrl_delta, "curve", ""));
	
	return new path_desc($p, $ops, "stroke");
    }
}            

class UseCase8 implements use_case{
    function use_case_description() {
	return "Circular Arc Segment with Cartesian Coordinates";
    }

    function create_example_path_desc($p) {
	$radius = 100;
	$angle = deg2rad(45);
	$control_1_x = $radius * cos($angle);
	$control_1_y = $radius * sin($angle);
	$control_2_x = $radius * cos($angle / 2);
	$control_2_y = $radius * sin($angle / 2);
	$control_3_x = $radius * cos(0);
	$control_3_y = $radius * sin(0);
	
	$ops = array(
	
	/*
	 * Build the arc segment. It implicitly starts at (0, 0).
	 */
	new add_path_point_op($control_1_x,
				    $control_1_y, "line", ""),
	new add_path_point_op($control_2_x, 
				    $control_2_y, "control", ""),
	new add_path_point_op($control_3_x, 
				    $control_3_y, "circular", ""),
	new add_path_point_op(0, 0, "line", ""));
	
	return new path_desc($p, $ops, "stroke fill");
    }
}

class UseCase9 implements use_case{
    function use_case_description() {
	return "Circular Arc Segment with Polar Coordinates";
    }

    /* Arc segments can be expressed much easier with
     * Polar Coordinates.
     */
    function create_example_path_desc($p) {
	$radius = 100;
	$angle = 45;
	
	$ops = array(
	
	/*
	 * Build the arc segment. It implicitly starts at (0, 0).
	 */
	new add_path_point_op($radius, $angle, "line", 
					"polar=true"),
	new add_path_point_op($radius, $angle / 2, "control", 
					"polar=true"),
	new add_path_point_op($radius, 0, "circular",
					"polar=true"),
	new add_path_point_op(0, 0, "line", "polar=true"));
	
	return new path_desc($p, $ops, "stroke fill");
    }
}



/**
 * Execute the example.
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Path Objects";

/* The page dimensions */
$a4_width = 595; $a4_height = 842;

/* Parameters for placing the table containing the path examples */
$margin = 50;
$tbl_llx = $margin; $tbl_lly = $margin; 
$tbl_urx = $a4_width - $margin; 
$tbl_ury = $a4_height - $margin;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $tbl = 0;
    
    $use_cases = array(
	new UseCase1(),
	new UseCase2(),
	new UseCase3(),
	new UseCase4(),
	new UseCase5(),
	new UseCase6(),
	new UseCase7(),
	new UseCase8(),
	new UseCase9());
    
    
    /*
     * Loop over the list of use cases, and for each create a table
     * cell with a descriptive header, and fit the path into the next
     * cell.
     */
    for ($i = 0; $i < count($use_cases); $i += 1) {
	$row = $i * 2 + 1;
	
	$c = $use_cases[$i];
	
	$row_group = "row_" . $i;
	
	$optlist = 
	    "fittextline={position=center font=" . $font 
	    . " fontsize=14} colspan=2 rowjoingroup=" . $row_group;

	$tbl = $p->add_table_cell($tbl, 1, $row, $c->use_case_description(),
		$optlist);
	if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());
	
	$desc = $c->create_example_path_desc($p);
	
	/*
	 * Remember path description for later deletion of path and
	 * textflow handles.
	 */
	$path_descs[] = $desc;
	
	/*
	 * Determine the height of the path object, and add a margin
	 * of 10% at top and bottom to calculate the height of the cell.
	 */
	$row_height = $p->info_path($desc->path, "height", "")
				    * (1 + 2 * PATH_BOX_MARGIN);
	
	/*
	 * Add a cell with the textflow that describe the operations
	 * that created the path.
	 */
	$p->add_table_cell($tbl, 1, $row + 1, "", 
		    "margin=5% textflow=" . $desc->textflow 
		    . " colwidth=65% rowjoingroup=" . $row_group);
	
	/*
	 * Add the path to a table cell.
	 */
	$p->add_table_cell($tbl, 2, $row + 1, "", 
		"path=" . $desc->path 
		. " fitpath={" . $desc->draw_path_options 
		. " fitmethod=nofit}"
		. " rowheight=" . $row_height
		. " colwidth=35%"
		. " rowjoingroup=" . $row_group);
    }
    
    /*
     * Place the table.
     */
    do {
	$p->begin_page_ext($a4_width, $a4_height, "");

	/*
	 * Options for the per-example header line
	 */
	$optlist = 
	    "fill={{area=rowodd fillcolor={gray 0.9}}} "
		. "stroke={{line=other}} ";

	/* Place the table instance */
	$result = $p->fit_table($tbl, $tbl_llx, $tbl_lly, $tbl_urx, $tbl_ury,
			    $optlist);

	if ($result == "_error")
	    throw new Exception("Couldn't place table : "
		    . $p->get_errmsg());
	
	$p->end_page_ext("");
    }
    while ($result == "_boxfull");
    
   
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=path_objects.pdf");
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

