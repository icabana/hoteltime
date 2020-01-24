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

class VikBookingViewConfig extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$dbo = JFactory::getDBO();
		$preset_tags = VikRequest::getInt('reset_tags', '', 'request');
		if ($preset_tags > 0) {
			$q = "UPDATE `#__vikbooking_orders` SET `colortag`=NULL;";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "UPDATE `#__vikbooking_config` SET `setting`='' WHERE `param`='bookingsctags';";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=config");
			exit;
		}

		$cookie = JFactory::getApplication()->input->cookie;
		$curtabid = $cookie->get('vbConfPt', '', 'string');
		$curtabid = empty($curtabid) ? 1 : (int)$curtabid;

		$this->curtabid = &$curtabid;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINCONFIGTITLE'), 'vikbookingconfig');
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::apply( 'saveconfig', JText::_('VBSAVE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VBANNULLA'));
		JToolBarHelper::spacer();
	}

}
