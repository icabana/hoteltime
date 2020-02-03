<?php  
/**------------------------------------------------------------------------
 * mod_VikContentSlider
 * ------------------------------------------------------------------------
 * author    Valentina Arras - Extensionsforjoomla.com
 * copyright Copyright (C) 2014 extensionsforjoomla.com. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.extensionsforjoomla.com
 * Technical Support:  templates@extensionsforjoomla.com
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die('Restricted Area');

//Joomla 3.0
if(!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
//


$moduleName = 'mod_vikcontentslider';
//$moduleID = $module->id;
$document = JFactory::getDocument();

require(JModuleHelper::getLayoutPath($moduleName));

?>