<?php
/* $Id: text_with_image_clipping_path.php,v 1.3 2012/05/03 14:00:38 stm Exp $
 * 
 * Text and image clipping paths:
 * Use the clipping path from a TIFF or JPEG image to shape text output.
 * 
 * Case 1:
 *      Fit image with clipping path into the center of two text columns
 * 
 * Case 2:
 *      Wrap text inside an image clipping path.
 *      
 * Case 3:
 *      Use clipping path to flow text around it
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: image file
 */

function create_textflow($p) {
    /* Repeat the dummy text to produce more contents */
    $counter = 20;
    $optlist1 = 
	"fontname=DejaVuSerif fontsize=10.5 encoding=unicode "
	    . "fillcolor={gray 0} alignment=justify";

    $optlist2 = 
	"fontname=DejaVuSerif fontsize=10.5 encoding=unicode "
	    . "fillcolor={rgb 1 0 0} charref";

    /*
     * Dummy text for filling the columns. Soft hyphens are marked with the
     * character reference "&shy;" (character references are enabled by the
     * charref option).
     */
    $text = 
	"Lorem ipsum dolor sit amet, consectetur adi&shy;pi&shy;sicing elit, "
	. "sed do eius&shy;mod tempor incidi&shy;dunt ut labore et dolore "
	. "magna ali&shy;qua. Ut enim ad minim ve&shy;niam, quis nostrud "
	. "exer&shy;citation ull&shy;amco la&shy;bo&shy;ris nisi ut "
	. "ali&shy;quip ex ea commodo con&shy;sequat. Duis aute irure dolor "
	. "in repre&shy;henderit in voluptate velit esse cillum dolore eu "
	. "fugiat nulla pari&shy;atur. Excep&shy;teur sint occae&shy;cat "
	. "cupi&shy;datat non proident, sunt in culpa qui officia "
	. "dese&shy;runt mollit anim id est laborum. ";

    $tf = 0;
    for ($i = 1; $i <= $counter; $i++) {
	$num = $i . " ";

	$tf = $p->add_textflow($tf, $num, $optlist2);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());

	$tf = $p->add_textflow($tf, $text, $optlist1);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());
    }

    return $tf;
}

/**
 * Interface for the different use cases.
 */
interface use_case {
    public function create_page_contents($p, $tf);

    public function use_case_description();
}

/*
 * Use case 1:
 * 
 * Fit the image with clipping into the center of the text box. Fit
 * the text into the text box, and wrap it around the path retrieved
 * from the image. The path must be scaled in the same manner as the
 * image was scaled.
 */
class UseCase1 implements use_case {
    function use_case_description() {
	return "Fit image with clipping path into the center "
		. "of two text columns";
    }

    function create_page_contents($p, $tf){
	global $image, $image_llx, $image_lly, $with_clipping_opts, $path, $placed_image_llx, $placed_image_lly, $scale_option, $tf, $llx1, $lly1, $urx1, $ury1, $textflow_opts, $llx2, $lly2, $urx2, $ury2;

	$p->fit_image($image, $image_llx, $image_lly,
			$with_clipping_opts);

	$textflow_opts = "wrap={offset=5 paths={{path="
		. $path . " refpoint={" . $placed_image_llx . " "
		. $placed_image_lly . "} " . $scale_option . "}}}";

	/* Fill the first column */
	$result = $p->fit_textflow($tf, $llx1, $lly1, $urx1,
		$ury1, $textflow_opts);

	/* Fill the second column if we have more text */
	if ($result != "_stop")
	    $p->fit_textflow($tf, $llx2, $lly2, $urx2, $ury2,
		$textflow_opts);
    }
};

/*
 * Use case 2:
 * 
 * Use the inversefill option to wrap text inside the path instead
 * of wrapping the text around the path (i.e. the path serves as
 * text container instead of creating a hole in the Textflow). For
 * creating a "hole" in the image, the image is placed without
 * honoring the clipping path, and the clipping path is used to draw
 * a white area inside the image
 */
class UseCase2 implements use_case {
    function use_case_description() {
	return "Wrap text inside the clipping path of an image";
    }

    function create_page_contents($p, $tf){
	global $image, $image_llx, $image_lly, $no_clipping_opts, $path, $placed_image_llx, $placed_image_lly, $scale_option, $tf, $llx1, $lly1, $textflow_opts, $urx2, $ury2;
	$p->fit_image($image, $image_llx, $image_lly, $no_clipping_opts);

	$p->draw_path($path, $placed_image_llx, $placed_image_lly,
		"fill=true fillcolor=white " . $scale_option);

	$p->fit_textflow($tf, $llx1, $lly1, $urx2, $ury2,
		"wrap={offset=5 inversefill paths={{path=" . $path
			. " refpoint={" . $placed_image_llx . " "
			. $placed_image_lly . "} " . $scale_option
			. "}}}");
    }
};

