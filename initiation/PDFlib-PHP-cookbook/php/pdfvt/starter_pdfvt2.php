<?php
/* $Id: starter_pdfvt2.php,v 1.2 2012/05/03 14:00:43 stm Exp $
 *
 * Starter sample for PDF/VT-2
 * Create a large number of invoices in a single PDF and make use of
 * the following PDF/VT-2 features:
 * - create a document part (DPart) hierarchy
 * - assign PDF/VT scope attributes to images and imported PDF pages
 * - add document part metadata (DPM) to the DPart root node and all page nodes
 * - use proxy/reference pairs for imported PDF pages for the letterhead
 *   and photographic images. The referenced PDFs are PDF/X-4p themselves.
 * - Since transparency is used (for the dashed rectangles in the proxies)
 *   we supply the required options to achieve GTS_Encapsulated status:
 *   - "transparencygroup" for the proxy templates
 *   - "mask" for the barcode image (use "renderingintent" for color images)
 *
 * Required software: PDFlib 8 VT Edition
 * Required data: PDF/X-4p input documents, fonts
 */


class articledata_s {
    function articledata_s($name, $price, $quantity) {
	$this->name = $name;
	$this->price = $price;
	$this->quantity = $quantity;
    }
};

class addressdata_s {
    function addressdata_s($firstname, $lastname, $flat, $street, $city) {
	$this->firstname = $firstname;
	$this->lastname = $lastname;
	$this->flat = $flat;
	$this->street = $street;
	$this->city = $city;
    }
};

define("MATRIXROWS", 32);
define("MATRIXDATASIZE", 4 * MATRIXROWS);

define("MAXRECORD", 100);
$stationeryfilename = "stationery_pdfx4p.pdf";
$fontname = "DejaVuSerif";
$title = "Starter PDF/VT-2";

