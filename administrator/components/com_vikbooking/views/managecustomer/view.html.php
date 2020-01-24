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

class VikBookingViewManagecustomer extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$idcust = $cid[0];
		}

		$customer = array();
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `id`=".(int)$idcust.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
				exit;
			}
		}
		$q = "SELECT * FROM `#__vikbooking_countries` ORDER BY `#__vikbooking_countries`.`country_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$countries = $dbo->loadAssocList();
		$wselcountries = '<select name="country"><option value="">----</option>'."\n";
		foreach ($countries as $key => $val) {
			$wselcountries .= '<option value="'.$val['country_3_code'].'"'.(count($customer) && $val['country_3_code'] == $customer['country'] ? ' selected="selected"' : '').'>'.$val['country_name'].'</option>'."\n";
		}
		$wselcountries .= '</select>';
		
		$this->customer = &$customer;
		$this->wselcountries = &$wselcountries;
		
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
			JToolBarHelper::title(JText::_('VBMAINMANAGECUSTOMERTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
				JToolBarHelper::apply( 'updatecustomerstay', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
				JToolBarHelper::save( 'updatecustomer', JText::_('VBSAVECLOSE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomer', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VBMAINMANAGECUSTOMERTITLE'), 'vikbooking');
			if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
				JToolBarHelper::save('savecustomer', JText::_('VBSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcustomer', JText::_('VBBACK'));
			JToolBarHelper::spacer();
		}
	}

}
