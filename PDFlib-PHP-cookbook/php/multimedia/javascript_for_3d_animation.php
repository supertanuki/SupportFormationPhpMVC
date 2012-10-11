<?php
/**
 * JavaScript for 3D animation: Load a PRC 3D model and animate it with 
 * JavaScript.
 *
 * Define a 3D view and load some 3D data with the view defined. JavaScript 
 * code animates the model by using a TimeEventHandler object.
 *
 * Acrobat 9 or above is required for viewing PDF documents containing a
 * PRC 3D model.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8.0.3
 * Required data: PRC data file
 * 
 * @version $Id: javascript_for_3d_animation.php,v 1.2 2012/05/03 14:00:42 stm Exp $
 */

/**
 * JavaScript code for rotating the model around the z axis.
 */
$JS_ANIMATION =
    "scene.lightScheme = scene.LIGHT_MODE_DAY;\n"
    . "var myTimeHandler = new TimeEventHandler();\n"
    . "myTimeHandler.onEvent = function(event)\n"
    . "{\n"
	. "var mesh = scene.meshes.getByIndex(0);\n"
	. "mesh.transform.rotateAboutZInPlace(0.02);\n"
    . "}\n"
    . "runtime.addEventHandler(myTimeHandler);\n";

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "JavaScript for 3D animation";

/* Required minimum PDFlib version */
$requiredversion = 803;
$requiredvstr = "8.0.3";


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0);
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);

    if ($major * 100 + $minor * 10 + $revision < $requiredversion)
	throw new Exception("Error: PDFlib " . $requiredvstr
	    . " or above is required");

    /* Start the document */
    if ($p->begin_document($outfile, "compatibility=1.7ext3") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Create a 3D view that shows the whole model.
     */
    $optlist = "type=PRC background={fillcolor=mediumslateblue} "
	. "camera2world={-1 0 0 "
			. "0 1 0.00157 "
			. "0 0.00157 -1 "
			. "0 -2.47457 332.5437}";
    if (($view = $p->create_3dview("Default", $optlist)) == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    make_3d_page($p, $font, $view, "Animate model with JavaScript",
				$JS_ANIMATION);

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=javascript_for_3d_animation.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;



/**
 * Display the 3D annotation and the JavaScript to animate the model
 * side-by-side on a landscape PDF page.
 * 
 * @param p
 *            PDFlib object
 * @param data
 *            3D data
 * @param view
 *            3D default view
 * @param font
 *            font for displaying the JavaScript code
 * @param title
 *            title to display above the JavaScript code
 * @param javascript
 *            JavaScript code
 * 
 * @throws PDFlibException
 * @throws Exception
 */
function make_3d_page($p, $font, $view, $title, $javascript) {
    /**
     * Page width in points (landscape).
     */
    $WIDTH = 842;

    /**
     * Page height in points (landscape).
     */
    $HEIGHT = 595;

    /**
     * Margin around textflow and 3D annotation.
     */
    $MARGIN = 50;

    $optlist;
    $tf = 0;
    $element_width = ($WIDTH - 3 * $MARGIN) / 2;
    $element_height = $HEIGHT - 2 * $MARGIN;
    $tf_xpos = $MARGIN;
    $tf_ypos = $MARGIN;
    $tf_width = $element_width;
    $_3d_xpos = 2 * $MARGIN + $element_width;
    $_3d_ypos = $MARGIN;
    $_3d_width = $element_width;
    
    $optlist = "font=" . $font . " fontsize=14 underline=true";
    $tf = $p->add_textflow($tf, $title . "\n\n", $optlist);
    $optlist = "font=" . $font . " fontsize=12 underline=false";
    $tf = $p->add_textflow($tf, "JavaScript code:\n\n", $optlist);
    $tf = $p->add_textflow($tf, $javascript, "");
    
    $p->begin_page_ext($WIDTH, $HEIGHT, "");

    /* Create a bookmark for jumping to this page */
    $p->create_bookmark($title, "");
    
    $p->fit_textflow($tf, $tf_xpos, $tf_ypos,
	    $tf_xpos + $tf_width, $tf_ypos + $element_height,
	    "fitmethod=auto");
    
    /*
     * Load 3D data with the view defined above and with the JavaScript
     * for animation.
     */
    $data = $p->load_3ddata("riemann.prc", "type=PRC views={" . $view . "} "
	. "script={" . $javascript . "}");
    if ($data == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /*
     * Create an annotation containing the loaded 3D data with the
     * defined 3D view as the initial view
     */
    $optlist = "contents=PRC 3Ddata= " . $data . " "
	. "3Dactivate={enable=open} 3Dinitialview=" . $view;
    $p->create_annotation($_3d_xpos, $_3d_ypos, 
	$_3d_xpos + $_3d_width, $_3d_ypos + $element_height, "3D",
	$optlist);

    $p->end_page_ext("");
}
?>
