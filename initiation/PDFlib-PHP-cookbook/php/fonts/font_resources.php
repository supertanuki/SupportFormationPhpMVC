<?php
/* $Id: font_resources.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Font resources:
 * Configure font resources and search for fonts
 *
 * In the easiest case, load a host font on Windows or Mac which is already
 * installed in the system. Then, use the "SearchPath" resource category to
 * define the folders for your fonts to be found. In the next case, use a font
 * where the font file name equals the font name. If the font name and font
 * file name are not equal, use the "FontOutline" resource category to define
 * a font name to be used by PDFlib. For Type 1 fonts, use the "FontOutline"
 * and the "FontAFM" (or "FontPFM") resource categories to define a font name
 * to be used in PDFlib. With info_font() get the font name and the font file
 * name.
 *
 * Required software: PDFlib Lite/PDFlib/PDFlib+PDI/PPS 7
 * Required data: font file
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Font Resources";

$x=20; 
$y=260;


try {
    $p = new PDFlib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
    throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Start page */
    $p->begin_page_ext(0, 0, "width=300 height=300");

    /* The convenient way: load a host font on Windows or Mac
     * which is already installed in the system. In this case no
     * prerequisites are required provided that you know the exact
     * (case-sensitive) name of the font (see the PDFlib Tutorial for
     * information about how to retrieve the exact name of a host font).
     * For example, if you installed the font "Verdana" in the system you
     * can load it just as follows.
     * (For PDFlib Lite: In all of the following $p->load_font() calls change
     * "unicode" to "winansi".)
     */

    /* The following will only work on Mac or Windows, provided the
     * font Verdana is installed in the system.
    $font = $p->load_font("Verdana", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 10);
    $p->fit_textline("Font file is installed in the system", $x, $y, "");
    $fontname = 
	$p->get_parameter("string", $p->info_font(font, "fontname", "api"));
    $p->fit_textline("Font name used in PDFlib: " . $fontname, $x, $y-=20, "");
     */

    /* If you do not want to use a host font the location of the font files
     * must be provided. Use the "SearchPath" resource category to define
     * the folder for your fonts to be searched in. Add similar
     * "Searchpath" commands to define further folders if necessary. PDFlib
     * will first search the font in all folders defined with "Searchpath".
     */
    $p->set_parameter("SearchPath", $searchpath);

    /* If you know the font name and the font file name (excluding the
     * file name extension such as ".ttf", ".otf", ".pfb" etc.) is equal
     * to the font name, you can simply load the font now.
     * In the following example a font with the name
     * "LuciduxSans-Oblique" is loaded from the font outline file
     * "LuciduxSans-Oblique.pfa".
     */
    $font = $p->load_font("LuciduxSans-Oblique", "unicode", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 10);
    $p->fit_textline("Font file name: LuciduxSans-Oblique.pfa", $x, $y-=40, "");
    $fontname = 
	$p->get_parameter("string", $p->info_font($font, "fontname", "api"));
    $p->fit_textline("Font name used in PDFlib: " . $fontname, $x, $y-=20, "");

    /* If the font file name is different from the font name, use the
     * "FontOutline" resource category to define a font name to be used by
     * PDFlib. In the following example the font name "GentiumItalic" is
     * connected to the TrueType outline font file "GenI102.TTF"
     * (see http://scripts.sil.org/gentium).
     */
    $p->set_parameter("FontOutline", "GentiumItalic=GenI102.TTF");

    /* Alternatively, you can supply an absolute path name such as
     *
     * $p->set_parameter("FontOutline", 
     *     "GentiumItalic=/usr/fonts/GenI102.TTF");
     *
     * In this case the font is loaded from the location defined above
     * without the searchpath being applied.
     */

    /* Now you can load the font using the name "GentiumItalic".
     */
    $font = $p->load_font("GentiumItalic", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 10);
    $p->fit_textline("Font file name: GenI102.TTF", $x, $y-=40, "");
    $fontname = 
	$p->get_parameter("string", $p->info_font($font, "fontname", "api"));
    $p->fit_textline("Font name used in PDFlib: " . $fontname, $x, $y-=20, "");

    /* In case of a PostScript Type 1 font two files are needed, the
     * font outline file and the font metrics file. For Type 1 fonts, use
     * the "FontOutline" and the "FontAFM" (or "FontPFM") resource
     * categories to define a font name to be used in PDFlib. In the
     * following example the font name "LuciduxSans" is connected
     * to the Type 1 outline file "lcdxsr.pfa" as well as to the Type 1
     * metrics file "lcdxsr.afm".
     */
    $p->set_parameter("FontOutline", "LuciduxSans=lcdxsr.pfa");
    $p->set_parameter("FontAFM", "LuciduxSans=lcdxsr.afm");

    /* Load the font "LuciduxSans" with embedding.  */
    $font = $p->load_font("LuciduxSans", "unicode", "embedding");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 10);
    $p->fit_textline("Font file names: lcdxsr.pfa and lcdxsr.afm",
	$x, $y-=40, "");
    $fontname =
	$p->get_parameter("string", $p->info_font($font, "fontname", "api"));
    $p->fit_textline("Font name used in PDFlib: " . $fontname, $x, $y-=20, "");
    
    /* Finish page */
    $p->end_page_ext("");

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=font_resource.pdf");
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

