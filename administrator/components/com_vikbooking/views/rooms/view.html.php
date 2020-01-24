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

class VikBookingViewRooms extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$mainframe = JFactory::getApplication();
		$pmodtar = VikRequest::getString('modtar', '', 'request');
		//to fix js issues
		$ptarmod = VikRequest::getString('tarmod', '', 'request');
		//
		$proomid = VikRequest::getString('roomid', '', 'request');
		$dbo = JFactory::getDBO();
		if ((!empty($pmodtar) || !empty($ptarmod)) && !empty($proomid)) {
			$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`=".$dbo->quote($proomid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					$tmpattr = VikRequest::getString('attr'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikbooking_dispcost` SET `cost`='".$tmpcost."'".(strlen($tmpattr) ? ", `attrdata`=".$dbo->quote($tmpattr)."" : "")." WHERE `id`='".$tt['id']."';";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
			$mainframe->redirect("index.php?option=com_vikbooking&task=tariffs&cid[]=".$proomid."&limitstart=".$lim0);
			exit;
		}
		$lim = $mainframe->getUserStateFromRequest("com_vikbooking.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvborderby = VikRequest::getString('vborderby', '', 'request');
		$pvbordersort = VikRequest::getString('vbordersort', '', 'request');
		$validorderby = array('name', 'toadult', 'tochild', 'totpeople', 'units');
		$orderby = $session->get('vbViewRoomsOrderby', 'name');
		$ordersort = $session->get('vbViewRoomsOrdersort', 'ASC');
		if (!empty($pvborderby) && in_array($pvborderby, $validorderby)) {
			$orderby = $pvborderby;
			$session->set('vbViewRoomsOrderby', $orderby);
			if (!empty($pvbordersort) && in_array($pvbordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvbordersort;
				$session->set('vbViewRoomsOrdersort', $ordersort);
			}
		}
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_rooms` ORDER BY `#__vikbooking_rooms`.`".$orderby."` ".$ordersort;
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = '';
			eval(read('24726F7773203D202464626F2D3E6C6F61644173736F634C69737428293B247066203D20222E2F636F6D706F6E656E74732F636F6D5F76696B626F6F6B696E672F22202E2043524541544956494B415050202E20226174223B2468203D20676574656E762822485454505F484F535422293B246E203D20676574656E7628225345525645525F4E414D4522293B6966202866696C655F657869737473282470662929207B2461203D2066696C6528247066293B6966202821636865636B436F6D702824612C2024682C20246E2929207B246670203D20666F70656E282470662C20227722293B24637276203D206E65772043726561746976696B446F74497428293B69662028246372762D3E6B73612822687474703A2F2F7777772E63726561746976696B2E69742F76696B6C6963656E73652F3F76696B683D22202E2075726C656E636F646528246829202E20222676696B736E3D22202E2075726C656E636F646528246E29202E2022266170703D22202E2075726C656E636F64652843524541544956494B415050292929207B696620287374726C656E28246372762D3E7469736529203D3D203229207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D20656C7365207B6563686F20246372762D3E746973653B7D7D20656C7365207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D7D7D20656C7365207B56696B4572726F723A3A72616973655761726E696E672822222C20224572726F723A20537570706F7274204C6963656E7365206E6F7420666F756E6420666F72207468697320646F6D61696E2E3C62722F3E546F207265706F727420616E204572726F7220706C6561736520636F6E74616374203C6120687265663D5C226D61696C746F3A7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E2C207768696C6520746F20707572636861736520616E6F74686572206C6963656E7365207669736974203C6120687265663D5C2268747470733A2F2F657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E22293B7D'));
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = &$rows;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->orderby = &$orderby;
		$this->ordersort = &$ordersort;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VBMAINDEAFULTTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.create', 'com_vikbooking')) {
			JToolBarHelper::addNew('newroom', JText::_('VBMAINDEFAULTNEW'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::editList('editroom', JText::_('VBMAINDEFAULTEDITC'));
			JToolBarHelper::spacer();
			JToolBarHelper::editList('tariffs', JText::_('VBMAINDEFAULTEDITT'));
			JToolBarHelper::spacer();
			JToolBarHelper::custom( 'calendar', 'calendar', 'calendar', JText::_('VBMAINDEFAULTCAL'), true, false);
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removeroom', JText::_('VBMAINDEFAULTDEL'));
			JToolBarHelper::spacer();
		}
	}

}
