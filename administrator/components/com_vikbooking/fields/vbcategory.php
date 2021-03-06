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

jimport('joomla.form.formfield');

class JFormFieldVbcategory extends JFormField { 
	protected $type = 'vbcategory';
	
	function getInput() {
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$categories="";
		$dbo = JFactory::getDBO();
		$q="SELECT * FROM `#__vikbooking_categories` ORDER BY `#__vikbooking_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allvbc=$dbo->loadAssocList();
			foreach($allvbc as $vbc) {
				$categories.='<option value="'.$vbc['id'].'"'.($this->value == $vbc['id'] ? " selected=\"selected\"" : "").'>'.$vbc['name'].'</option>';
			}
		}
		$html = '<select class="inputbox" name="' . $this->name . '" >';
		$html .= '<option value=""></option>';
		$html .= $categories;
		$html .='</select>';
		return $html;
    }
}


?>
