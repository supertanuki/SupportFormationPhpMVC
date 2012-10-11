<?php
/**
 * barcode_field: Create the three types of barcode fields that Acrobat
 * supports.
 * 
 * This barcode form works as follows:
 * - Text fields to the left serve as data source where the user can type.
 * - Barcode fields to the right reflect the value of the text fields,
 *   represented as barcodes in the PDF417, Data Matrix and QuickResponse
 *   (QR) symbologies.
 * - Each barcode field is assigned a JavaScript as "calculate" action.
 *   Whenever the "calculate" event fires, the barcodes will be recalculated.
 * - An additional "page open" action explicitly calls this.calculateNow()
 *   to force the barcodes to be rendered upon opening the page.
 * - The "Reset" button can be used to initialize all text fields (and
 *   therefore also the dependent barcode fields) to their original state.
 * 
 * Important: Displaying and printing the barcode fields will only work with
 * full Acrobat version 9 or newer, but not with Adobe Reader.
 * 
 * Required software: PDFlib/PDFlib+PDI/PPS 8.0.3
 * Required data: none
 * 
 * @version $Id: barcode_field.php,v 1.1 2012/03/28 15:46:55 rp Exp $
 */
/**
 * Margin from the page border. 
 */
define("MARGIN",30);

/**
 * Sample text to provide default data for the barcode fields.
 */
define("SAMPLE_TEXT",
    "To change the barcode field to the right, type in this box. "
    . "The barcode field to the right will reflect the contents of the "
    . "text field as barcode after the text field lost the focus. To "
    . "reset the contents of all barcode fields, click the 'Reset' button.");

/**
 * Tooltip text for text entry fields.
 */
define("TOOLTIP_TEXT", "Enter data for barcode field here");

/**
 * Page height.
 */
define("PAGE_HEIGHT",842);

/**
 * Page width.
 */
define("PAGE_WIDTH", 595);

/**
 * Height of reset button.
 */
define("RESET_BUTTON_HEIGHT", 50);

/**
 * Width of reset button.
 */
define("RESET_BUTTON_WIDTH", 120);

$outfile = "";
$title = "Barcode Fields";


/* Required minimum PDFlib version */
$requiredversion = 803;
$requiredvstr = "8.0.3";

