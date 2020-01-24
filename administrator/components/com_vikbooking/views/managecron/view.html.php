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

class VikBookingViewManagecron extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));

		$dbo = JFactory::getDBO();
		$row = array();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$cid[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$row = $dbo->loadAssoc();
			}
		}
		$allf = glob(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.'*.php');
		if (!(count($allf) > 0) || (!empty($cid[0]) && !(count($row) > 0))) {
			$mainframe = JFactory::getApplication();
			VikError::raiseWarning('', 'No class files for creating a cron.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=crons");
			exit;
		}
		
		$this->row = &$row;
		$this->allf = &$allf;
		
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
			JToolBarHelper::title(JText::_('VBMAINCRONSTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updatecronstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatecron', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcrons', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINCRONSTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::apply( 'createcronstay', JText::_('VBSAVE'));
				JToolBarHelper::save('createcron', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcrons', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}

}
