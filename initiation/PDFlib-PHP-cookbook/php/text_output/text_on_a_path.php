<?php
/* $Id: text_on_a_path.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * 
 * Create text on a path
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: Image file with clipping path
 */


/**
 * Interface for the different use cases.
 */
interface use_case {
    function create_page_content($p);
    function use_case_description();
}

class useCase1 implements use_case{
    function use_case_description() {
	return "Text on Explicitly Constructed Path";
    }

    function create_page_content($p){
	global $a4_width, $a4_height, $font;
	$x = $a4_width / 2; $y = $a4_height / 2;
	$radius = 150;
	
	/* Define the path in the origin */
	$path = $p->add_path_point(0, - $radius, 0, "move", "");
	$path = $p->add_path_point($path, $radius, 0, "control", "");
	$path = $p->add_path_point($path, - $radius, 0, "circular", "");

	/* Place text on the path */
	$p->setfont($font, 36);
	$p->fit_textline(
		"Long Distance Glider with sensational range!",
		$x, $y,
		"textpath={path=" . $path . "} " .
			    "position={left bottom}");

	/* We also draw the path for demonstration purposes */
	$p->draw_path($path, $x, $y, "stroke");
	
	$p->delete_path($path);
    }
};

class useCase2 implements use_case{
    function use_case_description() {
	return "Text on Clipping Path of Image";
    }

    function create_page_content($p){
	global $a4_width, $a4_height, $font, $imagefile;
	/*
	 * Load image and retrieve its clipping path.
	 */
	$image = $p->load_image("auto", $imagefile, "");
	if ($image == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());

	$image_clipping_path = $p->info_image($image, "clippingpath", "");
	if ($image_clipping_path == 0)
	    throw new Exception(
		"Error: Image does not contain a clipping path");
	
	/*
	 * Center the image on the page, scale it to half the size
	 * of the page.
	 */
	$img_width = $a4_height / 2; 
	$img_height = $a4_width / 2;
	$box_llx = $a4_width / 2 - $img_width / 2; 
	$box_lly = $a4_height / 2 - $img_height / 2;
	
	$fit_options = "boxsize={" . $img_width . " "
		. $img_height . "} position=center fitmethod=meet";
	$p->fit_image($image, $box_llx, $box_lly, $fit_options);
	
	$image_llx = $box_llx + $p->info_image($image, "x1", $fit_options);
	$image_lly = $box_lly + $p->info_image($image, "y1", $fit_options);

	/*
	 * Determine the scaling factors for the image.
	 */
	$img_scale_factor_x = $p->info_image($image, "fitscalex", $fit_options);
	$img_scale_factor_y = $p->info_image($image, "fitscaley", $fit_options);
	$scale_option = "scale={" . $img_scale_factor_x
	    . " " . $img_scale_factor_y . "}";
	
	/* Place text on the path; we start at 23% of the
	 * path length for a nice effect.
	 */
	$p->setfont($font, 24);
	$p->fit_textline(
	    "Hi! I'm Louise!",
	    $image_llx, $image_lly,
	    "textpath={path=" . $image_clipping_path . " " 
	    . $scale_option . "} position={23 bottom} " .
	    "matchbox={boxheight={capheight descender}}");
	
	$p->delete_path($image_clipping_path);
	$p->close_image($image);
    }
};

class useCase3 implements use_case{
    function use_case_description() {
	return "Create a Gap Between Text and Path";
    }

