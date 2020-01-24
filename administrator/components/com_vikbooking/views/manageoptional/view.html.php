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

class VikBookingViewManageoptional extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$tot_rooms = 0;
		$tot_rooms_options = 0;
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
				exit;
			}
			$row = $dbo->loadAssoc();
			$q = "SELECT COUNT(*) FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			$tot_rooms = (int)$dbo->loadResult();
			$q = "SELECT `idopt` FROM `#__vikbooking_rooms` WHERE `idopt` LIKE ".$dbo->quote("%".$row['id'].";%").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$all_opt = $dbo->loadAssocList();
				foreach ($all_opt as $k => $v) {
					$opt_parts = explode(';', $v['idopt']);
					if (in_array((string)$row['id'], $opt_parts)) {
						$tot_rooms_options++;
					}
				}
			}
		}
		
		$this->row = &$row;
		$this->tot_rooms = &$tot_rooms;
		$this->tot_rooms_options = &$tot_rooms_options;
		
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
			JToolBarHelper::title(JText::_('VBMAINOPTTITLEEDIT'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::save( 'updateoptional', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloptionals', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINOPTTITLENEW'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save( 'createoptionals', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'canceloptionals', JText::_('VBANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
