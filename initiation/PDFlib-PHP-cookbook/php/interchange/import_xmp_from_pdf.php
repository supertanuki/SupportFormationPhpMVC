<?php
/* $Id: import_xmp_from_pdf.php,v 1.3 2012/05/03 14:00:41 stm Exp $
 * Import XMP from PDF:
 * Retrieve the XMP metadata from an imported PDF document and write all
 * document-level XMP metadata to the output PDF 
 * 
 * Maintain existing XMP metadata when processing documents: Read the XMP 
 * stream from the imported document with the pCOS path "/Root/Metadata",
 * create a PVF file from the XMP and feed the document-level metadata to the
 * output document.
 *
 * Required software: PDFlib+PDI/PPS 7
 * Required data: PDF document with XMP metadata
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Import XMP from PDF";

$pdffile = "PDFlib-real-world.pdf";

try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Open the input PDF */
    $indoc = $p->open_pdi_document($pdffile, "");
    if ($indoc == 0)
	throw new Exception("Error: " + $p->get_errmsg());
    
    /* Check if any document-level metadata exists in the input document */
    $objtype = $p->pcos_get_string($indoc, "type:/Root/Metadata");
    if ($objtype =="stream")
    {
	/* If document-level metadata exists retrieve it using the pCOS
	 * path "/Root/Metadata". (Similarly, you could retrieve any
	 * existing XMP metadata on page, font, or image level, etc. using
	 * the pCOS path "pages[...]/Metadata", "images[...]/Metadata",
	 * "fonts[...]/Metadata", etc.)
	 */
	$metadata = $p->pcos_get_stream($indoc, "", "/Root/Metadata");
    }

    if ($metadata != "")
    {
	/* If document-level metadata is available, store it in a
	 * PDFlib virtual file (PVF)
	 */
	$p->create_pvf("/pvf/metadata", $metadata, "");
		    
	/* Start the output document and copy the XMP metadata from the PVF
	 * to it 
	 */
	if ($p->begin_document($outfile,
	    "metadata={filename=/pvf/metadata}") == 0)
	    throw new Exception("Error: " + $p->get_errmsg());
	
	$p->delete_pvf("/pvf/metadata");
    }
    else
    {
	/* Start the output document without copying any metadata */
	if ($p->begin_document($outfile, "") == 0)
	    throw new Exception("Error: " + $p->get_errmsg());
    }
     
    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Retrieve the number of pages for the input document */
    $endpage = $p->pcos_get_number($indoc, "length:pages");

    /* Loop over all pages of the input document */
    for ($pageno = 1; $pageno <= $endpage; $pageno++)
    {
	$page = $p->open_pdi_page($indoc, $pageno, "");

	if ($page == 0)
	    throw new Exception("Error: " + $p->get_errmsg());

	/* Dummy page size; will be adjusted later */
	$p->begin_page_ext(10, 10, "");

	/* Place the imported page without performing
	 * any changes on the output page
	 */
	$p->fit_pdi_page($page, 0, 0, "adjustpage");
	
	$p->end_page_ext("");

	$p->close_pdi_page($page);
    }

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=import_xmp_from_pdf.pdf");
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

