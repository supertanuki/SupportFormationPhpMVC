<?php
/* $Id: web_colornames.php,v 1.3 2012/05/03 14:00:39 stm Exp $
 * Web color names:
 * View a list of all cross-browser colors including the color name and 
 * hexadecimal color value as well as the corresponding RGB color values used
 * in PDFlib.
 * 
 * Cross-browser color names are a collection of nearly 150 color names which
 * are supported by all major browsers. For more information, see 
 * http://www.w3schools.com/html/html_colors.asp
 * http://www.w3.org/TR/SVG/types.html
 * http://en.wikipedia.org/wiki/Web_colors
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * RGB color values work fine with PDFlib Lite, it's only the PDFlib table
 * feature which is used to present the color list which is not supported by
 * PDFlib Lite.
 *    
 * Required data: none
 */

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$title = "Web Color Names";

$tbl=0;

$margin = 6;
$fontsize = 12;

/* Height of a table row which is the sum of a font size of 12 and the upper
 * and lower cell margin of 3 each
 */
$rowheight = 18;

/* Number of rows per table fitbox */
$nrows = 44;

/* Width of the first, second, and third column of the table */
$c1 = 130;
$c2 = 100;
$c3 = 100;
$c4 = 150;

/* Coordinates of the lower left corner of the table fitbox */
$llx = 35; 
$lly = 30;



