<?php
/* $Id: dot_leaders_with_tabs.php,v 1.2 2012/05/03 14:00:38 stm Exp $
 * Dot leaders with tabs:
 * Use leaders to fill the space defined by tabs between left-aligned and
 * right-aligned text, such as in a table of contents.
 * 
 * Place a Textflow representing a table of contents. In each line, the table
 * of contents contains tabs between the text entry and the corresponding
 * page number. The space defined by the tab will be filled with the
 * characters specified as leaders.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7 or above
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Dot Leaders with Tabs";

$tf = 0;

/* Option list for placing the Textflow: 
 * "ruler=100%" defines a tab position of 100% of the width, e.g. at
 * the right border of the text box. 
 * "hortabmethod=ruler" specifies to use the tab positions defined
 * with "ruler". 
 * "tabalignment=right" defines the tabs to be right-aligned.
 * We use the default leader character ".". To specify another
 * character(s), use the "text" suboption of the leader option, e.g.
 * "leader={text={+ }}" for defining the sequence "+ " as leaders. 
 */
$optlist = "fontname=Helvetica fontsize=12 " .
    "encoding=unicode leading=160% ruler=100% " .
    "hortabmethod=ruler tabalignment=right";

$text = 
    "<alignment=left>Introduction<leader={alignment={grid}}>" .
    "\t7<nextline>" .
    "<alignment=left>Chapter 1<leader={alignment={grid}}>" .
    "\t25<nextline>" .
    "<alignment=left>Chapter 2<leader={alignment={grid}}>" .
    "\t107<nextline>" .
    "<alignment=left>Chapter 3<leader={alignment={grid}}>" .
    "\t219<nextline>" .
    "<alignment=left>Appendix<leader={alignment={grid}}>\t240";
   
try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);
    
    /* Load the font */
    $font = $p->load_font("Helvetica-Bold", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Create a Textflow */
    $tf = $p->create_textflow($text, $optlist);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

    /* Loop until all of the text is placed; create new pages
     * as long as more text needs to be placed.
     */
    do {
	$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

	/* Place a text line with a title */
	$p->setfont($font, 18);
	$p->fit_textline("Table of Contents", 50, 740, "");
	
	/* Place the Textflow with the table of contents */
	$result = $p->fit_textflow($tf, 50, 600, 500, 700, "");

	$p->end_page_ext("");

	/* "_boxfull" means we must continue because there is more text;
	 * "_nextpage" is interpreted as "start new column"
	 */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if (!$result == "_stop") {
	/* "_boxempty" happens if the box is very small and doesn't
	 * hold any text at all.
	 */
	if ($result == "_boxempty")
	    throw new Exception("Error: Textflow box too small");
	else {
	    /* Any other return value is a user exit caused by
	     * the "return" option; this requires dedicated code to
	     * deal with.
	     */
	    throw new Exception("User return '" . $result .
		"' found in Textflow");
	}
    }

    $p->delete_textflow($tf);

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=dot_leaders_with_tabs.pdf");
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