/*
 * Use case 3:
 * 
 * Do not place the image, but use its clipping path to flow text
 * around it. The reference point for the clipping path is specified
 * relatively to the fitbox of the textflow by percentages of the
 * width and height of the fitbox in the "refpoint" suboption.
 */
class UseCase3 implements use_case {
    function use_case_description() {
	return "Use clipping path to flow text around it";
    }

    function create_page_contents($p, $tf){
	global $tf, $llx1, $lly1, $urx2, $ury2, $path, $scale_option;

	$p->fit_textflow($tf, $llx1, $lly1, $urx2, $ury2,
		"wrap={offset=5 paths={{path=" . $path
			. " refpoint={25% 25%} " . $scale_option
			. "}}}");
    }
};



$outfile = "";
$title = "Text With Image Clipping Path";
$imagefile = "child_clipped.jpg";
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "bytes");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $image = $p->load_image("auto", $imagefile, "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $path = $p->info_image($image, "clippingpath", "");
    if ($path == 0)
	throw new Exception(
		"Error: The image does not contain a clipping path");

    /*
     * Coordinates for laying out the text and the image.
     */

    /* The page dimensions */
    $a4_width = 595; $a4_height = 842;

    /* The margin at the left- and right-hand sides of the page */
    $margin = 50;

    /* The distance between the two columns */
    $column_distance = $margin / 2;

    /* Positions and sizes of the columns */
    $column_width = ($a4_width - (2 * $margin) - $column_distance) / 2;
    $column_height = $a4_height / 3;

    $llx1 = $margin; $lly1 = $a4_height / 3;
    $urx1 = $llx1 + $column_width; $ury1 = $lly1 + $column_height;

    $llx2 = $margin + $column_width + $column_distance; 
    $lly2 = $lly1;
    $urx2 = $llx2 + $column_width; $ury2 = $ury1;

    /* Size of the box that covers the two columns */
    $bbox_width = $urx2 - $llx1; 
    $bbox_height = $ury2 - $lly1;

    /*
     * The image will be centered into a box that covers a quarter of
     * the text box.
     */
    $image_width = $bbox_width / 2; 
    $image_height = $bbox_height / 2;
    $with_clipping_opts = "boxsize={" . $image_width . " "
	    . $image_height . "} " . "position=center fitmethod=meet";
    $no_clipping_opts = $with_clipping_opts . " ignoreclippingpath";

    /*
     * The position for displaying the title for the use case.
     */
    $title_llx = $llx1; $title_lly = $ury1;
    $title_urx = $title_llx + $bbox_width; 
    $title_ury = $title_lly + 100;
    /*
     * Box position for placing the image
     */
    $image_llx = $llx1 + ($bbox_width / 4);
    $image_lly = $lly1 + ($bbox_height / 4);

    /*
     * Determine reference point for the image after it was placed into
     * the center of the bounding box of the two columns.
     */
    $placed_image_llx = $image_llx + $p->info_image($image, "x1", 
	    $with_clipping_opts);
    $placed_image_lly = $image_lly + $p->info_image($image, "y1", 
	    $with_clipping_opts);

    /*
     * Determine the scaling factor for the image. "fitmethod=meet"
     * scales uniformly in x and y direction, so it is sufficient to
     * fetch one scaling factor.
     */
    $image_scale_factor = $p->info_image($image, "fitscalex",
	    $with_clipping_opts);
    $scale_option = "scale=" . $image_scale_factor;

    $use_cases = array( new UseCase1(), 
			new UseCase2(), 
			new UseCase3());

    /*
     * Create one page for each use case.
     */
    for ($i = 0; $i < count($use_cases); $i++ ) {
	$c = $use_cases[$i];

	$p->begin_page_ext($a4_width, $a4_height, "");

	/*
	 * Add a description for each use case.
	 */
	$desc = "Use case " . ($i + 1) . ": "
		. $c->use_case_description();
	$title_tf = $p->create_textflow($desc,
		"fontname=DejaVuSerif fontsize=16 encoding=unicode");
	$p->fit_textflow($title_tf, $title_llx, $title_lly, $title_urx,
		$title_ury, "");
	$p->delete_textflow($title_tf);

	/*
	 * Create a textflow and pass that to the create_page_contents()
	 * method to put the contents on the page.
	 */
	$tf = create_textflow($p);
	$c->create_page_contents($p, $tf);
	$p->delete_textflow($tf);

	$p->end_page_ext("");
    }

    $p->delete_path($path);
    $p->close_image($image);

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=text_with_image_clipping_path.pdf");
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

