<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

/**
* AlloggiatiPolizia child Class of VikBookingReport.
* The Class was designed to export the customers details for the Italian Police.
* 
* @see https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/TechSupp.aspx
* @see https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/CREAFILE.pdf
* @see https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/MANUALEALBERGHI.pdf
* @see https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/TABELLE.zip
*/
class VikBookingReportAlloggiatiPolizia extends VikBookingReport
{
	/**
	 * Property 'defaultKeySort' is used by the View that renders the report.
	 */
	public $defaultKeySort = 'idbooking';
	/**
	 * Property 'defaultKeyOrder' is used by the View that renders the report.
	 */
	public $defaultKeyOrder = 'ASC';
	/**
	 * Property 'customExport' is used by the View to display custom export buttons.
	 */
	public $customExport = '';
	/**
	 * Debug mode is activated by passing the value 'e4j_debug' > 0
	 */
	private $debug;

	/**
	 * Other private vars of this sub-class.
	 */
	private $comuniProvince;
	private $nazioni;
	private $documenti;

	/**
	 * Class constructor should define the name of the report and
	 * other vars. Call the parent constructor to define the DB object.
	 */
	function __construct()
	{
		$this->reportFile = basename(__FILE__, '.php');
		$this->reportName = JText::_('VBOREPORT'.strtoupper(str_replace('_', '', $this->reportFile)));
		$this->reportFilters = array();

		$this->cols = array();
		$this->rows = array();
		$this->footerRow = array();

		$this->debug = (VikRequest::getInt('e4j_debug', 0, 'request') > 0);

		$this->comuniProvince = array();
		$this->nazioni = array();

		parent::__construct();
	}

	/**
	 * Returns the name of this report.
	 *
	 * @return 	string
	 */
	public function getName()
	{
		return $this->reportName;
	}

	/**
	 * Returns the name of this file without .php.
	 *
	 * @return 	string
	 */
	public function getFileName()
	{
		return $this->reportFile;
	}

