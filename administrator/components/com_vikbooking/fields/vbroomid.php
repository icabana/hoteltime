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

class JFormFieldVbroomid extends JFormField { 
	protected $type = 'vbroomid';
	
	function getInput() {
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$rooms="";
		$dbo = JFactory::getDBO();
		$q="SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allvbr=$dbo->loadAssocList();
			foreach($allvbr as $vbr) {
				$rooms.='<option value="'.$vbr['id'].'"'.($this->value == $vbr['id'] ? " selected=\"selected\"" : "").'>'.$vbr['name'].'</option>';
			}
		}
		$html = '<select class="inputbox" name="' . $this->name . '" >';
		$html .= '<option value=""></option>';
		$html .= $rooms;
		$html .='</select>';
		return $html;
	}
}


?>
