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

class VikBookingViewTranslations extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$vbo_tn = VikBooking::getTranslator();
		
		$this->vbo_tn = &$vbo_tn;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINTRANSLATIONSTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking') || JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::apply( 'savetranslationstay', JText::_('VBSAVE'));
			JToolBarHelper::spacer();
			JToolBarHelper::save( 'savetranslation', JText::_('VBSAVECLOSE'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::cancel( 'cancel', JText::_('VBBACK'));
		JToolBarHelper::spacer();
	}

}
