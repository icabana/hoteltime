<?php
/**
 * Copyright (c) Extensionsforjoomla.com - E4J - Alessio <tech@e4j.com>
 * 
 * You should have received a copy of the License
 * along with this program.  If not, see <https://e4j.com/>.
 * 
 * For any bug, error please contact <tech@e4j.com>
 * We will try to fix it.
 * 
 * Extensionsforjoomla.com - All Rights Reserved
 * 
 */

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldHeader extends JFormField
{
	protected $type = 'Header';

	function getInput()
	{
		return $this->fetchElement($this->element['name'], $this->value, $this->element, $this->name);
	}
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array(JText::_($value));
		foreach ($node->children() as $option)
		{
			$options[] = $option->data();
		}
		
		return sprintf('<div style="float: left; width: 100%%; font-weight: bold; font-size: 120%%; text-transform:uppercase; border-bottom:2px solid #999900; color: #999900; padding: 5px 0; text-align: left;margin-bottom:10px;">%s</div>', call_user_func_array('sprintf', $options));
	}
}
?>