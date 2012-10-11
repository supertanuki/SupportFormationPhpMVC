<?php
/* $Id: starter_pdfx4.php,v 1.3 2012/05/03 14:00:41 stm Exp $
 *
 * PDF/X-4 starter:
 * Create PDF/X-4 conforming output with layer variants and transparency
 *
 * A low-level layer is created for each of several languages, as well
 * as an image layer. Each of the language layers together with the
 * image layer forms a "layer variant" according to PDF/X-4 (in Acrobat
 * layer variants are called "configurations").
 * This ensures that low-level layers cannot be enabled/disabled individually,
 * but only via the corresponding layer variant. This prevents accidental
 * printing of a language layer without the required image layer.
 *
 * The document contains transparent text which is allowed in
 * PDF/X-4, but not earlier PDF/X standards.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: font file, image file, ICC output intent profile
 *                (see www.pdflib.com for ICC profiles)
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";

$imagefile = "zebra.tif";


try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);

    if ($p->begin_document("", "pdfx=PDF/X-4") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_info("Creator", "PDFlib starter sample");
    $p->set_info("Title", "starter_pdfx4");

    if ($p->load_iccprofile("ISOcoated.icc", "usage=outputintent") == 0) {
	print("Error: " . $p->get_errmsg() . "\n");
	print("Please install the ICC profile package from "
		. "www.pdflib.com to run the PDF/X starter sample.\n");
	$p->delete();
	return(2);
    }

    /*
     * Define the low-level layers. These cannot be controlled directly
     * in Acrobat's layer pane.
     */

    $layer_english = $p->define_layer("English text", "");
    $layer_german = $p->define_layer("German text", "");
    $layer_french = $p->define_layer("French text", "");
    $layer_image = $p->define_layer("Images", "");

    /* Define a radio button relationship for the language layers.
     * Individual layers will only be visible in Acrobat X (but
     * not Acrobat 9).
     */
    $optlist = "group={" . $layer_english . " " . $layer_german
                . " " . $layer_french . "}";
    $p->set_layer_dependency("Radiobtn", $optlist);

    /*
     * Define the layer combinations for document variants. The variants
     * control the low-level layers, and can be activated in Acrobat 9's
     * layer pane. Using layer variants we can make sure that the image
     * layer cannot accidentally be disabled; it will always accompany
     * the text regardless of the selected language.
     */

    $optlist = "variantname={English variant} includelayers={"
	    . $layer_english . " " . $layer_image . "} "
	    . "defaultvariant=true createorderlist";
    $p->set_layer_dependency("Variant", $optlist);

    $optlist = "variantname={German variant} includelayers={"
	    . $layer_german . " " . $layer_image . "}";
    $p->set_layer_dependency("Variant", $optlist);

    $optlist = "variantname={French variant} includelayers={"
	    . $layer_french . " " . $layer_image . "}";
    $p->set_layer_dependency("Variant", $optlist);

    $p->begin_page_ext(595, 842, "");

    /* Font embedding is required for PDF/X */
    $font = $p->load_font("LuciduxSans-Oblique", "winansi", "embedding");

    if ($font == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->setfont($font, 24);

    $p->begin_layer($layer_english);

    $p->fit_textline("PDF/X-4 starter sample with layers", 50, 700, "");

    $p->begin_layer($layer_german);
    $p->fit_textline("PDF/X-4 Starter-Beispiel mit Ebenen", 50, 700, "");

    $p->begin_layer($layer_french);
    $p->fit_textline("PDF/X-4 Starter exemple avec des calques", 50, 700,
	    "");

    $p->begin_layer($layer_image);

    $p->setfont($font, 48);

    /* The RGB image needs an ICC profile; we use sRGB. */
    $icc = $p->load_iccprofile("sRGB", "");
    $optlist = "iccprofile=" . $icc;
    $image = $p->load_image("auto", $imagefile, $optlist);

    if ($image == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Place a diagonal stamp across the image area */
    $width = $p->info_image($image, "width", "");
    $height = $p->info_image($image, "height", "");

    $optlist = "boxsize={" . $width . " " . $height . "} stamp=ll2ur";
    $p->fit_textline("Zebra", 0, 0, $optlist);

    /* Set transparency in the graphics state */
    $gstate = $p->create_gstate("opacityfill=0.5");
    $p->set_gstate($gstate);

    /* Place the image on the page and close it */
    $p->fit_image($image, (double) 0.0, (double) 0.0, "");
    $p->close_image($image);

    /* Close all layers */
    $p->end_layer();

    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=starter_pdfx4.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in starter_pdfx4 sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;
?>