/* HTML color values and names */
$colors  =array(

    "800000", "Maroon",
    "8B0000", "DarkRed",
    "B22222", "FireBrick",
    "FF0000", "Red",
    "FA8072", "Salmon",
    "FF6347", "Tomato",
    "FF7F50", "Coral",
    "FF4500", "OrangeRed",
    "D2691E", "Chocolate",
    "F4A460", "SandyBrown",
    "FF8C00", "DarkOrange",
    "FFA500", "Orange",
    "B8860B", "DarkGoldenrod",
    "DAA520", "Goldenrod",
    "FFD700", "Gold",
    "808000", "Olive",
    "FFFF00", "Yellow",
    "9ACD32", "YellowGreen",
    "ADFF2F", "GreenYellow",
    "7FFF00", "Chartreuse",
    "7CFC00", "LawnGreen",
    "008000", "Green",
    "00FF00", "Lime",
    "32CD32", "LimeGreen",
    "00FF7F", "SpringGreen",
    "00FA9A", "MediumSpringGreen",
    "40E0D0", "Turquoise",
    "20B2AA", "LightSeaGreen",
    "48D1CC", "MediumTurquoise",
    "008080", "Teal",
    "008B8B", "DarkCyan",
    "00FFFF", "Aqua",
    "00FFFF", "Cyan",
    "00CED1", "DarkTurquoise",
    "00BFFF", "DeepSkyBlue",
    "1E90FF", "DodgerBlue",
    "4169E1", "RoyalBlue",
    "000080", "Navy",
    "00008B", "DarkBlue",
    "0000CD", "MediumBlue",
    "0000FF", "Blue",
    "8A2BE2", "BlueViolet",
    "9932CC", "DarkOrchid",
    "9400D3", "DarkViolet",
    "800080", "Purple",
    "8B008B", "DarkMagenta",
    "FF00FF", "Fuchsia",
    "FF00FF", "Magenta",
    "C71585", "MediumVioletRed",
    "FF1493", "DeepPink",
    "FF69B4", "HotPink",
    "DC143C", "Crimson",
    "A52A2A", "Brown",
    "CD5C5C", "IndianRed",
    "BC8F8F", "RosyBrown",
    "F08080", "LightCoral",
    "FFFAFA", "Snow",
    "FFE4E1", "MistyRose",
    "E9967A", "DarkSalmon",
    "FFA07A", "LightSalmon",
    "A0522D", "Sienna",
    "FFF5EE", "SeaShell",
    "8B4513", "SaddleBrown",
    "FFDAB9", "Peachpuff",
    "CD853F", "Peru",
    "FAF0E6", "Linen",
    "FFE4C4", "Bisque",
    "DEB887", "Burlywood",
    "D2B48C", "Tan",
    "FAEBD7", "AntiqueWhite",
    "FFDEAD", "NavajoWhite",
    "FFEBCD", "BlanchedAlmond",
    "FFEFD5", "PapayaWhip",
    "FFE4B5", "Moccasin",
    "F5DEB3", "Wheat",
    "FDF5E6", "Oldlace",
    "FFFAF0", "FloralWhite",
    "FFF8DC", "Cornsilk",
    "F0E68C", "Khaki",
    "FFFACD", "LemonChiffon",
    "EEE8AA", "PaleGoldenrod",
    "BDB76B", "DarkKhaki",
    "F5F5DC", "Beige",
    "FAFAD2", "LightGoldenrodYellow",
    "FFFFE0", "LightYellow",
    "FFFFF0", "Ivory",
    "6B8E23", "OliveDrab",
    "556B2F", "DarkOliveGreen",
    "8FBC8F", "DarkSeaGreen",
    "006400", "DarkGreen",
    "228B22", "ForestGreen",
    "90EE90", "LightGreen",
    "98FB98", "PaleGreen",
    "F0FFF0", "Honeydew",
    "2E8B57", "SeaGreen",
    "3CB371", "MediumSeaGreen",
    "F5FFFA", "Mintcream",
    "66CDAA", "MediumAquamarine",
    "7FFFD4", "Aquamarine",
    "2F4F4F", "DarkSlateGray",
    "AFEEEE", "PaleTurquoise",
    "E0FFFF", "LightCyan",
    "F0FFFF", "Azure",
    "5F9EA0", "CadetBlue",
    "B0E0E6", "PowderBlue",
    "ADD8E6", "LightBlue",
    "87CEEB", "SkyBlue",
    "87CEFA", "LightskyBlue",
    "4682B4", "SteelBlue",
    "F0F8FF", "AliceBlue",
    "708090", "SlateGray",
    "778899", "LightSlateGray",
    "B0C4DE", "LightsteelBlue",
    "6495ED", "CornflowerBlue",
    "E6E6FA", "Lavender",
    "F8F8FF", "GhostWhite",
    "191970", "MidnightBlue",
    "6A5ACD", "SlateBlue",
    "483D8B", "DarkSlateBlue",
    "7B68EE", "MediumSlateBlue",
    "9370DB", "MediumPurple",
    "4B0082", "Indigo",
    "BA55D3", "MediumOrchid",
    "DDA0DD", "Plum",
    "EE82EE", "Violet",
    "D8BFD8", "Thistle",
    "DA70D6", "Orchid",
    "FFF0F5", "LavenderBlush",
    "DB7093", "PaleVioletRed",
    "FFC0CB", "Pink",
    "FFB6C1", "LightPink",
    "000000", "Black",
    "696969", "DimGray",
    "808080", "Gray",
    "A9A9A9", "DarkGray",
    "C0C0C0", "Silver",
    "D3D3D3", "LightGrey",
    "DCDCDC", "Gainsboro",
    "F5F5F5", "WhiteSmoke",
    "FFFFFF", "White"
);


