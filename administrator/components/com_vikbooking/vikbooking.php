<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted access');

/* Portability and Adapters */
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "request.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_vikbooking" . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "error.php");

/* A high level of error reporting may disturb the channel manager responses, so we shut it up */
$er_l = VikRequest::getString('error_reporting');
$er_l = strlen($er_l) && intval($er_l == '-1') ? -1 : 0;
defined('VIKBOOKING_ERROR_REPORTING') OR define('VIKBOOKING_ERROR_REPORTING', $er_l);
error_reporting(VIKBOOKING_ERROR_REPORTING);

/* ACL */
if (!JFactory::getUser()->authorise('core.manage', 'com_vikbooking')) {
	return VikError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

/* Main libraries */
require_once(VBO_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikbooking.php");
require_once(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "helper.php");
require_once(VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "jv_helper.php");

/* Load assets for CSS and JS */
VikBooking::loadFontAwesome(true);
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_ADMIN_URI . 'vikbooking.css', array('version' => E4J_SOFTWARE_VERSION));
$document->addStyleSheet(VBO_ADMIN_URI . 'resources/fonts/vboicomoon.css');
$document->addStyleSheet(VBO_ADMIN_URI . 'resources/vikbooking_backendcustom.css');
JHtml::_('jquery.framework', true, true);
if (VikBooking::loadJquery()) {
	JHtml::_('script', VBO_SITE_URI . 'resources/jquery-1.12.4.min.js', false, true, false, false);
}
VikBooking::getVboApplication()->normalizeBackendStyles();

/* Framework Rendering */
jimport('joomla.application.component.controller');
$controller = JControllerVikBooking::getInstance('VikBooking');
$controller->execute(VikRequest::getCmd('task'));
$controller->redirect();
