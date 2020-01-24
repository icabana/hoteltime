<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

/* URI Constants for admin and site sections (with trailing slash) */
defined('VBO_ADMIN_URI') or define('VBO_ADMIN_URI', JUri::root().'administrator/components/com_vikbooking/');
defined('VBO_SITE_URI') or define('VBO_SITE_URI', JUri::root().'components/com_vikbooking/');
defined('VBO_ADMIN_URI_REL') or define('VBO_ADMIN_URI_REL', './administrator/components/com_vikbooking/');
defined('VBO_SITE_URI_REL') or define('VBO_SITE_URI_REL', './components/com_vikbooking/');
defined('VCM_ADMIN_URI') or define('VCM_ADMIN_URI', JUri::root().'administrator/components/com_vikchannelmanager/');
defined('VCM_SITE_URI') or define('VCM_SITE_URI', JUri::root().'components/com_vikchannelmanager/');

/* Path Constants for admin and site sections (with NO trailing directory separator) */
defined('VBO_ADMIN_PATH') or define('VBO_ADMIN_PATH', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikbooking');
defined('VBO_SITE_PATH') or define('VBO_SITE_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikbooking');
defined('VCM_ADMIN_PATH') or define('VCM_ADMIN_PATH', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikchannelmanager');
defined('VCM_SITE_PATH') or define('VCM_SITE_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikchannelmanager');

/* Other Constants that may not be available in the framework */
defined('JPATH_COMPONENT_SITE') or define('JPATH_COMPONENT_SITE', JPATH_SITE . DIRECTORY_SEPARATOR . 'com_vikbooking');
defined('JPATH_COMPONENT_ADMINISTRATOR') or define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'com_vikbooking');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/* Adapter for Controller and View Classes for compatiblity with the various frameworks */
if (!class_exists('JViewVikBooking') && class_exists('JViewLegacy')) {

	class JViewVikBooking extends JViewLegacy {
		/* adapter for JViewLegacy */
	}

	class JControllerVikBooking extends JControllerLegacy {
		/* adapter for JControllerLegacy */
	}

} elseif (!class_exists('JViewVikBooking') && class_exists('JView')) {

	class JViewVikBooking extends JView {
		/* adapter for JView */
	}

	class JControllerVikBooking extends JController {
		/* adapter for JController */
	}

}