try {
    $p = new PDFlib();

    $p->set_parameter("SearchPath", $searchpath);

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");

    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Load the font */
    $font = $p->load_font("Courier", "unicode", "");
    if ($font == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    /* Set some general cell options shared between all cells */
    $optlist = "fittextline={font=" . $font . " fontsize=" . $fontsize .
	" position={left center}} rowheight=" . $rowheight .
	" margin=" . $margin . " ";
     
    
    /* ----------------------------------
     * Add a header containing four cells
     * ----------------------------------
     */
 
    /* Set the current row */
    $row = 1;
    
    $cellopts = $optlist . "colwidth=" . $c1;
    
    $tbl = $p->add_table_cell($tbl, 1, $row, "Web Color Name", $cellopts);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $cellopts = $optlist . "colwidth=" . $c2;

    $tbl = $p->add_table_cell($tbl, 2, $row, "Hex RGB Value", $cellopts);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $cellopts = $optlist . "colwidth=" . $c3;

    $tbl = $p->add_table_cell($tbl, 3, $row, "   Color", $cellopts);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $cellopts = $optlist . "colwidth=" . $c4;

    $tbl = $p->add_table_cell($tbl, 4, $row, "  RGB Value in PDFlib", $cellopts);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ------------------------------------------------
     * For each color, add a row containing three cells
     * ------------------------------------------------
     */
    $row = 2;
    
    for ($i = 0; $i < count($colors); $i+=2)
    {
	
    /* --------------------------------------------------------------
     * Add the HTML color name in the first column of the current row
     * --------------------------------------------------------------
     */
    $cellopts = $optlist . "colwidth=" . $c1;

    $tbl = $p->add_table_cell($tbl, 1, $row, $colors[$i+1], $cellopts);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ---------------------------------------------
     * Add the HTML color value in the second column
     * ---------------------------------------------
     */
    $cellopts = $optlist . "colwidth=" . $c2;
    
    $tbl = $p->add_table_cell($tbl, 2, $row, "#" . $colors[$i], $cellopts);
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ---------------------------------------------------------------------
     * Retrieve the RGB values used in PDFlib for the current color.
     * Fetch the value of the i-th color. It consists of three pairs of hex
     * digits. Each pair indicates the Red, Green, or Blue component in the
     * range 0-FF which corresponds to the range 0-255 (decimal). To convert
     * each of the three bytes to the range 0-1 as used by PDFlib divide it
     * by 255.
     * ---------------------------------------------------------------------
     */
    $color = $colors[$i];
	   
    $red = hexdec(substr($color, 0, 2)) / 255.0;
    $green = hexdec(substr($color, 2, 2)) / 255.0;
    $blue = hexdec(substr($color, 4, 2)) / 255.0;

    
    /* --------------------------------------------------------------
     * Add a rectangle filled with the HTML color in the third column
     * --------------------------------------------------------------
     *
     * Since the cell doesn't cover a complete row but only one column it
     * cannot be filled with color using one of the row-based shading
     * options. We apply the Matchbox feature instead to fill the rectangle
     * covered by the cell with the RGB color value calculated above. 
     */
    $cellopts = "colwidth=" . $c3 . " matchbox={fillcolor={rgb " . $red . " " .
	$green . " " . $blue . "}}";
    
    $tbl = $p->add_table_cell($tbl, 3, $row, "", $cellopts);
    if ($tbl == 0)
	    throw new Exception("Error: " . $p->get_errmsg());
    
    
    /* ---------------------------------------------------------------------
     * Add the PDFlib color value in the fourth column of the current row.
     * Allow a maximum (and a minimum) of four digits in the fraction
     * portion of the number and output the PDFlib color values accordingly.
     * ---------------------------------------------------------------------
     */
    $cellopts = $optlist . " colwidth=" . $c4 . " marginleft=20";
    
    
    $tbl = $p->add_table_cell($tbl, 4, $row, sprintf("%.4f", $red) . " " .
	sprintf("%.4f", $green) . " " . sprintf("%.4f", $blue), $cellopts);
    
    if ($tbl == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $row++;
    } /* for */
    
    
    /* -----------------------------------------------------------------
     * Fit the table. Using "header=1" the table header will include the
     * first line. Using "line=horother linewidth=0.3" the ruling is 
     * specified with a line width of 0.3 for all horizontal lines. 
     * -----------------------------------------------------------------
     */
    $optlist = "header=1 stroke={ {line=horother linewidth=0.3}}";
    
    do {
	$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

	/* Place the table instance */
	$result = $p->fit_table($tbl, $llx, $lly, $llx + $c1 + $c2 + $c3 + $c4, 
	    $lly + $nrows * $rowheight, $optlist);

	if ($result == "_error")
	    throw new Exception ("Couldn't place table : " .
		$p->get_errmsg());

	$p->end_page_ext("");

    } while ($result == "_boxfull");

    /* Check the result; "_stop" means all is ok. */
    if (!$result == "_stop")
    {
	if ($result ==  "_error")
	{
	    throw new Exception ("Error when placing table: " .
		$p->get_errmsg());
	}
    }
    
    /* This will also delete Textflow handles used in the table */
    $p->delete_table($tbl, "");  
    
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=web_colornames.pdf");
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

