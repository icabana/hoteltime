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

class VboBookingHistory {

	private $bid;
	private $prevBooking;
	private $dbo;
	private $typesMap;

	public function __construct()
	{
		$this->bid = null;
		$this->prevBooking = null;
		$this->dbo = JFactory::getDbo();
		$this->typesMap = $this->getTypesMap();
	}

	/**
	 * Returns an array of types mapped to
	 * the corresponding language definition.
	 * All the history types should be listed here.
	 *
	 * @return 	array
	 */
	public function getTypesMap()
	{
		return array(
			//New booking with status Confirmed
			'NC' => JText::_('VBOBOOKHISTORYTNC'),
			//Booking modified from website
			'MW' => JText::_('VBOBOOKHISTORYTMW'),
			//Booking modified from back-end
			'MB' => JText::_('VBOBOOKHISTORYTMB'),
			//New booking from back-end
			'NB' => JText::_('VBOBOOKHISTORYTNB'),
			//New booking with status Pending
			'NP' => JText::_('VBOBOOKHISTORYTNP'),
			//Booking paid for the first time
			'P0' => JText::_('VBOBOOKHISTORYTP0'),
			//Booking paid for a second time
			'PN' => JText::_('VBOBOOKHISTORYTPN'),
			//Cancellation request message
			'CR' => JText::_('VBOBOOKHISTORYTCR'),
			//Booking cancelled via front-end website
			'CW' => JText::_('VBOBOOKHISTORYTCW'),
			//Booking auto cancelled via front-end
			'CA' => JText::_('VBOBOOKHISTORYTCA'),
			//Booking cancelled via back-end by admin
			'CB' => JText::_('VBOBOOKHISTORYTCB'),
			//Booking Receipt generated via back-end
			'BR' => JText::_('VBOBOOKHISTORYTBR'),
			//Booking Invoice generated
			'BI' => JText::_('VBOBOOKHISTORYTBI'),
			//Booking registration unset status by admin
			'RA' => JText::_('VBOBOOKHISTORYTRA'),
			//Booking checked-in status set by admin
			'RB' => JText::_('VBOBOOKHISTORYTRB'),
			//Booking checked-out status set by admin
			'RC' => JText::_('VBOBOOKHISTORYTRC'),
			//Booking no-show status set by admin
			'RZ' => JText::_('VBOBOOKHISTORYTRZ'),
			//Booking set to Confirmed by admin
			'TC' => JText::_('VBOBOOKHISTORYTTC'),
			//Booking set to Confirmed via App
			'AC' => JText::_('VBOBOOKHISTORYTAC'),
			//Booking modified from channel
			'MC' => JText::_('VBOBOOKHISTORYTMC'),
			//Booking cancelled from channel
			'CC' => JText::_('VBOBOOKHISTORYTCC'),
			//Booking removed via App
			'AR' => JText::_('VBOBOOKHISTORYTAR'),
			//Booking modified via App
			'AM' => JText::_('VBOBOOKHISTORYTAM'),
			//New booking via App
			'AN' => JText::_('VBOBOOKHISTORYTAN'),
			//New Booking from OTA
			'NO' => JText::_('VBOBOOKHISTORYTNO'),
			//Report affecting the booking
			'RP' => JText::_('VBOBOOKHISTORYTRP'),
			//Custom email sent to the customer by admin
			'CE' => JText::_('VBOBOOKHISTORYTCE'),
			//Custom SMS sent to the customer by admin
			'CS' => JText::_('VBOBOOKHISTORYTCS')
		);
	}

	/**
	 * Sets the current booking ID.
	 * 
	 * @param 	int 	$bid
	 *
	 * @return 	self
	 **/
	public function setBid($bid)
	{
		$this->bid = (int)$bid;

		return $this;
	}

	/**
	 * Sets the previous booking array.
	 * To calculate what has changed in the booking after the
	 * modification, VBO uses the method getLogBookingModification().
	 * VCM instead should use this method to tell the class that
	 * what has changed should be calculated to obtain the 'descr'
	 * text of the history record that will be stored.
	 * 
	 * @param 	array 	$booking
	 *
	 * @return 	self
	 **/
	public function setPrevBooking($booking)
	{
		if (is_array($booking)) {
			$this->prevBooking = $booking;
		}

		return $this;
	}

	/**
	 * Checks whether the type for the history record is valid.
	 *
	 * @param 	string 		$type
	 * @param 	[bool] 		$returnit 	if true, the translated value is returned. Otherwise boolean is returned
	 *
	 * @return 	boolean
	 */
	public function validType($type, $returnit = false)
	{
		if ($returnit) {
			return isset($this->typesMap[strtoupper($type)]) ? $this->typesMap[strtoupper($type)] : $type;
		}

		return isset($this->typesMap[strtoupper($type)]);
	}

	/**
	 * Reads the booking record.
	 * Returns false in case of failure.
	 *
	 * @param 	mixed 	
	 *
	 * @return 	array
	 */
	private function getBookingInfo()
	{
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$this->bid.";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() < 1) {
			return false;
		}
		return $this->dbo->loadAssoc();
	}

	/**
	 * Stores a new history record for the booking.
	 * 
	 * @param 	string 		$type 	the char-type of store we are making for the history
	 * @param 	[string] 	$descr 	the description of this booking record (optional)
	 *
	 * @return 	boolean
	 **/
	public function store($type, $descr = '')
	{
		if (is_null($this->bid) || !$this->validType($type)) {
			return false;
		}

		if (!$booking_info = $this->getBookingInfo()) {
			return false;
		}

		if (empty($descr) && is_array($this->prevBooking)) {
			//VCM (including the App) could set the previous booking information, so we need to calculate what has changed with the booking
			//load VBO language
			$lang = JFactory::getLanguage();
			$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $lang->getTag(), true);
			if (!class_exists('VikBooking')) {
				require_once(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikbooking.php');
			}
			$descr = VikBooking::getLogBookingModification($this->prevBooking);
		}
		
		$q = "INSERT INTO `#__vikbooking_orderhistory` (`idorder`, `dt`, `type`, `descr`, `totpaid`, `total`) VALUES (".$this->bid.", ".$this->dbo->quote(JFactory::getDate()->toSql(true)).", ".$this->dbo->quote($type).", ".(empty($descr) ? "NULL" : $this->dbo->quote($descr)).", ".(float)$booking_info['totpaid'].", ".(float)$booking_info['total'].");";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		$rid = $this->dbo->insertid();

		return ((int)$rid > 0);
	}

	/**
	 * Loads all the history records for this booking
	 *
	 * @return 	array
	 */
	public function loadHistory()
	{
		$history = array();

		if (empty($this->bid)) {
			return $history;
		}

		$q = "SELECT * FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$this->bid." ORDER BY `dt` DESC;";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows() > 0) {
			$history = $this->dbo->loadAssocList();
		}

		return $history;
	}

}