/* This is where font/image/PDF input files live. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";
$outfile = "";
$left = 55;
$right = 530;

$fontsize = 12; 
$baseopt = "encoding=winansi embedding "
	. "ruler       ={   30 45     275   375   475} "
	. "tabalignment={right left right right right} "
	. "hortabmethod=ruler fontsize=12 ";


$closingtext = "Terms of payment: <fillcolor={cmyk 0 1 1 0}>30 days net. "
	. "<fillcolor={gray 0}>90 days warranty starting at the day of sale. "
	. "This warranty covers defects in workmanship only. "
	. "<fontname=DejaVuSerif embedding encoding=winansi>Kraxi Systems, Inc. "
	. "<resetfont>will, at its option, repair or replace the "
	. "product under the warranty. This warranty is not transferable. "
	. "No returns or exchanges will be accepted for wet products.";

$articledata = array(
    new articledata_s("Super Kite", 20, 2),
    new articledata_s("Turbo Flyer", 40, 5),
    new articledata_s("Giga Trash", 180, 1),
    new articledata_s("Bare Bone Kit", 50, 3),
    new articledata_s("Nitty Gritty", 20, 10),
    new articledata_s("Pretty Dark Flyer", 75, 1),
    new articledata_s("Large Hadron Glider", 85, 1),
    new articledata_s("Flying Bat", 25, 1),
    new articledata_s("Simple Dimple", 40, 1),
    new articledata_s("Mega Sail", 95, 1),
    new articledata_s("Tiny Tin", 25, 1),
    new articledata_s("Monster Duck", 275, 1),
    new articledata_s("Free Gift", 0, 1)
);

$addressdata = array(
    new addressdata_s("Edith", "Poulard", "Suite C", "Main Street",
	    "New York"),
    new addressdata_s("Max", "Huber", "", "Lipton Avenue",
	    "Albuquerque"),
    new addressdata_s("Herbert", "Pakard", "App. 29", "Easel",
	    "Duckberg"),
    new addressdata_s("Charles", "Fever", "Office 3", "Scenic Drive",
	    "Los Angeles"),
    new addressdata_s("D.", "Milliband", "", "Old Harbour", "Westland"),
    new addressdata_s("Lizzy", "Tin", "Workshop", "Ford", "Detroit"),
    new addressdata_s("Patrick", "Black", "Backside",
	    "Woolworth Street", "Clover")
);

$salesrepnames = array( "Charles Ragner", "Hugo Baldwin",
    "Katie Blomock", "Ernie Bastel", "Lucy Irwin", "Bob Montagnier",
    "Chuck Hope", "Pierre Richard" );


$leading = $fontsize + 2;

try {
    $p = new pdflib();

    if ($p->begin_document($outfile,
	    "pdfvt=PDF/VT-2 pdfx=PDF/X-5pg usestransparency=true "
	    . "nodenamelist={root recipient} recordlevel=1") == 0) {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    $p->set_parameter("SearchPath", $searchpath);

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    /* Define output intent profile */
    if ($p->load_iccprofile("ISOcoated.icc",
	    "usage=outputintent urls={http://www.color.org}") == 0) {
	print("Error: " . $p->get_errmsg() . "\n");
	print("Please install the ICC profile package from "
		. "www.pdflib.com to run the PDF/VT-2 starter sample.\n");
	$p->delete();
	die();
    }

    /*
     * ----------------------------------- 
     * Load company stationery as background (used 
     * on first page for each recipient) by reference and 
     * construct proxy for it
     * -----------------------------------
     */
    $proxy_stationery = make_proxy($p, $stationeryfilename,
				    "Proxy for stationery");
    if ($proxy_stationery == 0)
    {
	throw new Exception("Error: " . $p->get_errmsg());
    }

    /*
     * ----------------------------------- 
     * Preload PDF images of all local sales reps (used on first page
     * for each recipient) by reference and construct proxy for it
     * -----------------------------------
     */
    for ($i = 0; $i < count($salesrepnames); $i++) {
	$description = "Proxy for sales rep image " . $i;
	$salesrepfilename = "sales_rep" . $i . ".pdf";
	
	$proxy_salesrepimage[$i] = make_proxy($p, $salesrepfilename, $description);

	if ($proxy_salesrepimage[$i] == 0) {
	    throw new Exception("Proxy error: " . $p->get_errmsg());
	}
    }

    define("ARTICLECOUNT", count($articledata));
    define("ADDRESSCOUNT", count($addressdata));

    /*
     * ----------------------------------- 
     * Construct DPM metadata for the DPart 
     * root node
     * -----------------------------------
     */
    $dpm = $p->poca_new("containertype=dict usage=dpm");
    $cip4_root = $p->poca_new("containertype=dict usage=dpm");
    $cip4_metadata = $p->poca_new("containertype=dict usage=dpm");

    $optlist = "type=dict key=CIP4_Root value=" . $cip4_root;
    $p->poca_insert($dpm, $optlist);

    $optlist = "type=dict key=CIP4_Metadata value=" . $cip4_metadata;
    $p->poca_insert($cip4_root, $optlist);

    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_Conformance value=base");
    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_Creator value=starter_pdfvt2");
    $p->poca_insert($cip4_metadata,
	    "type=string key=CIP4_JobID value={Kraxi Systems invoice}");

    /* Create root node in the DPart hierarchy and add DPM metadata */
    $optlist = "dpm=" . $dpm;
    $p->begin_dpart($optlist);

    $p->poca_delete($dpm);
    $p->poca_delete($cip4_root);
    $p->poca_delete($cip4_metadata);

    for ($record = 0; $record < MAXRECORD; $record++) {

	$firstname = $addressdata[get_random(ADDRESSCOUNT)]->firstname;
	$lastname = $addressdata[get_random(ADDRESSCOUNT)]->lastname;

	/*
	 * ----------------------------------- 
	 * Construct DPM metadata for the next 
	 * DPart node (i.e. the page)
	 * -----------------------------------
	 */
	$dpm = $p->poca_new("containertype=dict usage=dpm");
	$cip4_root = $p->poca_new("containertype=dict usage=dpm");
	$cip4_recipient = $p->poca_new("containertype=dict usage=dpm");
	$cip4_contact = $p->poca_new("containertype=dict usage=dpm");
	$cip4_person = $p->poca_new("containertype=dict usage=dpm");

	$optlist = "type=dict key=CIP4_Root value=" . $cip4_root;
	$p->poca_insert($dpm, $optlist);

	$optlist = "type=dict key=CIP4_Recipient value="
		. $cip4_recipient;
	$p->poca_insert($cip4_root, $optlist);

	$optlist = "type=string key=CIP4_UniqueID value={ID_" . $record
		. "}";
	$p->poca_insert($cip4_recipient, $optlist);

	$optlist = "type=dict key=CIP4_Contact value=" . $cip4_contact;
	$p->poca_insert($cip4_recipient, $optlist);

	$optlist = "type=dict key=CIP4_Person value=" . $cip4_person;
	$p->poca_insert($cip4_contact, $optlist);

	$optlist = "type=string key=CIP4_Firstname value={" . $firstname
		. "}";
	$p->poca_insert($cip4_person, $optlist);

	$optlist = "type=string key=CIP4_Lastname value={" . $lastname
		. "}";
	$p->poca_insert($cip4_person, $optlist);

	/*
	 * Create a new node in the document part hierarchy and add DPM
	 * metadata
	 */
	$optlist = "dpm=" . $dpm;
	$p->begin_dpart($optlist);

	$p->poca_delete($dpm);
	$p->poca_delete($cip4_root);
	$p->poca_delete($cip4_recipient);
	$p->poca_delete($cip4_contact);
	$p->poca_delete($cip4_person);

	/*
	 * Establish coordinates with the origin in the upper left
	 * corner.
	 */
	$p->begin_page_ext(0, 0,
		"topdown width=a4.width height=a4.height");

	/*
	 * ----------------------------------- 
	 * Place company stationery / proxy (template) as background
	 * on the page
	 * -----------------------------------
	 */
	$p->fit_image($proxy_stationery, 0, 842, "");

	/*
	 * ----------------------------------- 
	 * Place name and image proxy (template) of local sales
	 * rep on the page
	 * -----------------------------------
	 */
	$y = 177;
	$x = 455;

	$buf = "Local sales rep:";
	$optlist = "fontname=" . $fontname
		. " encoding=winansi embedding fontsize=9";
	$p->fit_textline($buf, $x, $y, $optlist);
	$p->fit_textline($salesrepnames[$record % count($salesrepnames)], $x,
		$y + 9, $optlist);

	$y = 280;
	
	/* Place the proxy on the page */
	$p->fit_image($proxy_salesrepimage[$record % count($salesrepnames)],
		$x, $y, "boxsize={90 90} fitmethod=meet");

	/*
	 * ----------------------------------- 
	 * Address of recipient
	 * -----------------------------------
	 */
	$y = 170;

	$optlist = "fontname=" . $fontname
		. " encoding=winansi embedding fontsize=" . $fontsize;
	$buf = $firstname . " " . $lastname;
	$p->fit_textline($buf, $left, $y, $optlist);

	$y += $leading;
	$p->fit_textline($addressdata[get_random(ADDRESSCOUNT)]->flat,
		$left, $y, $optlist);

	$y += $leading;
	$buf = "" . get_random(999) . " "
		. $addressdata[get_random(ADDRESSCOUNT)]->street;
	$p->fit_textline($buf, $left, $y, $optlist);

	$y += $leading;
	$buf = sprintf("%05d", get_random(99999)) . " "
		. $addressdata[get_random(ADDRESSCOUNT)]->city;
	$p->fit_textline($buf, $left, $y, $optlist);

	/*
	 * ----------------------------------- 
	 * Individual barcode image for each recipient
	 * -----------------------------------
	 */
	$datamatrix = create_datamatrix($record);
	$p->create_pvf("barcode", $datamatrix, "");

	/* The "mask" option helps us achieve GTS_Encapsulated status */
	$barcodeimage = $p->load_image("raw", "barcode",
		"bpc=1 components=1 width=32 height=32 invert "
			. "pdfvt={scope=singleuse} mask");
	if ($barcodeimage == 0) {
	    throw new Exception("Error: " . $p->get_errmsg());
	}

	$p->fit_image($barcodeimage, 280.0, 200.0, "scale=1.5");
	$p->close_image($barcodeimage);
	$p->delete_pvf("barcode");

	/*
	 * ----------------------------------- 
	 * Print header and date
	 * -----------------------------------
	 */
	$y = 300;
	$buf = "INVOICE 2011-" . ($record + 1);
	$optlist = "fontname=" . $fontname
		. " encoding=winansi embedding fontsize=" . $fontsize;
	$p->fit_textline($buf, $left, $y, $optlist);
	date_default_timezone_set('Europe/Berlin');
	$buf = date("M j, Y");
	$optlist = "fontname=" . $fontname
		. " encoding=winansi fontsize=" . $fontsize
		. " embedding " . "position {100 0}";
	$p->fit_textline($buf, $right, $y, $optlist);

	/* Print the invoice header line */
	$y = 370;
	$buf = "\tITEM\tDESCRIPTION\tQUANTITY\tPRICE\tAMOUNT";

	$optlist = $baseopt . " fontname=" . $fontname;
	$textflow = $p->create_textflow($buf, $optlist);

	if ($textflow == 0) {
	    throw new Exception("Error: " . $p->get_errmsg());
	}
	$p->fit_textflow($textflow, $left, $y - $leading, $right, $y, "");
	$p->delete_textflow($textflow);

	/*
	 * ----------------------------------- 
	 * Print variable-length article list 
	 * -----------------------------------
	 */
	$y += 2 * $leading;
	$total = 0;

	$optlist = $baseopt . " fontname=" . $fontname;

	for ($i = 0, $item = 0; $i < ARTICLECOUNT; $i++) {
	    $quantity = get_random(9) + 1;

	    if ((get_random(2) % 2) != 0)
		continue;

	    $item++;
	    $sum = $articledata[$i]->price * $quantity;

	    $buf = "\t" . $item . "\t" . $articledata[$i]->name . "\t"
		    . $quantity . "\t" . sprintf("%.2f", $articledata[$i]->price) . "\t"
		    . sprintf("%.2f", $sum);

	    $textflow = $p->create_textflow($buf, $optlist);

	    if ($textflow == 0) {
		throw new Exception("Error: " . $p->get_errmsg());
	    }
	    $p->fit_textflow($textflow, $left, $y - $leading, $right, $y, "");
	    $p->delete_textflow($textflow);

	    $y += $leading;
	    $total += $sum;
	}

	$y += $leading;

	$buf = "" . sprintf("%.2f", $total);
	$optlist = "fontname=" . $fontname
		. " encoding=winansi embedding " . "fontsize="
		. $fontsize . " position {100 0}";
	$p->fit_textline($buf, $right, $y, $optlist);

	/*
	 * ----------------------------------- 
	 * Constant closing text
	 * -----------------------------------
	 */

	$y += 5 * $leading;
	$optlist = $baseopt . " fontname=" . $fontname
		. " alignment=justify leading=120%";
	$textflow = $p->create_textflow($closingtext, $optlist);

	if ($textflow == 0) {
	    throw new Exception("Error: " . $p->get_errmsg());
	}
	$p->fit_textflow($textflow, $left, $y + 6 * $leading, $right, $y, "");
	$p->delete_textflow($textflow);

	$p->end_page_ext("");

	/* Close node in the document part hierarchy */
	$p->end_dpart("");
    }

    /* Close root node in the document part hierarchy */
    $p->end_dpart("");

    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=starter_pdfvt2.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred in sample:\n" .
        "[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        $e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;


