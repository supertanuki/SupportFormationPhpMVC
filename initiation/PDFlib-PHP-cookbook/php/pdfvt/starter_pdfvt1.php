<?php

# $Id: starter_pdfvt1.php,v 1.3 2012/05/03 14:00:43 stm Exp $
#
# Starter sample for PDF/VT-1
# Create a large number of invoices in a single PDF and make use of
# the following PDF/VT-1 features:
# - create a document part (DPart) hierarchy
# - assign PDF/VT scope attributes to images and imported PDF pages
# - add document part metadata (DPM) to the DPart root node and all page nodes
#
# Required software: PDFlib+PDI/PPS 8.1
# Required data: PDF background, fonts, several raster images
#

define('MAXRECORD', 100);

$i;
$stationery;
$page;
$record;
$barcodeimage;
$stationeryfilename = "stationery_pdfx4p.pdf";
$salesrepfilename = "sales_rep%d.jpg";
$fontname = "DejaVuSerif";

# This is where font/image/PDF input files live. Adjust as necessary.
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";

$left = 55;
$right = 530;


$fontsize = 12;
$leading;
$x;
$y;
$sum;
$total;
$buf;
$optlist;
$baseopt =
    "encoding=host embedding " .
    "ruler       ={   30 45     275   375   475} " .
    "tabalignment={right left right right right} " .
    "hortabmethod=ruler fontsize=12 ";
$textflow;
# TODO: proper choice of fonts */
$closingtext =
    "Terms of payment: <fillcolor={cmyk 0 1 1 0}>30 days net. " .
    "<fillcolor={gray 0}>90 days warranty starting at the day of sale. " .
    "This warranty covers defects in workmanship only. " .
    "<fontname=DejaVuSerif embedding encoding=host>Kraxi Systems, Inc. " .
    "<resetfont>will, at its option, repair or replace the " .
    "product under the warranty. This warranty is not transferable. " .
    "No returns or exchanges will be accepted for wet products.";

$articledata = array(
    array( "name"=>"Super Kite",		"price"=>20,  "quantity"=>2),
    array( "name"=>"Turbo Flyer",		"price"=>40,  "quantity"=>5),
    array( "name"=>"Giga Trash",		"price"=>180, "quantity"=>1),
    array( "name"=>"Bare Bone Kit",	"price"=>50,  "quantity"=>3),
    array( "name"=>"Nitty Gritty",	"price"=>20,  "quantity"=>10),
    array( "name"=>"Pretty Dark Flyer",	"price"=>75,  "quantity"=>1),
    array( "name"=>"Large Hadron Glider",	"price"=>85,  "quantity"=>1),
    array( "name"=>"Flying Bat",         	"price"=>25,  "quantity"=>1),
    array( "name"=>"Simple Dimple",      	"price"=>40,  "quantity"=>1),
    array( "name"=>"Mega Sail",          	"price"=>95,  "quantity"=>1),
    array( "name"=>"Tiny Tin",           	"price"=>25,  "quantity"=>1),
    array( "name"=>"Monster Duck",      	"price"=>275, "quantity"=>1),
    array( "name"=>"Free Gift",		"price"=>0,   1),
);

$addressdata = array(
    array( "firstname"=>"Edith", "lastname"=>"Poulard", "flat"=>"Suite C",
			    "street"=>"Main Street", "city"=>"New York"),
    array( "firstname"=>"Max", "lastname"=>"Huber", "flat"=>"",
			    "street"=>"Lipton Avenue", "city"=>"Albuquerque"),
    array( "firstname"=>"Herbert", "lastname"=>"Pakard", "flat"=>"App. 29",
			    "street"=>"Easel", "city"=>"Duckberg" ),
    array( "firstname"=>"Charles", "lastname"=>"Fever", "flat"=>"Office 3",
			    "street"=>"Scenic Drive", "city"=>"Los Angeles" ),
    array( "firstname"=>"D.", "lastname"=>"Milliband", "flat"=>"",
			    "street"=>"Old Harbour", "city"=>"Westland" ),
    array( "firstname"=>"Lizzy", "lastname"=>"Tin", "flat"=>"Workshop",
			    "street"=>"Ford", "city" => "Detroit" ),
    array( "firstname"=>"Patrick", "lastname"=>"Black", "flat"=>"Backside",
			    "street"=>"Woolworth Street", "city"=>"Clover" ),
);


