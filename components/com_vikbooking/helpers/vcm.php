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

class VboVcmInvoker {
	
	public $oids;
	public $sync_type;
	public $orig_booking;
	private $error;
	private $result;
	
	public function __construct() {
		$this->oids = array();
		$this->sync_type = 'new';
		$this->orig_booking = '';
		if (!class_exists('synchVikBooking')) {
			require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php");
		}
		$this->error = '';
		$this->result = false;
	}

	/**
	* Sets the booking IDs for the sync
	* @param oids
	*/
	public function setOids($oids) {
		$this->oids = $oids;
		return $this;
	}
	
	/**
	* Sets the type of synchronization for VCM
	* @param set_sync_type
	*/
	public function setSyncType($set_sync_type) {
		$this->sync_type = !in_array($set_sync_type, array('new', 'modify', 'cancel')) ? 'new' : $set_sync_type;
		return $this;
	}

	/**
	* Sets the original booking array
	* @param obooking (mixed string/array)
	* @param decode (bool)
	*/
	public function setOriginalBooking($obooking, $decode = false) {
		if (!empty($obooking)) {
			$original_booking = $decode === true ? json_decode(urldecode($obooking), true) : $obooking;
			if (is_array($original_booking) && @count($original_booking) > 0) {
				$this->orig_booking = $original_booking;
			}
		}
		return $this;
	}

	/**
	* Launch the synchronization with VCM
	*/
	public function doSync() {
		if (!is_array($this->oids) || !(count($this->oids) > 0)) {
			$this->setError('oids is empty.');
			return $this->result;
		}
		if ($this->sync_type == 'new') {
			foreach ($this->oids as $oid) {
				if (!empty($oid)) {
					$vcm = new synchVikBooking($oid);
					$vcm->setSkipCheckAutoSync();
					$rq_rs = $vcm->sendRequest();
					$this->result = $this->result || $rq_rs ? true : $this->result;
				}
			}
		} elseif ($this->sync_type == 'modify') {
			//only one Booking ID per request as the original booking is transmitted in JSON format or as an array if called via PHP execution.
			if (is_array($this->orig_booking) && @count($this->orig_booking) > 0) {
				foreach ($this->oids as $oid) {
					if (!empty($oid)) {
						$vcm = new synchVikBooking($oid);
						$vcm->setSkipCheckAutoSync();
						$vcm->setFromModification($this->orig_booking);
						$this->result = $vcm->sendRequest();
						break;
					}
				}
			} else {
				$this->setError('orig_booking is empty.');
			}
		} elseif ($this->sync_type == 'cancel') {
			foreach ($this->oids as $oid) {
				if (!empty($oid)) {
					$vcm = new synchVikBooking($oid);
					$vcm->setSkipCheckAutoSync();
					$vcm->setFromCancellation(array('id' => $oid));
					$rq_rs = $vcm->sendRequest();
					$this->result = $this->result || $rq_rs ? true : $this->result;
				}
			}
		}
		if ($this->result !== true && !(strlen($this->getError()) > 0)) {
			$this->setError('VCM returned errors');
		}
		return $this->result;
	}

	/**
	* Sets the class error variable
	* @param err_str
	*/
	private function setError($err_str) {
		$this->error .= $err_str;
	}

	/**
	* Returns the class error variable
	*/
	public function getError() {
		return $this->error;
	}
	
}
