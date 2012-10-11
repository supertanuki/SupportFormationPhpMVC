<?php
/* $Id: function_scopes.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Function scopes:
 * Explain the concept of scopes of the PDFlib API
 * 
 * Create a few pages and output some text, graphics and images. Show how scopes
 * are related and how function calls start or terminate scopes. Show which 
 * calls can be used in the respective situation and which calls are not
 * allowed. Use get_parameter("scope", 0) to query the current scope.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */

/* This is where the data files are. Adjust as necessary. */

$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Function Scopes";

$imagefile = "nesrin.jpg";

try {
    /* -------------------------------------------------------------------
     * Create a PDFlib object.
     * 
     * This call starts scope "object".
     * Only begin_document(), get_buffer(), and encoding_set_char() can be
     * called in this scope.
     * -------------------------------------------------------------------
     */
    $p = new PDFlib();
    
    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    
    
    /* --------------------------------------------------------------------
     * Start the document.
     * 
     * This call starts scope "document".
     * The following functions plus some others are allowed in this scope:
     * begin_page_ext(), resume_page(), load_image(), begin_template_ext(),
     * open_pdi_document(), open_pdi_page().
     * --------------------------------------------------------------------
     */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* We load the image before the first page, and use it on all pages.
     * Loading an image is allowed in scope "document".
     */
    $image = $p->load_image("auto", $imagefile, "");

    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    

    /* -------------------------------------------------------------------
     * Start the first page.
     * 
     * This call starts scope "page".
     * Many functions for creating page content are allowed in this scope.
     * -------------------------------------------------------------------
     */
    $p->begin_page_ext(595, 842, "");
    
    /* Load the font.
     *  
     * Loading a font is allowed in any scope except "object". Therefore,
     * it can also be done in scope "document" before starting the page.
     * 
     * For PDFlib Lite: change "unicode" to "winansi".
     */
    $font = $p->load_font("Helvetica", "unicode", "");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set the font, output an image, and output a text line.
     * 
     * These three function calls are allowed in scope "page" but cannot be
     * used in scope "document". This means that the page must have been
     * started first as accomplished above.
     */
    $p->setfont($font, 14);
    
    $p->fit_image($image,  200.0,  200.0, "scale=0.25");
    
    /* Output the scope.
     * 
     * Use get_parameter() with the key "scope" to query the current scope.
     */
    $scope = $p->get_parameter("scope", 0);
    $p->fit_textline("The current scope can be retrieved with " .
	"get_parameter(\"scope\", 0);", 20, 700, "");
    
    
    /* --------------------------------------------------------------------
     * Complete the first page.
     * 
     * This call terminates scope "page". After this call the current scope
     * will be "document" again. 
     * --------------------------------------------------------------------
     */
    $p->end_page_ext("");
    

    /* -----------------------------
     * Start the second page.
     * 
     * This call starts scope "page"
     * -----------------------------
     */
    $p->begin_page_ext(595, 842, "");

    /* Set the current fill color. 
     * 
     * This call is allowed in scope "page" but cannot be used in scope
     * "document".
     */
    $p->setcolor("fill", "rgb", 1.0, 0.0, 0.0, 0.0);
    
    
    /* -------------------------------------------------------------------
     * Add a rectangle to the current path.
     * 
     * This call starts scope "path".
     * Only other functions for constructing paths can be called in "path"
     * scope, e.g. lineto() or closepath(). None of the graphics state
     * functions or saving/restoring the graphics state, or setting the
     * current color must be used in "path" scope.
     * -------------------------------------------------------------------
     */
    $p->rect(200, 200, 250, 150);
    
    
    /* --------------------------------------------------------------------
     * Fill the interior of the path with the current fill color.
     * 
     * This call terminates scope "path". After this call the current scope
     * will be "page" again. 
     * --------------------------------------------------------------------
     */
    $p->fill();
    
    /* Set the current line width. This call is allowed in scope "page". */
    $p->setlinewidth(10);
    
    /* Set the current point for graphics output.
     * 
     * This function starts the scope "path".
     */
    $p->moveto(100, 500);
    
    /* Add a line to the current path */
    $p->lineto(300, 700);
    
    
    /* ---------------------------------------------------------------------
     * Stroke the path with the current line width and current stroke color,
     * and clear it.
     * 
     * This function terminates scope "path". After this call the current
     * scope will be "page" again.
     * --------------------------------------------------------------------- 
     */
    $p->stroke();
    

    /* --------------------------------------------------------------------
     * Complete the first page.
     * 
     * This call terminates scope "page". After this call the current scope
     * will be "document" again. 
     * --------------------------------------------------------------------
     */
    $p->end_page_ext("");

    /* Close the image. This call could have also be used in scope "page"
     * before ending the page.
     */
    $p->close_image($image);
    
    
    /* ----------------------------------------------------------------
     * This call terminates scope "document". The current scope will be
     * "object" again
     * ----------------------------------------------------------------
     */
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=function_scopes.pdf");
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
 