$salesrepnames = array(
    "Charles Ragner",
    "Hugo Baldwin",
    "Katie Blomock",
    "Ernie Bastel",
    "Lucy Irwin",
    "Bob Montagnier",
    "Chuck Hope",
    "Pierre Richard"
);


$salesrepimage;

$dpm=0;
$cip4_root;
$cip4_metadata;

$leading = $fontsize + 2;

# Simulate a datamatrix barcode */

define('MATRIXROWS', 32);
$MATRIXDATASIZE      = (4*MATRIXROWS);


function create_datamatrix($record)
{
    $data;
    $datastring = "";
    $i;
    $x;
    $d;

    for ($i=0; $i<MATRIXROWS; $i++)
    {
	$data[$i][0] = ((0xA3 + 1*$record + 17*$i) % 0xFF);
	$data[$i][1] = ((0xA2 + 3*$record + 11*$i) % 0xFF);
	$data[$i][2] = ((0xA0 + 5*$record +  7*$i) % 0xFF);
	$data[$i][3] = ((0x71 + 7*$record +  9*$i) % 0xFF);
    }
    for ($i=0; $i<MATRIXROWS; $i++)
    {
	$data[$i][0] |= 0x80;
	$data[$i][2] |= 0x80;
	if ($i%2) {
	    $data[$i][3] |= 0x01;
	} else {
	    $data[$i][3] &= 0xFE;
	}
    }
    for ($i=0; $i<4; $i++)
    {
	$data[MATRIXROWS/2-1][$i] = 0xFF;
	$data[MATRIXROWS-1][$i] = 0xFF;
    }

    # pack the datamatrix into a string
    for ($i=0; $i<MATRIXROWS; $i++) {
	foreach ($data[$i] as $d) {
	    $datastring = $datastring.pack("C", $d);
	}
    }
    return $datastring;
}