/* -------------------------------
 * Load page 1 of the specified PDF and use it as reference for
 * a proxy which consists of a transparent crossed-out rectangle
 * of the same size.
 */
function make_proxy($p, $targetname, $description) {
    $linewidth = 2;

    /* Create the template which will serve as proxy. The referenced
     * page (the target) is attached to the proxy.
     * The width and height parameters will be set in PDF_end_template_ext()
     * after we queried the size of the target page.
     * The "transparencygroup" option is provided to achieve GTS_Encapsulated
     * status.
     * You can add "pdfvt={xid={uuid:...} }" to optlist if you can
     * generate unique IDs.
     */

    $optlist = "reference={filename=" . $targetname 
	. " pagenumber=1} pdfvt={scope=file} "
	. "transparencygroup={colorspace=devicecmyk isolated=true}";
    $proxy = $p->begin_template_ext(0, 0, $optlist);

    if ($proxy == 0)
    {
	return $proxy;
    }

    /* Determine the coordinates of the target; we use it for
     * dimensioning the proxy appropriately.
     */
    $x1 = $p->info_image($proxy, "targetx1", "");
    $y1 = $p->info_image($proxy, "targety1", "");
    $x2 = $p->info_image($proxy, "targetx2", "");
    $y2 = $p->info_image($proxy, "targety2", "");
    $x3 = $p->info_image($proxy, "targetx3", "");
    $y3 = $p->info_image($proxy, "targety3", "");
    $x4 = $p->info_image($proxy, "targetx4", "");
    $y4 = $p->info_image($proxy, "targety4", "");

    $width = $x2 - $x1;
    $height = $y4 - $y1;

    /* Draw a transparent crossed-out rectangle to visualize the proxy.
     * Attention: if we use the exact corner points, one half of the
     * linewidth would end up outside the template, and therefore be
     * clipped.
     */
    $p->setlinewidth($linewidth);
    $p->setdashpattern("dasharray={10 5}");

    /* Make the dashed crossed-out rectangle transparent so that the proxy
     * does not obscure the underlying page contents.
     */
    $gstate = $p->create_gstate("opacitystroke=0.25 opacityfill=0.25");
    $p->set_gstate($gstate);

    $p->moveto($x1 + $linewidth / 2, $y1 + $linewidth / 2);
    $p->lineto($x2 - $linewidth / 2, $y2 + $linewidth / 2);
    $p->lineto($x3 - $linewidth / 2, $y3 - $linewidth / 2);
    $p->lineto($x4 + $linewidth / 2, $y4 - $linewidth / 2);
    $p->lineto($x1 + $linewidth / 2, $y1 + $linewidth / 2);
    $p->lineto($x3 - $linewidth / 2, $y3 - $linewidth / 2);
    $p->moveto($x2 - $linewidth / 2, $y2 + $linewidth / 2);
    $p->lineto($x4 + $linewidth / 2, $y4 - $linewidth / 2);
    $p->stroke();

    $fontsize = $width > 550 ? 24.0 : 48.0;
    $optlist = "fontname=LuciduxSans-Oblique encoding=winansi embedding " 
	    . "fontsize=" . $fontsize . " fitmethod=auto position=center "
	    . "boxsize={" . $width . " " . $height . "}";
    $p->fit_textline($description, 0, 0, $optlist);

    /* Make the $proxy template the same size as the target page */
    $p->end_template_ext($width, $height);

    return $proxy;
}
/**
 * Get a pseudo random number between 0 and n-1
 */
function get_random($n) {
    return rand(0, $n-1);
}

/**
 * Simulate a datamatrix barcode
 */


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


?>
