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
* Revenue child Class of VikBookingReport
*/
class VikBookingReportRevenue extends VikBookingReport
{
	/**
	 * Property 'defaultKeySort' is used by the View that renders the report.
	 */
	public $defaultKeySort = 'day';
	/**
	 * Property 'defaultKeyOrder' is used by the View that renders the report.
	 */
	public $defaultKeyOrder = 'ASC';
	/**
	 * Property 'exportAllowed' is used by the View to display the export button.
	 */
	public $exportAllowed = 1;
	/**
	 * Debug mode is activated by passing the value 'e4j_debug' > 0
	 */
	private $debug;

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

		//From Date Filter
		$filter_opt = array(
			'label' => '<label for="fromdate">'.JText::_('VBOREPORTSDATEFROM').'</label>',
			'html' => '<input type="text" id="fromdate" name="fromdate" value="" class="vbo-report-datepicker vbo-report-datepicker-from" />',
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

		//Room ID filter
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$all_rooms = $this->getRooms();
		$rooms = array();
		foreach ($all_rooms as $room) {
			$rooms[$room['id']] = $room['name'];
		}
		if (count($rooms)) {
			$rooms_sel_html = $vbo_app->getNiceSelect($rooms, $pidroom, 'idroom', JText::_('VBOSTATSALLROOMS'), JText::_('VBOSTATSALLROOMS'), '', '', 'idroom');
			$filter_opt = array(
				'label' => '<label for="idroom">'.JText::_('VBOREPORTSROOMFILT').'</label>',
				'html' => $rooms_sel_html,
				'type' => 'select',
				'name' => 'idroom'
			);
			array_push($this->reportFilters, $filter_opt);
		}

		//jQuery code for the datepicker calendars and select2
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$js = 'jQuery(document).ready(function() {
			jQuery(".vbo-report-datepicker:input").datepicker({
				maxDate: 0,
				dateFormat: "'.$this->getDateFormat('jui').'",
				onSelect: vboReportCheckDates
			});
			'.(!empty($pfromdate) ? 'jQuery(".vbo-report-datepicker-from").datepicker("setDate", "'.$pfromdate.'");' : '').'
			'.(!empty($ptodate) ? 'jQuery(".vbo-report-datepicker-to").datepicker("setDate", "'.$ptodate.'");' : '').'
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
		}';
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
		$pidroom = VikRequest::getInt('idroom', '', 'request');
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

