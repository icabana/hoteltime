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

// import Joomla view library
jimport('joomla.application.component.view');

class VikBookingViewManagecoupon extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$coupid = $cid[0];
		}

		$dbo = JFactory::getDBO();
		$coupon = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `id`=".(int)$coupid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$coupon = $dbo->loadAssoc();
			}
		}
		$wselrooms = "";
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rooms = $dbo->loadAssocList();
			$filterroomr = array();
			if (count($coupon) && strlen($coupon['idrooms']) > 0) {
				$cparts = explode(";", $coupon['idrooms']);
				foreach ($cparts as $fc) {
					if (!empty($fc)) {
						$filterroomr[] = $fc;
					}
				}
			}
			$wselrooms = "<select name=\"idrooms[]\" multiple=\"multiple\" size=\"5\">\n";
			foreach ($rooms as $c) {
				$wselrooms .= "<option value=\"".$c['id']."\"".(in_array($c['id'], $filterroomr) ? " selected=\"selected\"" : "").">".$c['name']."</option>\n";
			}
			$wselrooms .= "</select>\n";
		}
		
		$this->coupon = &$coupon;
		$this->wselrooms = &$wselrooms;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VBMAINCOUPONTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::save( 'updatecoupon', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcoupon', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINCOUPONTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save( 'createcoupon', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcoupon', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
