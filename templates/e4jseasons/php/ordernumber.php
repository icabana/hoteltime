<?php
/**
 * Copyright (c) Extensionsforjoomla.com - E4J - Alessio <tech@extensionsforjoomla.com>
 * 
 * You should have received a copy of the License
 * along with this program.  If not, see <http://www.extensionsforjoomla.com/>.
 * 
 * For any bug, error please contact <tech@extensionsforjoomla.com>
 * We will try to fix it.
 * 
 * Extensionsforjoomla.com - All Rights Reserved
 * 
 */

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldOrdernumber extends JFormField
{
	protected $type = 'ordernumber';

	function getInput()
	{

		$dbo = JFactory::getDbo();

		// load the update site record, if it exists
		$q = $dbo->getQuery(true);


		$q->select($dbo->qn('extension_id'))
			->from($dbo->qn('#__extensions'))
			->where($dbo->qn('element') .' = '.$dbo->q(str_replace('/php', '', str_replace(JPATH_SITE . '/templates/', '', dirname(__FILE__)))));

		//exit($q);

		$dbo->setQuery($q);
		$dbo->execute();

		$extension_id = $dbo->loadResult();

		// load the update site record, if it exists
		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('update_site_id'))
			->from($dbo->qn('#__update_sites_extensions'))
			->where($dbo->qn('extension_id').' = '.$dbo->q($extension_id));
		$dbo->setQuery($q);
		$dbo->execute();

		$updateSite = $dbo->loadResult();

		if ($updateSite) {
			// update the update site record
			$q = $dbo->getQuery(true);

			$q->update($dbo->qn('#__update_sites'))
				->set($dbo->qn('extra_query') . ' = ' . $dbo->q('ordnum=' . $this->value . '&wburi=' . JUri::root()))
				->set($dbo->qn('enabled') . ' = 1')
				->set($dbo->qn('last_check_timestamp') . ' = 0')
				->where($dbo->qn('update_site_id') .' = '.$dbo->q($updateSite));
			$dbo->setQuery($q);
			$dbo->execute();

			// Delete any existing updates (essentially flushes the updates cache for this update site)
			$q = $dbo->getQuery(true);

			$q->delete('#__updates')
				->where('update_site_id = '.$dbo->q($updateSite));
			
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$html = '<span style="font-weight: bold; color: red;">There was an issue fetching update sites. Please rebuild your update sites.</span></br>';
		}

		$html .= '<input type="text" name="' . $this->name . '" value="' . $this->value . '"/>';

		return $html;
	}
}
?>