	/**
	 * Returns the filters of this report.
	 *
	 * @return 	array
	 */
	public function getFilters()
	{
		if (count($this->reportFilters)) {
			//do not run this method twice, as it could load JS and CSS files.
			return $this->reportFilters;
		}

		//get VBO Application Object
		$vbo_app = new VboApplication();

		//load the jQuery UI Datepicker
		$this->loadDatePicker();

		//custom export button
		$this->customExport = '<a href="JavaScript: void(0);" onclick="vboDownloadSchedaPolizia();" class="vbcsvexport"><i class="fa fa-download"></i> <span>Download File</span></a>';

		//build the hidden values for the selection of Comuni & Province.
		$this->comuniProvince = $this->loadComuniProvince();
		$this->nazioni = $this->loadNazioni();
		$this->documenti = $this->loadDocumenti();
		$hidden_vals = '<div id="vbo-report-alloggiati-hidden" style="display: none;">';
		//Comuni
		$hidden_vals .= '	<div id="vbo-report-alloggiati-comune" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<select id="choose-comune" onchange="vboReportChosenComune(this);"><option value=""></option>';
		if (isset($this->comuniProvince['comuni']) && count($this->comuniProvince['comuni'])) {
			foreach ($this->comuniProvince['comuni'] as $code => $comune) {
				$hidden_vals .= '	<option value="'.$code.'">'.$comune.'</option>'."\n";
			}
		}
		$hidden_vals .= '		</select>';
		$hidden_vals .= '	</div>';
		//
		//Province
		$hidden_vals .= '	<div id="vbo-report-alloggiati-provincia" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<select id="choose-provincia" onchange="vboReportChosenProvincia(this);"><option value=""></option>';
		if (isset($this->comuniProvince['province']) && count($this->comuniProvince['province'])) {
			foreach ($this->comuniProvince['province'] as $code => $provincia) {
				$hidden_vals .= '	<option value="'.$code.'">'.$provincia.'</option>'."\n";
			}
		}
		$hidden_vals .= '		</select>';
		$hidden_vals .= '	</div>';
		//
		//Nazioni
		$hidden_vals .= '	<div id="vbo-report-alloggiati-nazione" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<select id="choose-nazione" onchange="vboReportChosenNazione(this);"><option value=""></option>';
		if (count($this->nazioni)) {
			foreach ($this->nazioni as $code => $nazione) {
				$hidden_vals .= '	<option value="'.$code.'">'.$nazione.'</option>'."\n";
			}
		}
		$hidden_vals .= '		</select>';
		$hidden_vals .= '	</div>';
		//
		//Documenti
		$hidden_vals .= '	<div id="vbo-report-alloggiati-doctype" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<select id="choose-documento" onchange="vboReportChosenDocumento(this);"><option value=""></option>';
		if (count($this->documenti)) {
			foreach ($this->documenti as $code => $documento) {
				$hidden_vals .= '	<option value="'.$code.'">'.$documento.'</option>'."\n";
			}
		}
		$hidden_vals .= '		</select>';
		$hidden_vals .= '	</div>';
		//
		//Sesso
		$hidden_vals .= '	<div id="vbo-report-alloggiati-sesso" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<select id="choose-sesso" onchange="vboReportChosenSesso(this);"><option value=""></option>';
		$sessos = array(
			1 => 'M',
			2 => 'F'
		);
		foreach ($sessos as $code => $ses) {
			$hidden_vals .= '	<option value="'.$code.'">'.$ses.'</option>'."\n";
		}
		$hidden_vals .= '		</select>';
		$hidden_vals .= '	</div>';
		//
		//Numero Documento
		$hidden_vals .= '	<div id="vbo-report-alloggiati-docnum" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<input type="text" size="40" id="choose-docnum" placeholder="Numero Documento..." value="" /><br/>';
		$hidden_vals .= '		<button type="button" class="btn" onclick="vboReportChosenDocnum(document.getElementById(\'choose-docnum\').value);">'.JText::_('VBAPPLY').'</button>';
		$hidden_vals .= '	</div>';
		//
		//Data di Nascita
		$hidden_vals .= '	<div id="vbo-report-alloggiati-dbirth" class="vbo-report-alloggiati-selcont" style="display: none;">';
		$hidden_vals .= '		<input type="text" size="40" id="choose-dbirth" placeholder="Data di Nascita" value="" /><br/>';
		$hidden_vals .= '		<button type="button" class="btn" onclick="vboReportChosenDbirth(document.getElementById(\'choose-dbirth\').value);">'.JText::_('VBAPPLY').'</button>';
		$hidden_vals .= '	</div>';
		//
		$hidden_vals .= '</div>';

		//From Date Filter (with hidden values for the dropdown menus of Comuni, Province, Stati etc..)
		$filter_opt = array(
			'label' => '<label for="fromdate">'.JText::_('VBOREPORTSDATEFROM').'</label>',
			'html' => '<input type="text" id="fromdate" name="fromdate" value="" class="vbo-report-datepicker vbo-report-datepicker-from" />'.$hidden_vals,
			'type' => 'calendar',
			'name' => 'fromdate'
		);
		array_push($this->reportFilters, $filter_opt);

		//To Date Filter
		$filter_opt = array(
			'label' => '<label for="todate">'.JText::_('VBOREPORTSDATETO').'</label>',
			'html' => '<input type="text" id="todate" name="todate" value="" class="vbo-report-datepicker vbo-report-datepicker-to" />',
			'type' => 'calendar',
			'name' => 'todate'
		);
		array_push($this->reportFilters, $filter_opt);

		//jQuery code for the datepicker calendars, select2 and triggers for the dropdown menus
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$js = 'var reportActiveCell = null, reportObj = {};
		jQuery(document).ready(function() {
			//prepare main filters
			jQuery(".vbo-report-datepicker:input").datepicker({
				maxDate: 0,
				dateFormat: "'.$this->getDateFormat('jui').'",
				onSelect: vboReportCheckDates
			});
			'.(!empty($pfromdate) ? 'jQuery(".vbo-report-datepicker-from").datepicker("setDate", "'.$pfromdate.'");' : '').'
			'.(!empty($ptodate) ? 'jQuery(".vbo-report-datepicker-to").datepicker("setDate", "'.$ptodate.'");' : '').'
			//prepare filler helpers
			jQuery("#vbo-report-alloggiati-hidden").children().detach().appendTo(".vbo-info-overlay-report");
			jQuery("#choose-comune").select2({placeholder: "- Seleziona un Comune -", width: "200px"});
			jQuery("#choose-provincia").select2({placeholder: "- Seleziona una Provincia -", width: "200px"});
			jQuery("#choose-nazione").select2({placeholder: "- Seleziona una Nazione -", width: "200px"});
			jQuery("#choose-documento").select2({placeholder: "- Seleziona un Documento -", width: "200px"});
			jQuery("#choose-sesso").select2({placeholder: "- Seleziona Sesso -", width: "200px"});
			jQuery("#choose-dbirth").datepicker({
				maxDate: 0,
				dateFormat: "dd/mm/yy",
				changeMonth: true,
				changeYear: true,
				yearRange: "'.(date('Y') - 100).':'.date('Y').'"
			});
			//click events
			jQuery(".vbo-report-load-comune").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-comune").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-provincia").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-provincia").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-nazione, .vbo-report-load-cittadinanza").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-nazione").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-doctype").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-doctype").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-docplace").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-comune").show();
				jQuery("#vbo-report-alloggiati-nazione").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-sesso").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-sesso").show();
				vboShowOverlay();
			});
			jQuery(".vbo-report-load-docnum").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-docnum").show();
				vboShowOverlay();
				setTimeout(function(){jQuery("#choose-docnum").focus();}, 500);
			});
			jQuery(".vbo-report-load-dbirth").click(function() {
				reportActiveCell = this;
				jQuery(".vbo-report-alloggiati-selcont").hide();
				jQuery("#vbo-report-alloggiati-dbirth").show();
				vboShowOverlay();
				//pretend the overlay is off, or navigating in the datepicker will close the modal.
				setTimeout(function(){vbo_overlay_on = false;}, 800);
				//
			});
		});
		function vboReportCheckDates(selectedDate, inst) {
			if (selectedDate === null || inst === null) {
				return;
			}
			var cur_from_date = jQuery(this).val();
			if (jQuery(this).hasClass("vbo-report-datepicker-from") && cur_from_date.length) {
				var nowstart = jQuery(this).datepicker("getDate");
				var nowstartdate = new Date(nowstart.getTime());
				jQuery(".vbo-report-datepicker-to").datepicker("option", {minDate: nowstartdate});
			}
		}
		function vboReportChosenComune(comune) {
			var c_code = comune.value;
			var c_val = comune.options[comune.selectedIndex].text;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					if (jQuery(reportActiveCell).hasClass("vbo-report-load-docplace")) {
						reportObj[nowindex].docplace = c_code;
					} else {
						reportObj[nowindex].combirth = c_code;
					}
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-comune").val("").select2("data", null, false);
		}
		function vboReportChosenProvincia(prov) {
			var c_code = prov.value;
			var c_val = prov.options[prov.selectedIndex].text;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					reportObj[nowindex].probirth = c_code;
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-provincia").val("").select2("data", null, false);
		}
		function vboReportChosenNazione(naz) {
			var c_code = naz.value;
			var c_val = naz.options[naz.selectedIndex].text;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					if (jQuery(reportActiveCell).hasClass("vbo-report-load-nazione")) {
						reportObj[nowindex].stabirth = c_code;
					} else if (jQuery(reportActiveCell).hasClass("vbo-report-load-docplace")) {
						reportObj[nowindex].docplace = c_code;
					} else {
						reportObj[nowindex].citizen = c_code;
					}
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-nazione").val("").select2("data", null, false);
		}
		function vboReportChosenDocumento(doctype) {
			var c_code = doctype.value;
			var c_val = doctype.options[doctype.selectedIndex].text;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					reportObj[nowindex].doctype = c_code;
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-documento").val("").select2("data", null, false);
		}
		function vboReportChosenSesso(sesso) {
			var c_code = sesso.value;
			var c_val = sesso.options[sesso.selectedIndex].text;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					reportObj[nowindex].gender = c_code;
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-sesso").val("").select2("data", null, false);
		}
		function vboReportChosenDocnum(val) {
			var c_code = val, c_val = val;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					reportObj[nowindex].docnum = c_code;
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-docnum").val("");
		}
		function vboReportChosenDbirth(val) {
			var c_code = val, c_val = val;
			if (reportActiveCell !== null) {
				var nowindex = jQuery(".vbo-reports-output table tbody tr").index(jQuery(reportActiveCell).closest("tr"));
				if (isNaN(nowindex) || parseInt(nowindex) < 0) {
					alert("Error, cannot find element to update.");
				} else {
					jQuery(reportActiveCell).addClass("vbo-report-load-elem-filled").find("span").text(c_val);
					if (!reportObj.hasOwnProperty(nowindex)) {
						reportObj[nowindex] = {};
					}
					reportObj[nowindex].dbirth = c_code;
				}
			}
			reportActiveCell = null;
			vboHideOverlay();
			jQuery("#choose-dbirth").val("");
		}
		//download function
		function vboDownloadSchedaPolizia() {
			if (!confirm("Sei sicuro di aver compilato tutti i dati della Scheda Alloggiati?")) {
				return false;
			}
			document.adminForm.target = "_blank";
			document.adminForm.action += "&tmpl=component";
			vboSetFilters({exportreport: "1", filler: JSON.stringify(reportObj)}, true);
			setTimeout(function() {
				document.adminForm.target = "";
				document.adminForm.action = document.adminForm.action.replace("&tmpl=component", "");
				vboSetFilters({exportreport: "0", filler: ""}, false);
			}, 1000);
		}
		';
		$this->setScript($js);

		return $this->reportFilters;
	}

	/**
	 * Loads the report data from the DB.
	 * Returns true in case of success, false otherwise.
	 * Sets the columns and rows for the report to be displayed.
	 *
	 * @return 	boolean
	 */
	public function getReportData()
	{
		if (strlen($this->getError())) {
			//Export functions may set errors rather than exiting the process, and the View may continue the execution to attempt to render the report.
			return false;
		}
		//Input fields and other vars
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pkrsort = VikRequest::getString('krsort', $this->defaultKeySort, 'request');
		$pkrsort = empty($pkrsort) ? $this->defaultKeySort : $pkrsort;
		$pkrorder = VikRequest::getString('krorder', $this->defaultKeyOrder, 'request');
		$pkrorder = empty($pkrorder) ? $this->defaultKeyOrder : $pkrorder;
		$pkrorder = $pkrorder == 'DESC' ? 'DESC' : 'ASC';
		$currency_symb = VikBooking::getCurrencySymb();
		$df = $this->getDateFormat();
		$datesep = VikBooking::getDateSeparator();
		if (empty($ptodate)) {
			$ptodate = $pfromdate;
		}
		//Get dates timestamps
		$from_ts = VikBooking::getDateTimestamp($pfromdate, 0, 0);
		$to_ts = VikBooking::getDateTimestamp($ptodate, 23, 59, 59);
		if (empty($pfromdate) || empty($from_ts) || empty($to_ts)) {
			$this->setError(JText::_('VBOREPORTSERRNODATES'));
			return false;
		}

		//Query to obtain the records (all check-ins within the dates filter)
		$records = array();
		$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`ts`,`o`.`days`,`o`.`checkin`,`o`.`checkout`,`o`.`totpaid`,`o`.`roomsnum`,`o`.`total`,`o`.`idorderota`,`o`.`channel`,`o`.`country`,".
			"`or`.`idorder`,`or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`t_first_name`,`or`.`t_last_name`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`extracosts`,`or`.`room_cost`,".
			"`co`.`idcustomer`,`co`.`pax_data`,`c`.`first_name`,`c`.`last_name`,`c`.`country` AS `customer_country`,`c`.`doctype`,`c`.`docnum`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth` ".
			"FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` AS `or` ON `or`.`idorder`=`o`.`id` ".
			"LEFT JOIN `#__vikbooking_customers_orders` AS `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` AS `c` ON `c`.`id`=`co`.`idcustomer` ".
			"WHERE `o`.`status`='confirmed' AND `o`.`closure`=0 AND `o`.`checkin`>=".$from_ts." AND `o`.`checkin`<=".$to_ts." ".
			"ORDER BY `o`.`checkin` ASC, `o`.`id` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$records = $this->dbo->loadAssocList();
		}
		if (!count($records)) {
			$this->setError(JText::_('VBOREPORTSERRNORESERV'));
			$this->setError('Nessun check-in nelle date selezionate.');
			return false;
		}

		//nest records with multiple rooms booked inside sub-array
		$bookings = array();
		foreach ($records as $v) {
			if (!isset($bookings[$v['id']])) {
				$bookings[$v['id']] = array();
			}
			array_push($bookings[$v['id']], $v);
		}

		//define the columns of the report
		$this->cols = array(
			//tipo
			array(
				'key' => 'tipo',
				'attr' => array(
					'class="vbo-report-longlbl"'
				),
				'label' => 'Tipo Alloggiato'
			),
			//check-in
			array(
				'key' => 'checkin',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBPICKUPAT')
			),
			//nights
			array(
				'key' => 'nights',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBDAYS')
			),
			//cognome
			array(
				'key' => 'cognome',
				'label' => JText::_('VBTRAVELERLNAME')
			),
			//nome
			array(
				'key' => 'nome',
				'label' => JText::_('VBTRAVELERNAME')
			),
			//sesso
			array(
				'key' => 'gender',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBCUSTOMERGENDER')
			),
			//data di nascita
			array(
				'key' => 'dbirth',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBCUSTOMERBDATE')
			),
			//comune di nascita
			array(
				'key' => 'combirth',
				'attr' => array(
					'class="center"'
				),
				'label' => 'Comune Nascita'
			),
			//provincia di nascita
			array(
				'key' => 'probirth',
				'attr' => array(
					'class="center"'
				),
				'label' => 'Provincia Nascita'
			),
			//stato di nascita
			array(
				'key' => 'stabirth',
				'attr' => array(
					'class="center"'
				),
				'label' => 'Stato Nascita'
			),
			//cittadinanza
			array(
				'key' => 'citizen',
				'attr' => array(
					'class="center"'
				),
				'label' => 'Cittadinanza'
			),
			//tipo documento
			array(
				'key' => 'doctype',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBCUSTOMERDOCTYPE')
			),
			//numero documento
			array(
				'key' => 'docnum',
				'attr' => array(
					'class="center"'
				),
				'label' => JText::_('VBCUSTOMERDOCNUM')
			),
			//luogo rilascio documento
			array(
				'key' => 'docplace',
				'attr' => array(
					'class="center"'
				),
				'label' => 'Luogo Rilascio'
			),
			//id booking
			array(
				'key' => 'idbooking',
				'attr' => array(
					'class="center"'
				),
				'label' => 'ID'
			)
		);

		//loop over the bookings to build the rows of the report
		$from_info = getdate($from_ts);
		foreach ($bookings as $gbook) {
			$guests_rows = array($gbook[0]);
			$tot_guests_rows = 1;
			$tipo = 16;
			//Codici Tipo Alloggiato
			// 16 = Ospite Singolo
			// 17 = Capofamiglia
			// 18 = Capogruppo
			// 19 = Familiare
			// 20 = Membro Gruppo
			//
			if (!empty($gbook[0]['pax_data'])) {
				$pax_data = json_decode($gbook[0]['pax_data'], true);
				if (count($pax_data)) {
					$guests_rows[0]['pax_data'] = $pax_data;
					$tot_guests_rows = 0;
					foreach ($pax_data as $roomguests) {
						$tot_guests_rows += count($roomguests);
					}
					for ($i = 1; $i < $tot_guests_rows; $i++) {
						array_push($guests_rows, $guests_rows[0]);
					}
					$tipo = count($guests_rows) > 1 ? 17 : $tipo;
				}
			}
			//create one row for each guest
			$guest_ind = 1;
			foreach ($guests_rows as $ind => $guests) {
				$use_tipo = $ind > 0 && $tipo == 17 ? 19 : $tipo;
				$insert_row = array();
				//Tipo Alloggiato
				array_push($insert_row, array(
					'key' => 'tipo',
					'callback' => function ($val) {
						switch ($val) {
							case 16:
								return 'Ospite Singolo';
							case 17:
								return 'Capofamiglia';
							case 18:
								return 'Capogruppo';
							case 19:
								return 'Familiare';
							case 20:
								return 'Membro Gruppo';
						}
						return '?';
					},
					'no_export_callback' => 1,
					'value' => $use_tipo
				));
				//Data Arrivo
				array_push($insert_row, array(
					'key' => 'checkin',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) {
						return date('d/m/Y', $val);
					},
					'value' => $guests['checkin']
				));
				//Giorni di Permanenza
				array_push($insert_row, array(
					'key' => 'nights',
					'attr' => array(
						'class="center"'
					),
					'value' => $guests['days']
				));
				//Cognome
				$cognome = !empty($guests['t_last_name']) ? $guests['t_last_name'] : $guests['last_name'];
				if (is_array($guests['pax_data']) && count($guests['pax_data']) > 0) {
					$j = 0;
					foreach ($guests['pax_data'] as $rnum => $rguests) {
						foreach ($rguests as $rguest) {
							$j++;
							if ($j == $guest_ind) {
								$cognome = !empty($rguest['last_name']) ? $rguest['last_name'] : $cognome;
								break 2;
							}
						}
					}
				}
				array_push($insert_row, array(
					'key' => 'cognome',
					'value' => $cognome
				));
				//Nome
				$nome = !empty($guests['t_first_name']) ? $guests['t_first_name'] : $guests['first_name'];
				if (is_array($guests['pax_data']) && count($guests['pax_data']) > 0) {
					$j = 0;
					foreach ($guests['pax_data'] as $rnum => $rguests) {
						foreach ($rguests as $rguest) {
							$j++;
							if ($j == $guest_ind) {
								$nome = !empty($rguest['first_name']) ? $rguest['first_name'] : $nome;
								break 2;
							}
						}
					}
				}
				array_push($insert_row, array(
					'key' => 'nome',
					'value' => $nome
				));
				//Sesso
				$gender = !empty($guests['gender']) && $guest_ind < 2 ? strtoupper($guests['gender']) : '';
				$gender = $gender == 'F' ? 2 : ($gender == 'M' ? 1 : $gender);
				array_push($insert_row, array(
					'key' => 'gender',
					'attr' => array(
						'class="center'.(empty($gender) ? ' vbo-report-load-sesso' : '').'"'
					),
					'callback' => function ($val) {
						return $val == 2 ? 2 : ($val == 1 ? 'M' : '?');
					},
					'value' => $gender
				));
				//Data di nascita
				$dbirth = !empty($guests['bdate']) && $guest_ind < 2 ? VikBooking::getDateTimestamp($guests['bdate'], 0, 0) : '';
				array_push($insert_row, array(
					'key' => 'dbirth',
					'attr' => array(
						'class="center'.(empty($dbirth) ? ' vbo-report-load-dbirth' : '').'"'
					),
					'callback' => function ($val) {
						return !empty($val) ? date('d/m/Y', $val) : '?';
					},
					'value' => $dbirth
				));
				//Comune di nascita
				array_push($insert_row, array(
					'key' => 'combirth',
					'attr' => array(
						'class="center vbo-report-load-comune"'
					),
					'value' => '?'
				));
				//Provincia di nascita
				array_push($insert_row, array(
					'key' => 'probirth',
					'attr' => array(
						'class="center vbo-report-load-provincia"'
					),
					'value' => '?'
				));
				//Stato di nascita
				array_push($insert_row, array(
					'key' => 'stabirth',
					'attr' => array(
						'class="center vbo-report-load-nazione"'
					),
					'value' => '?'
				));
				//Cittadinanza
				array_push($insert_row, array(
					'key' => 'citizen',
					'attr' => array(
						'class="center vbo-report-load-cittadinanza"'
					),
					'value' => '?'
				));
				//Tipo documento
				$doctype = $guest_ind < 2 ? '?' : '---';
				array_push($insert_row, array(
					'key' => 'doctype',
					'attr' => array(
						'class="center'.($guest_ind < 2 ? ' vbo-report-load-doctype' : '').'"'
					),
					'value' => $doctype
				));
				//Numero documento
				$docnum = $guest_ind < 2 ? $guests['docnum'] : '---';
				array_push($insert_row, array(
					'key' => 'docnum',
					'attr' => array(
						'class="center'.($guest_ind < 2 && empty($docnum) ? ' vbo-report-load-docnum' : '').'"'
					),
					'callback' => function ($val) {
						return empty($val) ? '?' : $val;
					},
					'value' => $docnum
				));
				//Luogo rilascio documento
				$docplace = $guest_ind < 2 ? '?' : '---';
				array_push($insert_row, array(
					'key' => 'docplace',
					'attr' => array(
						'class="center'.($guest_ind < 2 ? ' vbo-report-load-docplace' : '').'"'
					),
					'value' => $docplace
				));
				//id booking
				array_push($insert_row, array(
					'key' => 'idbooking',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) {
						return '<a href="index.php?option=com_vikbooking&task=editorder&cid[]='.$val.'" target="_blank"><i class="fa fa-external-link"></i> '.$val.'</a>';
					},
					'ignore_export' => 1,
					'value' => $guests['id']
				));

				//push fields in the rows array as a new row
				array_push($this->rows, $insert_row);
				//increment guest index
				$guest_ind++;
			}
		}

		//do not sort the rows for this report because the lines of the guests of the same booking must be consecutive
		//$this->sortRows($pkrsort, $pkrorder);

		//the footer row will just print the amount of records to export
		array_push($this->footerRow, array(
			array(
				'attr' => array(
					'class="vbo-report-total"'
				),
				'value' => '<h3>'.JText::_('VBOREPORTSTOTALROW').'</h3>'
			),
			array(
				'attr' => array(
					'colspan="'.(count($this->cols) - 1).'"'
				),
				'value' => count($this->rows)
			)
		));

		//Debug
		if ($this->debug) {
			$this->setWarning('path to report file = '.urlencode(dirname(__FILE__)).'<br/>');
			$this->setWarning('$total_rooms_units = '.$total_rooms_units.'<br/>');
			$this->setWarning('$bookings:<pre>'.print_r($bookings, true).'</pre><br/>');
		}
		//

		return true;
	}

	/**
	 * Generates the text file for the Italian Police, 
	 * then it sends it to output for download.
	 * In case of errors, the process is not terminated (exit)
	 * to let the View display the error message.
	 *
	 * @param 	int 	$export_type 	the view will pass this argument to the method to call different types of export.
	 *
	 * @return 	mixed 	void on success with script termination, false otherwise.
	 */
	public function customExport($export_type = 0)
	{
		if (!$this->getReportData()) {
			return false;
		}
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pfiller = VikRequest::getString('filler', '', 'request');
		$pfiller = !empty($pfiller) ? json_decode($pfiller, true) : array();
		$pfiller = !is_array($pfiller) ? array() : $pfiller;

		//debug
		//$this->setError('<pre>'.print_r($pfiller, true).'</pre><br/>');
		//return false;
		//

		//map of the rows keys with their related length
		$keys_length_map = array(
			'tipo' => 2,
			'checkin' => 10,
			'nights' => 2,
			'cognome' => 50,
			'nome' => 30,
			'gender' => 1,
			'dbirth' => 10,
			'combirth' => 9,
			'probirth' => 2,
			'stabirth' => 9,
			'citizen' => 9,
			'doctype' => 5,
			'docnum' => 20,
			'docplace' => 9
		);

		//pool of booking IDs to update their history
		$booking_ids = array();

		//array of lines (one line for each guest)
		$lines = array();

		//Push the lines of the Text file
		foreach ($this->rows as $ind => $row) {
			$line_cont = '';
			foreach ($row as $field) {
				if ($field['key'] == 'idbooking' && !in_array($field['value'], $booking_ids)) {
					array_push($booking_ids, $field['value']);
				}
				if (isset($field['ignore_export'])) {
					continue;
				}
				//report value
				$value = !isset($field['no_export_callback']) && isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value'];
				//check if a value for this field was filled in manually
				if (is_array($pfiller) && isset($pfiller[$ind]) && isset($pfiller[$ind][$field['key']])) {
					if (empty($value) || $value == '?' || !empty($pfiller[$ind][$field['key']])) {
						$value = $pfiller[$ind][$field['key']];
					}
				}
				//0 or '---' should be changed to an empty string (case of "-- Estero --" or field to be filled with Blank)
				$value = empty($value) || $value == '---' ? '' : $value;
				//concatenate the field to the current line
				$line_cont .= $this->valueFiller($value, $keys_length_map[$field['key']]);
			}
			//push the line in the array of lines
			array_push($lines, $line_cont);
		}

		//update the history for all bookings affected
		foreach ($booking_ids as $bid) {
			VikBooking::getBookingHistoryInstance()->setBid($bid)->store('RP', $this->reportName);
		}

		//Force text file download
		header("Content-type: text/plain");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="'.$this->reportName.'-'.str_replace('/', '_', $pfromdate).'-'.str_replace('/', '_', $ptodate).'.txt"');
		echo implode("\r\n", $lines);
		exit;
	}

	/**
	 * This method adds blank spaces to the string
	 * until the passed length of string is reached.
	 *
	 * @param 	string 		$val
	 * @param 	int 		$len
	 *
	 * @return 	string
	 */
	private function valueFiller($val, $len)
	{
		$len = empty($len) || (int)$len <= 0 ? strlen($val) : (int)$len;

		//clean up $val in case there is still a CR or LF
		$val = str_replace(array("\n", "\r", "\r\n"), '', $val);
		//
		
		if (strlen($val) < $len) {
			while (strlen($val) < $len) {
				$val .= ' ';
			}
		} elseif (strlen($val) > $len) {
			$val = substr($val, 0, $len);
		}

		return $val;
	}

	/**
	 * Parses the file Comuni.csv and returns two associative
	 * arrays: one for the Comuni and one for the Province.
	 * Every line of the CSV is composed of: Codice, Comune, Provincia.
	 *
	 * @return 	array
	 */
	private function loadComuniProvince()
	{
		$vals = array(
			'comuni' => array(
				0 => '-- Estero --'
			),
			'province' => array(
				0 => '-- Estero --'
			)
		);

		$csv = dirname(__FILE__).DIRECTORY_SEPARATOR.'Comuni.csv';
		$rows = file($csv);
		foreach ($rows as $row) {
			if (empty($row)) {
				continue;
			}
			$v = explode(';', $row);
			if (count($v) != 3) {
				continue;
			}
			$vals['comuni'][$v[0]] = $v[1];
			$vals['province'][$v[2]] = $v[2];
		}

		return $vals;
	}

	/**
	 * Parses the file Nazioni.csv and returns an associative
	 * array with the code and name of the Nazione.
	 * Every line of the CSV is composed of: Codice, Nazione.
	 *
	 * @return 	array
	 */
	private function loadNazioni()
	{
		$nazioni = array();

		$csv = dirname(__FILE__).DIRECTORY_SEPARATOR.'Nazioni.csv';
		$rows = file($csv);
		foreach ($rows as $row) {
			if (empty($row)) {
				continue;
			}
			$v = explode(';', $row);
			if (count($v) != 2) {
				continue;
			}
			$nazioni[$v[0]] = $v[1];
		}

		return $nazioni;
	}

	/**
	 * Parses the file Documenti.csv and returns an associative
	 * array with the code and name of the Documento.
	 * Every line of the CSV is composed of: Codice, Documento.
	 *
	 * @return 	array
	 */
	private function loadDocumenti()
	{
		$documenti = array();

		$csv = dirname(__FILE__).DIRECTORY_SEPARATOR.'Documenti.csv';
		$rows = file($csv);
		foreach ($rows as $row) {
			if (empty($row)) {
				continue;
			}
			$v = explode(';', $row);
			if (count($v) != 2) {
				continue;
			}
			$documenti[$v[0]] = $v[1];
		}

		return $documenti;
	}

}
