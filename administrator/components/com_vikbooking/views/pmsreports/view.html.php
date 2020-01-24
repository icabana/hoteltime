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

class VikBookingViewPmsreports extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$report_objs = array();
		
		$report_base = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR;
		require_once $report_base . 'report.php';
		$report_files = glob($report_base.'*.php');
		
		foreach ($report_files as $k => $report_path) {
			$report_file = str_replace($report_base, '', $report_path);
			if ($report_file == 'report.php') {
				unset($report_files[$k]);
				continue;
			}
			require_once $report_path;
			$classname = 'VikBookingReport'.str_replace(' ', '', ucwords(str_replace('.php', '', str_replace('_', ' ', $report_file))));
			if (!class_exists($classname)) {
				unset($report_files[$k]);
				continue;
			}
			if ($report_file == 'revenue.php' && count($report_objs)) {
				//make the "revenue.php" the first element of the list
				array_unshift($report_objs, new $classname);
			} else {
				array_push($report_objs, new $classname);
			}
		}
		
		$this->report_objs = &$report_objs;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINPMSREPORTSTITLE'), 'vikbooking');
	}

}
