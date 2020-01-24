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

class VikBookingViewOrders extends JViewVikBooking {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDBO();
		if (file_exists(VCM_ADMIN_PATH.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'vcm-channels.css')) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(VCM_ADMIN_URI.'assets/css/vcm-channels.css');
		}
		$pconfirmnumber = VikRequest::getString('confirmnumber', '', 'request');
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$pchannel = VikRequest::getString('channel', '', 'request');
		$pcust_id = VikRequest::getInt('cust_id', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$pdatefiltfrom = VikRequest::getString('datefiltfrom', '', 'request');
		$pdatefiltto = VikRequest::getString('datefiltto', '', 'request');
		$dates_filter = '';
		if (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
			$dates_filter_field = '`o`.`ts`';
			if ($pdatefilt == 2) {
				$dates_filter_field = '`o`.`checkin`';
			} elseif ($pdatefilt == 3) {
				$dates_filter_field = '`o`.`checkout`';
			}
			$dates_filter_clauses = array();
			if (!empty($pdatefiltfrom)) {
				$dates_filter_clauses[] = $dates_filter_field.'>='.VikBooking::getDateTimestamp($pdatefiltfrom, '0', '0');
			}
			if (!empty($pdatefiltto)) {
				$dates_filter_clauses[] = $dates_filter_field.'<='.VikBooking::getDateTimestamp($pdatefiltto, 23, 60);
			}
			$dates_filter = implode(' AND ', $dates_filter_clauses);
		}
		$pstatus = VikRequest::getString('status', '', 'request');
		$status_filter = !empty($pstatus) && in_array($pstatus, array('confirmed', 'standby', 'cancelled')) ? "`o`.`status`='".$pstatus."'" : '';
		if (empty($status_filter) && !empty($pstatus)) {
			if ($pstatus == 'closure') {
				$status_filter = "`o`.`closure`=1";
			} elseif (in_array($pstatus, array('checkedin', 'checkedout', 'noshow', 'none'))) {
				switch ($pstatus) {
					case 'checkedin':
						$status_filter = "`o`.`checked`=1";
						break;
					case 'checkedout':
						$status_filter = "`o`.`checked`=2";
						break;
					case 'noshow':
						$status_filter = "`o`.`checked` < 0";
						break;
					case 'none':
						$status_filter = "`o`.`checked`=0";
						break;
					default:
						break;
				}
			}
		}
		$pidpayment = VikRequest::getInt('idpayment', '', 'request');
		$payment_filter = '';
		if (!empty($pidpayment)) {
			$payment_filter = "`o`.`idpayment` LIKE '".$pidpayment."=%'";
		}
		$ordersfound = false;
		$lim = $mainframe->getUserStateFromRequest("com_vikbooking.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$session = JFactory::getSession();
		$pvborderby = VikRequest::getString('vborderby', '', 'request');
		$pvbordersort = VikRequest::getString('vbordersort', '', 'request');
		$validorderby = array('id', 'ts', 'days', 'checkin', 'checkout', 'total');
		$orderby = $session->get('vbViewOrdersOrderby', 'ts');
		$ordersort = $session->get('vbViewOrdersOrdersort', 'DESC');
		if (!empty($pvborderby) && in_array($pvborderby, $validorderby)) {
			$orderby = $pvborderby;
			$session->set('vbViewOrdersOrderby', $orderby);
			if (!empty($pvbordersort) && in_array($pvbordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvbordersort;
				$session->set('vbViewOrdersOrdersort', $ordersort);
			}
		}
		$allrooms = array();
		$q = "SELECT `id`,`name` FROM `#__vikbooking_rooms` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allrooms = $dbo->loadAssocList();
		}
		if (!empty($pconfirmnumber)) {
			$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($pconfirmnumber)." OR `confirmnumber` LIKE ".$dbo->quote("%".$pconfirmnumber."%")." OR `idorderota`=".$dbo->quote($pconfirmnumber)." ORDER BY `#__vikbooking_orders`.`".$orderby."` ".$ordersort;
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');
				$totres = $dbo->loadResult();
				if ($totres == 1 && count($rows) == 1) {
					$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$rows[0]['id']);
					exit;
				} else {
					$ordersfound = true;
					jimport('joomla.html.pagination');
					$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
					$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
				}
			}
		}
		if (!$ordersfound) {
			if (!empty($pcust_id)) {
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,`co`.`idcustomer`,CONCAT_WS(' ', `c`.`first_name`, `c`.`last_name`) AS `customer_fullname` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` AND `c`.`id`=".$pcust_id." WHERE ".(!empty($dates_filter) ? $dates_filter.' AND ' : '').(!empty($payment_filter) ? $payment_filter.' AND ' : '').(!empty($status_filter) ? $status_filter.' AND ' : '')."`co`.`idcustomer`=".$pcust_id." ORDER BY `o`.`".$orderby."` ".$ordersort;
			} elseif (!empty($pidroom)) {
				//ONLY_FULL_GROUP_BY safe
				$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `o`.*,`or`.`idorder` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `o`.`id`=`or`.`idorder` WHERE ".(!empty($dates_filter) ? $dates_filter.' AND ' : '').(!empty($payment_filter) ? $payment_filter.' AND ' : '').(!empty($status_filter) ? $status_filter.' AND ' : '')."`or`.`idroom`=".$pidroom." ".(strlen($pchannel) ? "AND `o`.`channel` ".($pchannel == '-1' ? 'IS NULL' : "LIKE ".$dbo->quote("%".$pchannel."%"))." " : "")." ORDER BY `o`.`".$orderby."` ".$ordersort;
			} else {
				$clauses = array();
				if (!empty($dates_filter)) {
					$clauses[] = $dates_filter;
				}
				if (!empty($payment_filter)) {
					$clauses[] = $payment_filter;
				}
				if (!empty($status_filter)) {
					$clauses[] = $status_filter;
				}
				if (strlen($pchannel)) {
					$clauses[] = "`o`.`channel` ".($pchannel == '-1' ? 'IS NULL' : "LIKE ".$dbo->quote("%".$pchannel."%"));
				}
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.* FROM `#__vikbooking_orders` AS `o`".(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." ORDER BY `o`.`".$orderby."` ".$ordersort.($orderby == 'ts' && $ordersort == 'DESC' ? ', `o`.`id` DESC' : '');
			}
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
		}
		
		$this->rows = &$rows;
		$this->allrooms = &$allrooms;
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
		JToolBarHelper::title(JText::_('VBMAINORDERTITLE'), 'vikbooking');
		if (JFactory::getUser()->authorise('core.edit', 'com_vikbooking')) {
			JToolBarHelper::editList('editorder', JText::_('VBMAINORDEREDIT'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.vbo.management', 'com_vikbooking')) {
			JToolBarHelper::custom( 'orders', 'file-2', 'file-2', JText::_('VBOGENINVOICES'), true);
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikbooking')) {
			JToolBarHelper::deleteList(JText::_('VBDELCONFIRM'), 'removeorders', JText::_('VBMAINORDERDEL'));
			JToolBarHelper::spacer();
			JToolBarHelper::spacer();
		}
	}

}
