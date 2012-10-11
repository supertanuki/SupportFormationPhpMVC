<?php
/* $Id: acrobat_menu_items.php,v 1.2 2012/05/03 14:00:39 stm Exp $
 * Acrobat menu items
 * Upon opening the page, create a full list of all menu item names in Acrobat.
 * With the spelling retrieved, the Acrobat menu item name can be used in PDFlib
 * actions to execute Acrobat menu items.
 * 
 * One of the retrieved menu item names can be provided in the "menuname" option
 * of the create_action() function to create an action of type "Named" for 
 * executing Acrobat menu items.
 * Create an action of type "Named" to execute the special Acrobat menu command
 * for opening the JavaScript console.
 * Create a JavaScript action to show the names of all Acrobat menu items in the
 * JavaScript console.
 * For the page trigger "open", supply the two JavaScript actions defined above.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: none
 */
/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Acrobat Menu Items";


$text =
    "Show the names of all Acrobat menu items.\n\n" .
    "If the JavaScript console doesn't open automatically proceed as " .
    "follows:\n" .
    "Acrobat 7: Select the \"Advanced, JavaScript, Debugger\" menu " .
    "item.\nAcrobat 8: Select the \"Advanced, Document Processing, " .
    "JavaScript Debugger\" menu item.";
    
$list_menu_names = 
    "function MenuList(m, level) \n" .
    "{ \n" .
    "    console.println(m.cName); \n" .
    "    if (m.oChildren != null) \n" .
    "        for (var i = 0; i < m.oChildren.length; i++) \n" .
    "            MenuList(m.oChildren[i], level + 1); \n" .
    "}\n" .
    "var m = app.listMenuItems(); \n" .
    "for (var i=0; i < m.length; i++) \n" .
    "    MenuList(m[i], 0);";

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

    $font = $p->load_font("Helvetica", "unicode", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Start a A4 landscape page */
    $p->begin_page_ext(0, 0, "width=a4.height height=a4.width");
    
    /* ----------------------------
     * Output some descriptive text
     * ----------------------------
     */
    $tf = $p->add_textflow(0, $text, 
	"fontname=Helvetica fontsize=12 encoding=unicode leading=120%");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->fit_textflow($tf, 20, 20, 550, 450, "");
    
       
    /* ---------------------------------------------------------------
     * Define JavaScript actions to be triggered upon opening the page
     * ---------------------------------------------------------------
     */
	
    /* Create an action of type "Named" to execute the Acrobat menu command
     * for opening the JavaScript console
     */
    $console = 
	$p->create_action("Named", "menuname={EScript:JSDebugger}");
   
    /* Create a JavaScript action to show the names of all Acrobat menu
     * items in the JavaScript console
     */
    $menuitems = 
	$p->create_action("JavaScript", "script {" . $list_menu_names . "}");
	
    /* Close the page. For the page trigger "open", supply the JavaScript
     * page actions defined above.
     */
    $optlist = "action {open={" . $console . " " . $menuitems . "}}";
	
    $p->end_page_ext($optlist);
	   
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=block_below_contents.pdf");
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