try {
    $p = new pdflib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    /* Check whether the required minimum PDFlib version is available */
    $major = $p->get_value("major", 0);
    $minor = $p->get_value("minor", 0);
    $revision = $p->get_value("revision", 0);

    if ($major * 100 + $minor * 10 + $revision < $requiredversion)
	throw new Exception("Error: PDFlib " . $requiredvstr
	    . " or above is required");
    
    if ($p->begin_document($outfile,
	    "compatibility=1.7ext3 pagelayout=singlepage") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title);

    $font = $p->load_font("Helvetica", "winansi", "");
    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    /*
     * Define JavaScript that recalculates all field contents on the
     * page.
     */
    $calculate_now_action =
	$p->create_action("JavaScript", "script { this.calculateNow(); }");
    
    /* Start page */
    $p->begin_page_ext(0, 0, 
	"width=a4.width height=a4.height "
	    . "action={open " . $calculate_now_action . "}");

    /*
     * Build the option list for the reset button, which has to contain
     * the names of all the fields to reset to their default values.
     */
    $reset_optlist = "namelist={";
    
    /*
     * Calculate available area and sizes for the text and barcode
     * fields. Barcode fields are twice as wide as the corresponding
     * text fields.
     */
    $num_fields = 3;
    $field_area_height =
	PAGE_HEIGHT - 3 * MARGIN - RESET_BUTTON_HEIGHT;
    $field_area_width =
	PAGE_WIDTH - 2 * MARGIN;
    $field_height =
	($field_area_height - ($num_fields - 1) * MARGIN) / $num_fields;
    $text_field_width = ($field_area_width - MARGIN) / 3;
    $barcode_field_width =
			($field_area_width - MARGIN) * 2 / 3;
    
    $field_number = 0;
    
    $text_field_name = "text_" . $field_number;
    $barcode_field_name = "barcode_" . $field_number;
    
    /*
     * Add the name of the text field to the list fields to reset.
     */
    $reset_optlist .= " {" . $text_field_name . "}";
    
    /*
     * Create text field.
     * 
     * Note that Acrobat at least up to version 10.1 contains a bug 
     * that makes it crash in case the first field on a page is a 
     * barcode field. Therefore make sure that a field of another type
     * is created first.
     */
    $optlist = "tooltip={PDF417 barcode:\n" . TOOLTIP_TEXT . "} "
	. "multiline=true bordercolor={gray 0} linewidth=1 "
	. "font=" . $font . " "
	. "currentvalue={PDF417 barcode:\n" . SAMPLE_TEXT . "} "
	. "defaultvalue={PDF417 barcode:\n" . SAMPLE_TEXT . "}";
    $x = MARGIN;
    $y = PAGE_HEIGHT - (MARGIN + RESET_BUTTON_HEIGHT )
		- ($field_number + 1) * (MARGIN + $field_height);
    $p->create_field($x, $y, $x + $text_field_width, $y + $field_height,
	$text_field_name, "textfield", $optlist);
    
    /*
     * Create an action that calculates the value for the barcode field
     * from the contents of the text field.
     */
    $calculate_action = create_calc_action($p, $text_field_name);
    
    /*
     * Create a PDF417 barcode:
     * 
     * - no compression (dataprep=0)
     * - apply error correction level 7 (ecc=7)
     * - x symbol width 3, x symbol height 6 (xsymheight=6 xsymwidth=3)
     */
    $optlist = "barcode={symbology=PDF417 dataprep=0 "
	. "ecc=7 xsymheight=6 xsymwidth=3} "
	. "action={calculate=" . $calculate_action . "} font=" . $font;
    $x = 2 * MARGIN + $text_field_width;
    $p->create_field($x, $y, $x + $barcode_field_width, $y + $field_height,
	$barcode_field_name, "textfield", $optlist);
    
    /*
     * Repeat same steps to create a Data Matrix barcode.
     */
    $field_number += 1;
    $text_field_name = "text_" . $field_number;
    $barcode_field_name = "barcode_" . $field_number;
    
    $reset_optlist .= " {" . $text_field_name . "}";
    
    $optlist = "tooltip={Data Matrix barcode:\n" . TOOLTIP_TEXT . "} "
	. "multiline=true bordercolor={gray 0} linewidth=1 "
	. "font=" . $font . " "
	. "currentvalue={Data Matrix barcode:\n" . SAMPLE_TEXT . "} "
	. "defaultvalue={Data Matrix barcode:\n" . SAMPLE_TEXT . "}";
    $x = MARGIN;
    $y = PAGE_HEIGHT - (MARGIN + RESET_BUTTON_HEIGHT )
	    - ($field_number + 1) * (MARGIN + $field_height);
    $p->create_field($x, $y, $x + $text_field_width, $y + $field_height,
	$text_field_name, "textfield", $optlist);
    $calculate_action = create_calc_action($p, $text_field_name);
    
    /*
     * Create the Data Matrix barcode.
     */
    $optlist = "barcode={symbology=DataMatrix dataprep=0 "
	. "ecc=0 xsymwidth=10} "
	. "action={calculate=" . $calculate_action . "} font=" . $font;
    $x = 2 * MARGIN + $text_field_width;
    $p->create_field($x, $y, $x + $barcode_field_width, $y + $field_height,
	$barcode_field_name, "textfield", $optlist);
    
    /*
     * Repeat same steps to create a QR Code barcode.
     */
    $field_number += 1;
    $text_field_name = "text_" . $field_number;
    $barcode_field_name = "barcode_" . $field_number;
    
    $reset_optlist .= " {" . $text_field_name . "}";
    
    $optlist = "tooltip={QR Code barcode:\n" . TOOLTIP_TEXT . "} "
	. "multiline=true bordercolor={gray 0} linewidth=1 "
	. "font=" . $font . " "
	. "currentvalue={QR Code barcode:\n" . SAMPLE_TEXT . "} "
	. "defaultvalue={QR Code barcode:\n" . SAMPLE_TEXT . "}";
    $x = MARGIN;
    $y = PAGE_HEIGHT - (MARGIN + RESET_BUTTON_HEIGHT )
	    - ($field_number + 1) * (MARGIN + $field_height);
    $p->create_field($x, $y, $x + $text_field_width, $y + $field_height,
	$text_field_name, "textfield", $optlist);
    $calculate_action = create_calc_action($p, $text_field_name);
    
    /*
     * Create the QR code barcode:
     * 
     * - apply error correction level 2 (ecc=2)
     * - x symbol width 10
     * - compress data before encoding (dataprep=1)
     */
    $optlist = "barcode={symbology=QRCode ecc=2 xsymwidth=10 dataprep=1} "
	. "action={calculate=" . $calculate_action . "} font=" . $font;
    $x = 2 * MARGIN + $text_field_width;
    $p->create_field($x, $y, $x + $barcode_field_width, $y + $field_height,
	$barcode_field_name, "textfield", $optlist);
    
    /*
     * Terminate the option list for the reset button, and create a
     * reset button that resets the text input fields to their default
     * values.
     */
    $reset_optlist .= "}";
    $reset_action = $p->create_action("ResetForm", $reset_optlist);

    $optlist = "action={activate=" . $reset_action . "} font={" . $font 
	    . "} caption={Reset} bordercolor=black "
	    . "tooltip={Reset all fields to default values}";
    $x = MARGIN;
    $y = PAGE_HEIGHT - MARGIN - RESET_BUTTON_HEIGHT;
    $p->create_field($x, $y, $x + RESET_BUTTON_WIDTH, $y + RESET_BUTTON_HEIGHT,
	"Reset", "PushButton", $optlist);
    
    /*
     * Create a text box with a warning that Adobe Reader does not
     * display the barcode fields.
     */
    $optlist = "font=" . $font . " fontsize=14 fillcolor=red";
    $tf = $p->add_textflow(0,
	"Note that the barcode fields only work with full Acrobat "
	    . "version 9 or newer, but not with Adobe Reader!",
	    $optlist);
    $x = 2 * MARGIN + RESET_BUTTON_WIDTH;
    $p->fit_textflow($tf, $x, $y,
	PAGE_WIDTH + 3 * MARGIN - RESET_BUTTON_WIDTH,
	$y + RESET_BUTTON_HEIGHT, "fitmethod=auto");
    
    $p->end_page_ext("");
    $p->end_document("");
    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=barcode_field.pdf");
    print $buf;

} catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n".
	"[" . $e->get_errnum() . "] " . $e->get_apiname() .
	": " . $e->get_errmsg() . "\n");
} catch (Exception $e) {
    die($e->getMessage());
}



/**
 * Create the action to calculate the value of the barcode field.
 * 
 * @param p
 *            the pdflib object
 * @param text_field_name
 *            the name of the text field that determines the value of the
 *            barcode field
 * 
 * @return the handle of the calculate action
 * 
 * @throws PDFlibException
 */
function create_calc_action($p, $text_field_name){
    $script =
	"script { "
	    . "try { "
		. "var fieldname = \"" . $text_field_name . "\"; "
		. "event.value = fieldname + \":\" + this.getField(fieldname).value;"
	    . "} "
	    . "catch(e) {"
		. "event.value = \"EXCEPTION\";"
	    . "}"
	. "}";
    return $p->create_action("JavaScript", $script);
}
?>