# create a new PDFlib object
try {
    $p = new PDFlib();
    $p->set_parameter("logging", "filename=trace.txt remove");

    if ($p->begin_document($outfile,
	    "pdfx=PDF/X-4 pdfvt=PDF/VT-1 usestransparency=false " .
	    "nodenamelist={root recipient} recordlevel=1") == 0)
    {
	die("Error: " . $p->get_errmsg());
    }

    $p->set_parameter("SearchPath", $searchpath);

    # This line is required to avoid problems on Japanese systems */
    $p->set_parameter("hypertextencoding", "host");

    $p->set_info("Creator", "PDFlib starter sample");
    $p->set_info("Title", "starter_pdfvt1");

    # Define output intent profile */
    if ($p->load_iccprofile("ISOcoated.icc", "usage=outputintent") == 0)
    {
	printf("Error: %s\n", $p->get_errmsg());
	die("Please install the ICC profile package from " .
	       "www.pdflib.com to run the PDF/VT-1 starter sample.\n");
    }

    # -----------------------------------
    # Load company stationery as background (used on first page
    # for each recipient)
    # -----------------------------------
    #/
    $stationery = $p->open_pdi_document($stationeryfilename, "");
    if ($stationery == 0) {
	die("Error: " . $p->get_errmsg());
    }

    $page = $p->open_pdi_page($stationery, 1,
	    "pdfvt={scope=global environment={Kraxi Systems}}");
    if ($page == 0) {
	die("Error: " . $p->get_errmsg());
    }

    # -----------------------------------
    # Preload images of all local sales reps (used on first page
    # for each recipient). To get encapsulated image XObjects,
    # the renderingintent option is used.
    # -----------------------------------
    #/
    for ($i=0; $i < 8; $i++)
    {
	$buf = sprintf($salesrepfilename, $i);
	$salesrepimage[$i] = $p->load_image("auto", $buf,
                                "pdfvt={scope=file} renderingintent=Perceptual");

	if ($salesrepimage[$i] == 0) {
	    die("Error: " . $p->get_errmsg());
	}
    }

    # -----------------------------------
    # Construct DPM metadata for the DPart root node
    # -----------------------------------
    #/
    $cip4_metadata = $p->poca_new("containertype=dict usage=dpm");
    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_Conformance value=base");
    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_Creator value=starter_pdfvt1");
    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_JobID value={Kraxi Systems invoice}");
	    
    $optlist = sprintf("containertype=dict usage=dpm " .
                        "type=dict key=CIP4_Metadata value=%d", $cip4_metadata);
    $cip4_root = $p->poca_new($optlist);
	    
    $optlist = sprintf("containertype=dict usage=dpm " .
                        "type=dict key=CIP4_Root value=%d", $cip4_root);
    $dpm = $p->poca_new($optlist);

    # Create root node in the DPart hierarchy and add DPM metadata  */
    $optlist = sprintf("dpm=%d", $dpm);
    $p->begin_dpart($optlist);

    $p->poca_delete($dpm);
    $p->poca_delete($cip4_root);
    $p->poca_delete($cip4_metadata);

    for ($record=0; $record < MAXRECORD; $record++)
    {
	$datamatrix;
	$item;
	$cip4_recipient;
	$cip4_contact;
	$cip4_person;
	$firstname;
	$lastname;

	$firstname = $addressdata[rand(0, count($addressdata)-1)]{"firstname"};
	$lastname = $addressdata[rand(0, count($addressdata)-1)]{"lastname"};

	# -----------------------------------
	# Construct DPM metadata for the next DPart node (i.e. the page)
	# -----------------------------------
	#/
	$dpm            = $p->poca_new("containertype=dict usage=dpm");
	$cip4_root      = $p->poca_new("containertype=dict usage=dpm");
	$cip4_recipient = $p->poca_new("containertype=dict usage=dpm");
	$cip4_contact   = $p->poca_new("containertype=dict usage=dpm");
	$cip4_person    = $p->poca_new("containertype=dict usage=dpm");

	$optlist = sprintf("type=dict key=CIP4_Root value=%d", $cip4_root);
	$p->poca_insert($dpm, $optlist);

	$optlist = sprintf("type=dict key=CIP4_Recipient value=%d",
						       $cip4_recipient);
	$p->poca_insert($cip4_root, $optlist);

	$optlist = sprintf("type=string key=CIP4_UniqueID value={ID_%d}",
			    $record);
	$p->poca_insert($cip4_recipient, $optlist);

	$optlist = sprintf("type=dict key=CIP4_Contact value=%d", $cip4_contact);
	$p->poca_insert($cip4_recipient, $optlist);

	$optlist = sprintf("type=dict key=CIP4_Person value=%d", $cip4_person);
	$p->poca_insert($cip4_contact, $optlist);

	$optlist = sprintf("type=string key=CIP4_Firstname value={%s}",
			    $firstname);
	$p->poca_insert($cip4_person, $optlist);

	$optlist = sprintf("type=string key=CIP4_Lastname value={%s}",
			    $lastname);
	$p->poca_insert($cip4_person, $optlist);

	# Create a new node in the document part hierarchy and
	# add DPM metadata
	#/
	$optlist = sprintf("dpm=%d", $dpm);
	$p->begin_dpart($optlist);

	$p->poca_delete($dpm);
	$p->poca_delete($cip4_root);
	$p->poca_delete($cip4_recipient);
	$p->poca_delete($cip4_contact);
	$p->poca_delete($cip4_person);

	# Establish coordinates with the origin in the upper left corner.*/
	$p->begin_page_ext(0, 0,
		"topdown width=a4.width height=a4.height");

	# -----------------------------------
	# Place company stationery as background on first page
	# for each recipient
	# -----------------------------------
	#/
	$p->fit_pdi_page($page, 0, 842, "");

	# -----------------------------------
	# Place name and image of local sales rep on first page
	# for each recipient
	# -----------------------------------
	#/
	$y = 177;
	$x = 455;

	$buf = "Local sales rep:";
	$optlist = sprintf("fontname=%s encoding=host embedding fontsize=9",
		$fontname);
	$p->fit_textline($buf, $x, $y, $optlist);
	$p->fit_textline($salesrepnames[$record % count($salesrepnames)],
		$x, $y+9, $optlist);

	$y = 280;
	$p->fit_image($salesrepimage[$record % count($salesrepnames)], $x, $y,
		"boxsize={90 90} fitmethod=meet");


	# -----------------------------------
	# Address of recipient
	# -----------------------------------
	#/
	$y = 170;

	$optlist = sprintf("fontname=%s encoding=host embedding fontsize=%f",
		$fontname, $fontsize);
	$buf = sprintf("%s %s", $firstname, $lastname);
	$p->fit_textline($buf, $left, $y, $optlist);

	$y += $leading;
	$buf = sprintf("%s",
		$addressdata[rand(0, count($addressdata)-1)]{"flat"});
	$p->fit_textline($buf, $left, $y, $optlist);

	$y += $leading;
	$buf = sprintf("%d %s",
		rand(0, 999),
		$addressdata[rand(0, count($addressdata)-1)]{"street"});
	$p->fit_textline($buf, $left, $y, $optlist);

	$y += $leading;
	$buf = sprintf("%05d %s",
		rand(0, 99999),
		$addressdata[rand(0, count($addressdata)-1)]{"city"});
	$p->fit_textline($buf, $left, $y, $optlist);


	# -----------------------------------
        # Individual barcode image for each recipient. To get encapsulated
        # image XObjects the renderingintent option is used.
	# -----------------------------------
	#/
	$datamatrix = create_datamatrix($record);
	$p->create_pvf("barcode", $datamatrix, "");

	$barcodeimage = $p->load_image("raw", "barcode",
		"bpc=1 components=1 width=32 height=32 invert " .
		"pdfvt={scope=singleuse} renderingintent=Saturation");
	if ($barcodeimage == 0) {
	    die("Error: " . $p->get_errmsg());
	}

	$p->fit_image($barcodeimage , 280.0, 200.0, "scale=1.5");
	$p->close_image($barcodeimage);
	$p->delete_pvf("barcode");


	# -----------------------------------
	# Print header and date
	# -----------------------------------
	#/
	$y = 300;
	$buf = sprintf("INVOICE 2011-%d", $record+1);
	$optlist = sprintf("fontname=%s encoding=host embedding fontsize=%d",
		$fontname, $fontsize);
	$p->fit_textline($buf, $left, $y, $optlist);

	# set timezone to avoid PHP warnings
	date_default_timezone_set('Europe/Berlin');
	$buf = date("F j,Y");
	$optlist = sprintf(
		"fontname=%s encoding=host fontsize=%d embedding " .
		"position {100 0}", $fontname, $fontsize);
	$p->fit_textline($buf, $right, $y, $optlist);

	# Print the invoice header line */
	$y = 370;
	$buf = sprintf("\tITEM\tDESCRIPTION\tQUANTITY\tPRICE\tAMOUNT");

	$optlist = sprintf("%s fontname=%s", $baseopt, $fontname);
	$textflow = $p->create_textflow($buf, $optlist);

	if ($textflow == 0)
	{
	    die("Error: " . $p->get_errmsg());
	}
	$p->fit_textflow($textflow, $left, $y-$leading, $right, $y, "");
	$p->delete_textflow($textflow);


	# -----------------------------------
	# Print variable-length article list
	# -----------------------------------
	#/
	$y += 2*$leading;
	$total = 0;

	$optlist = sprintf("%s fontname=%s", $baseopt, $fontname);

	for ($i = 0, $item=0; $i < count($articledata); $i++) {
	    $quantity = rand(0, 9) + 1;

	    if (rand(0, 2) % 2) {
		continue;
	    }

	    $item++;
	    $sum = $articledata[$i]{"price"} * $quantity;

	    $buf = sprintf("\t%d\t%s\t%d\t%.2f\t%.2f",
		$item, $articledata[$i]{"name"}, $quantity,
		$articledata[$i]{"price"}, $sum);

	    $textflow = $p->create_textflow($buf, $optlist);

	    if ($textflow == 0)
	    {
		die("Error: " . $p->get_errmsg());
	    }
	    $p->fit_textflow($textflow, $left, $y-$leading, $right, $y, "");
	    $p->delete_textflow($textflow);

	    $y += $leading;
	    $total += $sum;
	}

	$y += $leading;

	$buf = sprintf("%.2f", $total);
	$optlist = sprintf(
		"fontname=%s encoding=host embedding " .
		"fontsize=%f position {100 0}", $fontname, $fontsize);
	$p->fit_textline($buf, $right, $y, $optlist);

	# -----------------------------------
	# Constant closing text
	# -----------------------------------
	#/

	$y += 5*$leading;
	$optlist = sprintf(
		"%s fontname=%s alignment=justify leading=120%%",
		$baseopt, $fontname);
	$textflow = $p->create_textflow($closingtext, $optlist);

	if ($textflow == 0)
	{
	    die("Error: " . $p->get_errmsg());
	}
	$p->fit_textflow($textflow, $left, $y + 6*$leading, $right, $y, "");
	$p->delete_textflow($textflow);

	$p->end_page_ext("");

	# Close node in the document part hierarchy */
	$p->end_dpart("");
    }

    $p->close_pdi_page($page);
    $p->close_pdi_document($stationery);

    for ($i=0; $i<count($salesrepimage); $i++)
    {
	$p->close_image($salesrepimage[$i]);
    }

    # Close root node in the document part hierarchy */
    $p->end_dpart("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=starter_pdfvt1.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in starter_pdfvt1 sample:\n" .
	"[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
	$e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;
?>