    function create_page_content($p){
	global $a4_width, $a4_height, $font;
	/*
	 * Create a path defined by Bezier curves. The path extends
	 * through a box that is half the size of the page. The box
	 * is centered on the page.
	 */
	$box_center_x = $a4_width / 2;
	$box_center_y = $a4_height / 2;
	$box_width = $box_center_y; 
	$box_height = $box_center_x;
	$box_llx = $box_center_x - $box_width / 2; 
	$box_lly = $box_center_y - $box_height / 2;
	
	/*
	 * The path is defined through the implicit point at (0, 0)
	 * and six additional control points
	 */
	$step = $box_width / 6;
	$path = $p->add_path_point(0, $step * 1, 
		    $box_height / 2, "control", "");
	$path = $p->add_path_point($path, $step * 2, 
		    $box_height / 2, "control", "");
	$path = $p->add_path_point($path, $step * 3, 
		    $box_height / 2, "curve", "");
	$path = $p->add_path_point($path, $step * 4, 
		    $box_height / 2, "control", "");
	$path = $p->add_path_point($path, $step * 5, 
		    $box_height / 2, "control", "");
	$path = $p->add_path_point($path, $step * 6, 
		    $box_height, "curve", "");
	
	/* Place text on the path */
	$p->setfont($font, 24);
	$p->fit_textline(
	    "Long Distance Glider with sensational range!",
	    $box_llx, $box_lly,
	    "textpath={path=" . $path . "} position={center bottom} "
	    . "matchbox={boxheight={capheight descender}}");
	
	/* We also draw the path for demonstration purposes */
	$p->draw_path($path, $box_llx, $box_lly, "stroke");
	
	$p->delete_path($path);
    }
};

class useCase4 implements use_case{
    function use_case_description() {
	return "Place Text on Right Side of Path";
    }

    function create_page_content($p){
	global $a4_width, $a4_height, $font;
	/*
	 * Create a path defined by Bezier curves. The path extends
	 * through a box that is half the size of the page. The box
	 * is centered on the page.
	 */
	$box_center_x = $a4_width / 2;
	$box_center_y = $a4_height / 2;
	$box_width = $box_center_y; 
	$box_height = $box_center_x;
	$box_llx = $box_center_x - $box_width / 2; 
	$box_lly = $box_center_y - $box_height / 2;
	
	/*
	 * The path this time starts at the upper left corner of the
	 * box.
	 */
	$step = $box_width / 6;
	$path = $p->add_path_point(0, $step * 0, $box_height, 
				    "move", "");
	$path = $p->add_path_point($path, $step * 1, $box_height / 2, 
				    "control", "");
	$path = $p->add_path_point($path, $step * 2, $box_height / 2, 
				    "control", "");
	$path = $p->add_path_point($path, $step * 3, $box_height / 2, 
				    "curve", "");
	$path = $p->add_path_point($path, $step * 4, $box_height / 2, 
				    "control", "");
	$path = $p->add_path_point($path, $step * 5, $box_height / 2, 
				    "control", "");
	$path = $p->add_path_point($path, $step * 6, 0, "curve", "");
	
	/* Place text on the path */
	$p->setfont($font, 24);
	$p->fit_textline(
	    "Long Distance Glider with sensational range!",
	    $box_llx, $box_lly,
	    "textpath={path=" . $path . "} position={center top}");
	
	/* We also draw the path for demonstration purposes */
	$p->draw_path($path, $box_llx, $box_lly, "stroke");
	
	$p->delete_path($path);
    }
};
	

/**
 * Execute the example.
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$imagefile = "luise.jpg";
$title = "Text on a Path";

/* The page dimensions */
$a4_width = 595; $a4_height = 842;

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $use_cases = array(
	new UseCase1(),
	new UseCase2(),
	new UseCase3(),
	new UseCase4());
    
    /*
     * Place the use cases.
     */
    $headline_x = $a4_width / 2; 
    $headline_y = $a4_height * 4 / 5;
    
    for ($i = 0; $i < count($use_cases); $i += 1) {
	$c = $use_cases[$i];
	
	$p->begin_page_ext($a4_width, $a4_height, "");

	/*
	 * Options for the per-example header line
	 */
	$optlist = "position=center";
	$p->setfont($font, 30);
	$p->fit_textline($c->use_case_description(), $headline_x, $headline_y,
			$optlist);
	
	$c->create_page_content($p);

	$p->end_page_ext("");
    }
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=frame_around_image.pdf");
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
