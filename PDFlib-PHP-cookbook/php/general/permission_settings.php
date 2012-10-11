<?php
/* $Id: permission_settings.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Permission Settings:
 * Change the permission settings so that only commenting the PDF is allowed
 *
 * Define a master password of "PDFlib" and set the permissions so that
 * printing (noprint nohiresprint), content extraction (nocopy noaccessible)
 * and changing the page contents or form field definitions (noassemble) will
 * not be allowed, while creating annotations and filling out form fields will
 * be permitted.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Permission Settings";


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Define a master password of "PDFlib" and set the permissions so that
     * printing (noprint nohiresprint), contents extraction (nocopy
     * noaccessible) and changing the page contents or form field
     * definitions (noassemble) will not be allowed, while creating
     * annotations and filling out form fields will be permitted.
     */
    $optlist = "masterpassword=PDFlib permissions={noprint nohiresprint " .
	"nocopy noaccessible noassemble}";

    if ($p->begin_document($outfile, $optlist) == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Start page */
    $p->begin_page_ext(800, 400, "");

    $text = "The master password for this document is set to \"PDFlib\". " .
	   "The permissions for this document are set in a way that you " .
	   "can only create annotations. To accomplish this use the " .
	   "following options in the begin_document() call:<nextline>" .
	   "masterpassword=PDFlib<nextline>permissions={noprint " .
	   "nohiresprint nocopy noaccessible noassemble}";
    $tf = $p->create_textflow($text, "font=" . $font .
	" fontsize=20 leading=140%");
    $p->fit_textflow($tf, 50, 50, 750, 350, "");

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=permission_settings.php");
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

