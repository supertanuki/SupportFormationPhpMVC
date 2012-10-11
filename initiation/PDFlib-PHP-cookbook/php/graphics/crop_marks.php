<?php
/**
 * Crop marks.
 * 
 * Create crop marks which specify the "cutting area". The crop marks are
 * adjusted to the actual trim box of the PDF page.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: none
 * 
 * @version $Id: crop_marks.php,v 1.3 2012/05/03 14:00:37 stm Exp $
 */

/**
 * The width of the trim box.
 */
define("TRIMBOX_WIDTH", 595);

/**
 * The height of the trim box;
 */
define("TRIMBOX_HEIGHT", 842);

/**
 * Margin in x direction around trim box (at right and left)
 */
define("TRIMBOX_X_MARGIN", 80);

/**
 * Margin in y direction around trim box (at top and bottom)
 */
define("TRIMBOX_Y_MARGIN", 100);

/**
 * The gap from the crop mark to the corner of the trimbox
 */
define("CROP_MARK_GAP_TO_CORNER", 20);

/**
 * The number of triangles per triangled circle.
 */
define("NUM_TRIANGLES", 36);

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Crop Marks";


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    $p->set_parameter("errorpolicy", "exception");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " + $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /*
     * Construct the values for the height, width, mediabox and
     * trimbox constants.
     */
    $trimbox_llx = TRIMBOX_X_MARGIN;
    $trimbox_lly = TRIMBOX_Y_MARGIN;
    $trimbox_urx = $trimbox_llx + TRIMBOX_WIDTH;
    $trimbox_ury = $trimbox_lly + TRIMBOX_HEIGHT;
    $mediabox_width = TRIMBOX_WIDTH + 2 * TRIMBOX_X_MARGIN;
    $mediabox_height = TRIMBOX_HEIGHT + 2 * TRIMBOX_Y_MARGIN;

    $p->begin_page_ext(0, 0, "width=" . $mediabox_width . " height="
	    . $mediabox_height . " trimbox={" . $trimbox_llx . " "
	    . $trimbox_lly . " " . $trimbox_urx . " " . $trimbox_ury . "}");

    /*
     * Compute the length of the crop mark lines. As the margin around
     * the trim box is not necessarily equally large in x and y
     * direction, the smaller one takes precedence.
     */
    $crop_mark_x_length = TRIMBOX_X_MARGIN - 2 * CROP_MARK_GAP_TO_CORNER;
    $crop_mark_y_length = TRIMBOX_Y_MARGIN - 2 * CROP_MARK_GAP_TO_CORNER;
    $crop_mark_length = min($crop_mark_x_length, $crop_mark_y_length);


    /*
     * Create a path with the two lines for the crop mark
     */
    $crop_mark = 0;
    $crop_mark = $p->add_path_point($crop_mark, 0,
	    - CROP_MARK_GAP_TO_CORNER, "move",
	    "stroke nofill strokecolor={gray 0}");
    $crop_mark = $p->add_path_point($crop_mark, 0, -$crop_mark_length
	    - CROP_MARK_GAP_TO_CORNER, "line", "");
    $crop_mark = $p->add_path_point($crop_mark, -CROP_MARK_GAP_TO_CORNER,
	    0, "move", "stroke nofill strokecolor={gray 0}");
    $crop_mark = $p->add_path_point($crop_mark, -$crop_mark_length
	    - CROP_MARK_GAP_TO_CORNER, 0, "line", "");

    /*
     * The length of the crop mark line is 3 times the radius of
     * crop mark outer circle.
     */
    $crop_mark_radius = $crop_mark_length / 3;

    $registration_mark = create_registration_mark($p, $crop_mark_radius);
    $circle_with_rays = create_circle_with_rays($p, $crop_mark_radius);

    /*
     * Draw the crop marks at the four corners of the trim box.
     */
    $crop_mark_displacement = CROP_MARK_GAP_TO_CORNER
	    + $crop_mark_length / 2;

    for ($step = 0; $step < 4; $step++) {
	$x = TRIMBOX_X_MARGIN + intval((($step +1) % 4) / 2) * TRIMBOX_WIDTH;
	$y = TRIMBOX_Y_MARGIN + intval($step / 2) * TRIMBOX_HEIGHT;
	draw_corner($p, $step * 90, $x, $y, $crop_mark_displacement, 
		$crop_mark, $registration_mark, $circle_with_rays,
		($step + 1) % 2 == 0);
    }

    /*
     * Construct the shading bars.
     */
    $color_field_size = 2 * $crop_mark_radius;

    $colors = array( "{cmyk 0 0 0 1}", "{cmyk 1 1 1 0}",
	    "{cmyk 1 0 1 0}", "{cmyk 0 1 1 0}", "{cmyk 1 1 0 0}",
	    "{cmyk 0 0 1 0}", "{cmyk 0 1 0 0}", "{cmyk 1 0 0 0}" );

    $color_boxes = create_shading_bar($p, $color_field_size, $colors, true);

    $gray_shades = array( "{gray 0.1}", "{gray 0.2}",
	    "{gray 0.3}", "{gray 0.4}", "{gray 0.5}", "{gray 0.6}",
	    "{gray 0.7}", "{gray 0.8}", "{gray 0.9}", "{gray 1}", );

    $grayscale_boxes = create_shading_bar($p, $color_field_size,
	    $gray_shades, false);

    /*
     * Position color boxes aligned to the center of the horizontal crop
     * marks.
     */
    $p->draw_path($color_boxes,
	    TRIMBOX_X_MARGIN - CROP_MARK_GAP_TO_CORNER
		- $crop_mark_length / 2 - $color_field_size / 2,
	    TRIMBOX_Y_MARGIN + TRIMBOX_HEIGHT / 2 - count($colors) / 2
		    * $color_field_size,
	    "fill stroke");
    $p->draw_path($color_boxes,
	    TRIMBOX_X_MARGIN + TRIMBOX_WIDTH + CROP_MARK_GAP_TO_CORNER
		+ $crop_mark_length / 2 - $color_field_size / 2,
	    TRIMBOX_Y_MARGIN + TRIMBOX_HEIGHT / 2 
		- count($colors) / 2 * $color_field_size,
	    "fill stroke");

    /*
     * Position grayscale bar at the bottom aligned to the center of the
     * vertical crop marks.
     */
    $p->draw_path($grayscale_boxes,
	TRIMBOX_X_MARGIN + TRIMBOX_WIDTH / 2
	    - count($gray_shades) / 2 * $color_field_size,
	TRIMBOX_Y_MARGIN - CROP_MARK_GAP_TO_CORNER
	    - $crop_mark_length / 2 + $color_field_size / 2,
	"fill stroke orientate=270");

    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=crop_marks.pdf");
    print $buf;

}

