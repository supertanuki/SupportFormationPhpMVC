<?php
/* $Id: license_key.php,v 1.2 2009/08/04 13:31:59 rp Exp $
 * License key:
 * Apply your PDFlib license key using various methods to get rid of the demo 
 * stamp 
 * 
 * The first methos is to apply your license key at runtime or (for Windows
 * only) via a registry key. The second alternative method is to supply your
 * license key in a license file and to inform PDFlib about the license file
 * using set_parameter(), a system environment variable or (for Windows only)
 * a registry key.
 *   
 * Check for an existing license key by using set_parameter() with the
 * "nodemostamp" option.
 * 
 * Note that PDFlib, PDFlib+PDI, and PPS (PDFlib Personalization Server) are
 * different products which require different license keys although they are
 * delivered in a single package. PDFlib+PDI license keys will also be valid
 * for PDFlib, but not vice versa, and PPS license keys will be valid for
 * PDFlib+PDI and PDFlib. All license keys are platform-dependent, and can only
 * be used on the platform for which they have been purchased.
 *
 * Required software: PDFlib/PDFlib+PDI/PPS 7
 * Required data: valid license key for PDFlib, PDFlib+PDI, or PPS
 */

$outfile = "license_key_runtime.pdf";
$outfile2 = "license_key_invalid.pdf";
$title = "License Key";


$text=
    "You can apply your PDFlib license key using various methods to get " .
    "rid of the demo stamp. The first method is to apply your license " .
    "key at runtime or (for Windows only) via a registry key. The second " .
    "alternative method is to supply your license key " .
    "in a license file and to inform PDFlib about the license file using " .
    "set_parameter(), a system environment variable or (for Windows " .
    "only) a registry key. See the source code for a detailed description.";

try {
    $p = new PDFlib();
    

    /* --- First method:                    --- *
     * --- Apply the license key at runtime --- */
    
    /* Provide the "license" parameter with your license key. This must be
     * done immediately after having instantiated the PDFlib object with
     * "new pdflib()" In the following function call replace "0" with
     * your license key number.
     */ 
     $p->set_parameter("license", "0");
     
    /* On Windows you can also enter the license key in the following
     * registry key:
      
     HKEY_LOCAL_MACHINE\SOFTWARE\PDFlib\PDFlib\7.0.2 
      
     * with a version of 7.0.2 for example
     */
     
    /* (If you are working with the Windows COM .NET installer 
     * you can enter a license key when you install the PDFlib product.)
     */
     
    /* Using PDFlib+PDI functions if only the PDFlib license key has been
     * installed:
     * If you installed a valid PDFlib license key the PDI functionality
     * will no longer be available for testing. When a license key for a
     * product has already been installed, you can replace it with the
     * dummy license string "0" (zero) to enable functionality of a higher
     * product class for evaluation with:
     * 
     * $p->set_parameter("license", "0");
     * 
     * This will enable the previously disabled functions, and re-activate
     * the demo stamp across all pages. 
     * This also applies to PDFlib+PDI and PPS.
     */ 
    
    
    /* --- Alternative second method:               --- *
     * --- Supply the license key in a license file --- */
	
    /* Enter the license key in a text file according to the following
     * format(use the license file template "licensekeys.txt" which is
     * contained in all PDFlib distributions):
      
     PDFlib license file 1.0
     # Licensing information for PDFlib GmbH products
     PDFlib <version> <license key>
      
     * "PDFlib" indicates PDFlib, PDFlib+PDI, and PPS, respectively.
     * <version> is the PDFlib version number, e.g. 7.0.2, and 
     * <license key> is your license key. The license file may contain 
     * license keys for multiple PDFlib GmbH products on separate lines.
     */ 
    
    /* After the license key has been manually added to the license file,
     * now inform PDFlib about the license file
      
     $p->set_parameter("licensefile", "path/to/licensekeys.txt");
      
     * Alternatively, set the environment variable PDFLIBLICENSEFILE to
     * point to your license file. On Windows open the system control panel
     * and choose System, Advanced, Environment Variables, System variables.
     * On Unix apply a command similar to the following:
      
     export PDFLIBLICENSEFILE=/path/to/licensekeys.txt
     
     */
      
    /* On Windows you can also enter the name of the license file in the
     * following registry key:
      
     HKLM\Software\PDFlib\PDFLIBLICENSEFILE
     
     */
    
    /* This means we must check return values of load_font() etc. */
    $p->set_parameter("errorpolicy", "return");
    $p->set_parameter("textformat", "utf8");
    
    /* Start the first document. By default, a demo stamp will be generated
     * on all pages if no valid license key has been found. 
     */
    if ($p->begin_document($outfile, "") == 0)
	throw new Exception("Error: " . $p->get_errmsg());

    $p->set_info("Creator", "PDFlib Cookbook");
    $p->set_info("Title", $title );
    
    /* Start page */
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    
    $optlist =
	"fontname=Helvetica-Bold fontsize=16 encoding=unicode " .
	"fillcolor={gray 0} leading=140%";
    
    $tf = $p->add_textflow(0, $text, $optlist);
    if ($tf == 0)
	throw new Exception("Error: " . $p->get_errmsg());
    
    $p->fit_textflow($tf, 50, 200, 400, 600, "");
	   
    $p->end_page_ext("");
    
    $p->end_document("");
    
    } catch (PDFlibException $e){
	print("PDFlib exception occurred:\n" .
	    "[" . $e->get_errmsg() . "] " . $e->get_apiname() .
	    ": " . $e->get_errmsg() . "\n");
    } catch (Exception $e) {
	print($e->getMessage() . "\n");
    }

try {        
    /* By default a demo stamp will be created on all pages when no valid
     * license key has been found. However, we can force an exception in 
     * those cases. This is recommended to be immediately informed about 
     * problems with missing or invalid license keys which will result in
     * the unwanted demo stamp Before beginning a new document, we set the
     * "nodemostamp" parameter to "true". An exception will be thrown when
     * no valid license key has been found.
     */ 
    $p->set_parameter("nodemostamp", "true");

    /* Start a new document */
    $p->begin_document($outfile2, "");
    $p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
    $p->end_page_ext("");
    $p->end_document("");
    
    } catch (PDFlibException $e) {
	print("PDFlib exception occurred:\n" .
	    "[" . $e->get_errnum() . "] " . $e->get_apiname() .
	    ": " . $e->get_errmsg() . "\n");
	if ($e->get_errnum() == 1994)
	    print("This behaviour is expected since we did not\n" .
		"supply a valid license key and set the\n\"nodemostamp\" parameter to \"true\".");
    } catch (Exception $e) {
	die($e->getMessage());
    }
$p=0;
?>
