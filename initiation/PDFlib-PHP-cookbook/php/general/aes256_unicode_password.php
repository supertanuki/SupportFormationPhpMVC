<?php
/* 
 * $Id: aes256_unicode_password.php,v 1.3 2012/05/03 14:00:39 stm Exp $
 * 
 * Demonstrate AES-256 encryption and Unicode passwords.
 * 
 * The file can only be opened with Acrobat 9 and later.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 8
 * Required data: Norasi font
 */
$outfile = "";
$title = "AES-256 encryption and Unicode passwords";

/* This is where the data files are. Adjust as necessary. */
$searchpath = dirname(dirname(dirname(__FILE__)))."/input";


try {
    $p = new PDFlib();

    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");

    $p->set_parameter("SearchPath", $searchpath);
    $p->set_parameter("textformat", "utf8");
    $p->set_parameter("charref", "true");
    
    /*
     * The password demonstrates some features of Unicode passwords:
     * 
     * - Characters outside the normal 8-bit character range (THAI
     *   CHARACTER KO KHAI and THAI CHARACTER KHO KAI)
     *   
     * - Normalization of Unicode passwords (LATIN SMALL LIGATURE FF
     *   and LATIN SMALL LIGATURE FI are normalized to "ff" and "fi")
     */
    $thai_letters = "\xE0\xB8\x81\xE0\xB8\x82";
    $ligatures = "\xEF\xAC\x80 \xEF\xAC\x81";
    $normalized_ligatures ="ff fi";
    
    $password = $thai_letters . " " . $ligatures;
    $normalized_password = $thai_letters . " " 
				. $normalized_ligatures;

    /*
     * To get AES-256 encryption the PDF version must be set to
     * PDF 1.7 extension level 3.
     */
    $optlist = "compatibility=1.7ext3 "
	    . "masterpassword={\xEF\xBB\xBF" . $password . "}";

    if ($p->begin_document($outfile, $optlist) == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );

    $p->begin_page_ext(595, 842, "");

    $font = $p->load_font("Norasi", "unicode", "embedding");

    if ($font == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->setfont($font, 24);

    $p->set_text_pos(50, 700);
    $p->show("AES-256 encryption and Unicode passwords");
    
    $text =
	"This document produced by PDFlib is AES-256-encrypted. It "
	. "can only be opened by Acrobat 9 and later.\n\n"
	. "The master password of this document is:\n\n"
	. $password
	. "\n\nUse cut&paste to enter the password into the Acrobat "
	. "prompt for changing the security settings of the document.\n\n"
	. "Because the password is normalized, it can also be "
	. "entered like this (note the decomposed ligatures):\n\n"
	. $normalized_password;

    $tf = $p->add_textflow(0, $text, 
	"fontname=Norasi fontsize=18 encoding=unicode");
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    $p->fit_textflow($tf, 50, 50, 500, 650, "");
    
    $p->end_page_ext("");
    $p->end_document("");

    $buf = $p->get_buffer();
    $len = strlen($buf);

    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=starter_basic.pdf");
    print $buf;

}
catch (PDFlibException $e) {
    die("PDFlib exception occurred:\n" .  
	"[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " . 
	$e->get_errmsg() . "\n");
}
catch (Exception $e) {
    die($e);
}

$p = 0;
?>
