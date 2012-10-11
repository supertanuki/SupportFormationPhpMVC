<?php
/* $Id: font_info.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Font info:
 * Get various properties of a font such as font name, font style, or encoding
 *
 * Use info_font() with various keys and options to retrieve the required font
 * information
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Font Info";

$x=20; 
$xindent=150; 
$y=820; 
$yoffset=20;

try {
    $p = new PDFlib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

    /* Load a font */
    $font = $p->load_font("LuciduxSans-Oblique", "unicode", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 12);

    /* Get the font name: use "api" to get the font name used by PDFlib.
     * (use "acrobat" for the Acrobat font name or use "full" for
     * retrieving the full font name)
     */
    $p->fit_textline("fontname (api):", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "fontname", "api");
    if ($info > -1) {
	$fontname = $p->get_parameter("string", $info);
	$p->fit_textline($fontname, $xindent, $y, "");
    }

    /* Get the path name for the font outline file. This will be successful
     * since in our case the font file name is identical to the font name
     * "LuciduxSans-Oblique". In other cases the font file name can only be
     * retrieved if it has been configured using set_parameter() and the
     * "FontOutline" resource category.
     */
    $p->fit_textline("fontfile:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "fontfile", "");
    if ($info > -1) {
	$fontname = $p->get_parameter("string", $info);
	$p->fit_textline($fontname, $xindent, $y, "");
    }

    /* With PostScript Type 1 fonts, get the path name for the font metrics
     * file (AFM or PFM). This will be successful since in our case the
     * font metrics file name is identical to the font name
     * "LuciduxSans-Oblique". In other cases the font file name can only be
     * retrieved if it has been configured using set_parameter() and the
     * "FontAFM" or "FontPFM" resource category.
     */
    $p->fit_textline("metricsfile:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "metricsfile", "");
    if ($info > -1) {
	$fontname = $p->get_parameter("string", $info);
	$p->fit_textline($fontname, $xindent, $y, "");
    }

    /* Get the encoding of the font: use "api" to get the encoding name
     * as specified in PDFlib (use "actual" for the name of the encoding
     * actually used for the font)
     */
    $p->fit_textline("encoding (api):", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "encoding", "api");
    if ($info > -1) {
	$fontname = $p->get_parameter("string", $info);
	$p->fit_textline($fontname, $xindent, $y, "");
    }

    /* Get information about if the font is embedded as a CID font */
    $p->fit_textline("cidfont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "cidfont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is a host font */
    $p->fit_textline("hostfont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "hostfont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the supplement number of the character collection for fonts with
     * a standard CJK CMap
     */
    $p->fit_textline("supplement:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "supplement", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is a standard font */
    $p->fit_textline("standardfont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "standardfont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is a symbol font */
    $p->fit_textline("symbolfont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "symbolfont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is embedded as a CID font */
    $p->fit_textline("cidfont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "cidfont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is loaded with an encoding which
     * allows unicode text
     */
    $p->fit_textline("unicodefont:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "unicodefont", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if a font subset will be created */
    $p->fit_textline("willsubset:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "willsubset", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font will be embedded */
    $p->fit_textline("willembed:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "willembed", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get information about if the font is for vertical writing mode */
    $p->fit_textline("vertical:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "vertical", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the style of the font */
    $p->fit_textline("fontstyle:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "fontstyle", "");
    if ($info > -1) {
	$fontstyle = $p->get_parameter("string", $info);
	$p->fit_textline($fontstyle, $xindent, $y, "");
    }

    /* Get information about if the font style will be faked */
    $p->fit_textline("fontstyle (faked):", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "fontstyle", "faked");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the italic angle of the font (/ItalicAngle in the PDF font
     * descriptor)
     */
    $p->fit_textline("italicangle:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "italicangle", "");
    $p->fit_textline($info, $xindent, $y, "");

    /* Get the weight of the font (between 100 and 900)
     * 400=normal, 700=bold
     */
    $p->fit_textline("weight:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "weight", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the number of CIDs if the font uses a standard CMap */
    $p->fit_textline("numcids:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "numcids", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the number of glyphs in the font */
    $p->fit_textline("numglyphs:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "numglyphs", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Get the highest code value for the encoding of the font */
    $p->fit_textline("maxcode:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "maxcode", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Unicode value (for Unicode-compatible fonts) or code value
     * (for Symbol fonts) of the replacement character of the font
     */
    $p->fit_textline("replacementchar:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "replacementchar", "");
    if ($info > -1) {
	$p->fit_textline("U+00" .
	    strtoupper(dechex($info)), $xindent, $y, "");
    }

    /* Get the number of kerning pairs in the font */
    $p->fit_textline("kerningpairs:", $x, $y-=$yoffset, "");

    $info = $p->info_font($font, "kerningpairs", "");
    $p->fit_textline( $info, $xindent, $y, "");

    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=font_info.pdf");
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

