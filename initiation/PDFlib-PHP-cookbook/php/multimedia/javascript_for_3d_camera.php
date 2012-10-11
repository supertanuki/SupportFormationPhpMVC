<?php
/**
 * JavaScript for 3D camera: Load a PRC 3D model and align camera with 
 * JavaScript.
 *
 * Define a 3D view and load some 3D data with the view defined. JavaScript 
 * code positions the camera so its viewing vector is parallel to one of the 
 * x, y, z axes, that it looks towards the center of the bounding box, that a 
 * specific axis points upwards, and that the model is fully viewable.
 *
 * Acrobat 9 or above is required for viewing PDF documents containing a
 * PRC 3D model.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8.0.3
 * Required data: PRC data file
 * 
 * @version $Id: javascript_for_3d_camera.php,v 1.2 2012/05/03 14:00:42 stm Exp $
 */
/**
 * JavaScript code for viewing the model along the x axis.
 */
$JS_X_AXIS =
    "scene.lightScheme = scene.LIGHT_MODE_DAY;\n"
    
    /*
     * Retrieve bounding box and compute maximum extension of the plane
     * that is looked onto.
     */
    . "var bbox = scene.computeBoundingBox();\n"
    . "var zext = bbox.max.z - bbox.min.z;\n"
    . "var yext = bbox.max.y - bbox.min.y;\n"
    . "var maxext = Math.max(zext, yext);\n"
    
    /*
     * Compute the distance from the bounding box center. This formula was
     * derived heuristically.
     */
    . "var distance = bbox.center.x - bbox.min.x + 3 * maxext;\n"
    
    /*
     * Compute the camera position by adding the distance in the desired
     * direction to the bounding box center.
     */
    . "var cameraOffset = new Vector3(distance, 0, 0);\n"
    . "var cameraPos = new Vector3(bbox.center);\n"
    . "cameraPos.addInPlace(cameraOffset);\n"
    . "var activeCamera = scene.cameras.getByIndex(0);\n"
    
    /*
     * The "up" plane of the camera is determined by pointing towards
     * the viewing direction and by adding an offset to the axis that
     * shall point upwards (the z axis in this case).
     */
    . "activeCamera.up.set(bbox.center.x, bbox.center.y, "
			. "bbox.center.z + distance);\n"
    
    /*
     * Move the camera to the computed position and point it to the target
     * position.
     */
    . "activeCamera.position.set(cameraPos);\n"
    . "activeCamera.targetPosition.set(bbox.center);\n"
    . "scene.update();";

/**
 * JavaScript code for viewing the model along the y axis.
 */
$JS_Y_AXIS =
    "scene.lightScheme = scene.LIGHT_MODE_DAY;\n"
    . "var bbox = scene.computeBoundingBox();\n"
    . "var zext = bbox.max.z - bbox.min.z;\n"
    . "var xext = bbox.max.x - bbox.min.x;\n"
    . "var maxext = Math.max(zext, xext);\n"
    . "var distance = bbox.center.y - bbox.min.y + 3 * maxext;\n"
    . "var cameraOffset = new Vector3(0, distance, 0);\n"
    . "var cameraPos = new Vector3(bbox.center);\n"
    . "cameraPos.addInPlace(cameraOffset);\n"
    . "var activeCamera = scene.cameras.getByIndex(0);\n"
    . "activeCamera.up.set(bbox.center.x, bbox.center.y, "
			. "bbox.center.z + distance);\n"
    . "activeCamera.position.set(cameraPos);\n"
    . "activeCamera.targetPosition.set(bbox.center);\n"
    . "scene.update();";

/**
 * JavaScript code for viewing the model along the z axis.
 */
$JS_Z_AXIS =
    "scene.lightScheme = scene.LIGHT_MODE_DAY;\n"
    . "var bbox = scene.computeBoundingBox();\n"
    . "var xext = bbox.max.x - bbox.min.x;\n"
    . "var yext = bbox.max.y - bbox.min.y;\n"
    . "var maxext = Math.max(xext, yext);\n"
    . "var distance = bbox.center.z - bbox.min.z + 3 * maxext;\n"
    . "var cameraOffset = new Vector3(0, 0, distance);\n"
    . "var cameraPos = new Vector3(bbox.center);\n"
    . "cameraPos.addInPlace(cameraOffset);\n"
    . "var activeCamera = scene.cameras.getByIndex(0);\n"
    . "activeCamera.up.set(bbox.center.x, bbox.center.y + distance, "
				. "bbox.center.z);\n"
    . "activeCamera.position.set(cameraPos);\n"
    . "activeCamera.targetPosition.set(bbox.center);\n"
    . "scene.update();";

/* This is where the data files are. Adjust if necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "JavaScript for 3D camera";


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
     * Create a 3D view that only defines the background color.
     */
    $optlist = "type=PRC background={fillcolor=mediumslateblue}";
    if (($view = $p->create_3dview("Default", $optlist)) == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    make_3d_page($p, $font, $view, "View model along x axis", $JS_X_AXIS);
    make_3d_page($p, $font, $view, "View model along y axis", $JS_Y_AXIS);
    make_3d_page($p, $font, $view, "View model along z axis", $JS_Z_AXIS);

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=javascript_for_3d_camera.pdf");
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
 * Display the 3D annotation and the JavaScript to position/align the
 * camera side-by-side on a landscape PDF page.
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
 *            JavaScript code to position and align the camera
 * 
 * @throws PDFlibException
 * @throws Exception
 */
function make_3d_page($p, $font, $view, $title, $javascript){
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
     * Load some 3D data with the view defined above
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
