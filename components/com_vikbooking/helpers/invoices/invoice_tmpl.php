<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

defined('_VIKBOOKINGEXEC') OR die('Restricted Area');

/*
 * This is the Template used for generating any invoice
 * List of available special-tags that can be used in this template:
 *
 * {company_logo}
 * {company_info}
 * {invoice_number}
 * {invoice_suffix}
 * {invoice_date}
 * {invoice_products_descriptions}
 * {customer_info}
 * {invoice_totalnet}
 * {invoice_totaltax}
 * {invoice_grandtotal}
 * {checkin_date}
 * {checkout_date}
 * {num_nights}
 * {tot_guests}
 * {tot_adults}
 * {tot_children}
 * {inv_notes}
 * {customfield 2} - Will be replaced with the Custom Field of ID 2 (Last Name by default - you can use any ID)
 *
 * The record of the booking can be accessed from the following global array in case you need any extra special tag or to perform queries for a deeper customization level:
 * $booking_info (booking array)
 * $booking_rooms (booked rooms array)
 * Example: the ID of the booking is contained in $booking_info['id'] - you can see the whole array content with the code "print_r($booking_info)"
 *
 * It is also possible to access the customer information array by using this code:
 * $customer = VikBooking::getCPinIstance()->getCustomerFromBooking($booking_info['id']);
 * The variable $customer will always be an array, even if no customers were found. In this case, the array will be empty.
 * Debug the content of the array with the code "print_r($customer)" by placing it on any part of the PDF content below.
*/

//Custom Invoice PDF Template Parameters
define('VBO_INVOICE_PDF_PAGE_ORIENTATION', 'P'); //define a constant - P=portrait, L=landscape (P by default or if not specified)
define('VBO_INVOICE_PDF_UNIT', 'mm'); //define a constant - [pt=point, mm=millimeter, cm=centimeter, in=inch] (mm by default or if not specified)
define('VBO_INVOICE_PDF_PAGE_FORMAT', 'A4'); //define a constant - A4 by default or if not specified. Could be also a custom array of width and height but constants arrays are only supported in PHP7
define('VBO_INVOICE_PDF_MARGIN_LEFT', 10); //define a constant - 15 by default or if not specified
define('VBO_INVOICE_PDF_MARGIN_TOP', 10); //define a constant - 27 by default or if not specified
define('VBO_INVOICE_PDF_MARGIN_RIGHT', 10); //define a constant - 15 by default or if not specified
define('VBO_INVOICE_PDF_MARGIN_HEADER', 1); //define a constant - 5 by default or if not specified
define('VBO_INVOICE_PDF_MARGIN_FOOTER', 5); //define a constant - 10 by default or if not specified
define('VBO_INVOICE_PDF_MARGIN_BOTTOM', 5); //define a constant - 25 by default or if not specified
define('VBO_INVOICE_PDF_IMAGE_SCALE_RATIO', 1.25); //define a constant - ratio used to adjust the conversion of pixels to user units (1.25 by default or if not specified)
$invoice_params = array(
	'show_header' => 0, //0 = false (do not show the header) - 1 = true (show the header)
	'header_data' => array(), //if empty array, no header will be displayed. The array structure is: array(logo_in_tcpdf_folder, logo_width_mm, title, text, rgb-text_color, rgb-line_color). Example: array('logo.png', 30, 'Hotel xy', 'Versilia Coast, xyz street', array(0,0,0), array(0,0,0))
	'show_footer' => 0, //0 = false (do not show the footer) - 1 = true (show the footer)
	'pdf_page_orientation' => 'VBO_INVOICE_PDF_PAGE_ORIENTATION', //must be a constant - P=portrait, L=landscape (P by default)
	'pdf_unit' => 'VBO_INVOICE_PDF_UNIT', //must be a constant - [pt=point, mm=millimeter, cm=centimeter, in=inch] (mm by default)
	'pdf_page_format' => 'VBO_INVOICE_PDF_PAGE_FORMAT', //must be a constant defined above or an array of custom values like: 'pdf_page_format' => array(400, 300)
	'pdf_margin_left' => 'VBO_INVOICE_PDF_MARGIN_LEFT', //must be a constant - 15 by default
	'pdf_margin_top' => 'VBO_INVOICE_PDF_MARGIN_TOP', //must be a constant - 27 by default
	'pdf_margin_right' => 'VBO_INVOICE_PDF_MARGIN_RIGHT', //must be a constant - 15 by default
	'pdf_margin_header' => 'VBO_INVOICE_PDF_MARGIN_HEADER', //must be a constant - 5 by default
	'pdf_margin_footer' => 'VBO_INVOICE_PDF_MARGIN_FOOTER', //must be a constant - 10 by default
	'pdf_margin_bottom' => 'VBO_INVOICE_PDF_MARGIN_BOTTOM', //must be a constant - 25 by default
	'pdf_image_scale_ratio' => 'VBO_INVOICE_PDF_IMAGE_SCALE_RATIO', //must be a constant - ratio used to adjust the conversion of pixels to user units (1.25 by default)
	'header_font_size' => '10', //must be a number
	'body_font_size' => '10', //must be a number
	'footer_font_size' => '8' //must be a number
);
defined('_VIKBOOKING_INVOICE_PARAMS') OR define('_VIKBOOKING_INVOICE_PARAMS', '1');
//
?>

<table width="100%" border="0" cellspacing="1" cellpadding="2">
	<tr>
		<td width="70%">{company_logo}<br/>{company_info}</td>
		<td width="30%" align="right" valign="bottom">
			<table align="right" width="100%" style="border: 1px solid #ccc;" bgcolor="#f2f3f7" cellspacing="0" cellpadding="2">
				<tr>
					<td align="right"><strong><?php echo "No. de la Factura: "; ?> {invoice_number}{invoice_suffix}</strong></td>
				</tr>
				<tr>
					<td align="right"><strong><?php echo "Fecha: "; ?> {invoice_date}</strong></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br/>
<br/>
<br/>
<table width="100%" bgcolor="#f2f3f7" border="0" cellspacing="1" cellpadding="2">
	<tr bgcolor="#C5C5C5">
		<td width="40%"><strong><?php echo "Descripcion"; ?></strong></td>
		<td width="20%"><strong><?php echo "Subtotal"; ?></strong></td>
		<td width="20%"><strong><?php echo "Impuesto"; ?></strong></td>
		<td width="20%"><strong><?php echo "Total"; ?></strong></td>
	</tr>
	{invoice_products_descriptions}
</table>
<br/>
<table width="100%" border="0" cellspacing="1" cellpadding="2">
	<tr bgcolor="#f2f3f7">
		<td rowspan="3" valign="top"><strong><?php echo "Informacion del Cliente: "; ?></strong><br/>{customer_info}</td>
		<td rowspan="3" valign="top">
			<strong><?php echo "Datos de la Reserva"; ?></strong><br/>
			<?php echo "Fecha Check-in: "; ?>: {checkin_date}<br/>
			<?php echo "Fecha Check-out: "; ?>: {checkout_date}<br/>
			<?php echo "No. Huespedes: "; ?>: {tot_guests}
		</td>
		<td width="244" align="left"><strong><?php echo "Subtotal: "; ?></strong> {invoice_totalnet}</td>
	</tr>
	<tr bgcolor="#f2f3f7">
		<td align="left"><strong><?php echo "Impuestos: "; ?></strong> {invoice_totaltax}</td>
	</tr>
	<tr bgcolor="#f2f3f7">
		<td align="left" valign="bottom"><strong><u><?php echo "Total: "; ?></u></strong> {invoice_grandtotal}</td>
	</tr>
</table>
{inv_notes}