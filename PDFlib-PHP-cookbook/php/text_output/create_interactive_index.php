<?php
/* $Id: create_interactive_index.php,v 1.2 2012/05/03 14:00:38 stm Exp $ 
 * Create interactive index:
 * In a Textflow define some terms to be indexed and create a sorted index from
 * the indexed terms
 *
 * For indicating an indexed term in a Textflow use the inline options 
 * "matchbox" and "matchbox end" to create a matchbox at the position to which
 * the index entry will refer to. The matchbox name will be similar to the 
 * indexed term. Place the Textflow. Then, create the index by collecting all
 * matchboxes. Each index entry will consist of the matchbox name (indexed term)
 * and the respective page number. Provide the page number with a link
 * annotation to jump to the respective page. Matchboxes are used here a 
 * second time to indicate the active link area on the page number.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7.0.3
 * Required data: none
 */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Create Interactive Index";

/* Required minimum PDFlib version */
$requiredversion = 703;
$requiredvstr = "7.0.3";

$llx = 20; $lly = 20; $urx = 200; $ury = 200;
$pagewidth = 250; $pageheight = 230;

$tf = 0; $idx = 0;
 

/* Option list to indicate the start of a matchbox */
$startopts = "";

/* Option list to indicate the end of a matchbox */
$endopts = "matchbox=end";

/* Standard option list for adding a Textflow.
 * "avoidemptybegin" deletes empty lines at the beginning of a fitbox.
 * "charref" enables the substitution of numeric and character entity
 * or glyph name references, e.g. of the character reference "&shy;"
 * for a soft hyphen.
 */
$stdopts = "fontname=Helvetica fontsize=12 encoding=unicode " .
    "leading=120% charref avoidemptybegin ";
   
$ntexts = 10;
  
/* The text array contains pairs of strings. Each first string will be used
 * as indexed term, i.e. the text of an index marker.
 */
$texts = array(
    "Long Distance Glider",
    "\nWith this paper rocket you can send all your messages even when " .
    "sitting in a hall or in the cinema pretty near the back.\n\n",
    
    "Giant Wing",
    "\nAn unbelievable sailplane! It is amazingly robust and can even " .
    "do aerobatics. But it best suited to gliding.\n\n",
    
    "Cone Head Rocket",
    "\nThis paper arrow can be thrown with big swing. We launched it " .
    "from the roof of a hotel. It stayed in the air a long time and " .
    "covered a considerable distance.\n\n",
    
    "Super Dart",
    "\nThe super dart can fly giant loops with a radius of 4 or 5 " .
    "metres and cover very long distances. Its heavy cone point is " .
    "slightly bowed upwards to get the lift required for loops.\n\n",
    
    "German Bi-Plane",
    "\nBrand-new and ready for take-off. If you have lessons in the " .
    "history of aviation you can show your interest by letting it " .
    "land on your teacher's desk.\n\n",
);

