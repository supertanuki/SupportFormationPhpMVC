<?php
/* $Id: wrap_text_around_images.php,v 1.2 2012/05/03 14:00:38 stm Exp $ 
 * Wrap text around images:
 * Place images within a Textflow
 *
 * Case 1:
 * Place an image at a fixed position on the page. Use the "matchbox" option of
 * fit_image() to define the matchbox rectangle. Use the "usematchbox" option of
 * fit_textflow() to wrap the text around the matchbox rectangle for the image.
 * Case 2:
 * Place an image left-aligned and an image right-aligned at defined positions
 * within the Textflow. Use the "matchbox" option of fit_image() to define the
 * matchbox rectangle of the image. Use specific return values of fit_textflow()
 * for indicating the text line for the image to be placed.
 * Case 3:
 * To place some small icons at the beginning of some text lines in a 
 * Textflow use the inline options "matchbox" and "matchbox end" within the 
 * Textflow as well as the info_matchbox() function to retrieve the matchbox
 * instances and dimensions. 
 * Case 4: 
 * Place some images at certain text positions within a Textflow by using the 
 * "matchbox" and "matchbox end" inline options when placing the Textflow for
 * indicating the image positions.
 * Case 5: 
 * Place an image which covers several lines of text at a certain text position
 * within a Textflow. Use the "matchbox" and "matchbox end" inline options when
 * placing the Textflow for indicating the image position. Use the
 * "createwrapbox" option to indicate that the matchbox will be inserted as wrap
 * box in the Textflow for the text to wrap around.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: image file
 */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Wrap Text around Images";

$tf = 0;
$imageoptlist = ""; $numoptlist = ""; $textoptlist = "";
$llx = 100; $lly = 50; $urx = 450; $ury = 800;
$posx = 0; $posy = 0;
 

