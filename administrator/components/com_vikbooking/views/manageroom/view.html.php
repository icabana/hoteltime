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

class VikBookingViewManageroom extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$adultsdiff = "";
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$row = $dbo->loadAssoc();
			} else {
				VikError::raiseWarning('', 'Room not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			}
		}
		
		$q = "SELECT * FROM `#__vikbooking_categories`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$cats = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$q = "SELECT * FROM `#__vikbooking_characteristics`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$carats = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$optionals = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_adultsdiff` WHERE `idroom`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			$adultsdiff = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
		}
		
		$this->row = &$row;
		$this->cats = &$cats;
		$this->carats = &$carats;
		$this->optionals = &$optionals;
		$this->adultsdiff = &$adultsdiff;
		
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
			JToolBarHelper::title(JText::_('VBMAINROOMTITLEEDIT'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updateroomstay', JText::_('VBSAVE'));
				JToolBarHelper::save( 'updateroom', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancel', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINROOMTITLENEW'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save( 'createroom', JText::_('VBSAVECLOSE'));
				JToolBarHelper::apply( 'createroomstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancel', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