/* Index entry containing a name and a page number. For sorting the index
 * entries a compare method is provided.
 */

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
 
    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0); 
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);
	   
    if ($major*100 + $minor*10 + $revision < $requiredversion) 
	throw new Exception("Error: PDFlib " . $requiredvstr . 
	    " or above is required");
    
    /* Start the output document */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    
    /* ----------------------------------------------------------------
     * Add the text Textflow and define a matchbox on each indexed term
     * ----------------------------------------------------------------
     */
    
    /* Supply the standard options to the Textflow. This has to be done
     * only once. Further calls of add_textflow() for this Textflow will use
     * these settings by default.
     */
    $tf = $p->add_textflow(0, "", $stdopts);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Loop over all texts. Add each text and define a matchbox on each
     * indexed term. The matchbox name is set to the indexed term.
     */
    for ($i = 0; $i < $ntexts; $i+=2) {
	/* Add text and start a matchbox indicating an indexed term.
	 * Colorize the matchbox rectangle (only for illustration purposes)
	 */
	$startopts = "matchbox={name={" . $texts[$i] . "} " .
	    "fillcolor={rgb 0 0.95 0.95}}";
    
	$tf = $p->add_textflow($tf, $texts[$i], $startopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Add text and finish the matchbox */
	$tf = $p->add_textflow($tf, $texts[$i+1], $endopts);
	if ($tf == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
    
    
    /* --------------------------------------------------------------------
     * Place the text and retrieve all matchboxes (indexed terms) to create
     * the index entries from
     * --------------------------------------------------------------------
     */
	
    /* Initialize the current page number to be output in the index */
    $pageno = 0;
    
    /* Initialize the number of index entries */
    $entryno = 0;
    
    /* Loop until all of the text is placed; create new pages as long as
     * more text needs to be placed.
     */
    do
    {
	$p->begin_page_ext($pagewidth, $pageheight, "");
	$pageno++;

	/* Place the text */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury, "");
	
	/* Place a page number at the lower right corner of the page */
	$p->fit_textline($pageno, $pagewidth - 20, 10, 
	    "fontname=Helvetica encoding=unicode fontsize=12 " .
	    "fillcolor={rgb 0 0.95 0.95}");
	
	/* Create the index by creating an index entry from each matchbox on
	 * the page. Create an index entry by retrieving the matchbox name
	 * as well as the current page number.
	 * 
	 * (In our solution multiple index entries may refer to the same
	 * indexed term. An indexer for general use would combine entries
	 * for the same term into a single index entry with multiple
	 * page numbers. Implement this by creating a chain of multiple
	 * matchboxes for each indexed term.)
	 */
	
	/* Query the number of matchboxes on the page; the "num" parameter
	 * is set to 0 and will be ignored
	 */
	$mcount = $p->info_matchbox("*", 0, "count");
	
	for ($i = 1; $i <= $mcount; $i++)
	{
	    /* Get the matchbox name */
	    $minfo = $p->info_matchbox("*", $i, "name");
	
	    $mname = $p->get_parameter("string", $minfo);
	    
	    /* Retrieve the name of the matchbox to be used as the indexed 
	     * term and the page number to be used as the page number in the
	     * index entry
	     */
	    $newEntry[] = array("name" => $mname, "page" => $pageno);
		  
	    $entryno++;
	}
	 
	$p->end_page_ext("");

	/* "_boxfull" means we must continue because there is more text;
	 * "_nextpage" is interpreted as "start new column"
	 */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if (!$result == "_stop")
    {
	/* "_boxempty" happens if the box is very small and doesn't
	 * hold any text at all.
	 */
	if ($result ==  "_boxempty")
	    throw new Exception ("Error: Textflow box too small");
	else
	{
	    /* Any other return value is a user exit caused by
	     * the "return" option; this requires dedicated code to
	     * deal with.
	     */
	    throw new Exception ("User return '" . $result .
		"' found in Textflow");
	}
    }

    $p->delete_textflow($tf);
    

    /* -------------------------------------------------------------
     * Sort the list of index entries. Convert it to an array first.
     * -------------------------------------------------------------
     */

    $sortedIndex = $newEntry;
    usort($sortedIndex, "cmp");
    
    
    /* ---------------------------------------------------------------------
     * Construct the contents of the index page(s) based on the collected
     * pairs containing the indexed term plus the corresponding page number
     * ---------------------------------------------------------------------
     */ 
    /* Supply the standard options to the index Textflow. This has to be 
     * done only once for each Textflow. Further calls of add_textflow() for
     * this Textflow will use these settings by default.
     */
    $idx = $p->add_textflow(0, "", $stdopts);
    if ($idx == 0)
	throw new Exception("Error: " . $p->get_errmsg());
   
    /* Add the heading "Index" to the index Textflow */
    $idx = $p->add_textflow($idx, "Index\n\n", "");
    if ($idx == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add the collected and sorted index entries to the index Textflow */
    for ($i = 0; $i < count($sortedIndex); $i++) {
	/* Add the indexed term of the index entry */
	$idx = $p->add_textflow($idx, $sortedIndex[$i]{"name"} . "  ", 
	    "fillcolor={gray 0}");
	if ($idx == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
	
	/* Add the page number of the index entry. In addition, define a
	 * matchbox with a sequence number as the name. This matchbox will 
	 * be used later to define a link annotation on it to jump to the 
	 * respective page. 
	 */
	$idx = $p->add_textflow($idx, $sortedIndex[$i]{"page"}, 
	    "fillcolor={rgb 0 0.95 0.95} matchbox={name=" . $i . "}");
	if ($idx == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
		  
	$idx = $p->add_textflow($idx, "\n", $endopts);
	if ($idx == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    }
   
    
    /* ---------------------------------------------------------------------
     * Place the index Textflow with each entry consisting of a text, a page
     * number, and a link annotation on the page number
     * ---------------------------------------------------------------------
     */ 
       
    /* Initialize the current number of the index entry */
    $entryno = 0;
    
    /* Loop until all index entries are placed; create new pages as long as
     * more index entries need to be placed
     */
    do
    {
	$p->begin_page_ext($pagewidth, $pageheight, "");
	$pageno++;
	
	/* Fit the index Textflow */
	$result = $p->fit_textflow($idx, $llx, $lly, $urx, $ury, "");
	
	/* Place a page number */
	$p->fit_textline($pageno, $pagewidth - 20, 10, 
	    "fontname=Helvetica encoding=unicode fontsize=12 " .
	    "fillcolor={rgb 0 0.95 0.95}");
	
	/* Collect the index entries by retrieving the number of matchboxes
	 * on the current page
	 */
	$mcount = $p->info_matchbox("*", 1, "count");
	
	for ($i = 1; $i <= $mcount; $i++)
	{
	    /* Get the matchbox name which corresponds to the text of the
	     * index entry
	     */
	    $minfo = $p->info_matchbox("*", $i, "name");
	
	    $mname = $p->get_parameter("string", $minfo);
	    $action = $p->create_action("GoTo", "destination={page=" .
		($sortedIndex[$entryno]{"page"}) . "}");
	    
	    /* With the "GoTo" action, create a Link annotation on the 
	     * matchbox defined above. 0 rectangle coordinates will be
	     * replaced with matchbox coordinates.
	     */
	    $p->create_annotation(0, 0, 0, 0, "Link", 
		"action={activate " . $action . "} linewidth=0 " . 
		"usematchbox={" . $mname . "}");
	    
	    $entryno++;
	}
	$p->end_page_ext("");
	
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if (!$result == "_stop")
    {
	/* "_boxempty" happens if the box is very small and doesn't
	 * hold any text at all.
	 */
	if ($result ==  "_boxempty")
	    throw new Exception ("Error: Textflow box too small");
	else
	{
	    /* Any other return value is a user exit caused by
	     * the "return" option; this requires dedicated code to
	     * deal with.
	     */
	    throw new Exception ("User return '" . $result .
		"' found in Textflow");
	}
    }

    $p->delete_textflow($idx);

    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=create_interactive_index.pdf");
    print $buf;

    } catch (PDFlibException $e) {
        die("PDFlib exception occurred:\n".
            "[" . $e->get_errnum() . "] " . $e->get_apiname() .
            ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
        die($e->getMessage());
    }

$p=0;

function cmp($a, $b) {
    return strcmp($a["name"], $b["name"]);
}
?>