try {
    $p = new pdflib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    $p->set_parameter("charref", "true");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    
    /* ------------------------------------------------------------------
     * Case 1:
     * Place an image at a fixed position on the page. Use the "matchbox" 
     * option of fit_image() to define the matchbox rectangle. Use the
     * "usematchbox" option of fit_textflow() to wrap the text around the
     * matchbox rectangle for the image.
     * ------------------------------------------------------------------
     */
    
    /* Text to be placed on the page. Soft hyphens are marked with the 
     * character reference "&shy;" (character references are enabled by the
     * "charref" option).
     */
    $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new develop&shy;ments of the traditional " .
	"common paper planes. If your lesson, conference, or lecture " .
	"turn out to be deadly boring, you can have a wonderful time " .
	"with our planes. All our models are fol&shy;ded from one paper " .
	"sheet. They are exclu&shy;sively folded with&shy;out using any " .
	"adhesive. Several models are equipped with a folded landing " .
	"gear enabling a safe landing on the intended loca&shy;tion " .
	"provided that you have aimed well. Other models are able to fly " .
	"loops or cover long distances. Let them start from a vista " .
	"point in the mountains and see where they touch the ground. ";
    
    /* Option list for the output of a number */
    $numoptlist =
	"fontname=Helvetica-Bold fontsize=14 encoding=unicode " .
	"fillcolor={rgb 0.6 0.6 0.8} charref";
    
    /* Option list for the text output */
    $textoptlist =
	"fontname=Helvetica fontsize=10.5 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify";
    
    /* Load the image. Assign a matchbox called "img" to it to indicate the
     * rectangle for the text to wrap around later.
     */
    $image = $p->load_image("auto", "kraxi_logo_text.tif", "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Repeat some dummy text to produce more contents, place a number 
     * before each text and feed them to a Textflow object with alternating
     * options.
     */
    $count = 7;
    
    for ($i=1; $i<=$count; $i++)
    {
    $num = $i . " ";

    $tf = $p->add_textflow($tf, $num, $numoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tf = $p->add_textflow($tf, $text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    }

    /* Loop until all of the text is placed; create new pages
     * as long as more text needs to be placed.
     */
    $p->create_bookmark("Case 1: \"matchbox\" option of fit_image() and " .
	"\"wrap\" option of fit_textflow()",
	"");
    do
    {
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
  
    /* Place the image on a fixed position on the page. Assign a matchbox
     * called "img" to it to indicate the image rectangle to wrap the text
     * around later.
     */
    $p->fit_image($image, 200, 370, "boxsize={300 200} fitmethod=meet " .
	"position=center matchbox={name=img margin=-10}");

    /* Place the text while wrapping it around the matchbox called "img" */
    $result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	"verticalalign=justify linespreadlimit=120% " .
	"wrap={usematchboxes={{img}}}");

    $p->end_page_ext("");

    /* "_boxfull" means we must continue because there is more text;
     * "_nextpage" is interpreted as "start new column"
     */
    } while ($result == "_boxfull" || $result == "_nextpage");

    /* Check for errors */
    if ($result != "_stop")
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
    $p->close_image($image);

    $p->delete_textflow($tf);
    
    
    /* ---------------------------------------------------------------------
     * Case 2:
     * Place an image left-aligned and an image right-aligned at defined
     * positions within the Textflow. Use the "matchbox" option of 
     * fit_image() to define the matchbox rectangle of the image. Use 
     * specific return values of fit_textflow() for indicating the text line
     * for the image to be placed.
     * --------------------------------------------------------------------- 
     */
    
    /* Text to be placed on the plage. Soft hyphens are marked with the 
     * character reference "&shy;" (character references are enabled by the
     * "charref" option).
     */
    $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new develop&shy;ments of the traditional " .
	"common paper planes. If your lesson, conference, or lecture " .
	"turn out to be deadly boring, you can have a wonderful time " .
	"with our planes. All our models are fol&shy;ded from one paper " .
	"sheet. They are exclu&shy;sively folded with&shy;out using any " .
	"adhesive. Several models are equipped with a folded landing " .
	"gear enabling a safe landing on the intended loca&shy;tion " .
	"provided that you have aimed well. Other models are able to fly " .
	"loops or cover long distances. Let them start from a vista " .
	"point in the mountains and see where they touch the ground. ";
    
    /* Option list for the text to be added */
    $textoptlist =
	"fontname=Helvetica fontsize=10.5 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify";

    /* Add some text to the Textflow. Then add two nextlines and define the
     * return value "imageleft". Later, the Textflow portion defined above
     * will be placed with fit_textflow() and the "imageleft" value will be
     * returned indicating that now the left-aligned image should be placed.
     */
    $tf = 0;
    $tf = $p->add_textflow($tf, $text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tf = $p->add_textflow($tf, "", "nextline nextline return imageleft");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    /* Add some more text to the Textflow. Similar to the "imageleft"
     * return value above define the "imageright" return value to indicate
     * that now the right-aligned image should be placed. 
     */
    $tf = $p->add_textflow($tf, $text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    $tf = $p->add_textflow($tf, "", "nextline nextline return imageright");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $tf = $p->add_textflow($tf, $text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $image = $p->load_image("auto", "kraxi_logo.tif", "");
    if ($image == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $p->create_bookmark("Case 2: Specific Textflow return values to " .
	"indicate image positions", "");

    $posy = $ury;
    $ismatchbox = false;
    
    do
    {
	/* Fit the text and wrap it around the matchbox called "image" 
	 * if it is defined yet
	 */
	if ($ismatchbox) {
	    $textoptlist = "verticalalign=justify linespreadlimit=120% " .
	    "wrap={usematchboxes={{image}}} ";
	}
	else {
	    $textoptlist = "verticalalign=justify linespreadlimit=120% ";
	}
	
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $posy, $textoptlist);
	
	/* Retrieve the current text position */
	$posy = $p->info_textflow($tf, "textendy");

	if ($result == "imageleft"){
	    /* Textflow interrupted returning the keyword "imageleft".
	     * Place the image on the current left position of the Textflow
	     * fitbox.
	     */
	    $posx = $llx;
	    $imageoptlist = "position {0 100} matchbox={name=image " .
		"offsetright=10 offsettop=10 offsetbottom=-10}";
	    /* Reduce the posy position by a value similar to the 
	     * "offsettop" value defined above to create some distance
	     * from the previous text
	     */
	    $p->fit_image($image, $posx, $posy-10, $imageoptlist);
	    $ismatchbox = true;
	}
	if ($result == "imageright"){
	    /* Textflow interrupted with the keyword "imageleft".
	     * Place the image to the current left position of the fitbox.
	     */
	    $posx = $urx;
	    $imageoptlist = "position {100 100} matchbox={name=image " .
		"offsetleft=-10 offsettop=10 offsetbottom=-10}";
	    
	    /* Reduce the posy position by a value similar to the 
	     * "offsettop" value defined above to create some distance from
	     * the previous text
	     */
	    $p->fit_image($image, $posx, $posy-10, $imageoptlist);
	    $ismatchbox = true;
	}
	/* Create a new page if the text cannot be fit completely into the
	 * box
	 */ 
	if ($result == "_boxfull" || $result == "_boxempty"){
	    $p->end_page_ext("");
	    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	    $posy = $ury;
	}
	/* Go ahead with the rest of the text, until the text has been
	 * finished
	 */
    } while ($result != "_stop");

    $p->delete_textflow($tf);

    $p->end_page_ext("");
    $p->close_image($image);
    
    
    /* ------------------------------------------------------------------
     * Case 3:
     * Place some small icons at the beginning of the lines in a Textflow
     * using the inline options "matchbox" and "matchbox end" within the
     * Textflow.
     * ------------------------------------------------------------------
     */ 
  
    /* Text containing the macros defined in the option list below */
    $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new developments of the traditional common " .
	"paper planes. If your lesson, conference, or lecture turn out " .
	"to be deadly boring, you can have a wonderful time with our " .
	"planes. All our models are folded from one paper sheet. They " .
	"are exclusively folded without using any adhesive. Several " .
	"models are equipped with a folded landing gear enabling a safe " .
	"landing on the intended location provided that you have aimed " .
	"well. Other models are able to fly loops or cover long " .
	"distances. Let them start from a vista point in the mountains " .
	"and see where they touch the ground." .
	"<nextline><nextline>" .
	"Have a look at our new paper plane models!" .
	"<nextline><nextparagraph>" .
	"<&new><&end>Long Distance Glider <nextline>".
	"With this paper rocket you can send all your messages even when " .
	"sitting in a hall or in the cinema pretty near the back. " .
	"<nextline><nextparagraph>" .
	"<&arrow><&end>Giant Wing<nextline>" .
	"An unbelievable sailplane! It is amazingly robust and can even " .
	"do aerobatics. But it best suited to gliding." .
	"<nextline><nextparagraph>" .
	"<&new><&end>Cone Head Rocket<nextline>" .
	"This paper arrow can be thrown with big swing. We launched it " .
	"from the roof of a hotel. It stayed in the air a long time and " .
	"covered a considerable distance. " .
	"<nextline><nextparagraph>" .
	"<&arrow><&end>Super Dart<nextline>" .
	"The super dart can fly giant loops with a radius of 4 or 5 " .
	"metres and cover very long distances. Its heavy cone point is " .
	"slightly bowed upwards to get the lift required for loops." .
	"<nextline><nextparagraph>" .
	"<&arrow><&end>German Bi-Plane<nextline>" .
	"Brand-new and ready for take-off. If you have lessons in the " .
	"history of aviation you can show your interest by letting it " .
	"land on your teacher's desk." .
	"<nextline leftindent=0><nextparagraph>" .
	"To fold the famous rocket looper proceed as follows:" .
	"<nextparagraph><nextline>" .
	"Take a A4 sheet." .
	"Fold it lengthwise in the middle." .
	"Then, fold the upper corners down. " .
	"Fold the long sides inwards " .
	"that the points A and B meet on the central fold." .
	"<nextparagraph><nextline>" .
	"Fold the points C and D that the upper " .
	"corners meet with the central fold as well. " .
	"Fold the plane in the middle. Fold the wings " .
	"down that they close with the lower border of the plane.";
    
    /* Option list with some text options and the three macros "arrow", 
     * "new", and "end" to be used as inline options in the "features" text
     * below to indicate where to leave some space for the respective images
     * to be placed and the text to wrap around it.
     */
    $textoptlist =
	"macro {" .
	"new {matchbox={name=new boxwidth=15 boxheight=" .
	"    {ascender descender}} leftindent=15 " .
	"    parindent=-15} " .
	"arrow {matchbox={name=arrow boxwidth=15 boxheight={ascender " .
	"    descender}} leftindent=15 parindent=-15} " .
	"end {matchbox={end}} } " .
	"fontname=Helvetica fontsize=10.5 encoding=unicode " .
	"fillcolor={gray 0} alignment=justify";
   
    $matchboxname = array(
	    array("new", "new.jpg"),
	    array("arrow", "arrow.jpg")
    );
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create a bookmark on the current page */
    $p->create_bookmark("Case 3: Inline options \"matchbox\" and " .
	"\"matchbox end\" for create_textflow()", "");
    
    /* Create a Textflow containing inline options to define the matchboxes
     * "arrow" and "new" which indicates the positions for the arrow and the
     * new image to be placed.
     */        
    $tf = $p->create_textflow($text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    do {
	/* Fit the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	    "verticalalign=justify linespreadlimit=120% ");
	
	/* Loop over all icons ("new" and "arrow" in our case) to be placed
	 * in the respective matchboxes
	 */
	for ($m=0; $m < count($matchboxname); $m++){
	    $icon = $p->load_image("auto", $matchboxname[$m][1], "");
	    if ($icon == 0)
		throw new Exception("Error: " . $p->get_errmsg());

	    /* Retrieve the number of instances of the matchbox */
	    $numberOfMatchbox = 
		$p->info_matchbox($matchboxname[$m][0], 0, "count");
	    
	    /* Iterate over all matchbox instances and fill them with the
	     * icon
	     */
	    for ($i=1; $i<= $numberOfMatchbox; $i++)
	    {
		$x1 = $p->info_matchbox($matchboxname[$m][0], $i, "x1");
		$y1 = $p->info_matchbox($matchboxname[$m][0], $i, "y1");
		$width = $p->info_matchbox($matchboxname[$m][0], $i, "width");
		$height = $p->info_matchbox($matchboxname[$m][0], $i, "height");
		$p->fit_image($icon, $x1, $y1,
		    "boxsize {" . $width . " " . $height .
		    "} fitmethod meet");
	    }
	}
	if ($result == "_boxfull"){
	    $p->end_page_ext("");
	    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	    $posy = $ury;
	}
    } while ($result != "_stop");

    $p->delete_textflow($tf);
    
    $p->end_page_ext("");
    

    /* ------------------------------------------------------------------
     * Case 4: 
     * Place images at certain text positions within a line of a Textflow
     * by using the "matchbox" and "matchbox end" inline options when
     * placing the Textflow for indicating the image positions.
     * ------------------------------------------------------------------
     */
    
    /* Text containing the macros defined in the option list below */
    $text =
	"Have a look at our new paper plane models!" .
	"<nextline><nextparagraph>" .
	"Long Distance Glider <nextline>".
	"With this paper rocket you can send all your messages even " .
	"when sitting in a hall or in the cinema pretty near the back. " .
	"Print a photo of the paper plane by pressing the " .
	"<&print><&end> button. " .
	"Save a description of the paper plane by pressing the ".
	"<&saveas><&end> button.";
    
    /* Options list for creating the Textflow.
     * For each image to be placed within the Textflow a macro is defined
     * to specify the matchbox rectangle for the image to be placed in and 
     * the Textflow to wrap around.   
     * The macro "print" specifies a matchbox called "print". 
     * "boxwidth=40" defines the width of the matchbox rectangle.
     * "boxheight {ascender descender}" defines the vertical extent of the 
     * matchbox rectangle using the ascender of the font on the top and the
     * descender at the bottom. "offsettop=2" adds an offset of 2 on the top
     * of the rectangle.
     * The macro "saveas" specifies a matchbox called "saveas" with similar
     * options.
     * The macro "end" is used to finish the matchbox.
     */
    $textoptlist =
	"macro {" .
	"print {matchbox={name=print boxwidth=40 " .
	    "boxheight={ascender descender} offsettop=2}} " .
	"saveas {matchbox={name=saveas boxwidth=60 " .
	    "boxheight={ascender descender} offsettop=2}} " .
	"end {matchbox={end}} } " .
	"fontname=Helvetica fontsize=14 encoding=unicode " .
	"fillcolor={gray 0} leading=140% alignment=justify";
    
    $matchboxnames = array(
	    array("print", "fileprint.jpg"),
	    array("saveas", "filesaveas.jpg")
    );

    /* Add some text to the Textflow */
    $tf = $p->create_textflow($text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    /* Create a bookmark on the current page */
    $p->create_bookmark("Case 4: Inline options \"matchbox\" and " .
	"\"matchbox end\" for create_textflow()", "");
	
    $posy = $ury;
    
    do {
	/* Fit the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	    "verticalalign=justify linespreadlimit=120% ");
	
	/* Loop over all icons ("print" and "saveas" in our case) to be
	 * placed  in the respective matchboxes
	 */
	for ($m=0; $m < count($matchboxnames); $m++){
	    $icon = $p->load_image("auto", $matchboxnames[$m][1], "");
	    if ($icon == 0)
		throw new Exception("Error: " . $p->get_errmsg());

	    /* Retrieve the number of instances of the matchbox */
	    $numberOfMatchbox = 
		$p->info_matchbox($matchboxnames[$m][0], 0, "count");
	    
	    /* Iterate over all matchbox instances and fill them with the
	     * icon
	     */
	    for ($i=1; $i<= $numberOfMatchbox; $i++)
	    {
		$x1 = $p->info_matchbox($matchboxnames[$m][0], $i, "x1");
		$y1 = $p->info_matchbox($matchboxnames[$m][0], $i, "y1");
		$width = $p->info_matchbox($matchboxnames[$m][0], $i, "width");
		$height = $p->info_matchbox($matchboxnames[$m][0], $i, "height");
		$p->fit_image($icon, $x1, $y1,
		    "boxsize {" . $width . " " . $height .
		    "} fitmethod meet position=center");
	    }
	}
	if ($result == "_boxfull"){
	    $p->end_page_ext("");
	    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	    $posy = $ury;
	}
	else if ($result != "_stop")
	{
	    /* "_boxempty" happens if the box is very small and doesn't
	     * hold any text at all.
	     */
	    if ($result == "_boxempty")
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
    } while ($result != "_stop");

    $p->delete_textflow($tf);

    $p->end_page_ext("");
    $p->close_image($image);
    
    
    /* --------------------------------------------------------------------
     * Case 5: 
     * Place an image which covers several lines of text at a certain text
     * position within a Textflow. Use the "matchbox" and "matchbox end"
     * inline options when placing the Textflow for indicating the image
     * position. Use the "createwrapbox" option to indicate that the
     * matchbox will be inserted as wrap box in the Textflow for the text
     * to wrap around.
     * --------------------------------------------------------------------
     */
    
    /* Text which is placed on the page. Soft hyphens are marked
     * with the character reference "&shy;" (character references are 
     * enabled by the charref option).
     */
    $text =
	"Our paper planes are the ideal way of passing the time. We " .
	"offer revolutionary new develop&shy;ments of the traditional " .
	"common paper planes. If your lesson, conference, or lecture " .
	"turn out to be deadly boring, you can have a wonderful time " .
	"with our planes. All our models are fol&shy;ded from one paper " .
	"sheet.<&plane><&end>They are exclu&shy;sively folded " .
	"with&shy;out using any ad&shy;hesive. Several models are " .
	"equipped with a folded landing gear enabling a safe landing on " .
	"the intended loca&shy;tion provided that you have aimed well. " .
	"Other models are able to fly loops or cover long distances. " .
	"Let them start from a vista point in the mountains and see " .
	"where they touch the ground. ";
    
    /* Options list for creating the Textflow.
     * For the image to be placed within the Textflow a macro is defined
     * to specify the matchbox rectangle for the image to be placed in and 
     * the Textflow to wrap around.   
     * The macro "plane" specify a matchbox called "plane". 
     * "boxwidth=70" defines the width of the matchbox rectangle.
     * "boxheight {12 24}" defines the vertical extent of the matchbox 
     * rectangle with 12 above and 24 below the baseline of the text line.
     * "offsetleft=4" and "offsetright=-4" add some empty space on the left
     * and right of the matchbox rectangle. 
     * "createwrapbox" indicates that the matchbox will be inserted as wrap
     * box in the Textflow for the text to wrap around.
     * The macro "end" is used to finish the matchbox.
     */
    $textoptlist =
	"macro {" .
	"plane {matchbox={name=plane boxwidth=70 boxheight={12 24} " .
	    "offsetleft=4 offsetright=-4 createwrapbox}} " .
	"end {matchbox={end}} } " .
	"fontname=Helvetica fontsize=12 encoding=unicode " .
	"fillcolor={gray 0} leading=140% alignment=justify";
    
    /* Add some text to the Textflow */
    $tf = $p->create_textflow($text, $textoptlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $p->create_bookmark("Case 5: \"createwrapbox\" option of " .
	"create_textflow() to wrap text around image covering several " .
	"text lines", "");
    
    $posy = $ury;
    
    do {
	/* Fit the Textflow */
	$result = $p->fit_textflow($tf, $llx, $lly, $urx, $ury,
	    "verticalalign=justify linespreadlimit=120% ");
	
	$image = $p->load_image("auto", "kraxi_logo.tif", "");
	if ($image == 0)
	    throw new Exception("Error: " . $p->get_errmsg());

	/* Retrieve the number of instances of the matchbox */
	$numberOfMatchbox = 
	    $p->info_matchbox("plane", 0, "count");
	    
	/* Iterate over all matchbox instances and fill them with the
	 * image. In our case just one instance is present.
	 */
	for ($i=1; $i<= $numberOfMatchbox; $i++)
	{
	    $x1 = $p->info_matchbox("plane", $i, "x1");
	    $y1 = $p->info_matchbox("plane", $i, "y1");
	    $width = $p->info_matchbox("plane", $i, "width");
	    $height = $p->info_matchbox("plane", $i, "height");
	    $p->fit_image($image, $x1, $y1, "boxsize {" . $width . " " . $height .
		"} fitmethod meet position=center");
	}
	if ($result == "_boxfull"){
	    $p->end_page_ext("");
	    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
	    $posy = $ury;
	}
	else if ($result != "_stop")
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
    } while ($result != "_stop");

    $p->delete_textflow($tf);

    $p->end_page_ext("");
    $p->close_image($image);
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=wrap_text_around_images.pdf");
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
