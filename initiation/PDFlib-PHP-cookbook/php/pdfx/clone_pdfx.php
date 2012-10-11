<?php
/* $Id: clone_pdfx.php,v 1.2 2012/05/03 14:00:41 stm Exp $
 *
 * Clone PDF/A and PDF/X standard documents
 * This is useful as basis for additional processing,
 * such as stamping, adding XMP metadata, adding page content, etc.
 *
 * The following aspects of the input document will be cloned:
 * - PDF/A and PDF/X version (if supported by PDFlib)
 *   The following standards can not be cloned: PDF/A-1a:2005 and PDF/X-5n.
 * - output intent (if present)
 *   Referenced output intents for PDF/X-4p and PDF/X-5pg will also
 *   be cloned.
 * - page geometry, i.e. all page boxes and Rotate key
 *   Referenced external pages for PDF/X-5g and PDF/X-5pg will also
 *   be cloned.
 * - XMP document metadata
 *
 * To demonstrate coordinate transformations which may be required
 * to add new page content this topic adds a stamp across all pages.
 *
 * Input documents may conform to PDF/A and PDF/X simultaneously.
 *
 * Note: Except for the names of the input and output documents this is an exact
 * copy of example clone_pdfa. It is included
 * twice for being easily found under the PDF/A and PDF/X categories.
 *
 * required software: PDFlib/PDFlib+PDI/PPS 8 or above
 * required data: PDF/A or PDF/X input document
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";

$pdfinputfile = "PLOP-datasheet-PDFX-3-2002.pdf";
$optlist = "";
$clonemetadata = true;

/* The following standard flavors can be cloned: */
$supportedflavors = array( "PDF/A-1b:2005", "PDF/X-1a:2001",
    "PDF/X-1a:2003", "PDF/X-3:2002", "PDF/X-3:2003", "PDF/X-4",
    "PDF/X-4p", "PDF/X-5g", "PDF/X-5pg", "none" );


try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdfinputfile, "");
    if ($indoc == 0) {
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());
    }

    /*
     * Read PDF/X and PDF/A version of the input document
     */
    $pdfxversion = $p->pcos_get_string($indoc, "pdfx");
    $pdfaversion = $p->pcos_get_string($indoc, "pdfa");

    for ($i = 0; $i < count($supportedflavors); $i++) {
	if ($pdfxversion == $supportedflavors[$i]) {
	    $optlist .= " pdfx=" . $pdfxversion;
	    break;
	}
    }
    if ($i == count($supportedflavors))
	throw new Exception("Error: Cannot clone " . $pdfxversion
		. " documents");

    for ($i = 0; $i < count($supportedflavors); $i++) {
	if ($pdfaversion == $supportedflavors[$i]) {
	    $optlist .= " pdfa=" . $pdfaversion;
	    break;
	}
    }
    if ($i == count($supportedflavors))
	throw new Exception("Error: Cannot clone " . $pdfaversion
		. " documents");

    /*
     * Create a new document and clone PDF/A and PDF/X version
     */
    if ($p->begin_document("", $optlist) == 0)
	throw new Exception("Error: " . $p->get_apiname() . ": "
		. $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", "clone_pdfx");

    /*
     * Clone PDF/X output intent
     */
    $p->process_pdi($indoc, 0, "action=copyoutputintent");

    $endpage = (int) $p->pcos_get_number($indoc, "length:pages");

    /* Copy all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++) {
	$lowerleftcorner = array( 
		array( "x1", "y1" ), /* 0 degrees */
		array( "x2", "y2" ), /* 90 degrees */
		array( "x3", "y3" ), /* 180 degrees */
		array( "x4", "y4" ), /* 270 degrees */
	);
	$page = $p->open_pdi_page($indoc, $pageno, "cloneboxes");

	if ($page == 0) {
	    print "Error: " . $p->get_errmsg();
	    continue;
	}
	/* Dummy page size; will be adjusted by "cloneboxes" option */
	$p->begin_page_ext(10, 10, "");

	/*
	 * Query the geometry of the cloned page. This is required to
	 * account for translated or rotated pages if we want to add
	 * more contents to the page.
	 */
	$phi = $p->info_pdi_page($page, "rotate", "");

	/*
	 * Select the lower left corner depending on the rotation angle
	 */
	$x = $p->info_pdi_page($page, $lowerleftcorner[intval($phi / 90)][0],
		"");
	$y = $p->info_pdi_page($page, $lowerleftcorner[intval($phi / 90)][1],
		"");

	/*
	 * Place the imported page on the output page and clone all page
	 * boxes
	 */
	$p->fit_pdi_page($page, 0, 0, "cloneboxes");

	/* Font embedding is required for PDF/X */
	$font = $p->load_font("LuciduxSans-Oblique", "unicode",
		"embedding");

	if ($font == 0)
	    throw new Exception("Error: " . $p->get_apiname() . ": "
		    . $p->get_errmsg());

	$p->setfont($font, 24);

	/*
	 * Adjust the coordinate system to facilitate adding new page
	 * content on top of the cloned page.
	 */
	$p->translate($x, $y);
	$p->rotate($phi);

	$width = $p->info_pdi_page($page, "pagewidth", "");
	$height = $p->info_pdi_page($page, "pageheight", "");

	/*
	 * Add some text on each page. PDFlib will automatically set the
	 * default black color in a standard-conforming way.
	 */
	$p->fit_textline("Cloned page", 0, 0,
		"textrendering=1 stamp=ll2ur boxsize={" . $width . " "
			. $height . "}");

	$p->close_pdi_page($page);
	$p->end_page_ext("");
    }

    /*
     * Clone XMP metadata if applicable
     */

    /*
     * These old flavors are based on PDF 1.3 which doesn't support XMP
     * metadata
     */
    if ($pdfxversion == "PDF/X-1a:2001"
	    || $pdfxversion == "PDF/X-3:2002") {
	$clonemetadata = false;
    }

    if ($clonemetadata
	    && $p->pcos_get_string($indoc, "type:/Root/Metadata") == "stream") {
	$xmp = $p->pcos_get_stream($indoc, "", "/Root/Metadata");
	$p->create_pvf("/xmp/document.xmp", $xmp, "");
	$optlist = "metadata={filename=" . "/xmp/document.xmp" . "}";
    }
    else {
	$optlist = "";
    }

    $p->end_document($optlist);
    $p->delete_pvf("/xmp/document.xmp");
    $p->close_pdi_document($indoc);

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=clone_pdfx.pdf");
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
