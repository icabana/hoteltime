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

class VikBookingViewManageseason extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$sid = $cid[0];
		}

		$sdata = array();
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_seasons` WHERE `id`=".$dbo->quote($sid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if (!($dbo->getNumRows() > 0)) {
				$mainframe->redirect("index.php?option=com_vikbooking&task=seasons");
				exit;
			}
			$sdata = $dbo->loadAssoc();
		}
		$split = count($sdata) ? explode(",", $sdata['idrooms']) : array();
		$adults_diff = array();
		$adults_diff_ovr = count($sdata) && !empty($sdata['occupancy_ovr']) ? json_decode($sdata['occupancy_ovr'], true) : array();
		$wsel = "";
		$q = "SELECT `id`,`name`,`fromadult`,`toadult` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wsel .= "<select id=\"idrooms\" name=\"idrooms[]\" multiple=\"multiple\" size=\"5\">\n";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wsel .= "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $split) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
				if ($d['fromadult'] < $d['toadult']) {
					$room_adults_diff = VikBooking::loadRoomAdultsDiff($d['id']);
					for ($i = $d['fromadult']; $i <= $d['toadult']; $i++) { 
						if (array_key_exists($d['id'], $adults_diff_ovr) && array_key_exists($i, $adults_diff_ovr[$d['id']])) {
							$adults_diff_ovr[$d['id']][$i]['override'] = 1;
							$adults_diff[$d['id']][$i] = $adults_diff_ovr[$d['id']][$i];
							continue;
						}
						if (array_key_exists($i, $room_adults_diff)) {
							$adults_diff[$d['id']][$i] = $room_adults_diff[$i];
						} else {
							$adults_diff[$d['id']][$i] = array('chdisc' => 1, 'valpcent' => 1, 'pernight' => 1, 'value' => '');
						}
					}
				} else {
					$adults_diff[$d['id']] = array();
				}
			}
			$wsel .= "</select>\n";
		} else {
			VikError::raiseWarning('', 'No Rooms.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			exit;
		}
		$wpricesel = "";
		$splitprices = count($sdata) ? explode(",", $sdata['idprices']) : array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_prices` ORDER BY `#__vikbooking_prices`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wpricesel .= "<select name=\"idprices[]\" multiple=\"multiple\" size=\"5\">\n";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wpricesel .= "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $splitprices) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
			}
			$wpricesel .= "</select>\n";
		}
		
		$this->sdata = &$sdata;
		$this->wsel = &$wsel;
		$this->wpricesel = &$wpricesel;
		$this->adults_diff = &$adults_diff;
		
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
			JToolBarHelper::title(JText::_('VBMAINSEASONTITLEEDIT'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updateseasonstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updateseason', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
				JToolBarHelper::custom('removeseasons', 'purge', 'purge', JText::_('VBMAINSEASONSDEL'), false, false);
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelseason', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINSEASONTITLENEW'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save( 'createseason', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::custom('createseason_new', 'save-new', 'save-new', JText::_('VBSAVENEW'), false, false);
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelseason', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