catch (PDFlibException $e) {
    die("PDFlib exception occurred in crop_marks sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;

/**
 * Create a path object for a circle with rays towards the center.
 * 
 * @param p
 *            the pdflib object
 * @param radius
 *            the radius of the circle
 * @return a handle for the newly created path object
 * 
 */
function create_circle_with_rays($p, $radius){
    /*
     * Circle with rays to the center.
     */
    $circle_with_rays = 0;
    $circle_with_rays = $p->add_path_point($circle_with_rays,
	    -$radius, 0, "move",
	    "nofill stroke strokecolor={gray 0}");
    $circle_with_rays = $p->add_path_point($circle_with_rays,
	    $radius, 0, "control", "");
    $circle_with_rays = $p->add_path_point($circle_with_rays,
	    -$radius, 0, "circular", "");

    /*
     * The inner circle that remains open is 1/10 the size of the outer
     * circle.
     */
    $inner_radius = $radius / 10;
    $angle_step = 2 * M_PI / NUM_TRIANGLES;
    /*
     * Construct the triangles easily by using polar coordinates.
     */
    for ($step = 0; $step < NUM_TRIANGLES; $step += 1) {
	/*
	 * Move to inner vertex of arc segment.
	 */
	$circle_with_rays = $p->add_path_point($circle_with_rays, 
	    $inner_radius, $step * $angle_step, "move",
	    "fill nostroke close fillcolor={gray 0} polar radians");

	/*
	 * Construct an arc segment.
	 */
	$circle_with_rays = $p->add_path_point($circle_with_rays,
	    $radius, $step * $angle_step - $angle_step / 4,
	    "line", "polar radians");
	$circle_with_rays = $p->add_path_point($circle_with_rays,
	    $radius, $step * $angle_step,
	    "control", "polar radians");
	$circle_with_rays = $p->add_path_point($circle_with_rays,
	    $radius, $step * $angle_step + $angle_step / 4,
	    "circular", "polar radians");
    }
    
    return $circle_with_rays;
}

/**
 * Create a path object for a registration mark.
 * 
 * @param p
 *            the pdflib object
 * @param radius
 *            the radius of the crop mark
 * 
 * @return a handle for the new path object
 * 
 */
function create_registration_mark($p, $radius){
    $registration_mark = 0;

    /*
     * Long black lines
     */
    for ($step = 0; $step < 2; $step += 1) {
	$registration_mark = $p->add_path_point($registration_mark,
		$radius, $step * 90,
		"move", "stroke nofill strokecolor={gray 0} polar");
	$registration_mark = $p->add_path_point($registration_mark,
		$radius, ($step + 2) * 90, "line", "polar");
    }

    /*
     * Inner circle
     */
    $registration_mark = $p->add_path_point($registration_mark,
	    -$radius / 3, 0, "move",
	    "fill nostroke strokecolor={gray 0} fillcolor={gray 0}");
    $registration_mark = $p->add_path_point($registration_mark,
	    $radius / 3, 0, "control", "");
    $registration_mark = $p->add_path_point($registration_mark,
	    -$radius / 3, 0, "circular", "");

    /*
     * Short white lines
     */
    for ($step = 0; $step < 2; $step += 1) {
	$registration_mark =
	    $p->add_path_point($registration_mark, $radius / 3, $step * 90,
		"move", "stroke nofill strokecolor={gray 1} polar");
	$registration_mark = $p->add_path_point($registration_mark,
		$radius / 3, ($step + 2) * 90, "line", "polar");
    }

    /*
     * Outer circle
     */
    $registration_mark = $p->add_path_point($registration_mark, -2
	    * $radius / 3, 0, "move",
	    "stroke nofill strokecolor={gray 0}");
    $registration_mark = $p->add_path_point($registration_mark,
	    2 * $radius / 3, 0, "control", "");
    $registration_mark = $p->add_path_point($registration_mark, -2
	    * $radius / 3, 0, "circular", "");
    
    return $registration_mark;
}

/**
 * Create a shading bar as a path object with colored boxes.
 * 
 * @param p
 *            the pdflib object
 * @param color_field_size
 *            size of the square color boxes
 * @param colors
 *            array of PDFlib color names
 * @param stroke
 *            whether to stroke the boxes with a black border
 *            
 * @return the path handle for the shading bar
 * 
 */
function create_shading_bar($p, $color_field_size, $colors, $stroke) {
    $shading_bar = 0;
    for ($step = 0; $step < count($colors); $step += 1) {
	$lly = $step * $color_field_size;
	$stroke_opt = $stroke ? "stroke" : "nostroke";
	$shading_bar = $p->add_path_point($shading_bar, 0, $lly, "move",
		$stroke_opt . " fill close strokecolor={gray 0} fillcolor="
			. $colors[$step]);
	$shading_bar = $p->add_path_point($shading_bar, 0, $lly
		+ $color_field_size, "line", "");
	$shading_bar = $p->add_path_point($shading_bar, $color_field_size, $lly
		+ $color_field_size, "line", "");
	$shading_bar = $p->add_path_point($shading_bar, $color_field_size, $lly,
		"line", "");
    }
    return $shading_bar;
}

/**
 * Create the crop marks and other graphic elements for one corner.
 * 
 * @param p
 *            the pdflib object
 * @param angle
 *            the rotation of the graphic elements
 * @param x
 *            the x coordinate
 * @param y
 *            the y coordinate
 * @param crop_mark_displacement
 *            displacement of the crop marks relative to the corner
 * @param crop_mark
 *            path handle for the crop mark
 * @param registration_mark
 *            path handle for the registration mark
 * @param circle_with_rays
 *            path handle for the circle containing rays
 * @param draw_circle_with_rays
 *            whether to draw the triangled circle
 * 
 */
function draw_corner($p, $angle, $x, $y, $crop_mark_displacement, 
	$crop_mark, $registration_mark, $circle_with_rays,
	$draw_circle_with_rays){
    $p->save();
    $p->translate($x, $y);
    $p->rotate($angle);
    $p->draw_path($crop_mark, 0, 0, "fill stroke");
    $p->draw_path($registration_mark, -$crop_mark_displacement,
	    $crop_mark_displacement, "fill stroke");
    $p->draw_path($registration_mark, $crop_mark_displacement,
	    -$crop_mark_displacement, "fill stroke");
    if ($draw_circle_with_rays) {
	$p->draw_path($circle_with_rays, -$crop_mark_displacement,
		-$crop_mark_displacement, "fill stroke");
    }
    $p->restore();
}