		//Query to obtain the records
		$records = array();
		$q = "SELECT `o`.`id`,`o`.`ts`,`o`.`days`,`o`.`checkin`,`o`.`checkout`,`o`.`totpaid`,`o`.`roomsnum`,`o`.`total`,`o`.`idorderota`,`o`.`channel`,`o`.`country`,`o`.`tot_taxes`,".
			"`o`.`tot_city_taxes`,`o`.`tot_fees`,`o`.`cmms`,`or`.`idorder`,`or`.`idroom`,`or`.`optionals`,`or`.`cust_cost`,`or`.`cust_idiva`,`or`.`extracosts`,`or`.`room_cost` ".
			"FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` AS `or` ON `or`.`idorder`=`o`.`id` ".
			"WHERE `o`.`status`='confirmed' AND `o`.`closure`=0 AND `o`.`checkout`>=".$from_ts." AND `o`.`checkin`<=".$to_ts." ".(!empty($pidroom) ? "AND `or`.`idroom`=".(int)$pidroom." " : "").
			"ORDER BY `o`.`checkin` ASC, `o`.`id` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$records = $this->dbo->loadAssocList();
		}
		if (!count($records)) {
			$this->setError(JText::_('VBOREPORTSERRNORESERV'));
			return false;
		}

		//nest records with multiple rooms booked inside sub-array
		$bookings = array();
		foreach ($records as $v) {
			if (!isset($bookings[$v['id']])) {
				$bookings[$v['id']] = array();
			}
			//calculate the from_ts and to_ts values for later comparison
			$in_info = getdate($v['checkin']);
			$out_info = getdate($v['checkout']);
			$v['from_ts'] = mktime(0, 0, 0, $in_info['mon'], $in_info['mday'], $in_info['year']);
			$v['to_ts'] = mktime(23, 59, 59, $out_info['mon'], ($out_info['mday'] - 1), $out_info['year']);
			//
			array_push($bookings[$v['id']], $v);
		}

		//define the columns of the report
		$this->cols = array(
			//date
			array(
				'key' => 'day',
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEDAY')
			),
			//rooms sold
			array(
				'key' => 'rooms_sold',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUERSOLD')
			),
			//total bookings
			array(
				'key' => 'tot_bookings',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUETOTB')
			),
			//% occupancy
			array(
				'key' => 'occupancy',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEPOCC')
			),
			//IBE revenue
			array(
				'key' => 'ibe_revenue',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEREVWEB')
			),
			//OTAs revenue
			array(
				'key' => 'ota_revenue',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEREVOTA')
			),
			//ADR
			array(
				'key' => 'adr',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEADR'),
				'tip' => JText::_('VBOREPORTREVENUEADRHELP')
			),
			//RevPAR
			array(
				'key' => 'revpar',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEREVPAR'),
				'tip' => JText::_('VBOREPORTREVENUEREVPARH')
			),
			//Taxes
			array(
				'key' => 'taxes',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUETAX')
			),
			//Revenue
			array(
				'key' => 'revenue',
				'attr' => array(
					'class="center"'
				),
				'sortable' => 1,
				'label' => JText::_('VBOREPORTREVENUEREV')
			)
		);

		$total_rooms_units = $this->countRooms($pidroom);

		//loop over the dates of the report to build the rows
		$from_info = getdate($from_ts);
		$to_info = getdate($to_ts);
		while ($from_info[0] <= $to_info[0]) {
			//prepare default fields for this row
			$day_ts = $from_info[0];
			$curwday = $this->getWdayString($from_info['wday'], 'short');
			$rooms_sold = 0;
			$tot_bookings = 0;
			$occupancy = 0;
			$ibe_revenue = 0;
			$ota_revenue = 0;
			$adr = 0;
			$revpar = 0;
			$taxes = 0;
			$revenue = 0;
			//calculate the report details for this day
			foreach ($bookings as $gbook) {
				if ($from_info[0] >= $gbook[0]['from_ts'] && $from_info[0] <= $gbook[0]['to_ts']) {
					//this booking affects the current day
					$rooms_sold += $gbook[0]['roomsnum'];
					$tot_bookings++;
					//calculate net revenue and taxes
					$tot_net = $gbook[0]['total'] - (float)$gbook[0]['tot_taxes'] - (float)$gbook[0]['tot_city_taxes'] - (float)$gbook[0]['tot_fees'] - (float)$gbook[0]['cmms'];
					$tot_net = $tot_net / (int)$gbook[0]['days'];
					$revenue += $tot_net;
					if (!empty($gbook[0]['idorderota']) && !empty($gbook[0]['channel'])) {
						$ota_revenue += $tot_net;
					} else {
						$ibe_revenue += $tot_net;
					}
					$taxes += ((float)$gbook[0]['tot_taxes'] + (float)$gbook[0]['tot_city_taxes'] + (float)$gbook[0]['tot_fees'] + (float)$gbook[0]['cmms']) / (int)$gbook[0]['days'];
				}
			}
			$occupancy = round(($rooms_sold * 100 / $total_rooms_units), 2);
			$adr = $rooms_sold > 0 ? $revenue / $rooms_sold : 0;
			$revpar = $revenue / $total_rooms_units;
			//push fields in the rows array as a new row
			array_push($this->rows, array(
				array(
					'key' => 'day',
					'callback' => function ($val) use ($df, $datesep, $curwday) {
						return $curwday.', '.date(str_replace("/", $datesep, $df), $val);
					},
					'value' => $day_ts
				),
				array(
					'key' => 'rooms_sold',
					'attr' => array(
						'class="center"'
					),
					'value' => $rooms_sold
				),
				array(
					'key' => 'tot_bookings',
					'attr' => array(
						'class="center"'
					),
					'value' => $tot_bookings
				),
				array(
					'key' => 'occupancy',
					'attr' => array(
						'class="center"'
					),
					'value' => $occupancy
				),
				array(
					'key' => 'ibe_revenue',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $ibe_revenue
				),
				array(
					'key' => 'ota_revenue',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $ota_revenue
				),
				array(
					'key' => 'adr',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $adr
				),
				array(
					'key' => 'revpar',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $revpar
				),
				array(
					'key' => 'taxes',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $taxes
				),
				array(
					'key' => 'revenue',
					'attr' => array(
						'class="center"'
					),
					'callback' => function ($val) use ($currency_symb) {
						return $currency_symb.' '.VikBooking::numberFormat($val);
					},
					'value' => $revenue
				)
			));
			//next day iteration
			$from_info = getdate(mktime(0, 0, 0, $from_info['mon'], ($from_info['mday'] + 1), $from_info['year']));
		}

		//sort rows
		$this->sortRows($pkrsort, $pkrorder);

		//loop over the rows to build the footer row with the totals
		$foot_rooms_sold = 0;
		$foot_tot_bookings = 0;
		$foot_ibe_revenue = 0;
		$foot_ota_revenue = 0;
		$foot_taxes = 0;
		$foot_revenue = 0;
		foreach ($this->rows as $row) {
			$foot_rooms_sold += $row[1]['value'];
			$foot_tot_bookings += $row[2]['value'];
			$foot_ibe_revenue += $row[4]['value'];
			$foot_ota_revenue += $row[5]['value'];
			$foot_taxes += $row[8]['value'];
			$foot_revenue += $row[9]['value'];
		}
		array_push($this->footerRow, array(
			array(
				'attr' => array(
					'class="vbo-report-total"'
				),
				'value' => '<h3>'.JText::_('VBOREPORTSTOTALROW').'</h3>'
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'value' => $foot_rooms_sold
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'value' => $foot_tot_bookings
			),
			array(
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikBooking::numberFormat($val);
				},
				'value' => $foot_ibe_revenue
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikBooking::numberFormat($val);
				},
				'value' => $foot_ota_revenue
			),
			array(
				'value' => ''
			),
			array(
				'value' => ''
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikBooking::numberFormat($val);
				},
				'value' => $foot_taxes
			),
			array(
				'attr' => array(
					'class="center"'
				),
				'callback' => function ($val) use ($currency_symb) {
					return $currency_symb.' '.VikBooking::numberFormat($val);
				},
				'value' => $foot_revenue
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
	 * Generates the report columns and rows, then it outputs a CSV file
	 * for download. In case of errors, the process is not terminated (exit)
	 * to let the View display the error message.
	 *
	 * @return 	mixed 	void on success with script termination, false otherwise.
	 */
	public function exportCSV()
	{
		if (!$this->getReportData()) {
			return false;
		}
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');

		$csvlines = array();

		//Push the head of the CSV file
		$csvcols = array();
		foreach ($this->cols as $col) {
			array_push($csvcols, $col['label']);
		}
		array_push($csvlines, $csvcols);

		//Push the rows of the CSV file
		foreach ($this->rows as $row) {
			$csvrow = array();
			foreach ($row as $field) {
				array_push($csvrow, (isset($field['callback']) && is_callable($field['callback']) ? $field['callback']($field['value']) : $field['value']));
			}
			array_push($csvlines, $csvrow);
		}

		//Force CSV download
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="'.$this->reportName.'-'.str_replace('/', '_', $pfromdate).'-'.str_replace('/', '_', $ptodate).'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

}
