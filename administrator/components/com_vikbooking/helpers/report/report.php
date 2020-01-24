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
* Reports parent Class of all sub-classes
*/
abstract class VikBookingReport
{
	protected $reportName = '';
	protected $reportFile = '';
	protected $reportFilters = array();
	protected $reportScript = '';
	protected $warning = '';
	protected $error = '';
	protected $dbo;

	protected $cols = array();
	protected $rows = array();
	protected $footerRow = array();

	/**
	 * Class constructor should define the name of
	 * the report and the filters to be displayed.
	 */
	public function __construct() {
		$this->dbo = JFactory::getDbo();
	}

	/**
	 * Extending Classes should define this method
	 * to get the name of the report.
	 */
	abstract public function getName();

	/**
	 * Extending Classes should define this method
	 * to get the name of class file.
	 */
	abstract public function getFileName();

	/**
	 * Extending Classes should define this method
	 * to get the filters of the report.
	 */
	abstract public function getFilters();

	/**
	 * Extending Classes should define this method
	 * to generate the report data (cols and rows).
	 */
	abstract public function getReportData();

	/**
	 * Loads the jQuery UI Datepicker.
	 * Method used only by sub-classes.
	 *
	 * @return 	self
	 */
	protected function loadDatePicker()
	{
		$vbo_app = new VboApplication();
		$vbo_app->loadDatePicker();

		return $this;
	}

	/**
	 * Loads all the rooms in VBO and returns the array.
	 *
	 * @return 	array
	 */
	protected function getRooms()
	{
		$rooms = array();
		$q = "SELECT * FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$rooms = $this->dbo->loadAssocList();
		}

		return $rooms;
	}

	/**
	 * Returns the number of total units for all rooms, or for a specific room.
	 * By default, the rooms unpublished are skipped, and all rooms are used.
	 * 
	 * @param 	[int] 	$idroom
	 * @param 	[bool] 	$published
	 *
	 * @return 	int
	 */
	protected function countRooms($idroom = 0, $published = 1)
	{
		$totrooms = 0;
		$clauses = array();
		if ((int)$idroom > 0) {
			$clauses[] = "`id`=".(int)$idroom;
		}
		if ($published) {
			$clauses[] = "`avail`=1";
		}
		$q = "SELECT SUM(`units`) FROM `#__vikbooking_rooms`".(count($clauses) ? " WHERE ".implode(' AND ', $clauses) : "").";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$totrooms = (int)$this->dbo->loadResult();
		}

		return $totrooms;
	}

	/**
	 * Concatenates the JavaScript rules.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setScript($str)
	{
		$this->reportScript .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current script string.
	 *
	 * @return 	string
	 */
	public function getScript()
	{
		return rtrim($this->reportScript, "\n");
	}

	/**
	 * Returns the date format in VBO for date, jQuery UI, Joomla.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$type
	 *
	 * @return 	string
	 */
	protected function getDateFormat($type = 'date')
	{
		$nowdf = VikBooking::getDateFormat();
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
			$juidf = 'dd/mm/yy';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
			$juidf = 'mm/dd/yy';
		} else {
			$df = 'Y/m/d';
			$juidf = 'yy/mm/dd';
		}

		switch ($type) {
			case 'jui':
				return $juidf;
			case 'joomla':
				return $nowdf;
			default:
				return $df;
		}
	}

	/**
	 * Returns the translated weekday.
	 * Uses the back-end language definitions.
	 *
	 * @param 	int 	$wday
	 * @param 	string 	$type 	use 'long' for the full name of the week, short for the 3-char version
	 *
	 * @return 	string
	 */
	protected function getWdayString($wday, $type = 'long')
	{
		$wdays_map_long = array(
			JText::_('VBWEEKDAYZERO'),
			JText::_('VBWEEKDAYONE'),
			JText::_('VBWEEKDAYTWO'),
			JText::_('VBWEEKDAYTHREE'),
			JText::_('VBWEEKDAYFOUR'),
			JText::_('VBWEEKDAYFIVE'),
			JText::_('VBWEEKDAYSIX')
		);

		$wdays_map_short = array(
			JText::_('VBSUN'),
			JText::_('VBMON'),
			JText::_('VBTUE'),
			JText::_('VBWED'),
			JText::_('VBTHU'),
			JText::_('VBFRI'),
			JText::_('VBSAT')
		);

		if ($type != 'long') {
			return isset($wdays_map_short[(int)$wday]) ? $wdays_map_short[(int)$wday] : '';
		}

		return isset($wdays_map_long[(int)$wday]) ? $wdays_map_long[(int)$wday] : '';
	}

	/**
	 * Sets the columns for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportCols($arr)
	{
		$this->cols = $arr;

		return $this;
	}

	/**
	 * Returns the columns for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportCols()
	{
		return $this->cols;
	}

	/**
	 * Sorts the rows of the report by key.
	 *
	 * @param 	string 		$krsort 	the key attribute of the array pairs
	 * @param 	string 		$krorder 	ascending (ASC) or descending (DESC)
	 *
	 * @return 	void
	 */
	protected function sortRows($krsort, $krorder)
	{
		if (empty($krsort) || !(count($this->rows))) {
			return;
		}

		$map = array();
		foreach ($this->rows as $k => $row) {
			foreach ($row as $kk => $v) {
				if (isset($v['key']) && $v['key'] == $krsort) {
					$map[$k] = $v['value'];
				}
			}
		}
		if (!(count($map))) {
			return;
		}

		if ($krorder == 'ASC') {
			asort($map);
		} else {
			arsort($map);
		}

		$sorted = array();
		foreach ($map as $k => $v) {
			$sorted[$k] = $this->rows[$k];
		}

		$this->rows = $sorted;
	}

	/**
	 * Sets the rows for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportRows($arr)
	{
		$this->rows = $arr;

		return $this;
	}

	/**
	 * Returns the rows for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportRows()
	{
		return $this->rows;
	}

	/**
	 * Sets the footer row (the totals) for this report.
	 *
	 * @param 	array 	$arr
	 *
	 * @return 	self
	 */
	protected function setReportFooterRow($arr)
	{
		$this->footerRow = $arr;

		return $this;
	}

	/**
	 * Returns the footer row for this report.
	 * Should be called after getReportData()
	 * or the returned array will be empty.
	 *
	 * @return 	array
	 */
	public function getReportFooterRow()
	{
		return $this->footerRow;
	}

	/**
	 * Sets warning messages by concatenating the existing ones.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setWarning($str)
	{
		$this->warning .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current warning string.
	 *
	 * @return 	string
	 */
	public function getWarning()
	{
		return rtrim($this->warning, "\n");
	}

	/**
	 * Sets errors by concatenating the existing ones.
	 * Method used only by sub-classes.
	 *
	 * @param 	string 		$str
	 *
	 * @return 	self
	 */
	protected function setError($str)
	{
		$this->error .= $str."\n";

		return $this;
	}

	/**
	 * Gets the current error string.
	 *
	 * @return 	string
	 */
	public function getError()
	{
		return rtrim($this->error, "\n");
	}
}