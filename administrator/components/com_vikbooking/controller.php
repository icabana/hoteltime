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

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikBookingController extends JControllerVikBooking {

	/**
	 * Default controller's method when no task is defined,
	 * or no method exists for that task. If a View is requested.
	 * attempts to set it, otherwise sets the default View.
	 */
	function display($cachable = false, $urlparams = array()) {

		$view = VikRequest::getVar('view', '');
		$header_val = '';

		if (!empty($view)) {
			VikRequest::setVar('view', $view);
		} else {
			$header_val = '18';
			VikRequest::setVar('view', 'dashboard');
		}

		VikBookingHelper::printHeader($header_val);
		
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function pmsreports() {
		VikBookingHelper::printHeader("pmsreports");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'pmsreports'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function ratesoverv() {
		VikBookingHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'ratesoverv'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function stats() {
		VikBookingHelper::printHeader("stats");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'stats'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcron() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecron'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcron() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecron'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function crons() {
		VikBookingHelper::printHeader("crons");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'crons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function calc_rates() {
		$response = 'e4j.error.ErrorCode(1) Server is blocking the self-request';
		$currencysymb = VikBooking::getCurrencySymb();
		$vbo_df = VikBooking::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$id_room = VikRequest::getInt('id_room', '', 'request');
		$checkin = VikRequest::getString('checkin', '', 'request');
		$nights = VikRequest::getInt('num_nights', 1, 'request');
		$adults = VikRequest::getInt('num_adults', 0, 'request');
		$children = VikRequest::getInt('num_children', 0, 'request');
		$checkin_ts = strtotime($checkin);
		if (empty($checkin_ts)) {
			$checkin = date('Y-m-d');
			$checkin_ts = strtotime($checkin);
		}
		$is_dst = date('I', $checkin_ts);
		$checkout_ts = $checkin_ts;
		for ($i = 1; $i <= $nights; $i++) { 
			$checkout_ts += 86400;
			$is_now_dst = date('I', $checkout_ts);
			if ($is_dst != $is_now_dst) {
				if ((int)$is_dst == 1) {
					$checkout_ts += 3600;
				} else {
					$checkout_ts -= 3600;
				}
				$is_dst = $is_now_dst;
			}
		}
		$checkout = date('Y-m-d', $checkout_ts);
		if (function_exists('curl_init')) {
			$endpoint = JURI::root().'index.php?option=com_vikbooking&task=tac_av_l';
			$rates_data = 'e4jauth=%s&req_type=hotel_availability&start_date='.$checkin.'&end_date='.$checkout.'&nights='.$nights.'&num_rooms=1&adults[]='.$adults.'&children[]='.$children;
			$ch = curl_init($endpoint);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, sprintf($rates_data, md5('vbo.e4j.vbo')));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
			$res = curl_exec($ch);
			if ($curl_errno = curl_errno($ch)) {
				$response = "e4j.error.curl Error (".curl_errno($ch)."): ".curl_error($ch);
			} else {
				$arr_res = json_decode($res, true);
				if (is_array($arr_res)) {
					if (!array_key_exists('e4j.error', $arr_res)) {
						if (array_key_exists($id_room, $arr_res)) {
							$response = '';
							foreach ($arr_res[$id_room] as $rate) {
								$response .= '<div class="vbo-calcrates-rateblock">';
								$response .= '<span class="vbo-calcrates-ratename">'.$rate['pricename'].'</span>';
								$response .= '<span class="vbo-calcrates-ratenet"><span>'.JText::_('VBCALCRATESNET').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['cost']).'</span>';
								$response .= '<span class="vbo-calcrates-ratetax"><span>'.JText::_('VBCALCRATESTAX').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['taxes']).'</span>';
								if (!empty($rate['city_taxes'])) {
									$response .= '<span class="vbo-calcrates-ratecitytax"><span>'.JText::_('VBCALCRATESCITYTAX').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['city_taxes']).'</span>';
								}
								if (!empty($rate['fees'])) {
									$response .= '<span class="vbo-calcrates-ratefees"><span>'.JText::_('VBCALCRATESFEES').'</span>'.$currencysymb.' '.VikBooking::numberFormat($rate['fees']).'</span>';
								}
								$tot = $rate['cost'] + $rate['taxes'] + $rate['city_taxes'] + $rate['fees'];
								$tot = round($tot, 2);
								$response .= '<span class="vbo-calcrates-ratetotal"><span>'.JText::_('VBCALCRATESTOT').'</span>'.$currencysymb.' '.VikBooking::numberFormat($tot).'</span>';
								if (array_key_exists('affdays', $rate) && $rate['affdays'] > 0) {
									$response .= '<span class="vbo-calcrates-ratespaffdays"><span>'.JText::_('VBCALCRATESSPAFFDAYS').'</span>'.$rate['affdays'].'</span>';
								}
								if (array_key_exists('diffusagediscount', $rate) && count($rate['diffusagediscount']) > 0) {
									foreach ($rate['diffusagediscount'] as $occupancy => $disc) {
										$response .= '<span class="vbo-calcrates-rateoccupancydisc"><span>'.JText::sprintf('VBCALCRATESADUOCCUPANCY', $occupancy).'</span>- '.$currencysymb.' '.VikBooking::numberFormat($disc).'</span>';
										break;
									}
								} elseif (array_key_exists('diffusagecost', $rate) && count($rate['diffusagecost']) > 0) {
									foreach ($rate['diffusagecost'] as $occupancy => $charge) {
										$response .= '<span class="vbo-calcrates-rateoccupancycharge"><span>'.JText::sprintf('VBCALCRATESADUOCCUPANCY', $occupancy).'</span>+ '.$currencysymb.' '.VikBooking::numberFormat($charge).'</span>';
										break;
									}
								}
								$response .= '</div>';
							}
							//Debug
							//$response .= '<br/><pre>'.print_r($arr_res, true).'</pre><br/>';
						} else {
							$response = 'e4j.error.'.JText::sprintf('VBCALCRATESROOMNOTAVAILCOMBO', date($df, $checkin_ts), date($df, $checkout_ts));
						}
					} else {
						$response = 'e4j.error.'.$arr_res['e4j.error'];
					}
				} else {
					$response = (strpos($res, 'e4j.error') === false ? 'e4j.error' : '').$res;
				}
			}
			curl_close($ch);
		}
		//Do not do only echo trim($response); or the currency symbol will not be encoded on some servers
		echo json_encode(array(trim($response)));
		exit;
	}

	function cron_exec() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'cronexec'));
	
		parent::display();
	}

	function downloadcron() {
		$pcron_id = VikRequest::getInt('cron_id', '', 'request');
		$pcron_name = VikRequest::getString('cron_name', '', 'request');
		
		$file_cont = '<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */
$cron_id = "'.$pcron_id.'";
$cron_key = "'.VikBooking::getCronKey().'";
$url = "'.JURI::root().'index.php?option=com_vikbooking&task=cron_exec&tmpl=component";

$fields = array(
	"cron_id" => $cron_id,
	"cronkey" => md5($cron_key),
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 VikBooking/1.10");
$res = curl_exec($ch);
curl_close($ch);

echo $res;';
		
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Length: ".filesize($file_cont).";");
		header("Content-Disposition: attachment; filename=".$pcron_name.".php");
		header("Content-Type: application/php; "); 
		header("Content-Transfer-Encoding: binary");

		echo $file_cont;
		die;
	}

	function cronlogs() {
		$dbo = JFactory::getDBO();
		$pcron_id = VikRequest::getInt('cron_id', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$pcron_id.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$cron_data = $dbo->loadAssoc();
			$cron_data['logs'] = empty($cron_data['logs']) ? '--------' : $cron_data['logs'];
			echo '<pre>'.print_r($cron_data['logs'], true).'</pre>';
		}
	}

	function updatecron() {
		$this->do_updatecron();
	}

	function updatecronstay() {
		$this->do_updatecron(true);
	}

	private function do_updatecron($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhereup = VikRequest::getInt('where', '', 'request');
		$pcron_name = VikRequest::getString('cron_name', '', 'request');
		$pclass_file = VikRequest::getString('class_file', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$ppublished = intval($ppublished) == 1 ? 1 : 0;
		$vikcronparams = VikRequest::getVar('vikcronparams', array(), 'request', 'none', VIKREQUEST_ALLOWHTML);
		$cronparamarr = array();
		$cronparamstr = '';
		if (count($vikcronparams) > 0) {
			foreach ($vikcronparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$cronparamarr[$setting] = $cont;
				}
			}
			if (count($cronparamarr) > 0) {
				$cronparamstr = json_encode($cronparamarr);
			}
		}
		$goto = "index.php?option=com_vikbooking&task=crons";
		if (empty($pcron_name) || empty($pclass_file) || empty($pwhereup)) {
			$mainframe->redirect($goto);
			exit;
		}
		//launch update() method if available
		if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file)) {
			require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file);
			if (method_exists('VikCronJob', 'update')) {
				$cron_obj = new VikCronJob($pwhereup, $cronparamarr);
				$cron_obj->update();
			}
		}
		//
		$q = "UPDATE `#__vikbooking_cronjobs` SET `cron_name`=".$dbo->quote($pcron_name).",`class_file`=".$dbo->quote($pclass_file).",`params`=".$dbo->quote($cronparamstr).",`published`=".(int)$ppublished." WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::_('VBOCRONUPDATED'));
		if ($stay) {
			$goto = "index.php?option=com_vikbooking&task=editcron&cid[]=".$pwhereup;
		}
		$mainframe->redirect($goto);
	}

	function createcron() {
		$this->do_createcron();
	}

	function createcronstay() {
		$this->do_createcron(true);
	}

	private function do_createcron($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pcron_name = VikRequest::getString('cron_name', '', 'request');
		$pclass_file = VikRequest::getString('class_file', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$ppublished = intval($ppublished) == 1 ? 1 : 0;
		$vikcronparams = VikRequest::getVar('vikcronparams', array(), 'request', 'none', VIKREQUEST_ALLOWHTML);
		$cronparamarr = array();
		$cronparamstr = '';
		if (count($vikcronparams) > 0) {
			foreach ($vikcronparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$cronparamarr[$setting] = $cont;
				}
			}
			if (count($cronparamarr) > 0) {
				$cronparamstr = json_encode($cronparamarr);
			}
		}
		$goto = "index.php?option=com_vikbooking&task=crons";
		if (empty($pcron_name) || empty($pclass_file)) {
			$goto = "index.php?option=com_vikbooking&task=newcron";
			$mainframe->redirect($goto);
			exit;
		}
		$q = "INSERT INTO `#__vikbooking_cronjobs` (`cron_name`,`class_file`,`params`,`published`) VALUES (".$dbo->quote($pcron_name).", ".$dbo->quote($pclass_file).", ".$dbo->quote($cronparamstr).", ".(int)$ppublished.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			//launch install() method if available
			if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file)) {
				require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$pclass_file);
				if (method_exists('VikCronJob', 'install')) {
					$cron_obj = new VikCronJob($lid, $cronparamarr);
					$cron_obj->install();
				}
			}
			//
			$mainframe->enqueueMessage(JText::_('VBOCRONSAVED'));
			if ($stay) {
				$goto = "index.php?option=com_vikbooking&task=editcron&cid[]=".$lid;
			}
		}
		$mainframe->redirect($goto);
	}

	function removecrons() {
		$ids = VikRequest::getVar('cid', array());
		if (count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "SELECT * FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$cur_cron = $dbo->loadAssoc();
					//launch uninstall() method if available
					if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$cur_cron['class_file'])) {
						require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'cronjobs'.DIRECTORY_SEPARATOR.$cur_cron['class_file']);
						if (method_exists('VikCronJob', 'uninstall')) {
							$cron_obj = new VikCronJob($cur_cron['id'], json_decode($cur_cron['params'], true));
							$cron_obj->uninstall();
						}
					}
					//
					$q = "DELETE FROM `#__vikbooking_cronjobs` WHERE `id`=".(int)$d.";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=crons");
	}

	function packages() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'packages'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newpackage() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepackage'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editpackage() {
		VikBookingHelper::printHeader("packages");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepackage'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createpackage() {
		$this->do_createpackage();
	}

	function createpackagestay() {
		$this->do_createpackage(true);
	}

	private function do_createpackage($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$palias = VikRequest::getString('alias', '', 'request');
		$palias = empty($palias) ? $pname : $palias;
		$palias = JFilterOutput::stringURLSafe($palias);
		$pimg = VikRequest::getVar('img', null, 'files', 'array');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pexcludeday = VikRequest::getVar('excludeday', array());
		$strexcldates = array();
		foreach ($pexcludeday as $exclday) {
			if (!empty($exclday)) {
				$strexcldates[] = $exclday;
			}
		}
		$strexcldates = implode(';', $strexcldates);
		$prooms = VikRequest::getVar('rooms', array());
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = $pmaxlos < 0 ? 0 : $pmaxlos;
		$pmaxlos = $pmaxlos < $pminlos ? 0 : $pmaxlos;
		$pcost = VikRequest::getFloat('cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$ppernight_total = VikRequest::getInt('pernight_total', '', 'request');
		$ppernight_total = $ppernight_total == 1 ? 1 : 2;
		$pperperson = VikRequest::getInt('perperson', '', 'request');
		$pperperson = $pperperson > 0 ? 1 : 0;
		$pshowoptions = VikRequest::getInt('showoptions', '', 'request');
		$pshowoptions = $pshowoptions >= 1 && $pshowoptions <= 3 ? $pshowoptions : 1;
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdescr = VikRequest::getString('shortdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pconditions = VikRequest::getString('conditions', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbenefits = VikRequest::getString('benefits', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptsinit = VikBooking::getDateTimestamp($pfrom, '0', '0');
		$ptsend = VikBooking::getDateTimestamp($pto, '23', '59');
		$ptsinit = empty($ptsinit) ? time() : $ptsinit;
		$ptsend = empty($ptsend) || $ptsend < $ptsinit ? $ptsinit : $ptsend;
		//file upload
		jimport('joomla.filesystem.file');
		$gimg = "";
		if (isset($pimg) && strlen(trim($pimg['name']))) {
			$pautoresize = VikRequest::getString('autoresize', '', 'request');
			$presizeto = VikRequest::getInt('resizeto', '', 'request');
			$creativik = new vikResizer();
			$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimg['name'])));
			$src = $pimg['tmp_name'];
			$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
			$j = "";
			if (file_exists($dest.$filename)) {
				$j = rand(171, 1717);
				while (file_exists($dest.$j.$filename)) {
					$j++;
				}
			}
			$finaldest = $dest.$j.$filename;
			$check = getimagesize($pimg['tmp_name']);
			if ($check[2] & imagetypes()) {
				if (VikBooking::uploadFile($src, $finaldest)) {
					$gimg = $j.$filename;
					//orig img
					$origmod = true;
					if ($pautoresize == "1" && !empty($presizeto)) {
						$origmod = $creativik->proportionalImage($finaldest, $dest.'big_'.$j.$filename, $presizeto, $presizeto);
					} else {
						VikBooking::uploadFile($finaldest, $dest.'big_'.$j.$filename, true);
					}
					//thumb
					$thumb = $creativik->proportionalImage($finaldest, $dest.'thumb_'.$j.$filename, 250, 250);
					if (!$thumb || !$origmod) {
						if (file_exists($dest.'big_'.$j.$filename)) @unlink($dest.'big_'.$j.$filename);
						if (file_exists($dest.'thumb_'.$j.$filename)) @unlink($dest.'thumb_'.$j.$filename);
						VikError::raiseWarning('', 'Error Uploading the File: '.$pimg['name']);
					}
					@unlink($finaldest);
				} else {
					VikError::raiseWarning('', 'Error while uploading image');
				}
			} else {
				VikError::raiseWarning('', 'Uploaded file is not an Image');
			}
		}
		//
		$goto = "index.php?option=com_vikbooking&task=packages";
		$q = "INSERT INTO `#__vikbooking_packages` (`name`,`alias`,`img`,`dfrom`,`dto`,`excldates`,`minlos`,`maxlos`,`cost`,`idiva`,`pernight_total`,`perperson`,`descr`,`shortdescr`,`benefits`,`conditions`,`showoptions`) VALUES (".$dbo->quote($pname).", ".$dbo->quote($palias).", ".$dbo->quote($gimg).", ".(int)$ptsinit.", ".(int)$ptsend.", ".$dbo->quote($strexcldates).", ".(int)$pminlos.", ".(int)$pmaxlos.", ".$dbo->quote($pcost).",'".$paliq."', ".(int)$ppernight_total.", ".(int)$pperperson.", ".$dbo->quote($pdescr).", ".$dbo->quote($pshortdescr).", ".$dbo->quote($pbenefits).", ".$dbo->quote($pconditions).", ".(int)$pshowoptions.");";
		$dbo->setQuery($q);
		$dbo->execute();
		$lid = $dbo->insertid();
		if (!empty($lid)) {
			$mainframe->enqueueMessage(JText::_('VBOPKGSAVED'));
			if ($stay) {
				$goto = "index.php?option=com_vikbooking&task=editpackage&cid[]=".$lid;
			}
			foreach ($prooms as $roomid) {
				if (!empty($roomid)) {
					$q = "INSERT INTO `#__vikbooking_packages_rooms` (`idpackage`,`idroom`) VALUES (".(int)$lid.", ".(int)$roomid.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$mainframe->redirect($goto);
	}

	function updatepackage() {
		$this->do_updatepackage();
	}

	function updatepackagestay() {
		$this->do_updatepackage(true);
	}

	private function do_updatepackage($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhereup = VikRequest::getInt('whereup', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_packages` WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$pkg_data = $dbo->loadAssoc();
		} else {
			VikError::raiseWarning('', 'Not Found.');
			$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
			exit;
		}
		$pname = VikRequest::getString('name', '', 'request');
		$palias = VikRequest::getString('alias', '', 'request');
		$palias = empty($palias) ? $pname : $palias;
		$palias = JFilterOutput::stringURLSafe($palias);
		$pimg = VikRequest::getVar('img', null, 'files', 'array');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pexcludeday = VikRequest::getVar('excludeday', array());
		$strexcldates = array();
		foreach ($pexcludeday as $exclday) {
			if (!empty($exclday)) {
				$strexcldates[] = $exclday;
			}
		}
		$strexcldates = implode(';', $strexcldates);
		$prooms = VikRequest::getVar('rooms', array());
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = $pmaxlos < 0 ? 0 : $pmaxlos;
		$pmaxlos = $pmaxlos < $pminlos ? 0 : $pmaxlos;
		$pcost = VikRequest::getFloat('cost', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$ppernight_total = VikRequest::getInt('pernight_total', '', 'request');
		$ppernight_total = $ppernight_total == 1 ? 1 : 2;
		$pperperson = VikRequest::getInt('perperson', '', 'request');
		$pperperson = $pperperson > 0 ? 1 : 0;
		$pshowoptions = VikRequest::getInt('showoptions', '', 'request');
		$pshowoptions = $pshowoptions >= 1 && $pshowoptions <= 3 ? $pshowoptions : 1;
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdescr = VikRequest::getString('shortdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pconditions = VikRequest::getString('conditions', '', 'request', VIKREQUEST_ALLOWRAW);
		$pbenefits = VikRequest::getString('benefits', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptsinit = VikBooking::getDateTimestamp($pfrom, '0', '0');
		$ptsend = VikBooking::getDateTimestamp($pto, '23', '59');
		$ptsinit = empty($ptsinit) ? time() : $ptsinit;
		$ptsend = empty($ptsend) || $ptsend < $ptsinit ? $ptsinit : $ptsend;
		//file upload
		jimport('joomla.filesystem.file');
		$gimg = "";
		if (isset($pimg) && strlen(trim($pimg['name']))) {
			$pautoresize = VikRequest::getString('autoresize', '', 'request');
			$presizeto = VikRequest::getInt('resizeto', '', 'request');
			$creativik = new vikResizer();
			$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimg['name'])));
			$src = $pimg['tmp_name'];
			$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
			$j = "";
			if (file_exists($dest.$filename)) {
				$j = rand(171, 1717);
				while (file_exists($dest.$j.$filename)) {
					$j++;
				}
			}
			$finaldest = $dest.$j.$filename;
			$check = getimagesize($pimg['tmp_name']);
			if ($check[2] & imagetypes()) {
				if (VikBooking::uploadFile($src, $finaldest)) {
					$gimg = $j.$filename;
					//orig img
					$origmod = true;
					if ($pautoresize == "1" && !empty($presizeto)) {
						$origmod = $creativik->proportionalImage($finaldest, $dest.'big_'.$j.$filename, $presizeto, $presizeto);
					} else {
						VikBooking::uploadFile($finaldest, $dest.'big_'.$j.$filename, true);
					}
					//thumb
					$thumb = $creativik->proportionalImage($finaldest, $dest.'thumb_'.$j.$filename, 250, 250);
					if (!$thumb || !$origmod) {
						if (file_exists($dest.'big_'.$j.$filename)) @unlink($dest.'big_'.$j.$filename);
						if (file_exists($dest.'thumb_'.$j.$filename)) @unlink($dest.'thumb_'.$j.$filename);
						VikError::raiseWarning('', 'Error Uploading the File: '.$pimg['name']);
					}
					@unlink($finaldest);
				} else {
					VikError::raiseWarning('', 'Error while uploading image');
				}
			} else {
				VikError::raiseWarning('', 'Uploaded file is not an Image');
			}
		}
		//
		$goto = "index.php?option=com_vikbooking&task=packages";
		$q = "UPDATE `#__vikbooking_packages` SET `name`=".$dbo->quote($pname).",`alias`=".$dbo->quote($palias)."".(!empty($gimg) ? ",`img`=".$dbo->quote($gimg) : "").",`dfrom`=".(int)$ptsinit.",`dto`=".(int)$ptsend.",`excldates`=".$dbo->quote($strexcldates).",`minlos`=".(int)$pminlos.",`maxlos`=".(int)$pmaxlos.",`cost`=".$dbo->quote($pcost).",`idiva`='".$paliq."',`pernight_total`=".(int)$ppernight_total.",`perperson`=".(int)$pperperson.",`descr`=".$dbo->quote($pdescr).",`shortdescr`=".$dbo->quote($pshortdescr).",`benefits`=".$dbo->quote($pbenefits).",`conditions`=".$dbo->quote($pconditions).",`showoptions`=".(int)$pshowoptions." WHERE `id`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "DELETE FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".(int)$pwhereup.";";
		$dbo->setQuery($q);
		$dbo->execute();
		foreach ($prooms as $roomid) {
			if (!empty($roomid)) {
				$q = "INSERT INTO `#__vikbooking_packages_rooms` (`idpackage`,`idroom`) VALUES (".(int)$pwhereup.", ".(int)$roomid.");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe->enqueueMessage(JText::_('VBOPKGUPDATED'));
		if ($stay) {
			$goto = "index.php?option=com_vikbooking&task=editpackage&cid[]=".$pwhereup;
		}
		$mainframe->redirect($goto);
	}

	function removepackages() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikbooking_packages` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_packages_rooms` WHERE `idpackage`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
	}

	function calendar() {
		VikBookingHelper::printHeader("19");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'calendar'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function rooms() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'rooms'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newroom() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageroom'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editroom() {
		VikBookingHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageroom'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createroom() {
		$this->do_createroom();
	}

	function createroomstay() {
		$this->do_createroom(true);
	}

	private function do_createroom($stay = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmalldesc = VikRequest::getString('smalldesc', '', 'request', VIKREQUEST_ALLOWRAW);
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pfromadult = VikRequest::getInt('fromadult', '', 'request');
		$ptoadult = VikRequest::getInt('toadult', '', 'request');
		$pfromchild = VikRequest::getInt('fromchild', '', 'request');
		$ptochild = VikRequest::getInt('tochild', '', 'request');
		$ptotpeople = VikRequest::getInt('totpeople', '', 'request');
		$pmintotpeople = VikRequest::getInt('mintotpeople', '', 'request');
		$pmintotpeople = $pmintotpeople < 1 ? 1 : $pmintotpeople;
		$plastavail = VikRequest::getString('lastavail', '', 'request');
		$plastavail = empty($plastavail) ? 0 : intval($plastavail);
		$pcustprice = VikRequest::getString('custprice', '', 'request');
		$pcustprice = empty($pcustprice) ? '' : floatval($pcustprice);
		$pcustpricetxt = VikRequest::getString('custpricetxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcustpricesubtxt = VikRequest::getString('custpricesubtxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$ppricecal = VikRequest::getInt('pricecal', '', 'request');
		$pdefcalcost = VikRequest::getString('defcalcost', '', 'request');
		$pmaxminpeople = VikRequest::getString('maxminpeople', '', 'request');
		$pcimgcaption = VikRequest::getVar('cimgcaption', array(0));
		$pmaxminpeople = in_array($pmaxminpeople, array('0', '1', '2', '3', '4', '5')) ? $pmaxminpeople : '0';
		$pseasoncal = VikRequest::getInt('seasoncal', 0, 'request');
		$pseasoncal = $pseasoncal >= 0 || $pseasoncal <= 3 ? $pseasoncal : 0;
		$pseasoncal_nights = VikRequest::getString('seasoncal_nights', '', 'request');
		$pseasoncal_prices = VikRequest::getString('seasoncal_prices', '', 'request');
		$pseasoncal_restr = VikRequest::getString('seasoncal_restr', '', 'request');
		$pmulti_units = VikRequest::getInt('multi_units', '', 'request');
		$pmulti_units = $punits > 1 ? $pmulti_units : 0;
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$scalnights_arr = array();
		if (!empty($pseasoncal_nights)) {
			$scalnights = explode(',', $pseasoncal_nights);
			foreach ($scalnights as $scalnight) {
				if (intval(trim($scalnight)) > 0) {
					$scalnights_arr[] = intval(trim($scalnight));
				}
			}
		}
		if (count($scalnights_arr) > 0) {
			$pseasoncal_nights = implode(', ', $scalnights_arr);
		} else {
			$pseasoncal_nights = '';
			$pseasoncal = 0;
		}
		$roomparams = array('lastavail' => $plastavail, 'custprice' => $pcustprice, 'custpricetxt' => $pcustpricetxt, 'custpricesubtxt' => $pcustpricesubtxt, 'reqinfo' => $preqinfo, 'pricecal' => $ppricecal, 'defcalcost' => floatval($pdefcalcost), 'maxminpeople' => $pmaxminpeople, 'seasoncal' => $pseasoncal, 'seasoncal_nights' => $pseasoncal_nights, 'seasoncal_prices' => $pseasoncal_prices, 'seasoncal_restr' => $pseasoncal_restr, 'multi_units' => $pmulti_units, 'custptitle' => $pcustptitle, 'custptitlew' => $pcustptitlew, 'metakeywords' => $pmetakeywords, 'metadescription' => $pmetadescription);
		//distinctive features
		$roomparams['features'] = array();
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) { 
				$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
				$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$roomparams['features'][$i][$use_key] = $distf_value[$distf_k];
					}
				}
			}
		}
		//
		$roomparamstr = json_encode($roomparams);
		jimport('joomla.filesystem.file');
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikBooking::caniWrite($updpath) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = $updpath;
			$thumbsdest = $updpath;
			$dest = $updpath;
			$moreimagestr="";
			$captiontexts = array();
			$imgcaptions = array();
			foreach ($pimages['name'] as $kk=>$ci) {
				if (!empty($ci)) {
					$arrimgs[] = $kk;
					$captiontexts[] = $pcimgcaption[$kk];
				}
			}
			if (is_array($arrimgs)) {
				foreach ($arrimgs as $ki => $imgk) {
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j = "";
						if (file_exists($dest.$filename)) {
							$j = rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest = $dest.$j.$filename;
						$check = getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikBooking::uploadFile($src, $finaldest)) {
								$gimg = $j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
								}
								//thumb
								$thumbsize = VikBooking::getThumbSize();
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr .= $j.$filename.";;";
									$imgcaptions[] = $captiontexts[$ki];
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//end more images
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat) {
					if (!empty($ccat)) {
						$pccatdef.=$ccat.";";
					}
				}
			} else {
				$pccatdef="";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat) {
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			if ($pfromadult > $ptoadult) {
				$pfromadult = 1;
				$ptoadult = 1;
			}
			if ($pfromchild > $ptochild) {
				$pfromchild = 1;
				$ptochild = 1;
			}
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_rooms` (`name`,`img`,`idcat`,`idcarat`,`idopt`,`info`,`avail`,`units`,`moreimgs`,`fromadult`,`toadult`,`fromchild`,`tochild`,`smalldesc`,`totpeople`,`mintotpeople`,`params`,`imgcaptions`,`alias`) VALUES(".$dbo->quote($pcname).",".$dbo->quote($picon).",".$dbo->quote($pccatdef).",".$dbo->quote($pccaratdef).",".$dbo->quote($pcoptionaldef).",".$dbo->quote($pcdescr).",".$dbo->quote($pcavaildef).",".($punits > 0 ? $dbo->quote($punits) : "'1'").", ".$dbo->quote($moreimagestr).", '".$pfromadult."', '".$ptoadult."', '".$pfromchild."', '".$ptochild."', ".$dbo->quote($psmalldesc).", ".$ptotpeople.", ".$pmintotpeople.", ".$dbo->quote($roomparamstr).", ".$dbo->quote(json_encode($imgcaptions)).",".$dbo->quote($psefalias).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				if ($stay === true) {
					$mainframe->enqueueMessage(JText::_('VBOROOMSAVEOK').' - <a href="index.php?option=com_vikbooking&task=tariffs&cid[]='.$lid.'">'.JText::_('VBOGOTORATES').'</a>');
					$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$lid);
					exit;
				} else {
					$mainframe->redirect("index.php?option=com_vikbooking&task=tariffs&cid[]=".$lid);
				}
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	function updateroom() {
		$this->do_updateroom();
	}

	function updateroomstay() {
		$this->do_updateroom(true);
	}

	private function do_updateroom($stay = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmalldesc = VikRequest::getString('smalldesc', '', 'request', VIKREQUEST_ALLOWRAW);
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pactmoreimgs = VikRequest::getString('actmoreimgs', '', 'request');
		$pfromadult = VikRequest::getInt('fromadult', '', 'request');
		$ptoadult = VikRequest::getInt('toadult', '', 'request');
		$pfromchild = VikRequest::getInt('fromchild', '', 'request');
		$ptochild = VikRequest::getInt('tochild', '', 'request');
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array(0));
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array(0));
		$padultsdiffnum = VikRequest::getVar('adultsdiffnum', array(0));
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array(0));
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array(0));
		$ptotpeople = VikRequest::getInt('totpeople', '', 'request');
		$pmintotpeople = VikRequest::getInt('mintotpeople', '', 'request');
		$pmintotpeople = $pmintotpeople < 1 ? 1 : $pmintotpeople;
		$plastavail = VikRequest::getString('lastavail', '', 'request');
		$plastavail = empty($plastavail) ? 0 : intval($plastavail);
		$pcustprice = VikRequest::getString('custprice', '', 'request');
		$pcustprice = empty($pcustprice) ? '' : floatval($pcustprice);
		$pcustpricetxt = VikRequest::getString('custpricetxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcustpricesubtxt = VikRequest::getString('custpricesubtxt', '', 'request', VIKREQUEST_ALLOWRAW);
		$preqinfo = VikRequest::getInt('reqinfo', '', 'request');
		$ppricecal = VikRequest::getInt('pricecal', '', 'request');
		$pdefcalcost = VikRequest::getString('defcalcost', '', 'request');
		$pmaxminpeople = VikRequest::getString('maxminpeople', '', 'request');
		$pcimgcaption = VikRequest::getVar('cimgcaption', array(0));
		$pimgsorting = VikRequest::getVar('imgsorting', array(0));
		$pupdatecaption = VikRequest::getInt('updatecaption', '', 'request');
		$pmaxminpeople = in_array($pmaxminpeople, array('0', '1', '2', '3', '4', '5')) ? $pmaxminpeople : '0';
		$pseasoncal = VikRequest::getInt('seasoncal', 0, 'request');
		$pseasoncal = $pseasoncal >= 0 || $pseasoncal <= 3 ? $pseasoncal : 0;
		$pseasoncal_nights = VikRequest::getString('seasoncal_nights', '', 'request');
		$pseasoncal_prices = VikRequest::getString('seasoncal_prices', '', 'request');
		$pseasoncal_restr = VikRequest::getString('seasoncal_restr', '', 'request');
		$pmulti_units = VikRequest::getInt('multi_units', '', 'request');
		$pmulti_units = $punits > 1 ? $pmulti_units : 0;
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$scalnights_arr = array();
		if (!empty($pseasoncal_nights)) {
			$scalnights = explode(',', $pseasoncal_nights);
			foreach ($scalnights as $scalnight) {
				if (intval(trim($scalnight)) > 0) {
					$scalnights_arr[] = intval(trim($scalnight));
				}
			}
		}
		if (count($scalnights_arr) > 0) {
			$pseasoncal_nights = implode(', ', $scalnights_arr);
		} else {
			$pseasoncal_nights = '';
			$pseasoncal = 0;
		}
		$roomparams = array('lastavail' => $plastavail, 'custprice' => $pcustprice, 'custpricetxt' => $pcustpricetxt, 'custpricesubtxt' => $pcustpricesubtxt, 'reqinfo' => $preqinfo, 'pricecal' => $ppricecal, 'defcalcost' => floatval($pdefcalcost), 'maxminpeople' => $pmaxminpeople, 'seasoncal' => $pseasoncal, 'seasoncal_nights' => $pseasoncal_nights, 'seasoncal_prices' => $pseasoncal_prices, 'seasoncal_restr' => $pseasoncal_restr, 'multi_units' => $pmulti_units, 'custptitle' => $pcustptitle, 'custptitlew' => $pcustptitlew, 'metakeywords' => $pmetakeywords, 'metadescription' => $pmetadescription);
		//distinctive features
		$roomparams['features'] = array();
		$newfeatures = array();
		if ($punits > 0) {
			for ($i=1; $i <= $punits; $i++) { 
				$distf_name = VikRequest::getVar('feature-name'.$i, array(0));
				$distf_lang = VikRequest::getVar('feature-lang'.$i, array(0));
				$distf_value = VikRequest::getVar('feature-value'.$i, array(0));
				foreach ($distf_name as $distf_k => $distf) {
					if (strlen($distf) > 0 && strlen($distf_value[$distf_k]) > 0) {
						$use_key = strlen($distf_lang[$distf_k]) > 0 ? $distf_lang[$distf_k] : $distf;
						$roomparams['features'][$i][$use_key] = $distf_value[$distf_k];
						if ($distf_k < 1) {
							//check only the first feature
							$newfeatures[$i][$use_key] = $distf_value[$distf_k];
						}
					}
				}
			}
		}
		//
		$roomparamstr = json_encode($roomparams);
		jimport('joomla.filesystem.file');
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikBooking::caniWrite($updpath) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			//more images
			$creativik = new vikResizer();
			$bigsdest = $updpath;
			$thumbsdest = $updpath;
			$dest = $updpath;
			$moreimagestr=$pactmoreimgs;
			$captiontexts = array();
			$imgcaptions = array();
			//captions of uploaded extra images
			if (!empty($pactmoreimgs)) {
				$sploimgs = explode(';;', $pactmoreimgs);
				foreach ($sploimgs as $ki => $oimg) {
					if (!empty($oimg)) {
						$oldcaption = VikRequest::getString('caption'.$ki, '', 'request', VIKREQUEST_ALLOWHTML);
						$imgcaptions[] = $oldcaption;
					}
				}
			}
			//
			foreach ($pimages['name'] as $kk=>$ci) {
				if (!empty($ci)) {
					$arrimgs[] = $kk;
					$captiontexts[] = $pcimgcaption[$kk];
				}
			}
			if (@count($arrimgs) > 0) {
				foreach ($arrimgs as $ki => $imgk) {
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j = "";
						if (file_exists($dest.$filename)) {
							$j = rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest = $dest.$j.$filename;
						$check = getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikBooking::uploadFile($src, $finaldest)) {
								$gimg = $j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
								}
								//thumb
								$thumbsize = VikBooking::getThumbSize();
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr .= $j.$filename.";;";
									$imgcaptions[] = $captiontexts[$ki];
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//sorting of extra images
			$sorted_extraim = array();
			$sorted_captions = array();
			$extraim_parts = explode(';;', $moreimagestr);
			foreach ($pimgsorting as $k => $v) {
				$capkey = -1;
				if (isset($extraim_parts[$k])) {
					$sorted_extraim[] = $v;
					foreach ($extraim_parts as $oldk => $oldv) {
						if ($oldv == $v) {
							$capkey = $oldk;
							break;
						}
					}
				}
				if (isset($imgcaptions[$capkey])) {
					$sorted_captions[] = $imgcaptions[$capkey];
				}
			}
			$tot_sorted_im = count($sorted_extraim);
			if ($tot_sorted_im != count($extraim_parts)) {
				foreach ($extraim_parts as $k => $v) {
					if ($k <= ($tot_sorted_im - 1)) {
						continue;
					}
					$sorted_extraim[] = $v;
					if (isset($imgcaptions[$k])) {
						$sorted_captions[] = $imgcaptions[$k];
					}
				}
			}
			$moreimagestr = implode(';;', $sorted_extraim);
			$imgcaptions = $sorted_captions;
			//end more images
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat) {
					if (!empty($ccat)) {
						$pccatdef .= $ccat.";";
					}
				}
			} else {
				$pccatdef = "";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat) {
					$pccaratdef .= $ccarat.";";
				}
			} else {
				$pccaratdef = "";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional) {
					$pcoptionaldef .= $coptional.";";
				}
			} else {
				$pcoptionaldef = "";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			if ($pfromadult > $ptoadult) {
				$pfromadult = 1;
				$ptoadult = 1;
			}
			if ($pfromchild > $ptochild) {
				$pfromchild = 1;
				$ptochild = 1;
			}
			$dbo = JFactory::getDBO();
			//adults charges/discounts
			$adchdisctouch = false;
			$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`='".$pwhereup."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$oldroom = $dbo->loadAssocList();
			$oldroom = $oldroom[0];
			if ($oldroom['fromadult'] == $pfromadult && $oldroom['toadult'] == $ptoadult) {
				if ($oldroom['toadult'] > 1 && $oldroom['fromadult'] < $oldroom['toadult'] && @count($padultsdiffnum) > 0) {
					$startadind = $oldroom['fromadult'] > 0 ? $oldroom['fromadult'] : 1;
					for($adi = $startadind; $adi <= $oldroom['toadult']; $adi++) {
						foreach ($padultsdiffnum as $kad=>$vad) {
							if (intval($vad) == intval($adi) && strlen($padultsdiffval[$kad]) > 0) {
								$adchdisctouch = true;
								$inschdisc = intval($padultsdiffchdisc[$kad]) == 1 ? 1 : 2;
								$insvalpcent = intval($padultsdiffvalpcent[$kad]) == 1 ? 1 : 2;
								$inspernight = intval($padultsdiffpernight[$kad]) == 1 ? 1 : 0;
								$insvalue = floatval($padultsdiffval[$kad]);
								//check if it exists
								$q = "SELECT `id` FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() > 0) {
									if ($insvalue > 0) {
										//update
										$q = "UPDATE `#__vikbooking_adultsdiff` SET `chdisc`='".$inschdisc."', `valpcent`='".$insvalpcent."', `value`='".$insvalue."', `pernight`='".$inspernight."' WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
										$dbo->setQuery($q);
										$dbo->execute();
									} else {
										//delete
										$q = "DELETE FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."' AND `adults`='".$adi."';";
										$dbo->setQuery($q);
										$dbo->execute();
									}
								} else {
									//insert
									$q = "INSERT INTO `#__vikbooking_adultsdiff` (`idroom`,`chdisc`,`valpcent`,`value`,`adults`,`pernight`) VALUES('".$oldroom['id']."', '".$inschdisc."', '".$insvalpcent."', '".$insvalue."', '".$adi."', '".$inspernight."');";
									$dbo->setQuery($q);
									$dbo->execute();
								}
							}
						}
					}
				}
			} else {
				//min and max adults num have changed, delete
				$q = "DELETE FROM `#__vikbooking_adultsdiff` WHERE `idroom`='".$oldroom['id']."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			if ($adchdisctouch == true) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VBUPDROOMADCHDISCSAVED'));
			}
			//
			//check distinctive features if there were any changes
			$old_rparams = json_decode($oldroom['params'], true);
			if (array_key_exists('features', $old_rparams)) {
				$oldfeatures = array();
				foreach ($old_rparams['features'] as $rnumunit => $oldfeat) {
					foreach ($oldfeat as $featname => $featval) {
						$oldfeatures[$rnumunit][$featname] = $featval;
						break;
					}
				}
				if ($oldfeatures != $newfeatures) {
					//changes were made to the first index (Room Number by default) of the distinctive features
					//set to NULL all the already set roomindexes in bookings
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=NULL WHERE `idroom`=".(int)$oldroom['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			$q = "UPDATE `#__vikbooking_rooms` SET `name`=".$dbo->quote($pcname).",".(strlen($picon) > 0 ? "`img`='".$picon."'," : "")."`idcat`=".$dbo->quote($pccatdef).",`idcarat`=".$dbo->quote($pccaratdef).",`idopt`=".$dbo->quote($pcoptionaldef).",`info`=".$dbo->quote($pcdescr).",`avail`=".$dbo->quote($pcavaildef).",`units`=".($punits > 0 ? $dbo->quote($punits) : "'1'").",`moreimgs`=".$dbo->quote($moreimagestr).",`fromadult`='".$pfromadult."',`toadult`='".$ptoadult."',`fromchild`='".$pfromchild."',`tochild`='".$ptochild."',`smalldesc`=".$dbo->quote($psmalldesc).",`totpeople`=".$ptotpeople.",`mintotpeople`=".$pmintotpeople.",`params`=".$dbo->quote($roomparamstr).",`imgcaptions`=".$dbo->quote(json_encode($imgcaptions)).",`alias`=".$dbo->quote($psefalias)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->enqueueMessage(JText::_('VBUPDROOMOK'));
		if ($pupdatecaption == 1 || $stay === true) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$pwhereup);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	function modavail() {
		$cid = VikRequest::getVar('cid', array(0));
		$room = $cid[0];
		if (!empty($room)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `avail` FROM `#__vikbooking_rooms` WHERE `id`=".$dbo->quote($room).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikbooking_rooms` SET `avail`='".(intval($get[0]['avail'])==1 ? 0 : 1)."' WHERE `id`=".$dbo->quote($room).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	function removeroom() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_rooms` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `idroom`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	function tariffs() {
		VikBookingHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffs'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function removetariffs() {
		$ids = VikRequest::getVar('cid', array(0));
		$proomid = VikRequest::getString('roomid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x=explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=tariffs&cid[]=".$proomid);
	}

	function editbusy() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editbusy'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function updatebusy() {
		$this->do_updatebusy();
	}

	function updatebusydoinv() {
		$this->do_updatebusy('geninvoices');
	}

	private function do_updatebusy($callback = '') {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pcheckinh = VikRequest::getString('checkinh', '', 'request');
		$pcheckinm = VikRequest::getString('checkinm', '', 'request');
		$pcheckouth = VikRequest::getString('checkouth', '', 'request');
		$pcheckoutm = VikRequest::getString('checkoutm', '', 'request');
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pareprices = VikRequest::getString('areprices', '', 'request');
		$ptotpaid = VikRequest::getString('totpaid', '', 'request');
		$pfrominv = VikRequest::getInt('frominv', '', 'request');
		$pvcm = VikRequest::getInt('vcm', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pextracn = VikRequest::getVar('extracn', array());
		$pextracc = VikRequest::getVar('extracc', array());
		$pextractx = VikRequest::getVar('extractx', array());
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$actnow = time();
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".$pidorder."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$ord = $dbo->loadAssocList();
			$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$ord[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			//do not touch this array property because it's used by VCM
			$ord[0]['rooms_info'] = $ordersrooms;
			//Package or custom rate
			$is_package = !empty($ord[0]['pkg']) ? true : false;
			$is_cust_cost = false;
			foreach ($ordersrooms as $kor => $or) {
				if ($is_package !== true && !empty($or['cust_cost']) && $or['cust_cost'] > 0.00) {
					$is_cust_cost = true;
					break;
				}
			}
			//
			//VikBooking 1.5 room switching
			$toswitch = array();
			$idbooked = array();
			$rooms_units = array();
			$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			$all_rooms = $dbo->loadAssocList();
			foreach ($all_rooms as $rr) {
				$rooms_units[$rr['id']]['name'] = $rr['name'];
				$rooms_units[$rr['id']]['units'] = $rr['units'];
			}
			foreach ($ordersrooms as $ind => $or) {
				$switch_command = VikRequest::getString('switch_'.$or['id'], '', 'request');
				if (!empty($switch_command) && intval($switch_command) != $or['idroom'] && array_key_exists(intval($switch_command), $rooms_units)) {
					$idbooked[$or['idroom']]++;
					$orkey = count($toswitch);
					$toswitch[$orkey]['from'] = $or['idroom'];
					$toswitch[$orkey]['to'] = intval($switch_command);
					$toswitch[$orkey]['record'] = $or;
				}
			}
			if (count($toswitch) > 0 && (!empty($ordersrooms[0]['idtar']) || $is_package || $is_cust_cost)) {
				foreach ($toswitch as $ksw => $rsw) {
					$plusunit = array_key_exists($rsw['to'], $idbooked) ? $idbooked[$rsw['to']] : 0;
					if (!VikBooking::roomBookable($rsw['to'], ($rooms_units[$rsw['to']]['units'] + $plusunit), $ord[0]['checkin'], $ord[0]['checkout'])) {
						unset($toswitch[$ksw]);
						VikError::raiseWarning('', JText::sprintf('VBSWITCHRERR', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']));
					}
				}
				if (count($toswitch) > 0) {
					//reset first record rate
					reset($ordersrooms);
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$ordersrooms[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					$app = JFactory::getApplication();
					foreach ($toswitch as $ksw => $rsw) {
						$q = "UPDATE `#__vikbooking_ordersrooms` SET `idroom`=".$rsw['to'].",`idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$rsw['record']['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$app->enqueueMessage(JText::sprintf('VBSWITCHROK', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']));
						//update Notes field for this booking to keep track of the previous room that was assigned
						$prev_room_name = array_key_exists($rsw['from'], $rooms_units) ? $rooms_units[$rsw['from']]['name'] : '';
						if (!empty($prev_room_name)) {
							$new_notes = JText::sprintf('VBOPREVROOMMOVED', $prev_room_name, date($df.' H:i:s'))."\n".$ord[0]['adminnotes'];
							$q = "UPDATE `#__vikbooking_orders` SET `adminnotes`=".$dbo->quote($new_notes)." WHERE `id`=".(int)$ord[0]['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//
						if ($ord[0]['status'] == 'confirmed') {
							//update record in _busy
							$q = "SELECT `b`.`id`,`b`.`idroom`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$cur_busy = $dbo->loadAssocList();
								$q = "UPDATE `#__vikbooking_busy` SET `idroom`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `idroom`=".$cur_busy[0]['idroom']." LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							//Invoke Channel Manager
							$vcm_autosync = VikBooking::vcmAutoUpdate();
							if ($vcm_autosync > 0) {
								$vcm_obj = VikBooking::getVcmInvoker();
								$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
								$sync_result = $vcm_obj->doSync();
								if ($sync_result === false) {
									$vcm_err = $vcm_obj->getError();
									VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
								}
							} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
								VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '')."&cid[]=".$ord[0]['id']).'"/><button type="submit" class="btn btn-primary">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button></form>');
							}
							//
						} elseif ($ord[0]['status'] == 'standby') {
							//remove record in _tmplock
							$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					//Booking History
					VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', VikBooking::getLogBookingModification($ord[0]));
					//
					$app->redirect("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '').($pfrominv == 1 ? '&frominv=1' : '')."&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
					exit;
				}
			}
			//
			$first = VikBooking::getDateTimestamp($pcheckindate, $pcheckinh, $pcheckinm);
			$second = VikBooking::getDateTimestamp($pcheckoutdate, $pcheckouth, $pcheckoutm);
			if ($second > $first) {
				$secdiff = $second - $first;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikBooking::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
						}
					}
				}
				$groupdays = VikBooking::getGroupDays($first, $second, $daysdiff);
				$opertwounits = true;
				$units_counter = array();
				foreach ($ordersrooms as $ind => $or) {
					if (!isset($units_counter[$or['idroom']])) {
						$units_counter[$or['idroom']] = -1;
					}
					$units_counter[$or['idroom']]++;
				}
				foreach ($ordersrooms as $ind => $or) {
					$num = $ind + 1;
					$check = "SELECT `b`.`id`,`b`.`checkin`,`b`.`realback`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`='" . $or['idroom'] . "' AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`!='".$ord[0]['id']."';";
					$dbo->setQuery($check);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$busy = $dbo->loadAssocList();
						foreach ($groupdays as $gday) {
							$bfound = 0;
							foreach ($busy as $bu) {
								if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
									$bfound++;
								}
							}
							if ($bfound >= ($or['units'] - $units_counter[$or['idroom']]) || ($ord[0]['status'] == 'confirmed' && !VikBooking::roomNotLocked($or['idroom'], $or['units'], $first, $second))) {
								$opertwounits = false;
							}
						}
					}
				}
				if ($opertwounits === true) {
					//update dates, customer information, amount paid and busy records before checking the rates
					$realback = VikBooking::getHoursRoomAvail() * 3600;
					$realback += $second;
					$newtotalpaid = strlen($ptotpaid) > 0 ? floatval($ptotpaid) : "";
					$roomsnum = $ord[0]['roomsnum'];
					//Vik Booking 1.10 - Add Room to existing booking
					$room_added = false;
					$padd_room_id = VikRequest::getInt('add_room_id', '', 'request');
					$padd_room_adults = VikRequest::getInt('add_room_adults', 2, 'request');
					$padd_room_children = VikRequest::getInt('add_room_children', 0, 'request');
					$padd_room_fname = VikRequest::getString('add_room_fname', '', 'request');
					$padd_room_lname = VikRequest::getString('add_room_lname', '', 'request');
					$padd_room_price = VikRequest::getFloat('add_room_price', 0, 'request');
					$paliq_add_room = VikRequest::getInt('aliq_add_room', 0, 'request');
					if ($padd_room_id > 0 && ($padd_room_adults + $padd_room_children) > 0) {
						//no need to re-validate the availability for this new room, as it was made via JS in the View.
						//increase the rooms number for later update, and insert the new room record
						$roomsnum++;
						$q = "INSERT INTO `#__vikbooking_ordersrooms` (`idorder`,`idroom`,`adults`,`children`,`t_first_name`,`t_last_name`,`cust_cost`,`cust_idiva`) VALUES(".$ord[0]['id'].", ".$padd_room_id.", ".$padd_room_adults.", ".$padd_room_children.", ".$dbo->quote($padd_room_fname).", ".$dbo->quote($padd_room_lname).", ".($padd_room_price > 0 ? $dbo->quote($padd_room_price) : 'NULL').", ".($padd_room_price > 0 && !empty($paliq_add_room) ? $dbo->quote($paliq_add_room) : 'NULL').");";
						$dbo->setQuery($q);
						$dbo->execute();
						$room_added = true;
					}
					//Vik Booking 1.10 - Remove Room from existing booking
					$room_removed = false;
					$prm_room_oid = VikRequest::getInt('rm_room_oid', '', 'request');
					if ($prm_room_oid > 0 && $roomsnum > 1) {
						//check if the requested room record exists for removal
						$q = "SELECT * FROM `#__vikbooking_ordersrooms` WHERE `id`=".$prm_room_oid." AND `idorder`=".$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$room_before_rm = $dbo->loadAssoc();
							//decrease the rooms number for later update, and remove the requested room record
							$roomsnum--;
							$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `id`=".$prm_room_oid." AND `idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							$room_removed = $room_before_rm['idroom'];
						}
					}
					//
					//update booking's basic information (customer data, dates, tot paid, number of rooms)
					$q = "UPDATE `#__vikbooking_orders` SET `custdata`=".$dbo->quote($pcustdata).", `days`='".$daysdiff."', `checkin`='".$first."', `checkout`='".$second."'".(strlen($newtotalpaid) > 0 ? ", `totpaid`='".$newtotalpaid."'" : "").", `roomsnum`=".(int)$roomsnum." WHERE `id`='".$ord[0]['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					if ($ord[0]['status'] == 'confirmed') {
						$q = "SELECT `b`.`id`,`b`.`idroom` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`='".$ord[0]['id']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						$allbusy = $dbo->loadAssocList();
						foreach ($allbusy as $bb) {
							$q = "UPDATE `#__vikbooking_busy` SET `checkin`='".$first."', `checkout`='".$second."', `realback`='".$realback."' WHERE `id`='".$bb['id']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//Vik Booking 1.10 - Add Room to existing (Confirmed) booking
						if ($room_added === true) {
							//add busy record for the new room unit
							$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".$padd_room_id.", ".$dbo->quote($first).", ".$dbo->quote($second).", ".$dbo->quote($realback).");";
							$dbo->setQuery($q);
							$dbo->execute();
							$newbusyid = $dbo->insertid();
							$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".$ord[0]['id'].", ".(int)$newbusyid.");";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//Vik Booking 1.10 - Remove Room from existing (Confirmed) booking
						if ($room_removed !== false) {
							//remove busy record for the removed room
							foreach ($allbusy as $bb) {
								if ($bb['idroom'] == $room_removed) {
									//remove the first room with this ID that was booked
									$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`=".$bb['id']." AND `idroom`=".$room_removed.";";
									$dbo->setQuery($q);
									$dbo->execute();
									$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".$ord[0]['id']." AND `idbusy`=".$bb['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
									break;
								}
							}
						}
						//
						if ($ord[0]['checkin'] != $first || $ord[0]['checkout'] != $second || $room_added === true || $room_removed !== false) {
							//Invoke Channel Manager
							$vcm_autosync = VikBooking::vcmAutoUpdate();
							if ($vcm_autosync > 0) {
								$vcm_obj = VikBooking::getVcmInvoker();
								$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
								$sync_result = $vcm_obj->doSync();
								if ($sync_result === false) {
									$vcm_err = $vcm_obj->getError();
									VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
								}
							} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
								VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '')."&cid[]=".$ord[0]['id']).'"/><button type="submit" class="btn btn-primary">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button></form>');
							}
							//
						}
					}
					$upd_esit = JText::_('RESUPDATED');
					//
					$isdue = 0;
					$tot_taxes = 0;
					$tot_city_taxes = 0;
					$tot_fees = 0;
					$doup = true;
					$tars = array();
					$cust_costs = array();
					$rooms_costs_map = array();
					$arrpeople = array();
					foreach ($ordersrooms as $kor => $or) {
						//Vik Booking 1.10 - Remove from existing booking
						if ($room_removed !== false) {
							if ($or['id'] == $prm_room_oid) {
								//do not consider this room for the calculation of the new total amount
								//we can unset this array for later use, because the channel manager has already been invoked.
								unset($ordersrooms[$kor]);
								continue;
							}
						}
						//
						$num = $kor + 1;
						$padults = VikRequest::getString('adults'.$num, '', 'request');
						$pchildren = VikRequest::getString('children'.$num, '', 'request');
						if (strlen($padults) || strlen($pchildren)) {
							$arrpeople[$num]['adults'] = (int)$padults;
							$arrpeople[$num]['children'] = (int)$pchildren;
						}
						$ppriceid = VikRequest::getString('priceid'.$num, '', 'request');
						$ppkgid = VikRequest::getString('pkgid'.$num, '', 'request');
						$pcust_cost = VikRequest::getString('cust_cost'.$num, '', 'request');
						$paliq = VikRequest::getString('aliq'.$num, '', 'request');
						if ($is_package === true && !empty($ppkgid)) {
							$pkg_cost = $or['cust_cost'];
							$pkg_idiva = $or['cust_idiva'];
							$pkg_info = VikBooking::getPackage($ppkgid);
							if (is_array($pkg_info) && count($pkg_info) > 0) {
								$use_adults = array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num]) ? $arrpeople[$num]['adults'] : $or['adults'];
								$pkg_cost = $pkg_info['pernight_total'] == 1 ? ($pkg_info['cost'] * $daysdiff) : $pkg_info['cost'];
								$pkg_cost = $pkg_info['perperson'] == 1 ? ($pkg_cost * ($use_adults > 0 ? $use_adults : 1)) : $pkg_cost;
								$pkg_cost = VikBooking::sayPackagePlusIva($pkg_cost, $pkg_info['idiva']);
							}
							$cust_costs[$num] = array('pkgid' => $ppkgid, 'cust_cost' => $pkg_cost, 'aliq' => $pkg_idiva);
							$isdue += $pkg_cost;
							$cost_minus_tax = VikBooking::sayPackageMinusIva($pkg_cost, $pkg_idiva);
							$tot_taxes += ($pkg_cost - $cost_minus_tax);
							continue;
						}
						if (empty($ppriceid) && !empty($pcust_cost) && floatval($pcust_cost) > 0) {
							$cust_costs[$num] = array('cust_cost' => $pcust_cost, 'aliq' => $paliq);
							$isdue += (float)$pcust_cost;
							$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$pcust_cost, (int)$paliq);
							$tot_taxes += ((float)$pcust_cost - $cost_minus_tax);
							continue;
						}
						$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `idroom`='".$or['idroom']."' AND `days`='".$daysdiff."' AND `idprice`='".$ppriceid."';";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							$tar = VikBooking::applySeasonsRoom($tar, $ord[0]['checkin'], $ord[0]['checkout']);
							//different usage
							if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
								$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
								//Occupancy Override
								$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
								$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
								//
								if (is_array($diffusageprice)) {
									//set a charge or discount to the price(s) for the different usage of the room
									foreach ($tar as $kpr => $vpr) {
										if ($diffusageprice['chdisc'] == 1) {
											//charge
											if ($diffusageprice['valpcent'] == 1) {
												//fixed value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
												$tar[$kpr]['diffusagecost'] = "+".$aduseval;
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
											} else {
												//percentage value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
												$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $aduseval;
											}
										} else {
											//discount
											if ($diffusageprice['valpcent'] == 1) {
												//fixed value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
												$tar[$kpr]['diffusagecost'] = "-".$aduseval;
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
											} else {
												//percentage value
												$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
												$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
												$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
												$tar[$kpr]['room_base_cost'] = $vpr['cost'];
												$tar[$kpr]['cost'] = $aduseval;
											}
										}
									}
								}
							}
							//
							$cost_plus_tax = VikBooking::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice']);
							$isdue += $cost_plus_tax;
							if ($cost_plus_tax == $tar[0]['cost']) {
								$cost_minus_tax = VikBooking::sayCostMinusIva($tar[0]['cost'], $tar[0]['idprice']);
								$tot_taxes += ($tar[0]['cost'] - $cost_minus_tax);
							} else {
								$tot_taxes += ($cost_plus_tax - $tar[0]['cost']);
							}
							$tars[$num] = $tar;
							$rooms_costs_map[$num] = $tar[0]['cost'];
						} else {
							$doup = false;
							break;
						}
					}
					if ($doup === true) {
						if ($room_added === true) {
							//Vik Booking 1.10 - Add Room to existing booking may require to increase the total amount, and taxes
							$padd_room_price = VikRequest::getFloat('add_room_price', 0, 'request');
							$paliq_add_room = VikRequest::getInt('aliq_add_room', 0, 'request');
							if (!empty($padd_room_price) && floatval($padd_room_price) > 0) {
								$isdue += (float)$padd_room_price;
								$cost_minus_tax = VikBooking::sayPackageMinusIva((float)$padd_room_price, (int)$paliq_add_room);
								$tot_taxes += ((float)$padd_room_price - $cost_minus_tax);
							}
							//
						}
						$toptionals = '';
						$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$toptionals = $dbo->loadAssocList();
						}
						foreach ($ordersrooms as $kor => $or) {
							$num = $kor + 1;
							$pt_first_name = VikRequest::getString('t_first_name'.$num, '', 'request');
							$pt_last_name = VikRequest::getString('t_last_name'.$num, '', 'request');
							$wop = "";
							if (is_array($toptionals)) {
								foreach ($toptionals as $opt) {
									if (!empty($opt['ageintervals']) && ($or['children'] > 0 || (array_key_exists($num, $arrpeople) && array_key_exists('children', $arrpeople[$num]))) ) {
										$tmpvar = VikRequest::getVar('optid'.$num.$opt['id'], array(0));
										if (is_array($tmpvar) && count($tmpvar) > 0 && !empty($tmpvar[0])) {
											$opt['quan'] = 1;
											$optagecosts = VikBooking::getOptionIntervalsCosts($opt['ageintervals']);
											$optagenames = VikBooking::getOptionIntervalsAges($opt['ageintervals']);
											$optagepcent = VikBooking::getOptionIntervalsPercentage($opt['ageintervals']);
											$optorigname = $opt['name'];
											foreach ($tmpvar as $chvar) {
												$optorigcost = $optagecosts[($chvar - 1)];
												if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
													//percentage value of the adults tariff
													if ($is_package !== true && array_key_exists($num, $tars)) {
														//type of price
														$optorigcost = $tars[$num][0]['cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
														//package
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
														//custom rate + custom tax rate
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													}
												} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
													//VBO 1.10 - percentage value of room base cost
													if ($is_package !== true && array_key_exists($num, $tars)) {
														//type of price
														$usecost = isset($tars[$num][0]['room_base_cost']) ? $tars[$num][0]['room_base_cost'] : $tars[$num][0]['cost'];
														$optorigcost = $usecost * $optagecosts[($chvar - 1)] / 100;
													} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
														//package
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
														//custom rate + custom tax rate
														$optorigcost = $cust_costs[$num]['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
													}
												}
												$opt['cost'] = $optorigcost;
												$opt['name'] = $optorigname.' ('.$optagenames[($chvar - 1)].')';
												$opt['chageintv'] = $chvar;
												$wop.=$opt['id'].":".$opt['quan']."-".$chvar.";";
												$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $opt['quan']) : ($opt['cost'] * $opt['quan']));
												if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $realcost > $opt['maxprice']) {
													$realcost = $opt['maxprice'];
												}
												$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $opt['idiva']);
												if ($opt['is_citytax'] == 1) {
													$tot_city_taxes += $tmpopr;
												} elseif ($opt['is_fee'] == 1) {
													$tot_fees += $tmpopr;
												} else {
													if ($tmpopr == $realcost) {
														$opt_minus_iva = VikBooking::sayOptionalsMinusIva($realcost, $opt['idiva']);
														$tot_taxes += ($realcost - $opt_minus_iva);
													} else {
														$tot_taxes += ($tmpopr - $realcost);
													}
												}
												$isdue += $tmpopr;
											}
										}
									} else {
										$tmpvar = VikRequest::getString('optid'.$num.$opt['id'], '', 'request');
										//options forced per child fix, no age intervals, like children tourist taxes
										$forcedquan = 1;
										$forceperday = false;
										$forceperchild = false;
										if (intval($opt['forcesel']) == 1 && strlen($opt['forceval']) > 0 && strlen($tmpvar) > 0) {
											$forceparts = explode("-", $opt['forceval']);
											$forcedquan = intval($forceparts[0]);
											$forceperday = intval($forceparts[1]) == 1 ? true : false;
											$forceperchild = intval($forceparts[2]) == 1 ? true : false;
											$tmpvar = $forcedquan;
											$tmpvar = $forceperchild === true && array_key_exists($num, $arrpeople) && array_key_exists('children', $arrpeople[$num]) ? ($tmpvar * $arrpeople[$num]['children']) : $tmpvar;
										}
										//
										if (!empty($tmpvar)) {
											$wop.=$opt['id'].":".$tmpvar.";";
											$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $tmpvar) : ($opt['cost'] * $tmpvar));
											if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $realcost > $opt['maxprice']) {
												$realcost = $opt['maxprice'];
												if (intval($opt['hmany']) == 1 && intval($tmpvar) > 1) {
													$realcost = $opt['maxprice'] * $tmpvar;
												}
											}
											if ($opt['perperson'] == 1) {
												$num_adults = array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num]) ? $arrpeople[$num]['adults'] : $num_adults;
												$realcost = $realcost * $num_adults;
											}
											$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $opt['idiva']);
											if ($opt['is_citytax'] == 1) {
												$tot_city_taxes += $tmpopr;
											} elseif ($opt['is_fee'] == 1) {
												$tot_fees += $tmpopr;
											} else {
												if ($tmpopr == $realcost) {
													$opt_minus_iva = VikBooking::sayOptionalsMinusIva($realcost, $opt['idiva']);
													$tot_taxes += ($realcost - $opt_minus_iva);
												} else {
													$tot_taxes += ($tmpopr - $realcost);
												}
											}
											$isdue += $tmpopr;
										}
									}
								}
							}
							$upd_fields = array();
							if ($is_package !== true && array_key_exists($num, $tars)) {
								//type of price
								$upd_fields[] = "`idtar`='".$tars[$num][0]['id']."'";
								$upd_fields[] = "`cust_cost`=NULL";
								$upd_fields[] = "`cust_idiva`=NULL";
								$upd_fields[] = "`room_cost`=".(array_key_exists($num, $rooms_costs_map) ? $dbo->quote($rooms_costs_map[$num]) : "NULL");
							} elseif ($is_package === true && array_key_exists($num, $cust_costs)) {
								//packages do not update name or cost, just set again the same package ID to avoid risks of empty upd_fields to update
								$upd_fields[] = "`idtar`=NULL";
								$upd_fields[] = "`pkg_id`='".$cust_costs[$num]['pkgid']."'";
								$upd_fields[] = "`cust_cost`='".$cust_costs[$num]['cust_cost']."'";
								$upd_fields[] = "`cust_idiva`='".$cust_costs[$num]['aliq']."'";
								$upd_fields[] = "`room_cost`=NULL";
							} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
								//custom rate + custom tax rate
								$upd_fields[] = "`idtar`=NULL";
								$upd_fields[] = "`cust_cost`='".$cust_costs[$num]['cust_cost']."'";
								$upd_fields[] = "`cust_idiva`='".$cust_costs[$num]['aliq']."'";
								$upd_fields[] = "`room_cost`=NULL";
							}
							if (is_array($toptionals)) {
								$upd_fields[] = "`optionals`='".$wop."'";
							}
							if (!empty($pt_first_name) || !empty($pt_last_name)) {
								$upd_fields[] = "`t_first_name`=".$dbo->quote($pt_first_name);
								$upd_fields[] = "`t_last_name`=".$dbo->quote($pt_last_name);
							}
							if (array_key_exists($num, $arrpeople) && array_key_exists('adults', $arrpeople[$num])) {
								$upd_fields[] = "`adults`=".intval($arrpeople[$num]['adults']);
								$upd_fields[] = "`children`=".intval($arrpeople[$num]['children']);
							}
							//calculate the extra costs and increase taxes + isdue
							$extracosts_arr = array();
							if (count($pextracn) > 0 && count($pextracn[$num]) > 0) {
								foreach ($pextracn[$num] as $eck => $ecn) {
									if (strlen($ecn) > 0 && array_key_exists($eck, $pextracc[$num]) && is_numeric($pextracc[$num][$eck])) {
										$ecidtax = array_key_exists($eck, $pextractx[$num]) && intval($pextractx[$num][$eck]) > 0 ? (int)$pextractx[$num][$eck] : '';
										$extracosts_arr[] = array('name' => $ecn, 'cost' => (float)$pextracc[$num][$eck], 'idtax' => $ecidtax);
										$ecplustax = !empty($ecidtax) ? VikBooking::sayOptionalsPlusIva((float)$pextracc[$num][$eck], $ecidtax) : (float)$pextracc[$num][$eck];
										$ecminustax = !empty($ecidtax) ? VikBooking::sayOptionalsMinusIva((float)$pextracc[$num][$eck], $ecidtax) : (float)$pextracc[$num][$eck];
										$ectottax = (float)$pextracc[$num][$eck] - $ecminustax;
										$isdue += $ecplustax;
										$tot_taxes += $ectottax;
									}
								}
							}
							if (count($extracosts_arr) > 0) {
								$upd_fields[] = "`extracosts`=".$dbo->quote(json_encode($extracosts_arr));
							} else {
								$upd_fields[] = "`extracosts`=NULL";
							}
							//end extra costs
							if (count($upd_fields) > 0) {
								$q = "UPDATE `#__vikbooking_ordersrooms` SET ".implode(', ', $upd_fields)." WHERE `idorder`='".$ord[0]['id']."' AND `idroom`='".$or['idroom']."' AND `id`='".$or['id']."';";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						$q = "UPDATE `#__vikbooking_orders` SET `total`='".$isdue."', `tot_taxes`='".$tot_taxes."', `tot_city_taxes`='".$tot_city_taxes."', `tot_fees`='".$tot_fees."' WHERE `id`='".$ord[0]['id']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						$upd_esit = JText::_('VBORESRATESUPDATED');
						//Customer Booking
						if ($ord[0]['status'] == 'confirmed') {
							$q = "SELECT `idcustomer` FROM `#__vikbooking_customers_orders` WHERE `idorder`='".$ord[0]['id']."';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$customer_id = $dbo->loadResult();
								$cpin = VikBooking::getCPinIstance();
								$cpin->is_admin = true;
								$cpin->updateBookingCommissions($ord[0]['id'], $customer_id);
							}
						}
						//
					}
					//Booking History
					VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', VikBooking::getLogBookingModification($ord[0]));
					//
					$mainframe->enqueueMessage($upd_esit);
				} else {
					VikError::raiseWarning('', JText::_('VBROOMNOTRIT')." ".date($df.' H:i', $first)." ".JText::_('VBROOMNOTCONSTO')." ".date($df.' H:i', $second));
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRPREV'));
			}
			if ($callback == 'geninvoices') {
				$mainframe->redirect("index.php?option=com_vikbooking&task=orders&cid[]=".$ord[0]['id']."&confirmgen=1");
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=editbusy".($pvcm == 1 ? '&vcm=1' : '').($pfrominv == 1 ? '&frominv=1' : '')."&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
		}
	}

	function removebusy() {
		$dbo = JFactory::getDBO();
		$prev_conf_ids = array();
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($pidorder).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$rows = $dbo->loadAssocList();
			if ($rows[0]['status'] != 'cancelled') {
				$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($rows[0]['status'] == 'confirmed') {
					$prev_conf_ids[] = $rows[0]['id'];
				}
				//Booking History
				VikBooking::getBookingHistoryInstance()->setBid($rows[0]['id'])->store('CB');
				//
			}
			$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ordbusy = $dbo->loadAssocList();
				foreach ($ordbusy as $ob) {
					$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($rows[0]['status'] == 'cancelled') {
				$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_orders` WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VBMESSDELBUSY'));
		}
		if (count($prev_conf_ids) > 0) {
			$prev_conf_ids_str = '';
			foreach ($prev_conf_ids as $prev_id) {
				$prev_conf_ids_str .= '&cid[]='.$prev_id;
			}
			//Invoke Channel Manager
			$vcm_autosync = VikBooking::vcmAutoUpdate();
			if ($vcm_autosync > 0) {
				$vcm_obj = VikBooking::getVcmInvoker();
				$vcm_obj->setOids($prev_conf_ids)->setSyncType('cancel');
				$sync_result = $vcm_obj->doSync();
				if ($sync_result === false) {
					$vcm_err = $vcm_obj->getError();
					VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
				}
			} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
				$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=cancel'.$prev_conf_ids_str.'&returl='.urlencode('index.php?option=com_vikbooking&task=orders');
				VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button>');
			}
			//
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	function unlockrecords() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking");
	}

	function sortoption() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr = $dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	function sortpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pmode = VikRequest::getString('mode', '', 'request');
		if (!empty($pmode) && !empty($sortid)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_gpayments` ORDER BY `#__vikbooking_gpayments`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_gpayments` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	function resendordemail() {
		$this->do_resendorderemail();
	}

	function sendcancordemail() {
		$this->do_resendorderemail(true);
	}

	private function do_resendorderemail($cancellation = false) {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vbo_tn = VikBooking::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vbo_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `or`.*,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			$vbo_tn->translateContents($ordersrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
			$currencyname = VikBooking::getCurrencyName();
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $order[0]['checkout'];
			$rooms = array();
			$tars = array();
			$arrpeople = array();
			$is_package = !empty($order[0]['pkg']) ? true : false;
			//send mail
			$ftitle = VikBooking::getFrontTitle();
			$nowts = time();
			$viklink = JURI::root()."index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			foreach ($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				$rooms[$num] = $or;
				$arrpeople[$num]['adults'] = $or['adults'];
				$arrpeople[$num]['children'] = $or['children'];
				if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
					//package or custom cost set from the back-end
					continue;
				}
				$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='".$or['idtar']."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$tar = $dbo->loadAssocList();
					$tar = VikBooking::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
					//different usage
					if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
						$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
						//Occupancy Override
						$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
						$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
						//
						if (is_array($diffusageprice)) {
							//set a charge or discount to the price(s) for the different usage of the room
							foreach ($tar as $kpr => $vpr) {
								$tar[$kpr]['diffusage'] = $or['adults'];
								if ($diffusageprice['chdisc'] == 1) {
									//charge
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
										$tar[$kpr]['diffusagecost'] = "+".$aduseval;
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
									} else {
										//percentage value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
										$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $aduseval;
									}
								} else {
									//discount
									if ($diffusageprice['valpcent'] == 1) {
										//fixed value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
										$tar[$kpr]['diffusagecost'] = "-".$aduseval;
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
									} else {
										//percentage value
										$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
										$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
										$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
										$tar[$kpr]['room_base_cost'] = $vpr['cost'];
										$tar[$kpr]['cost'] = $aduseval;
									}
								}
							}
						}
					}
					//
					$tars[$num] = $tar[0];
				} else {
					VikError::raiseWarning('', JText::_('VBERRNOFAREFOUND'));
				}
			}
			$pcheckin = $order[0]['checkin'];
			$pcheckout = $order[0]['checkout'];
			$secdiff = $pcheckout - $pcheckin;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikBooking::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
					}
				}
			}
			$isdue = 0;
			$pricestr = array();
			$optstr = array();
			foreach ($ordersrooms as $kor => $or) {
				$num = $kor + 1;
				if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
					//package cost or cust_cost should always be inclusive of taxes
					$calctar = $or['cust_cost'];
					$isdue += $calctar;
					$pricestr[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::_('VBOROOMCUSTRATEPLAN'))).": ".$calctar." ".$currencyname;
				} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
					$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
					$calctar = VikBooking::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
					$tars[$num]['calctar'] = $calctar;
					$isdue += $calctar;
					$pricestr[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
				}
				if (!empty($or['optionals'])) {
					$stepo = explode(";", $or['optionals']);
					foreach ($stepo as $oo) {
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$actopt = $dbo->loadAssocList();
								$vbo_tn->translateContents($actopt, '#__vikbooking_optionals', array(), array(), (!empty($order[0]['lang']) ? $order[0]['lang'] : null));
								$chvar = '';
								if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
									$optagecosts = VikBooking::getOptionIntervalsCosts($actopt[0]['ageintervals']);
									$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
									$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
									$agestept = explode('-', $stept[1]);
									$stept[1] = $agestept[0];
									$chvar = $agestept[1];
									if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
										//percentage value of the adults tariff
										if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
											$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
										} else {
											$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
											$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
										}
									} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
										//VBO 1.10 - percentage value of room base cost
										if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
											$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
										} else {
											$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
											$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
										}
									}
									$actopt[0]['chageintv'] = $chvar;
									$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
								} else {
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
								}
								if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
									$realcost = $actopt[0]['maxprice'];
									if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt[0]['maxprice'] * $stept[1];
									}
								}
								if ($actopt[0]['perperson'] == 1) {
									$realcost = $realcost * $or['adults'];
								}
								$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
								$isdue += $tmpopr;
								$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
							}
						}
					}
				}
				//custom extra costs
				if (!empty($or['extracosts'])) {
					$cur_extra_costs = json_decode($or['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$isdue += $ecplustax;
						$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
					}
				}
				//
			}
			//vikbooking 1.1 coupon
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			//
			//ConfirmationNumber
			$confirmnumber = $order[0]['confirmnumber'];
			//end ConfirmationNumber
			$esit_mess = JText::sprintf('VBORDEREMAILRESENT', $order[0]['custmail']);
			$status_str = JText::_('VBCOMPLETED');
			if ($cancellation) {
				$confirmnumber = '';
				$esit_mess = JText::sprintf('VBCANCORDEREMAILSENT', $order[0]['custmail']);
				$status_str = JText::_('VBCANCELLED');
			} elseif ($order[0]['status'] == 'standby') {
				$confirmnumber = '';
				$status_str = JText::_('VBWAITINGFORPAYMENT');
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage($esit_mess);
			//force the original total amount if rates have changed
			if (number_format($isdue, 2) != number_format($order[0]['total'], 2)) {
				$isdue = $order[0]['total'];
			}
			//
			VikBooking::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VBRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $rooms, $order[0]['checkin'], $order[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, $status_str, $order[0]['id'], $order[0]['coupon'], $arrpeople, $confirmnumber);
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$oid);
	}

	function setordconfirmed() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vbo_tn = VikBooking::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vbo_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$q = "SELECT `or`.*,`r`.`id` AS `r_reference_id`,`r`.`name`,`r`.`units`,`r`.`fromadult`,`r`.`toadult`,`r`.`params` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$order[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			$vbo_tn->translateContents($ordersrooms, '#__vikbooking_rooms', array('id' => 'r_reference_id'));
			$currencyname = VikBooking::getCurrencyName();
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $order[0]['checkout'];
			$allbook = true;
			$notavail = array();
			foreach ($ordersrooms as $ind => $or) {
				if (!VikBooking::roomBookable($or['idroom'], $or['units'], $order[0]['checkin'], $order[0]['checkout'])) {
					$allbook = false;
					$notavail[] = $or['name']." (".JText::_('VBMAILADULTS').": ".$or['adults'].($or['children'] > 0 ? " - ".JText::_('VBMAILCHILDREN').": ".$or['children'] : "").")";
				}
			}
			if (!$allbook) {
				VikError::raiseWarning('', JText::_('VBERRCONFORDERNOTAVROOM').' '.implode(", ", $notavail).'<br/>'.JText::_('VBUNABLESETRESCONF'));
			} else {
				$rooms = array();
				$tars = array();
				$arrpeople = array();
				$is_package = !empty($order[0]['pkg']) ? true : false;
				foreach ($ordersrooms as $ind => $or) {
					$q = "INSERT INTO `#__vikbooking_busy` (`idroom`,`checkin`,`checkout`,`realback`) VALUES(".(int)$or['idroom'].", ".(int)$order[0]['checkin'].", ".(int)$order[0]['checkout'].", ".(int)$realback.");";
					$dbo->setQuery($q);
					$dbo->execute();
					$lid = $dbo->insertid();
					$q = "INSERT INTO `#__vikbooking_ordersbusy` (`idorder`,`idbusy`) VALUES(".(int)$oid.", ".(int)$lid.");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				$q = "UPDATE `#__vikbooking_orders` SET `status`='confirmed' WHERE `id`=".(int)$order[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=".(int)$order[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//Booking History
				VikBooking::getBookingHistoryInstance()->setBid($order[0]['id'])->store('TC');
				//
				//send mail
				$ftitle = VikBooking::getFrontTitle();
				$nowts = time();
				$viklink = JURI::root()."index.php?option=com_vikbooking&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
				//Assign room specific unit
				$set_room_indexes = VikBooking::autoRoomUnit();
				$room_indexes_usemap = array();
				//
				foreach ($ordersrooms as $kor => $or) {
					$num = $kor + 1;
					$rooms[$num] = $or;
					$arrpeople[$num]['adults'] = $or['adults'];
					$arrpeople[$num]['children'] = $or['children'];
					//Assign room specific unit
					if ($set_room_indexes === true) {
						$room_indexes = VikBooking::getRoomUnitNumsAvailable($order[0], $or['r_reference_id']);
						$use_ind_key = 0;
						if (count($room_indexes)) {
							if (!array_key_exists($or['r_reference_id'], $room_indexes_usemap)) {
								$room_indexes_usemap[$or['r_reference_id']] = $use_ind_key;
							} else {
								$use_ind_key = $room_indexes_usemap[$or['r_reference_id']];
							}
							$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".(int)$room_indexes[$use_ind_key]." WHERE `id`=".(int)$or['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							//update rooms references for the customer email sending function
							$rooms[$num]['roomindex'] = (int)$room_indexes[$use_ind_key];
							//
							$room_indexes_usemap[$or['r_reference_id']]++;
						}
					}
					//
					if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
						//package or custom cost set from the back-end
						continue;
					}
					$q = "SELECT * FROM `#__vikbooking_dispcost` WHERE `id`='".$or['idtar']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$tar = $dbo->loadAssocList();
						$tar = VikBooking::applySeasonsRoom($tar, $order[0]['checkin'], $order[0]['checkout']);
						//different usage
						if ($or['fromadult'] <= $or['adults'] && $or['toadult'] >= $or['adults']) {
							$diffusageprice = VikBooking::loadAdultsDiff($or['idroom'], $or['adults']);
							//Occupancy Override
							$occ_ovr = VikBooking::occupancyOverrideExists($tar, $or['adults']);
							$diffusageprice = $occ_ovr !== false ? $occ_ovr : $diffusageprice;
							//
							if (is_array($diffusageprice)) {
								//set a charge or discount to the price(s) for the different usage of the room
								foreach ($tar as $kpr => $vpr) {
									$tar[$kpr]['diffusage'] = $or['adults'];
									if ($diffusageprice['chdisc'] == 1) {
										//charge
										if ($diffusageprice['valpcent'] == 1) {
											//fixed value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
											$tar[$kpr]['diffusagecost'] = "+".$aduseval;
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $vpr['cost'] + $aduseval;
										} else {
											//percentage value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? round(($vpr['cost'] * $diffusageprice['value'] / 100) * $tar[$kpr]['days'] + $vpr['cost'], 2) : round(($vpr['cost'] * (100 + $diffusageprice['value']) / 100), 2);
											$tar[$kpr]['diffusagecost'] = "+".$diffusageprice['value']."%";
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $aduseval;
										}
									} else {
										//discount
										if ($diffusageprice['valpcent'] == 1) {
											//fixed value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? 1 : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? $diffusageprice['value'] * $tar[$kpr]['days'] : $diffusageprice['value'];
											$tar[$kpr]['diffusagecost'] = "-".$aduseval;
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $vpr['cost'] - $aduseval;
										} else {
											//percentage value
											$tar[$kpr]['diffusagecostpernight'] = $diffusageprice['pernight'] == 1 ? $vpr['cost'] : 0;
											$aduseval = $diffusageprice['pernight'] == 1 ? round($vpr['cost'] - ((($vpr['cost'] / $tar[$kpr]['days']) * $diffusageprice['value'] / 100) * $tar[$kpr]['days']), 2) : round(($vpr['cost'] * (100 - $diffusageprice['value']) / 100), 2);
											$tar[$kpr]['diffusagecost'] = "-".$diffusageprice['value']."%";
											$tar[$kpr]['room_base_cost'] = $vpr['cost'];
											$tar[$kpr]['cost'] = $aduseval;
										}
									}
								}
							}
						}
						//
						$tars[$num] = $tar[0];
					} else {
						VikError::raiseWarning('', JText::_('VBERRNOFAREFOUND'));
					}
				}
				$pcheckin = $order[0]['checkin'];
				$pcheckout = $order[0]['checkout'];
				$secdiff = $pcheckout - $pcheckin;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikBooking::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
						}
					}
				}
				$isdue = 0;
				$pricestr = array();
				$optstr = array();
				foreach ($ordersrooms as $kor => $or) {
					$num = $kor + 1;
					if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
						//package cost or cust_cost should always be inclusive of taxes
						$calctar = $or['cust_cost'];
						$isdue += $calctar;
						$pricestr[$num] = (!empty($or['pkg_name']) ? $or['pkg_name'] : (!empty($or['otarplan']) ? ucwords($or['otarplan']) : JText::_('VBOROOMCUSTRATEPLAN'))).": ".$calctar." ".$currencyname;
					} elseif (array_key_exists($num, $tars) && is_array($tars[$num])) {
						$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
						$calctar = VikBooking::sayCostPlusIva($display_rate, $tars[$num]['idprice']);
						$tars[$num]['calctar'] = $calctar;
						$isdue += $calctar;
						$pricestr[$num] = VikBooking::getPriceName($tars[$num]['idprice'], $vbo_tn) . ": " . $calctar . " " . $currencyname . (!empty($tars[$num]['attrdata']) ? "\n" . VikBooking::getPriceAttr($tars[$num]['idprice'], $vbo_tn) . ": " . $tars[$num]['attrdata'] : "");
					}
					if (!empty($or['optionals'])) {
						$stepo = explode(";", $or['optionals']);
						foreach ($stepo as $oo) {
							if (!empty($oo)) {
								$stept = explode(":", $oo);
								$q = "SELECT * FROM `#__vikbooking_optionals` WHERE `id`=" . $dbo->quote($stept[0]) . ";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$actopt = $dbo->loadAssocList();
									$vbo_tn->translateContents($actopt, '#__vikbooking_optionals');
									$chvar = '';
									if (!empty($actopt[0]['ageintervals']) && $or['children'] > 0 && strstr($stept[1], '-') != false) {
										$optagecosts = VikBooking::getOptionIntervalsCosts($actopt[0]['ageintervals']);
										$optagenames = VikBooking::getOptionIntervalsAges($actopt[0]['ageintervals']);
										$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt[0]['ageintervals']);
										$agestept = explode('-', $stept[1]);
										$stept[1] = $agestept[0];
										$chvar = $agestept[1];
										if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 1) {
											//percentage value of the adults tariff
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = !empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost'];
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										} elseif (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] == 2) {
											//VBO 1.10 - percentage value of room base cost
											if ($is_package === true || (!empty($or['cust_cost']) && $or['cust_cost'] > 0.00)) {
												$optagecosts[($chvar - 1)] = $or['cust_cost'] * $optagecosts[($chvar - 1)] / 100;
											} else {
												$display_rate = isset($tars[$num]['room_base_cost']) ? $tars[$num]['room_base_cost'] : (!empty($or['room_cost']) ? $or['room_cost'] : $tars[$num]['cost']);
												$optagecosts[($chvar - 1)] = $display_rate * $optagecosts[($chvar - 1)] / 100;
											}
										}
										$actopt[0]['chageintv'] = $chvar;
										$actopt[0]['name'] .= ' ('.$optagenames[($chvar - 1)].')';
										$actopt[0]['quan'] = $stept[1];
										$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
									} else {
										$actopt[0]['quan'] = $stept[1];
										$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
									}
									if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									if ($actopt[0]['perperson'] == 1) {
										$realcost = $realcost * $or['adults'];
									}
									$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $actopt[0]['idiva']);
									$isdue += $tmpopr;
									$optstr[$num][] = ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ": " . $tmpopr . " " . $currencyname . "\n";
								}
							}
						}
					}
					//custom extra costs
					if (!empty($or['extracosts'])) {
						$cur_extra_costs = json_decode($or['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
							$isdue += $ecplustax;
							$optstr[$num][] = $ecv['name'] . ": " . $ecplustax . " " . $currencyname."\n";
						}
					}
					//
				}
				//vikbooking 1.1 coupon
				$usedcoupon = false;
				$origisdue = $isdue;
				if (strlen($order[0]['coupon']) > 0) {
					$usedcoupon = true;
					$expcoupon = explode(";", $order[0]['coupon']);
					$isdue = $isdue - $expcoupon[1];
				}
				//
				//ConfirmationNumber
				$confirmnumber = VikBooking::generateConfirmNumber($order[0]['id'], true);
				//end ConfirmationNumber
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VBORDERSETASCONF'));
				VikBooking::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VBRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $rooms, $order[0]['checkin'], $order[0]['checkout'], $pricestr, $optstr, $isdue, $viklink, JText::_('VBCOMPLETED'), $order[0]['id'], $order[0]['coupon'], $arrpeople, $confirmnumber);
				//SMS skipping the administrator
				VikBooking::sendBookingSMS($order[0]['id'], array('admin'));
				//
				//Invoke Channel Manager
				$vcm_autosync = VikBooking::vcmAutoUpdate();
				if ($vcm_autosync > 0) {
					$vcm_obj = VikBooking::getVcmInvoker();
					$vcm_obj->setOids(array($order[0]['id']))->setSyncType('new');
					$sync_result = $vcm_obj->doSync();
					if ($sync_result === false) {
						$vcm_err = $vcm_obj->getError();
						VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
					}
				} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
					$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=new&cid[]='.$order[0]['id'].'&returl='.urlencode('index.php?option=com_vikbooking&task=editorder&cid[]='.$order[0]['id']);
					VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button>');
				}
				//
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$oid);
	}

	function payments() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'payments'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newpayment() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editpayment() {
		VikBookingHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createpayment() {
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$phidenonrefund = VikRequest::getInt('hidenonrefund', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikbooking_gpayments` WHERE `file`=".$dbo->quote($ppayment).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "INSERT INTO `#__vikbooking_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`,`params`,`hidenonrefund`) VALUES(".$dbo->quote($pname).",".$dbo->quote($ppayment).",'".$setpub."',".$dbo->quote($pnote).",".$dbo->quote($pcharge).",'".$psetconfirmed."','".$pshownotealw."','".$pval_pcent."','".$pch_disc."',".$dbo->quote($payparamstr).",".($phidenonrefund > 0 ? '1' : '0').");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VBPAYMENTSAVED'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newpayment");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=newpayment");
		}
	}

	function updatepayment() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getFloat('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$phidenonrefund = VikRequest::getInt('hidenonrefund', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWRAW);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment) && !empty($pwhere)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikbooking_gpayments` WHERE `file`=".$dbo->quote($ppayment)." AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "UPDATE `#__vikbooking_gpayments` SET `name`=".$dbo->quote($pname).",`file`=".$dbo->quote($ppayment).",`published`='".$setpub."',`note`=".$dbo->quote($pnote).",`charge`=".$dbo->quote($pcharge).",`setconfirmed`='".$psetconfirmed."',`shownotealw`='".$pshownotealw."',`val_pcent`='".$pval_pcent."',`ch_disc`='".$pch_disc."',`params`=".$dbo->quote($payparamstr).",`hidenonrefund`=".($phidenonrefund > 0 ? '1' : '0')." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VBPAYMENTUPDATED'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editpayment&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editpayment&cid[]=".$pwhere);
		}
	}

	function removepayments() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_gpayments` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	function modavailpayment() {
		$cid = VikRequest::getVar('cid', array(0));
		$idp = $cid[0];
		if (!empty($idp)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `published` FROM `#__vikbooking_gpayments` WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikbooking_gpayments` SET `published`=".(intval($get[0]['published']) == 1 ? '0' : '1')." WHERE `id`=".intval($idp).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	function seasons() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'seasons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newseason() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editseason() {
		VikBookingHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function updateseason() {
		$this->do_updateseason();
	}

	function updateseasonstay() {
		$this->do_updateseason(true);
	}

	private function do_updateseason($stay = false) {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$pcheckinincl = VikRequest::getString('checkinincl', '', 'request');
		$pcheckinincl = $pcheckinincl == 1 ? 1 : 0;
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromo = $ppromo == 1 ? 1 : 0;
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array());
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array());
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array());
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array());
		$occupancy_ovr = array();
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$roomstr="";
			$roomids = array();
			if (@count($pidrooms) > 0) {
				foreach ($pidrooms as $room) {
					if (empty($room)) {
						continue;
					}
					$roomstr.="-".$room."-,";
					$roomids[] = (int)$room;
				}
			}
			$pricestr="";
			$priceids = array();
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
					$priceids[] = (int)$price;
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
				$second = VikBooking::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
						}
					}
					if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
						if ($basetwo[0] >= $leapts) {
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					$losverridestr = "";
					if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
						foreach ($pnightsoverrides as $ko => $no) {
							if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
								$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
								$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
							}
						}
					}
					//Occupancy Override
					if (count($padultsdiffval) > 0) {
						foreach ($padultsdiffval as $rid => $valovr_arr) {
							if (!is_array($valovr_arr) || !is_array($padultsdiffchdisc[$rid]) || !is_array($padultsdiffvalpcent[$rid]) || !is_array($padultsdiffpernight[$rid])) {
								continue;
							}
							foreach ($valovr_arr as $occ => $valovr) {
								if (!(strlen($valovr) > 0) || !(strlen($padultsdiffchdisc[$rid][$occ]) > 0) || !(strlen($padultsdiffvalpcent[$rid][$occ]) > 0) || !(strlen($padultsdiffpernight[$rid][$occ]) > 0)) {
									continue;
								}
								if (!array_key_exists($rid, $occupancy_ovr)) {
									$occupancy_ovr[$rid] = array();
								}
								$occupancy_ovr[$rid][$occ] = array('chdisc' => (int)$padultsdiffchdisc[$rid][$occ], 'valpcent' => (int)$padultsdiffvalpcent[$rid][$occ], 'pernight' => (int)$padultsdiffpernight[$rid][$occ], 'value' => (float)$valovr);
							}
						}
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
				}
			}
			if ($valid) {
				$q = "UPDATE `#__vikbooking_seasons` SET `type`='".($ptype == "1" ? "1" : "2")."',`from`=".$dbo->quote($sfrom).",`to`=".$dbo->quote($sto).",`diffcost`=".$dbo->quote($pdiffcost).",`idrooms`=".$dbo->quote($roomstr).",`spname`=".$dbo->quote($pspname).",`wdays`='".$wdaystr."',`checkinincl`='".$pcheckinincl."',`val_pcent`='".$pval_pcent."',`losoverride`=".$dbo->quote($losverridestr).",`roundmode`=".(!empty($proundmode) ? "'".$proundmode."'" : "NULL").",`year`=".($pyeartied == 1 ? $tieyear : "NULL").",`idprices`=".$dbo->quote($pricestr).",`promo`=".$ppromo.",`promodaysadv`=".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").",`promotxt`=".$dbo->quote($ppromotxt).",`promominlos`=".(!empty($ppromominlos) ? $ppromominlos : "0").",`occupancy_ovr`=".(count($occupancy_ovr) > 0 ? $dbo->quote(json_encode($occupancy_ovr)) : "NULL")." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VBSEASONUPDATED'));
				//update session values
				$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
				if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
					$updforvcm['dfrom'] = $updforvcm['dfrom'] > $first ? $first : $updforvcm['dfrom'];
				} else {
					$updforvcm['dfrom'] = $first;
				}
				if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
					$updforvcm['dto'] = $updforvcm['dto'] < $second ? $second : $updforvcm['dto'];
				} else {
					$updforvcm['dto'] = $second;
				}
				if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
					foreach ($roomids as $rid) {
						if (!in_array($rid, $updforvcm['rooms'])) {
							$updforvcm['rooms'][] = $rid;
						}
					}
				} else {
					$updforvcm['rooms'] = $roomids;
				}
				if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
					foreach ($roomids as $rid) {
						if (array_key_exists($rid, $updforvcm['rplans'])) {
							$updforvcm['rplans'][$rid] = $updforvcm['rplans'][$rid] + $priceids;
						} else {
							$updforvcm['rplans'][$rid] = $priceids;
						}
					}
				} else {
					$updforvcm['rplans'] = array();
					foreach ($roomids as $rid) {
						$updforvcm['rplans'][$rid] = $priceids;
					}
				}
				$session->set('vbVcmRatesUpd', $updforvcm);
				//
				if ($stay) {
					$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
				} else {
					$mainframe->redirect("index.php?option=com_vikbooking&task=seasons");
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATEROOMSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editseason&cid[]=".$pwhere);
		}
	}

	function createseason() {
		$this->do_createseason();
	}

	function createseason_new() {
		$this->do_createseason(true);
	}

	private function do_createseason($andnew = false) {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getFloat('diffcost', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$pcheckinincl = VikRequest::getString('checkinincl', '', 'request');
		$pcheckinincl = $pcheckinincl == 1 ? 1 : 0;
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromominlos = VikRequest::getInt('promominlos', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$padultsdiffchdisc = VikRequest::getVar('adultsdiffchdisc', array());
		$padultsdiffval = VikRequest::getVar('adultsdiffval', array());
		$padultsdiffvalpcent = VikRequest::getVar('adultsdiffvalpcent', array());
		$padultsdiffpernight = VikRequest::getVar('adultsdiffpernight', array());
		$occupancy_ovr = array();
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$roomstr="";
			$roomids = array();
			if (@count($pidrooms) > 0) {
				foreach ($pidrooms as $room) {
					if (empty($room)) {
						continue;
					}
					$roomstr.="-".$room."-,";
					$roomids[] = (int)$room;
				}
			}
			$pricestr="";
			$priceids = array();
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
					$priceids[] = (int)$price;
				}
			}
			$valid = true;
			$double_records = array();
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first=VikBooking::getDateTimestamp($pfrom, 0, 0);
				$second=VikBooking::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone=getdate($first);
					$basets=mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom=$baseone[0] - $basets;
					$basetwo=getdate($second);
					$basets=mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto=$basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					$losverridestr = "";
					if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
						foreach ($pnightsoverrides as $ko => $no) {
							if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
								$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
								$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
							}
						}
					}
					//Occupancy Override
					if (count($padultsdiffval) > 0) {
						foreach ($padultsdiffval as $rid => $valovr_arr) {
							if (!is_array($valovr_arr) || !is_array($padultsdiffchdisc[$rid]) || !is_array($padultsdiffvalpcent[$rid]) || !is_array($padultsdiffpernight[$rid])) {
								continue;
							}
							foreach ($valovr_arr as $occ => $valovr) {
								if (!(strlen($valovr) > 0) || !(strlen($padultsdiffchdisc[$rid][$occ]) > 0) || !(strlen($padultsdiffvalpcent[$rid][$occ]) > 0) || !(strlen($padultsdiffpernight[$rid][$occ]) > 0)) {
									continue;
								}
								if (!array_key_exists($rid, $occupancy_ovr)) {
									$occupancy_ovr[$rid] = array();
								}
								$occupancy_ovr[$rid][$occ] = array('chdisc' => (int)$padultsdiffchdisc[$rid][$occ], 'valpcent' => (int)$padultsdiffvalpcent[$rid][$occ], 'pernight' => (int)$padultsdiffpernight[$rid][$occ], 'value' => (float)$valovr);
							}
						}
					}
					//
					//check if seasons dates are valid
					//VikBooking 1.6, clause `to`>=".$dbo->quote($sfrom)" was changed to `to`>".$dbo->quote($sfrom) to avoid issues with rates for leap years when not tied to the year and entered the year before the leap
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>".$dbo->quote($sfrom)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond = $dbo->getNumRows();
					if ($totsecond > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					$q = "SELECT `id`,`spname` FROM `#__vikbooking_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `idrooms`=".$dbo->quote($roomstr)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr)." AND `promo`=".$ppromo." AND `losoverride`=".$dbo->quote($losverridestr)." AND `occupancy_ovr`".(count($occupancy_ovr) > 0 ? "=".$dbo->quote(json_encode($occupancy_ovr)) : " IS NULL").";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid = false;
						$similar = $dbo->loadAssocList();
						foreach ($similar as $sim) {
							$double_records[] = $sim['spname'];
						}
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
				}
			}
			if ($valid) {
				$q = "INSERT INTO `#__vikbooking_seasons` (`type`,`from`,`to`,`diffcost`,`idrooms`,`spname`,`wdays`,`checkinincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`,`promominlos`,`occupancy_ovr`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($roomstr).", ".$dbo->quote($pspname).", ".$dbo->quote($wdaystr).", '".$pcheckinincl."', '".$pval_pcent."', ".$dbo->quote($losverridestr).", ".(!empty($proundmode) ? "'".$proundmode."'" : "NULL").", ".($pyeartied == 1 ? $tieyear : "NULL").", ".$dbo->quote($pricestr).", ".($ppromo == 1 ? '1' : '0').", ".(!empty($ppromodaysadv) ? $ppromodaysadv : "NULL").", ".$dbo->quote($ppromotxt).", ".(!empty($ppromominlos) ? $ppromominlos : "0").", ".(count($occupancy_ovr) ? $dbo->quote(json_encode($occupancy_ovr)) : "NULL").");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VBSEASONSAVED'));
				//update session values
				$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
				if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
					$updforvcm['dfrom'] = $updforvcm['dfrom'] > $first ? $first : $updforvcm['dfrom'];
				} else {
					$updforvcm['dfrom'] = $first;
				}
				if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
					$updforvcm['dto'] = $updforvcm['dto'] < $second ? $second : $updforvcm['dto'];
				} else {
					$updforvcm['dto'] = $second;
				}
				if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
					foreach ($roomids as $rid) {
						if (!in_array($rid, $updforvcm['rooms'])) {
							$updforvcm['rooms'][] = $rid;
						}
					}
				} else {
					$updforvcm['rooms'] = $roomids;
				}
				if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
					foreach ($roomids as $rid) {
						if (array_key_exists($rid, $updforvcm['rplans'])) {
							$updforvcm['rplans'][$rid] = $updforvcm['rplans'][$rid] + $priceids;
						} else {
							$updforvcm['rplans'][$rid] = $priceids;
						}
					}
				} else {
					$updforvcm['rplans'] = array();
					foreach ($roomids as $rid) {
						$updforvcm['rplans'][$rid] = $priceids;
					}
				}
				$session->set('vbVcmRatesUpd', $updforvcm);
				//
				$mainframe->redirect("index.php?option=com_vikbooking&task=".($andnew ? 'newseason' : 'seasons'));
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATEROOMSLOCSEASON').(count($double_records) > 0 ? ' ('.implode(', ', $double_records).')' : ''));
				$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=newseason");
		}
	}

	function removeseasons () {
		$ids = VikRequest::getVar('cid', array(0));
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		if (!empty($pwhere)) {
			$ids[] = $pwhere;
		}
		$tot_removed = 0;
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				if (empty($d)) {
					continue;
				}
				$q = "DELETE FROM `#__vikbooking_seasons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$tot_removed++;
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBRECORDSREMOVED', $tot_removed));
		$mainframe->redirect("index.php?option=com_vikbooking&task=seasons".(!empty($pidroom) ? '&idroom='.$pidroom : ''));
	}

	function updatecustomer() {
		$this->do_updatecustomer();
	}

	function updatecustomerstay() {
		$this->do_updatecustomer(true);
	}

	private function do_updatecustomer($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pcheckin = VikRequest::getInt('checkin', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pwhere) && !empty($pfirst_name) && !empty($plast_name)) {
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
				exit;
			}
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `email`=".$dbo->quote($pemail)." AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$cpin = VikBooking::getCPinIstance();
				if (empty($ppin)) {
					$ppin = $customer['pin'];
				} elseif ($cpin->pinExists($ppin, $customer['pin'])) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$pischannel = $pischannel > 0 ? 1 : 0;
				$pcalccmmon = $pcalccmmon > 0 ? 1 : 0;
				$papplycmmon = $papplycmmon > 0 ? 1 : 0;
				$pchname = str_replace(' ', '', trim($pchname));
				$pchname = strlen($pchname) <= 0 && $pischannel > 0 ? str_replace(' ', '', trim($pfirst_name.' '.$plast_name)) : $pchname;
				$chparams = array(
					'commission' => ($pcommission > 0.00 ? $pcommission : 0),
					'calccmmon' => $pcalccmmon,
					'applycmmon' => $papplycmmon,
					'chcolor' => $pchcolor,
					'chname' => $pchname
				);
				$q = "UPDATE `#__vikbooking_customers` SET `first_name`=".$dbo->quote($pfirst_name).",`last_name`=".$dbo->quote($plast_name).",`email`=".$dbo->quote($pemail).",`phone`=".$dbo->quote($pphone).",`country`=".$dbo->quote($pcountry).",`pin`=".$dbo->quote($ppin).",`ujid`=".$dbo->quote($pujid).",`address`=".$dbo->quote($paddress).",`city`=".$dbo->quote($pcity).",`zip`=".$dbo->quote($pzip).",`doctype`=".$dbo->quote($pdoctype).",`docnum`=".$dbo->quote($pdocnum).(!empty($gimg) ? ",`docimg`=".$dbo->quote($gimg) : "").",`notes`=".$dbo->quote($pnotes).",`ischannel`=".$pischannel.",`chdata`=".$dbo->quote(json_encode($chparams)).",`company`=".$dbo->quote($pcompany).",`vat`=".$dbo->quote($pvat).",`gender`=".$dbo->quote($pgender).",`bdate`=".$dbo->quote($pbdate).",`pbirth`=".$dbo->quote($ppbirth)." WHERE `id`=".(int)$pwhere.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$cpin->pluginCustomerSync($pwhere, 'update');
				//Update all the bookings affected by this Customer ID as a sales channel
				$source_name = 'customer'.$pwhere.'_'.$pchname;
				if ($pischannel > 0) {
					$oid_clause = '';
					if ($customer['ischannel'] < 1) {
						//Was not a sales channel but now it is, so update all his bookings
						$q = "SELECT `idorder` FROM `#__vikbooking_customers_orders` WHERE `idcustomer`=".$customer['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$all_bids = $dbo->loadAssocList();
							$bids = array();
							foreach ($all_bids as $bid) {
								if (!in_array($bid['idorder'], $bids)) {
									$bids[] = $bid['idorder'];
								}
							}
							$oid_clause = " OR `id` IN (".implode(',', $bids).")";
						}
					}
					$q = "UPDATE `#__vikbooking_orders` SET `channel`=".$dbo->quote($source_name)." WHERE `channel` LIKE 'customer".$pwhere."%'".$oid_clause.";";
				} else {
					$q = "UPDATE `#__vikbooking_orders` SET `channel`=NULL,`cmms`=NULL WHERE `channel` LIKE 'customer".$pwhere."%';";
				}
				$dbo->setQuery($q);
				$dbo->execute();
				//
				$mainframe->enqueueMessage(JText::_('VBCUSTOMERSAVED'));
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				//check if coming from the Check-in view or not
				if (!empty($pcheckin) && !empty($pbid)) {
					VikError::raiseWarning('', JText::_('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect("index.php?option=com_vikbooking&task=bookingcheckin&cid[]=".$pbid.($ptmpl == 'component' ? '&tmpl=component' : ''));
					exit;
				} else {
					VikError::raiseWarning('', JText::_('VBERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
					$mainframe->redirect("index.php?option=com_vikbooking&task=editcustomer&cid[]=".$pwhere);
					exit;
				}
			}
		}
		//check if coming from the Check-in view
		if (!empty($pcheckin) && !empty($pbid)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=bookingcheckin&cid[]=".$pbid.($ptmpl == 'component' ? '&tmpl=component' : ''));
			exit;
		}
		//
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=editcustomer&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
		}
	}

	function savecustomer() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pcheckin = VikRequest::getInt('checkin', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pfirst_name) && !empty($plast_name)) {
			$cpin = VikBooking::getCPinIstance();
			$q = "SELECT * FROM `#__vikbooking_customers` WHERE `email`=".$dbo->quote($pemail)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				if (empty($ppin)) {
					$ppin = $cpin->generateUniquePin();
				} elseif ($cpin->pinExists($ppin)) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$pischannel = $pischannel > 0 ? 1 : 0;
				$pcalccmmon = $pcalccmmon > 0 ? 1 : 0;
				$papplycmmon = $papplycmmon > 0 ? 1 : 0;
				$pchname = str_replace(' ', '', trim($pchname));
				$pchname = strlen($pchname) <= 0 && $pischannel > 0 ? str_replace(' ', '', trim($pfirst_name.' '.$plast_name)) : $pchname;
				$chparams = array(
					'commission' => ($pcommission > 0.00 ? $pcommission : 0),
					'calccmmon' => $pcalccmmon,
					'applycmmon' => $papplycmmon,
					'chcolor' => $pchcolor,
					'chname' => $pchname
				);
				$q = "INSERT INTO `#__vikbooking_customers` (`first_name`,`last_name`,`email`,`phone`,`country`,`pin`,`ujid`,`address`,`city`,`zip`,`doctype`,`docnum`,`docimg`,`notes`,`ischannel`,`chdata`,`company`,`vat`,`gender`,`bdate`,`pbirth`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcountry).", ".$dbo->quote($ppin).", ".$dbo->quote($pujid).", ".$dbo->quote($paddress).", ".$dbo->quote($pcity).", ".$dbo->quote($pzip).", ".$dbo->quote($pdoctype).", ".$dbo->quote($pdocnum).", ".$dbo->quote($gimg).", ".$dbo->quote($pnotes).", ".$pischannel.", ".$dbo->quote(json_encode($chparams)).", ".$dbo->quote($pcompany).", ".$dbo->quote($pvat).", ".$dbo->quote($pgender).", ".$dbo->quote($pbdate).", ".$dbo->quote($ppbirth).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				$cpin->pluginCustomerSync($lid, 'insert');
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::_('VBCUSTOMERSAVED'));
					//check if coming from the Check-in view
					if (!empty($pcheckin) && !empty($pbid)) {
						$cpin->setNewPin($ppin);
						$cpin->setNewCustomerId($lid);
						$cpin->saveCustomerBooking($pbid);
						$mainframe->redirect("index.php?option=com_vikbooking&task=bookingcheckin&cid[]=".$pbid.($ptmpl == 'component' ? '&tmpl=component' : ''));
						exit;
					}
				}
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				//check if coming from the Check-in view or not
				if (!empty($pcheckin) && !empty($pbid)) {
					$cpin->setNewPin($ex_customer['pin']);
					$cpin->setNewCustomerId($ex_customer['id']);
					$cpin->saveCustomerBooking($pbid);
					VikError::raiseWarning('', JText::_('VBERRCUSTOMEREMAILEXISTS').' ('.$ex_customer['first_name'].' '.$ex_customer['last_name'].')');
					$mainframe->redirect("index.php?option=com_vikbooking&task=bookingcheckin&cid[]=".$pbid.($ptmpl == 'component' ? '&tmpl=component' : ''));
					exit;
				} else {
					VikError::raiseWarning('', JText::_('VBERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikbooking&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
				}
			}
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	function customers() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customers'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcustomer() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcustomer() {
		VikBookingHelper::printHeader("22");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function removecustomers() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			$cpin = VikBooking::getCPinIstance();
			foreach ($ids as $d) {
				$cpin->pluginCustomerSync($d, 'delete');
				$q = "DELETE FROM `#__vikbooking_customers` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	function restrictions() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'restrictions'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newrestriction() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editrestriction() {
		VikBookingHelper::printHeader("restrictions");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerestriction'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createrestriction() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallrooms = VikRequest::getString('allrooms', '', 'request');
		$pallrooms = $pallrooms == "1" ? 1 : 0;
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ridr = '';
		$roomidsforsess = array();
		if (!empty($pidrooms) && @count($pidrooms) && $pallrooms == 0) {
			foreach ($pidrooms as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$roomidsforsess[] = (int)$idr;
			}
		} elseif ($pallrooms > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$roomidsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd) && $pmaxlos < 1) {
			VikError::raiseWarning('', JText::_('VBUSELESSRESTRICTION'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikbooking_restrictions` WHERE `month`='".$pmonth."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VBRESTRICTIONMONTHEXISTS'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VBRESTRICTIONERRDRANGE'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
				} else {
					$pdfrom = VikBooking::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikBooking::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			//update session values
			if (!($pdfrom > 0)) {
				$attemptyear = (int)date('Y');
				$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
				if ($attemptfrom < time()) {
					$attemptyear++;
					$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
				}
				$attemptto = mktime(0, 0, 0, $pmonth, date('t', $attemptfrom), $attemptyear);
			} else {
				$attemptfrom = $pdfrom;
				$attemptto = $pdto;
			}
			$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
			if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
				$updforvcm['dfrom'] = $updforvcm['dfrom'] > $attemptfrom ? $attemptfrom : $updforvcm['dfrom'];
			} else {
				$updforvcm['dfrom'] = $attemptfrom;
			}
			if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
				$updforvcm['dto'] = $updforvcm['dto'] < $attemptto ? $attemptto : $updforvcm['dto'];
			} else {
				$updforvcm['dto'] = $attemptto;
			}
			if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
				foreach ($roomidsforsess as $rid) {
					if (!in_array($rid, $updforvcm['rooms'])) {
						$updforvcm['rooms'][] = $rid;
					}
				}
			} else {
				$updforvcm['rooms'] = $roomidsforsess;
			}
			if (!array_key_exists('rplans', $updforvcm) || !is_array($updforvcm['rplans'])) {
				$updforvcm['rplans'] = array();
			}
			$session->set('vbVcmRatesUpd', $updforvcm);
			//
			$q = "INSERT INTO `#__vikbooking_restrictions` (`name`,`month`,`wday`,`minlos`,`multiplyminlos`,`maxlos`,`dfrom`,`dto`,`wdaytwo`,`wdaycombo`,`allrooms`,`idrooms`,`ctad`,`ctdd`) VALUES(".$dbo->quote($pname).", '".$pmonth."', ".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").", '".$pminlos."', '".$pmultiplyminlos."', '".$pmaxlos."', ".$pdfrom.", ".$pdto.", ".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").", ".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").", ".$pallrooms.", ".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", ".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", ".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL").");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VBRESTRICTIONSAVED'));
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
			} else {
				VikError::raiseWarning('', 'Error while saving');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikbooking&task=newrestriction");
			}
		}
	}

	function updaterestriction() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pwhere = VikRequest::getInt('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$pmonth = VikRequest::getInt('month', '', 'request');
		$pmonth = empty($pmonth) ? 0 : $pmonth;
		$pname = empty($pname) ? 'Restriction '.$pmonth : $pname;
		$pdfrom = VikRequest::getString('dfrom', '', 'request');
		$pdto = VikRequest::getString('dto', '', 'request');
		$pwday = VikRequest::getString('wday', '', 'request');
		$pwdaytwo = VikRequest::getString('wdaytwo', '', 'request');
		$pwdaytwo = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday == $pwdaytwo ? '' : $pwdaytwo;
		$pcomboa = VikRequest::getString('comboa', '', 'request');
		$pcomboa = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboa : '';
		$pcombob = VikRequest::getString('combob', '', 'request');
		$pcombob = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombob : '';
		$pcomboc = VikRequest::getString('comboc', '', 'request');
		$pcomboc = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcomboc : '';
		$pcombod = VikRequest::getString('combod', '', 'request');
		$pcombod = strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo ? $pcombod : '';
		$combostr = '';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboa) ? $pcomboa.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombob) ? $pcombob.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcomboc) ? $pcomboc.':' : ':';
		$combostr .= strlen($pwday) > 0 && strlen($pwdaytwo) > 0 && $pwday != $pwdaytwo && !empty($pcombod) ? $pcombod : '';
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pmaxlos = VikRequest::getInt('maxlos', '', 'request');
		$pmaxlos = empty($pmaxlos) ? 0 : $pmaxlos;
		$pmultiplyminlos = VikRequest::getString('multiplyminlos', '', 'request');
		$pmultiplyminlos = empty($pmultiplyminlos) ? 0 : 1;
		$pallrooms = VikRequest::getString('allrooms', '', 'request');
		$pallrooms = $pallrooms == "1" ? 1 : 0;
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ridr = '';
		$roomidsforsess = array();
		if (!empty($pidrooms) && @count($pidrooms) && $pallrooms == 0) {
			foreach ($pidrooms as $idr) {
				if (empty($idr)) {
					continue;
				}
				$ridr .= '-'.$idr.'-;';
				$roomidsforsess[] = (int)$idr;
			}
		} elseif ($pallrooms > 0) {
			$q = "SELECT `id` FROM `#__vikbooking_rooms`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetchids = $dbo->loadAssocList();
				foreach ($fetchids as $fetchid) {
					$roomidsforsess[] = (int)$fetchid['id'];
				}
			}
		}
		$pcta = VikRequest::getInt('cta', '', 'request');
		$pctd = VikRequest::getInt('ctd', '', 'request');
		$pctad = VikRequest::getVar('ctad', array());
		$pctdd = VikRequest::getVar('ctdd', array());
		if ($pminlos == 1 && strlen($pwday) == 0 && empty($pctad) && empty($pctdd) && $pmaxlos < 1) {
			VikError::raiseWarning('', JText::_('VBUSELESSRESTRICTION'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
		} else {
			//check if there are restrictions for this month
			if ($pmonth > 0) {
				$q = "SELECT `id` FROM `#__vikbooking_restrictions` WHERE `month`='".$pmonth."' AND `id`!='".$pwhere."';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					VikError::raiseWarning('', JText::_('VBRESTRICTIONMONTHEXISTS'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
				}
				$pdfrom = 0;
				$pdto = 0;
			} else {
				//dates range
				if (empty($pdfrom) || empty($pdto)) {
					VikError::raiseWarning('', JText::_('VBRESTRICTIONERRDRANGE'));
					$mainframe = JFactory::getApplication();
					$mainframe->redirect("index.php?option=com_vikbooking&task=editrestriction&cid[]=".$pwhere);
				} else {
					$pdfrom = VikBooking::getDateTimestamp($pdfrom, 0, 0);
					$pdto = VikBooking::getDateTimestamp($pdto, 0, 0);
				}
			}
			//CTA and CTD
			$setcta = array();
			$setctd = array();
			if ($pcta > 0 && count($pctad) > 0) {
				foreach ($pctad as $ctwd) {
					if (strlen($ctwd)) {
						$setcta[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			if ($pctd > 0 && count($pctdd) > 0) {
				foreach ($pctdd as $ctwd) {
					if (strlen($ctwd)) {
						$setctd[] = '-'.(int)$ctwd.'-';
					}
				}
			}
			//
			//update session values
			if (!($pdfrom > 0)) {
				$attemptyear = (int)date('Y');
				$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
				if ($attemptfrom < time()) {
					$attemptyear++;
					$attemptfrom = mktime(0, 0, 0, $pmonth, 1, $attemptyear);
				}
				$attemptto = mktime(0, 0, 0, $pmonth, date('t', $attemptfrom), $attemptyear);
			} else {
				$attemptfrom = $pdfrom;
				$attemptto = $pdto;
			}
			$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
			if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
				$updforvcm['dfrom'] = $updforvcm['dfrom'] > $attemptfrom ? $attemptfrom : $updforvcm['dfrom'];
			} else {
				$updforvcm['dfrom'] = $attemptfrom;
			}
			if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
				$updforvcm['dto'] = $updforvcm['dto'] < $attemptto ? $attemptto : $updforvcm['dto'];
			} else {
				$updforvcm['dto'] = $attemptto;
			}
			if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
				foreach ($roomidsforsess as $rid) {
					if (!in_array($rid, $updforvcm['rooms'])) {
						$updforvcm['rooms'][] = $rid;
					}
				}
			} else {
				$updforvcm['rooms'] = $roomidsforsess;
			}
			if (!array_key_exists('rplans', $updforvcm) || !is_array($updforvcm['rplans'])) {
				$updforvcm['rplans'] = array();
			}
			$session->set('vbVcmRatesUpd', $updforvcm);
			//
			$q = "UPDATE `#__vikbooking_restrictions` SET `name`=".$dbo->quote($pname).",`month`='".$pmonth."',`wday`=".(strlen($pwday) > 0 ? "'".$pwday."'" : "NULL").",`minlos`='".$pminlos."',`multiplyminlos`='".$pmultiplyminlos."',`maxlos`='".$pmaxlos."',`dfrom`=".$pdfrom.",`dto`=".$pdto.",`wdaytwo`=".(strlen($pwday) > 0 && strlen($pwdaytwo) > 0 ? intval($pwdaytwo) : "NULL").",`wdaycombo`=".(strlen($combostr) > 0 ? $dbo->quote($combostr) : "NULL").",`allrooms`=".$pallrooms.",`idrooms`=".(strlen($ridr) > 0 ? $dbo->quote($ridr) : "NULL").", `ctad`=".(count($setcta) > 0 ? $dbo->quote(implode(',', $setcta)) : "NULL").", `ctdd`=".(count($setctd) > 0 ? $dbo->quote(implode(',', $setctd)) : "NULL")." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VBRESTRICTIONSAVED'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
		}
	}

	function removerestrictions() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_restrictions` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
	}

	function prices() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'prices'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newprice() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editprice() {
		VikBookingHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getInt('praliq', '', 'request');
		$pbreakfast_included = VikRequest::getInt('breakfast_included', '', 'request');
		$pbreakfast_included = $pbreakfast_included == 1 ? 1 : 0;
		$pfree_cancellation = VikRequest::getInt('free_cancellation', '', 'request');
		$pfree_cancellation = $pfree_cancellation == 1 ? 1 : 0;
		$pcanc_deadline = VikRequest::getInt('canc_deadline', '', 'request');
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pminhadv = VikRequest::getInt('minhadv', '', 'request');
		$pminhadv = $pminhadv < 0 ? 0 : $pminhadv;
		$pcanc_policy = VikRequest::getString('canc_policy', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_prices` (`name`,`attr`,`idiva`,`breakfast_included`,`free_cancellation`,`canc_deadline`,`canc_policy`,`minlos`,`minhadv`) VALUES(".$dbo->quote($pprice).", ".$dbo->quote($pattr).", ".$dbo->quote($ppraliq).", ".$pbreakfast_included.", ".$pfree_cancellation.", ".$pcanc_deadline.", ".$dbo->quote($pcanc_policy).", ".$pminlos.", ".$pminhadv.");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	function updateprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getInt('praliq', '', 'request');
		$pbreakfast_included = VikRequest::getInt('breakfast_included', '', 'request');
		$pbreakfast_included = $pbreakfast_included == 1 ? 1 : 0;
		$pfree_cancellation = VikRequest::getInt('free_cancellation', '', 'request');
		$pfree_cancellation = $pfree_cancellation == 1 ? 1 : 0;
		$pcanc_deadline = VikRequest::getInt('canc_deadline', '', 'request');
		$pminlos = VikRequest::getInt('minlos', '', 'request');
		$pminlos = $pminlos < 1 ? 1 : $pminlos;
		$pminhadv = VikRequest::getInt('minhadv', '', 'request');
		$pminhadv = $pminhadv < 0 ? 0 : $pminhadv;
		$pcanc_policy = VikRequest::getString('canc_policy', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikbooking_prices` SET `name`=".$dbo->quote($pprice).",`attr`=".$dbo->quote($pattr).",`idiva`=".$dbo->quote($ppraliq).",`breakfast_included`=".$pbreakfast_included.",`free_cancellation`=".$pfree_cancellation.",`canc_deadline`=".$pcanc_deadline.",`canc_policy`=".$dbo->quote($pcanc_policy).",`minlos`=".$pminlos.",`minhadv`=".$pminhadv." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	function removeprice() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_prices` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikbooking_dispcost` WHERE `idprice`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	function iva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'iva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newiva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editiva() {
		VikBookingHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getFloat('aliqperc', '', 'request');
		$pbreakdown_name = VikRequest::getVar('breakdown_name', array());
		$pbreakdown_rate = VikRequest::getVar('breakdown_rate', array());
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$breakdown_str = '';
			if (count($pbreakdown_name) > 0) {
				$breakdown_values = array();
				$bkcount = 0;
				$tot_sub_aliq = 0;
				foreach ($pbreakdown_name as $key => $subtax) {
					if (!empty($subtax) && floatval($pbreakdown_rate[$key]) > 0) {
						$breakdown_values[$bkcount]['name'] = $subtax;
						$breakdown_values[$bkcount]['aliq'] = (float)$pbreakdown_rate[$key];
						$tot_sub_aliq += (float)$pbreakdown_rate[$key];
						$bkcount++;
					}
				}
				if (count($breakdown_values) > 0) {
					$breakdown_str = json_encode($breakdown_values);
					if ($tot_sub_aliq < (float)$paliqperc || $tot_sub_aliq > (float)$paliqperc) {
						VikError::raiseWarning('', JText::_('VBOTAXBKDWNERRNOMATCH'));
					}
				}
			}
			$q = "INSERT INTO `#__vikbooking_iva` (`name`,`aliq`,`breakdown`) VALUES(".$dbo->quote($paliqname).", ".$dbo->quote($paliqperc).", ".(empty($breakdown_str) ? 'NULL' : $dbo->quote($breakdown_str)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	function updateiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getFloat('aliqperc', '', 'request');
		$pbreakdown_name = VikRequest::getVar('breakdown_name', array());
		$pbreakdown_rate = VikRequest::getVar('breakdown_rate', array());
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$breakdown_str = '';
			if (count($pbreakdown_name) > 0) {
				$breakdown_values = array();
				$bkcount = 0;
				$tot_sub_aliq = 0;
				foreach ($pbreakdown_name as $key => $subtax) {
					if (!empty($subtax) && floatval($pbreakdown_rate[$key]) > 0) {
						$breakdown_values[$bkcount]['name'] = $subtax;
						$breakdown_values[$bkcount]['aliq'] = (float)$pbreakdown_rate[$key];
						$tot_sub_aliq += (float)$pbreakdown_rate[$key];
						$bkcount++;
					}
				}
				if (count($breakdown_values) > 0) {
					$breakdown_str = json_encode($breakdown_values);
					if ($tot_sub_aliq < (float)$paliqperc || $tot_sub_aliq > (float)$paliqperc) {
						VikError::raiseWarning('', JText::_('VBOTAXBKDWNERRNOMATCH'));
					}
				}
			}
			$q = "UPDATE `#__vikbooking_iva` SET `name`=".$dbo->quote($paliqname).",`aliq`=".$dbo->quote($paliqperc).",`breakdown`=".(empty($breakdown_str) ? 'NULL' : $dbo->quote($breakdown_str))." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	function removeiva() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_iva` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

	function categories() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'categories'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcat() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecategory'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcat() {
		VikBookingHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecategory'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createcat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_categories` (`name`,`descr`) VALUES(".$dbo->quote($pcatname).", ".$dbo->quote($pdescr).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	function updatecat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikbooking_categories` SET `name`=".$dbo->quote($pcatname).", `descr`=".$dbo->quote($pdescr)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	function removecat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_categories` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	function carat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'carat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcarat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcarat() {
		VikBookingHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createcarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikbooking_characteristics` (`name`,`icon`,`textimg`) VALUES(".$dbo->quote($pcaratname).", ".$dbo->quote($picon).", ".$dbo->quote($pcarattextimg).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	function updatecarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikbooking_characteristics` SET `name`=".$dbo->quote($pcaratname).",".(strlen($picon) > 0 ? "`icon`='".$picon."'," : "")."`textimg`=".$dbo->quote($pcarattextimg)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	function removecarat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `icon` FROM `#__vikbooking_characteristics` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['icon']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['icon'])) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['icon']);
					}
				}	
				$q = "DELETE FROM `#__vikbooking_characteristics` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	function coupons() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'coupons'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcoupon() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcoupon() {
		VikBookingHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createcoupon() {
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getString('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getFloat('mintotord', '', 'request');
		$stridrooms = "";
		if (@count($pidrooms) > 0 && $pallvehicles != 1) {
			foreach ($pidrooms as $ch) {
				if (!empty($ch)) {
					$stridrooms .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
			$second = VikBooking::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `code`=".$dbo->quote($pcode).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VBCOUPONEXISTS'));
		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VBCOUPONSAVEOK'));
			$q = "INSERT INTO `#__vikbooking_coupons` (`code`,`type`,`percentot`,`value`,`datevalid`,`allvehicles`,`idrooms`,`mintotord`) VALUES(".$dbo->quote($pcode).",'".$ptype."','".$ppercentot."',".$dbo->quote($pvalue).",'".$strdatevalid."','".$pallvehicles."','".$stridrooms."', ".$dbo->quote($pmintotord).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	function updatecoupon() {
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getString('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pidrooms = VikRequest::getVar('idrooms', array(0));
		$pwhere = VikRequest::getString('where', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getFloat('mintotord', '', 'request');
		$stridrooms = "";
		if (@count($pidrooms) > 0 && $pallvehicles != 1) {
			foreach ($pidrooms as $ch) {
				if (!empty($ch)) {
					$stridrooms .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikBooking::getDateTimestamp($pfrom, 0, 0);
			$second = VikBooking::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikbooking_coupons` WHERE `code`=".$dbo->quote($pcode)." AND `id`!='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VBCOUPONEXISTS'));
		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VBCOUPONSAVEOK'));
			$q = "UPDATE `#__vikbooking_coupons` SET `code`=".$dbo->quote($pcode).",`type`='".$ptype."',`percentot`='".$ppercentot."',`value`=".$dbo->quote($pvalue).",`datevalid`='".$strdatevalid."',`allvehicles`='".$pallvehicles."',`idrooms`='".$stridrooms."',`mintotord`=".$dbo->quote($pmintotord)." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	function removecoupons() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_coupons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	function removemoreimgs() {
		$mainframe = JFactory::getApplication();
		$proomid = VikRequest::getInt('roomid', '', 'request');
		$pimgind = VikRequest::getInt('imgind', '', 'request');
		if (strlen($pimgind) > 0) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `moreimgs` FROM `#__vikbooking_rooms` WHERE `id`='".$proomid."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$actmore=$dbo->loadResult();
			if (strlen($actmore) > 0) {
				$actsplit = explode(';;', $actmore);
				if ($pimgind < 0) {
					foreach ($actsplit as $img) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'big_'.$img);
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'thumb_'.$img);
					}
					$actsplit = array(0);
				} else {
					if (array_key_exists($pimgind, $actsplit)) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'big_'.$actsplit[$pimgind]);
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'thumb_'.$actsplit[$pimgind]);
						unset($actsplit[$pimgind]);
					}
				}
				$newstr="";
				foreach ($actsplit as $oi) {
					if (!empty($oi)) {
						$newstr.=$oi.';;';
					}
				}
				$q = "UPDATE `#__vikbooking_rooms` SET `moreimgs`=".$dbo->quote($newstr)." WHERE `id`='".$proomid."';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=editroom&cid[]=".$proomid);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	function sortfield() {
		$mainframe = JFactory::getApplication();
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikbooking_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking");
		}
	}

	function customf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newcustomf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editcustomf() {
		VikBookingHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function createcustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `ordering` FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$getlast = $dbo->loadResult();
			$newsortnum = $getlast + 1;
		} else {
			$newsortnum = 1;
		}
		$q = "INSERT INTO `#__vikbooking_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ptype).", ".$dbo->quote($choosestr).", ".$dbo->quote($prequired).", ".$dbo->quote($newsortnum).", ".$dbo->quote($pisemail).", ".$dbo->quote($ppoplink).", ".$pisnominative.", ".$pisphone.", ".$dbo->quote($fieldflag).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	function updatecustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "UPDATE `#__vikbooking_custfields` SET `name`=".$dbo->quote($pname).",`type`=".$dbo->quote($ptype).",`choose`=".$dbo->quote($choosestr).",`required`=".$dbo->quote($prequired).",`isemail`=".$dbo->quote($pisemail).",`poplink`=".$dbo->quote($ppoplink).",`isnominative`=".$pisnominative.",`isphone`=".$pisphone.",`flag`=".$dbo->quote($fieldflag)." WHERE `id`=".$dbo->quote($pwhere).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	function removecustomf() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikbooking_custfields` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	function overv() {
		VikBookingHelper::printHeader("15");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'overv'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function translations() {
		VikBookingHelper::printHeader("21");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'translations'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function savetranslation() {
		$this->do_savetranslation();
	}

	function savetranslationstay() {
		$this->do_savetranslation(true);
	}

	private function do_savetranslation($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$table = VikRequest::getString('vbo_table', '', 'request');
		$cur_langtab = VikRequest::getString('vbo_lang', '', 'request');
		$langs = $vbo_tn->getLanguagesList();
		$xml_tables = $vbo_tn->getTranslationTables();
		if (!empty($table) && array_key_exists($table, $xml_tables)) {
			$tn = VikRequest::getVar('tn', array(), 'request', 'array', VIKREQUEST_ALLOWRAW);
			$tn_saved = 0;
			$table_cols = $vbo_tn->getTableColumns($table);
			$table = $vbo_tn->replacePrefix($table);
			foreach ($langs as $ltag => $lang) {
				if ($ltag == $vbo_tn->default_lang) {
					continue;
				}
				if (array_key_exists($ltag, $tn) && count($tn[$ltag]) > 0) {
					foreach ($tn[$ltag] as $reference_id => $translation) {
						$lang_translation = array();
						foreach ($table_cols as $field => $fdetails) {
							if (!array_key_exists($field, $translation)) {
								continue;
							}
							$ftype = $fdetails['type'];
							if ($ftype == 'skip') {
								continue;
							}
							if ($ftype == 'json') {
								$translation[$field] = json_encode($translation[$field]);
							}
							$lang_translation[$field] = $translation[$field];
						}
						if (count($lang_translation) > 0) {
							$q = "SELECT `id` FROM `#__vikbooking_translations` WHERE `table`=".$dbo->quote($table)." AND `lang`=".$dbo->quote($ltag)." AND `reference_id`=".$dbo->quote((int)$reference_id).";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$last_id = $dbo->loadResult();
								$q = "UPDATE `#__vikbooking_translations` SET `content`=".$dbo->quote(json_encode($lang_translation))." WHERE `id`=".(int)$last_id.";";
							} else {
								$q = "INSERT INTO `#__vikbooking_translations` (`table`,`lang`,`reference_id`,`content`) VALUES (".$dbo->quote($table).", ".$dbo->quote($ltag).", ".$dbo->quote((int)$reference_id).", ".$dbo->quote(json_encode($lang_translation)).");";
							}
							$dbo->setQuery($q);
							$dbo->execute();
							$tn_saved++;
						}
					}
				}
			}
			if ($tn_saved > 0) {
				$mainframe->enqueueMessage(JText::_('VBOTRANSLSAVEDOK'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VBTRANSLATIONERRINVTABLE'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking".($stay ? '&task=translations&vbo_table='.$vbo_tn->replacePrefix($table).'&vbo_lang='.$cur_langtab : '').'&limitstart='.$vbo_tn->lim0.'&limit='.$vbo_tn->lim);
	}

	function choosebusy() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'choosebusy'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function orders() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'orders'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function vieworders() {
		//alias method of orders() for backward compatibility with VCM
		$this->orders();
	}

	function editorder() {
		VikBookingHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editorder'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function removeorders() {
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
		$prev_conf_ids = array();
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if ($rows[0]['status'] != 'cancelled') {
						$q = "UPDATE `#__vikbooking_orders` SET `status`='cancelled' WHERE `id`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($rows[0]['status'] == 'confirmed') {
							$prev_conf_ids[] = $rows[0]['id'];
						}
						//Booking History
						VikBooking::getBookingHistoryInstance()->setBid($rows[0]['id'])->store('CB');
						//
					}
					$q = "SELECT * FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$ordbusy = $dbo->loadAssocList();
						foreach ($ordbusy as $ob) {
							$q = "DELETE FROM `#__vikbooking_busy` WHERE `id`='".$ob['idbusy']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$q = "DELETE FROM `#__vikbooking_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($rows[0]['status'] == 'cancelled') {
						$q = "DELETE FROM `#__vikbooking_customers_orders` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_orderhistory` WHERE `idorder`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikbooking_orders` WHERE `id`=".(int)$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->enqueueMessage(JText::_('VBMESSDELBUSY'));
		}
		if (count($prev_conf_ids) > 0) {
			$prev_conf_ids_str = '';
			foreach ($prev_conf_ids as $prev_id) {
				$prev_conf_ids_str .= '&cid[]='.$prev_id;
			}
			//Invoke Channel Manager
			$vcm_autosync = VikBooking::vcmAutoUpdate();
			if ($vcm_autosync > 0) {
				$vcm_obj = VikBooking::getVcmInvoker();
				$vcm_obj->setOids($prev_conf_ids)->setSyncType('cancel');
				$sync_result = $vcm_obj->doSync();
				if ($sync_result === false) {
					$vcm_err = $vcm_obj->getError();
					VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a> '.(strlen($vcm_err) > 0 ? '('.$vcm_err.')' : ''));
				}
			} elseif (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
				$vcm_sync_url = 'index.php?option=com_vikbooking&task=invoke_vcm&stype=cancel'.$prev_conf_ids_str.'&returl='.urlencode('index.php?option=com_vikbooking&task=orders');
				VikError::raiseNotice('', JText::_('VBCHANNELMANAGERINVOKEASK').' <button type="button" class="btn btn-primary" onclick="document.location.href=\''.$vcm_sync_url.'\';">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button>');
			}
			//
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
	}

	function config() {
		VikBookingHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function saveconfig() {
		$dbo = JFactory::getDBO();
		$pallowbooking = VikRequest::getString('allowbooking', '', 'request');
		$pdisabledbookingmsg = VikRequest::getString('disabledbookingmsg', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptimeopenstorefh = VikRequest::getString('timeopenstorefh', '', 'request');
		$ptimeopenstorefm = VikRequest::getString('timeopenstorefm', '', 'request');
		$ptimeopenstoreth = VikRequest::getString('timeopenstoreth', '', 'request');
		$ptimeopenstoretm = VikRequest::getString('timeopenstoretm', '', 'request');
		$phoursmorebookingback = VikRequest::getString('hoursmorebookingback', '', 'request');
		$phoursmoreroomavail = VikRequest::getString('hoursmoreroomavail', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$pdatesep = VikRequest::getString('datesep', '', 'request');
		$pdatesep = empty($pdatesep) ? "/" : $pdatesep;
		$presmodcanc = VikRequest::getInt('resmodcanc', 1, 'request');
		$presmodcancmin = VikRequest::getInt('resmodcancmin', 1, 'request');
		$pshowcategories = VikRequest::getString('showcategories', '', 'request');
		$pshowchildren = VikRequest::getString('showchildren', '', 'request');
		$psearchsuggestions = VikRequest::getInt('searchsuggestions', '', 'request');
		$ptokenform = VikRequest::getString('tokenform', '', 'request');
		$padminemail = VikRequest::getString('adminemail', '', 'request');
		$psenderemail = VikRequest::getString('senderemail', '', 'request');
		$pminuteslock = VikRequest::getString('minuteslock', '', 'request');
		$pminautoremove = VikRequest::getInt('minautoremove', '', 'request');
		$pfooterordmail = VikRequest::getString('footerordmail', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptermsconds = VikRequest::getString('termsconds', '', 'request', VIKREQUEST_ALLOWHTML);
		$prequirelogin = VikRequest::getString('requirelogin', '', 'request');
		$pautoroomunit = VikRequest::getInt('autoroomunit', '', 'request');
		$ptodaybookings = VikRequest::getInt('todaybookings', '', 'request');
		$ptodaybookings = $ptodaybookings === 1 ? 1 : 0;
		$ploadbootstrap = VikRequest::getInt('loadbootstrap', '', 'request');
		$ploadbootstrap = $ploadbootstrap === 1 ? 1 : 0;
		$pusefa = VikRequest::getInt('usefa', '', 'request');
		$pusefa = $pusefa > 0 ? 1 : 0;
		$ploadjquery = VikRequest::getString('loadjquery', '', 'request');
		$ploadjquery = $ploadjquery == "yes" ? "1" : "0";
		$pcalendar = VikRequest::getString('calendar', '', 'request');
		$pcalendar = $pcalendar == "joomla" ? "joomla" : "jqueryui";
		$penablecoupons = VikRequest::getString('enablecoupons', '', 'request');
		$penablecoupons = $penablecoupons == "1" ? 1 : 0;
		$penablepin = VikRequest::getString('enablepin', '', 'request');
		$penablepin = $penablepin == "1" ? 1 : 0;
		$pmindaysadvance = VikRequest::getInt('mindaysadvance', '', 'request');
		$pmindaysadvance = $pmindaysadvance < 0 ? 0 : $pmindaysadvance;
		$pautodefcalnights = VikRequest::getInt('autodefcalnights', '', 'request');
		$pautodefcalnights = $pautodefcalnights >= 1 ? $pautodefcalnights : '1';
		$pnumrooms = VikRequest::getInt('numrooms', '', 'request');
		$pnumrooms = $pnumrooms > 0 ? $pnumrooms : '5';
		$pnumadultsfrom = VikRequest::getString('numadultsfrom', '', 'request');
		$pnumadultsfrom = intval($pnumadultsfrom) >= 0 ? $pnumadultsfrom : '1';
		$pnumadultsto = VikRequest::getString('numadultsto', '', 'request');
		$pnumadultsto = intval($pnumadultsto) > 0 ? $pnumadultsto : '10';
		if (intval($pnumadultsfrom) > intval($pnumadultsto)) {
			$pnumadultsfrom = '1';
			$pnumadultsto = '10';
		}
		$pnumchildrenfrom = VikRequest::getString('numchildrenfrom', '', 'request');
		$pnumchildrenfrom = intval($pnumchildrenfrom) >= 0 ? $pnumchildrenfrom : '1';
		$pnumchildrento = VikRequest::getString('numchildrento', '', 'request');
		$pnumchildrento = intval($pnumchildrento) > 0 ? $pnumchildrento  : '4';
		if (intval($pnumchildrenfrom) > intval($pnumchildrento)) {
			$pnumadultsfrom = '1';
			$pnumadultsto = '4';
		}
		$confnumadults = $pnumadultsfrom.'-'.$pnumadultsto;
		$confnumchildren = $pnumchildrenfrom.'-'.$pnumchildrento;
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = intval($pmaxdate) < 1 ? 2 : $pmaxdate;
		$pmaxdateinterval = VikRequest::getString('maxdateinterval', '', 'request');
		$pmaxdateinterval = !in_array($pmaxdateinterval, array('d', 'w', 'm', 'y')) ? 'y' : $pmaxdateinterval;
		$maxdate_str = '+'.$pmaxdate.$pmaxdateinterval;
		$pcronkey = VikRequest::getString('cronkey', '', 'request');
		$pcdsfrom = VikRequest::getVar('cdsfrom', array());
		$pcdsto = VikRequest::getVar('cdsto', array());
		$closing_dates = array();
		if (count($pcdsfrom)) {
			foreach ($pcdsfrom as $kcd => $vcdfrom) {
				if (!empty($vcdfrom) && array_key_exists($kcd, $pcdsto) && !empty($pcdsto[$kcd])) {
					$tscdfrom = VikBooking::getDateTimestamp($vcdfrom, '0', '0');
					$tscdto = VikBooking::getDateTimestamp($pcdsto[$kcd], '0', '0');
					if (!empty($tscdfrom) && !empty($tscdto) && $tscdto >= $tscdfrom) {
						$cdval = array('from' => $tscdfrom, 'to' => $tscdto);
						if (!in_array($cdval, $closing_dates)) {
							$closing_dates[] = $cdval;
						}
					}
				}
			}
		}
		$psmartsearch = VikRequest::getString('smartsearch', '', 'request');
		$psmartsearch = $psmartsearch == "dynamic" ? "dynamic" : "automatic";
		$pvbosef = VikRequest::getInt('vbosef', '', 'request');
		$vbosef = file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php');
		if ($pvbosef === 1) {
			if (!$vbosef) {
				rename(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'_router.php', VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php');
			}
		} else {
			if ($vbosef) {
				rename(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'router.php', VBO_SITE_PATH.DIRECTORY_SEPARATOR.'_router.php');
			}
		}
		$pmultilang = VikRequest::getString('multilang', '', 'request');
		$pmultilang = $pmultilang == "1" ? 1 : 0;
		$pvcmautoupd = VikRequest::getInt('vcmautoupd', '', 'request');
		$pvcmautoupd = $pvcmautoupd > 0 ? 1 : 0;
		$res_backend_path = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
		$picon="";
		if (intval($_FILES['sitelogo']['error']) == 0 && trim($_FILES['sitelogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['sitelogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['sitelogo']['name'])));
				if (file_exists($res_backend_path.$safename)) {
					$j=1;
					while (file_exists($res_backend_path.$j.$safename)) {
						$j++;
					}
					$pwhere=$res_backend_path.$j.$safename;
				} else {
					$j="";
					$pwhere=$res_backend_path.$safename;
				}
				VikBooking::uploadFile($_FILES['sitelogo']['tmp_name'], $pwhere);
				if (!getimagesize($pwhere)) {
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
				}
			}
			if (!empty($picon)) {
				$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($picon)." WHERE `param`='sitelogo';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$pbackicon = "";
		if (intval($_FILES['backlogo']['error']) == 0 && trim($_FILES['backlogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['backlogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['backlogo']['name'])));
				if (file_exists($res_backend_path.$safename)) {
					$j=1;
					while (file_exists($res_backend_path.$j.$safename)) {
						$j++;
					}
					$pwhere=$res_backend_path.$j.$safename;
				} else {
					$j="";
					$pwhere=$res_backend_path.$safename;
				}
				VikBooking::uploadFile($_FILES['backlogo']['tmp_name'], $pwhere);
				if (!getimagesize($pwhere)) {
					@unlink($pwhere);
					$pbackicon="";
				} else {
					@chmod($pwhere, 0644);
					$pbackicon=$j.$safename;
				}
			}
			if (!empty($pbackicon)) {
				$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='backlogo';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pbackicon)." WHERE `param`='backlogo';";
					$dbo->setQuery($q);
					$dbo->execute();
				} else {
					$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('backlogo',".$dbo->quote($pbackicon).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pvcmautoupd)." WHERE `param`='vcmautoupd';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pallowbooking) || $pallowbooking!="1") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='allowbooking';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='allowbooking';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowcategories) || $pshowcategories!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showcategories';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showcategories';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowchildren) || $pshowchildren!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showchildren';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showchildren';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psearchsuggestions."' WHERE `param`='searchsuggestions';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ptokenform) || $ptokenform!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='tokenform';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='tokenform';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pfooterordmail)." WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pdisabledbookingmsg)." WHERE `param`='disabledbookingmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		//terms and conditions
		$q = "SELECT `id`,`setting` FROM `#__vikbooking_texts` WHERE `param`='termsconds';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($ptermsconds)." WHERE `param`='termsconds';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikbooking_texts` (`param`,`exp`,`setting`) VALUES ('termsconds','Terms and Conditions',".$dbo->quote($ptermsconds).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($padminemail)." WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($psenderemail)." WHERE `param`='senderemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pdateformat)) {
			$pdateformat="%d/%m/%Y";
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdateformat)." WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdatesep)." WHERE `param`='datesep';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($presmodcanc)." WHERE `param`='resmodcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($presmodcancmin)." WHERE `param`='resmodcancmin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pminuteslock)." WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pminautoremove)." WHERE `param`='minautoremove';";
		$dbo->setQuery($q);
		$dbo->execute();
		$openingh=$ptimeopenstorefh * 3600;
		$openingm=$ptimeopenstorefm * 60;
		$openingts=$openingh + $openingm;
		$closingh=$ptimeopenstoreth * 3600;
		$closingm=$ptimeopenstoretm * 60;
		$closingts=$closingh + $closingm;
		//check if the check-in/out times have changed and if there are future bookings with the old time to prevent availability errors
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='timeopenstore';";
		$dbo->setQuery($q);
		$dbo->execute();
		$prevtimes = $dbo->loadResult();
		if ($prevtimes != $openingts."-".$closingts) {
			$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `checkout`>".time().";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				VikError::raiseWarning('', JText::_('VBOCONFIGWARNDIFFCHECKINOUT'));
				/**
				 * VBO 1.10 Patch - we concatenate a button to unify the check-in/out times
				 * for all reservations to avoid issues with the availability.
				 * 
				 * @since 	August 29th 2018
				 */
				VikError::raiseWarning('', '<br/><a href="index.php?option=com_vikbooking&task=unifycheckinout&fh='.$ptimeopenstorefh.'&fm='.$ptimeopenstorefm.'&th='.$ptimeopenstoreth.'&tm='.$ptimeopenstoretm.'" class="btn btn-large btn-warning">'.JText::_('VBAPPLY').'</a>');
				//
			}
		}
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$openingts."-".$closingts."' WHERE `param`='timeopenstore';";
		$dbo->setQuery($q);
		$dbo->execute();
		//set the hours of extended gratuity period to the difference between checkin and checkout if checkout is later
		$phoursmorebookingback = "0";
		if ($closingts > $openingts) {
			$diffcheck = ($closingts - $openingts) / 3600;
			$phoursmorebookingback = ceil($diffcheck);
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$phoursmorebookingback."' WHERE `param`='hoursmorebookingback';";
		$dbo->setQuery($q);
		$dbo->execute();
		$phoursmoreroomavail = "0";
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$phoursmoreroomavail."' WHERE `param`='hoursmoreroomavail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmultilang."' WHERE `param`='multilang';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".($prequirelogin == "1" ? "1" : "0")."' WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".($pautoroomunit == 1 ? "1" : "0")."' WHERE `param`='autoroomunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$ptodaybookings."' WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$ploadbootstrap."' WHERE `param`='bootstrap';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".(string)$pusefa."' WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$ploadjquery."' WHERE `param`='loadjquery';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pcalendar."' WHERE `param`='calendar';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$penablecoupons."' WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$penablepin."' WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmindaysadvance."' WHERE `param`='mindaysadvance';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pautodefcalnights."' WHERE `param`='autodefcalnights';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pnumrooms."' WHERE `param`='numrooms';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$confnumadults."' WHERE `param`='numadults';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$confnumchildren."' WHERE `param`='numchildren';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".json_encode($closing_dates)."' WHERE `param`='closingdates';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmartsearch."' WHERE `param`='smartsearch';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$maxdate_str."' WHERE `param`='maxdate';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcronkey)." WHERE `param`='cronkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pfronttitle = VikRequest::getString('fronttitle', '', 'request');
		$pfronttitletag = VikRequest::getString('fronttitletag', '', 'request');
		$pfronttitletagclass = VikRequest::getString('fronttitletagclass', '', 'request');
		$pshowfooter = VikRequest::getString('showfooter', '', 'request');
		$pintromain = VikRequest::getString('intromain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pclosingmain = VikRequest::getString('closingmain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencyname = VikRequest::getString('currencyname', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencysymb = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencycodepp = VikRequest::getString('currencycodepp', '', 'request');
		$pnumdecimals = VikRequest::getString('numdecimals', '', 'request');
		$pnumdecimals = intval($pnumdecimals);
		$pdecseparator = VikRequest::getString('decseparator', '', 'request');
		$pdecseparator = empty($pdecseparator) ? '.' : $pdecseparator;
		$pthoseparator = VikRequest::getString('thoseparator', '', 'request');
		$numberformatstr = $pnumdecimals.':'.$pdecseparator.':'.$pthoseparator;
		$pshowpartlyreserved = VikRequest::getString('showpartlyreserved', '', 'request');
		$pshowpartlyreserved = $pshowpartlyreserved == "yes" ? 1 : 0;
		$pshowcheckinoutonly = VikRequest::getInt('showcheckinoutonly', '', 'request');
		$pshowcheckinoutonly = $pshowcheckinoutonly > 0 ? 1 : 0;
		$pnumcalendars = VikRequest::getInt('numcalendars', '', 'request');
		$pnumcalendars = $pnumcalendars > -1 ? $pnumcalendars : 3;
		$pthumbsize = VikRequest::getInt('thumbsize', '', 'request');
		$pfirstwday = VikRequest::getString('firstwday', '', 'request');
		$pfirstwday = intval($pfirstwday) >= 0 && intval($pfirstwday) <= 6 ? $pfirstwday : '0';
		$pbctagname = VikRequest::getVar('bctagname', array());
		$pbctagcolor = VikRequest::getVar('bctagcolor', array());
		$pbctagrule = VikRequest::getVar('bctagrule', array());
		$bctags_arr = array();
		$bctags_rules = array();
		if (count($pbctagname) > 0) {
			foreach ($pbctagname as $bctk => $bctv) {
				if (!empty($bctv) && !empty($pbctagcolor[$bctk]) && strlen($pbctagrule[$bctk]) > 0) {
					if (intval($pbctagrule[$bctk]) == 0 || !in_array($pbctagrule[$bctk], $bctags_rules)) {
						$bctags_rules[] = $pbctagrule[$bctk];
						$bctags_arr[] = array('color' => $pbctagcolor[$bctk], 'name' => $bctv, 'rule' => $pbctagrule[$bctk]);
					}
				}
			}
		}
		//theme
		$ptheme = VikRequest::getString('theme', '', 'request');
		if (empty($ptheme) || $ptheme == 'default') {
			$ptheme = 'default';
		} else {
			$validtheme = false;
			$themes = glob(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'*');
			if (count($themes) > 0) {
				$strip = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR;
				foreach ($themes as $th) {
					if (is_dir($th)) {
						$tname = str_replace($strip, '', $th);
						if ($tname == $ptheme) {
							$validtheme = true;
							break;
						}
					}
				}
			}
			if ($validtheme == false) {
				$ptheme = 'default';
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ptheme)." WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pshowpartlyreserved)." WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pshowcheckinoutonly)." WHERE `param`='showcheckinoutonly';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pnumcalendars)." WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pthumbsize)." WHERE `param`='thumbsize';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfirstwday)." WHERE `param`='firstwday';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pfronttitle)." WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfronttitletag)." WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pfronttitletagclass)." WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowfooter) || $pshowfooter!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='showfooter';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='showfooter';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pintromain)." WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pclosingmain)." WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencyname)." WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencysymb)." WHERE `param`='currencysymb';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcurrencycodepp)." WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($numberformatstr)." WHERE `param`='numberformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Bookings color tags
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='bookingsctags';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($bctags_arr))." WHERE `param`='bookingsctags';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('bookingsctags',".$dbo->quote(json_encode($bctags_arr)).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		
		$pivainclusa = VikRequest::getString('ivainclusa', '', 'request');
		$ptaxsummary = VikRequest::getString('taxsummary', '', 'request');
		$ptaxsummary = empty($ptaxsummary) || $ptaxsummary != "yes" ? "0" : "1";
		$pccpaypal = VikRequest::getString('ccpaypal', '', 'request');
		$ppaytotal = VikRequest::getString('paytotal', '', 'request');
		$ppayaccpercent = VikRequest::getString('payaccpercent', '', 'request');
		$ptypedeposit = VikRequest::getString('typedeposit', '', 'request');
		$ptypedeposit = $ptypedeposit == 'fixed' ? 'fixed' : 'pcent';
		$pdepoverrides = VikRequest::getString('depoverrides', '', 'request');
		$ppaymentname = VikRequest::getString('paymentname', '', 'request');
		$pmultipay = VikRequest::getString('multipay', '', 'request');
		$pmultipay = $pmultipay == "yes" ? 1 : 0;
		$pdepifdaysadv = VikRequest::getInt('depifdaysadv', '', 'request');
		$pnodepnonrefund = VikRequest::getInt('nodepnonrefund', '', 'request');
		$pdepcustchoice = VikRequest::getString('depcustchoice', '', 'request');
		$pdepcustchoice = $pdepcustchoice == "yes" ? 1 : 0;
		if (empty($pivainclusa) || $pivainclusa!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='ivainclusa';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='ivainclusa';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$ptaxsummary."' WHERE `param`='taxsummary';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ppaytotal) || $ppaytotal!="yes") {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='0' WHERE `param`='paytotal';";
		} else {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='1' WHERE `param`='paytotal';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pccpaypal)." WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($ppaymentname)." WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ppayaccpercent)." WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($ptypedeposit)." WHERE `param`='typedeposit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pdepoverrides)." WHERE `param`='depoverrides';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pmultipay."' WHERE `param`='multipay';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pdepifdaysadv."' WHERE `param`='depifdaysadv';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pnodepnonrefund."' WHERE `param`='nodepnonrefund';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$pdepcustchoice."' WHERE `param`='depcustchoice';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pdisclaimer = VikRequest::getString('disclaimer', '', 'request', VIKREQUEST_ALLOWHTML);
		$psendemailwhen = VikRequest::getInt('sendemailwhen', '', 'request');
		$psendemailwhen = $psendemailwhen > 1 ? 2 : 1;
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($psendemailwhen)." WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($pdisclaimer)." WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();

		//SMS APIs
		$psmsapi = VikRequest::getString('smsapi', '', 'request');
		$psmsautosend = VikRequest::getString('smsautosend', '', 'request');
		$psmsautosend = intval($psmsautosend) > 0 ? 1 : 0;
		$psmssendto = VikRequest::getVar('smssendto', array());
		$sms_sendto = array();
		foreach ($psmssendto as $sto) {
			if (in_array($sto, array('admin', 'customer'))) {
				$sms_sendto[] = $sto;
			}
		}
		$psmssendwhen = VikRequest::getInt('smssendwhen', '', 'request');
		$psmssendwhen = $psmssendwhen > 1 ? 2 : 1;
		$psmsadminphone = VikRequest::getString('smsadminphone', '', 'request');
		$psmsadmintpl = VikRequest::getString('smsadmintpl', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertpl = VikRequest::getString('smscustomertpl', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmsadmintplpend = VikRequest::getString('smsadmintplpend', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertplpend = VikRequest::getString('smscustomertplpend', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmsadmintplcanc = VikRequest::getString('smsadmintplcanc', '', 'request', VIKREQUEST_ALLOWRAW);
		$psmscustomertplcanc = VikRequest::getString('smscustomertplcanc', '', 'request', VIKREQUEST_ALLOWRAW);
		$viksmsparams = VikRequest::getVar('viksmsparams', array());
		$smsparamarr = array();
		if (count($viksmsparams) > 0) {
			foreach ($viksmsparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$smsparamarr[$setting] = $cont;
				}
			}
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsapi."' WHERE `param`='smsapi';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsautosend."' WHERE `param`='smsautosend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($sms_sendto))." WHERE `param`='smssendto';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmssendwhen."' WHERE `param`='smssendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`='".$psmsadminphone."' WHERE `param`='smsadminphone';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($smsparamarr))." WHERE `param`='smsparams';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintpl)." WHERE `param`='smsadmintpl';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertpl)." WHERE `param`='smscustomertpl';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintplpend)." WHERE `param`='smsadmintplpend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertplpend)." WHERE `param`='smscustomertplpend';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmsadmintplcanc)." WHERE `param`='smsadmintplcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_texts` SET `setting`=".$dbo->quote($psmscustomertplcanc)." WHERE `param`='smscustomertplcanc';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::_('VBSETTINGSAVED'));
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	/**
	 * Unify the check-in and check-out times for all reservations.
	 * 
	 * @since 	1.10 - Patch August 29th 2018
	 */
	function unifycheckinout() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$fh = VikRequest::getInt('fh', 12, 'request');
		$fm = VikRequest::getInt('fm', 0, 'request');
		$th = VikRequest::getInt('th', 10, 'request');
		$tm = VikRequest::getInt('tm', 0, 'request');
		$totmod = 0;
		$totbookmod = 0;
		$q = "SELECT * FROM `#__vikbooking_busy`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$records = $dbo->loadAssocList();
		foreach ($records as $v) {
			$info_start = getdate($v['checkin']);
			$info_end = getdate($v['checkout']);
			$new_start = mktime($fh, $fm, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$new_end = mktime($th, $tm, 0, $info_end['mon'], $info_end['mday'], $info_end['year']);
			$q = "UPDATE `#__vikbooking_busy` SET `checkin`=".$new_start.",`checkout`=".$new_end.",`realback`=".$new_end." WHERE `id`=".$v['id']." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totmod++;
		}

		$q = "SELECT * FROM `#__vikbooking_orders`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$records = $dbo->loadAssocList();
		foreach ($records as $v) {
			$info_start = getdate($v['checkin']);
			$info_end = getdate($v['checkout']);
			$new_start = mktime($fh, $fm, 0, $info_start['mon'], $info_start['mday'], $info_start['year']);
			$new_end = mktime($th, $tm, 0, $info_end['mon'], $info_end['mday'], $info_end['year']);
			$q = "UPDATE `#__vikbooking_orders` SET `checkin`=".$new_start.",`checkout`=".$new_end." WHERE `id`=".$v['id']." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totbookmod++;
		}
		$mainframe->enqueueMessage('OK: '.$totbookmod);
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	function savetmplfile() {
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcont = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$exists = file_exists($fpath) ? true : false;
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		$fpath = file_exists($fpath) ? $fpath : '';
		if (!empty($fpath)) {
			$fp = fopen($fpath, 'wb');
			$byt = (int)fwrite($fp, $pcont);
			fclose($fp);
			if ($byt > 0) {
				$mainframe->enqueueMessage(JText::_('VBOUPDTMPLFILEOK'));
			} else {
				VikError::raiseWarning('', JText::_('VBOUPDTMPLFILENOBYTES'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VBOUPDTMPLFILEERR'));
		}
		$mainframe->redirect("index.php?option=com_vikbooking&task=edittmplfile&path=".$fpath."&tmpl=component");

		exit;
	}

	function edittmplfile() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'edittmplfile'));
	
		parent::display();
	}

	function invoices() {
		VikBookingHelper::printHeader("invoices");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'invoices'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function downloadinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids) > 0) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id` IN (".implode(', ', $ids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$invoices = $dbo->loadAssocList();
				if (!(count($invoices) > 1)) {
					//Single Invoice Download
					if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoices[0]['file_name'])) {
						header("Content-type:application/pdf");
						header("Content-Disposition:attachment;filename=".$invoices[0]['file_name']);
						readfile(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoices[0]['file_name']);
						exit;
					}
				} else {
					//Multiple Invoices Download
					$to_zip = array();
					foreach ($invoices as $k => $invoice) {
						$to_zip[$k]['name'] = $invoice['file_name'];
						$to_zip[$k]['path'] = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$invoice['file_name'];
					}
					if (class_exists('ZipArchive')) {
						$zip_path = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.date('Y-m-d').'-invoices.zip';
						$zip = new ZipArchive;
						$zip->open($zip_path, ZipArchive::CREATE);
						foreach ($to_zip as $k => $zipv) {
							$zip->addFile($zipv['path'], $zipv['name']);
						}
						$zip->close();
						header("Content-type:application/zip");
						header("Content-Disposition:attachment;filename=".date('Y-m-d').'-invoices.zip');
						header("Content-Length:".filesize($zip_path));
						readfile($zip_path);
						unlink($zip_path);
						exit;
					} else {
						//Class ZipArchive does not exist
						VikError::raiseWarning('', 'Class ZipArchive does not exists on your server. Download the files one by one.');
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	function resendinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (!(count($ids) > 0)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
			exit;
		}
		$dbo = JFactory::getDBO();
		$bookings = array();
		$q = "SELECT `i`.`id` AS `id_invoice`,`o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_invoices` AS `i` LEFT JOIN `#__vikbooking_orders` `o` ON `o`.`id`=`i`.`idorder` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `i`.`id` IN (".implode(', ', $ids).") AND `o`.`status`='confirmed' AND `o`.`total` > 0 ORDER BY `o`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
		}
		if (!(count($bookings) > 0)) {
			VikError::raiseWarning('', JText::_('VBOGENINVERRNOBOOKINGS'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
			exit;
		}
		$tot_generated = 0;
		$tot_sent = 0;
		foreach ($bookings as $bkey => $booking) {
			$send_res = VikBooking::sendBookingInvoice($booking['id_invoice'], $booking);
			if ($send_res !== false) {
				$tot_sent++;
			}
		}
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', $tot_generated, $tot_sent));
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	function removeinvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$tot_removed = 0;
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "SELECT * FROM `#__vikbooking_invoices` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$cur_invoice = $dbo->loadAssoc();
					if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$cur_invoice['file_name'])) {
						unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'generated'.DIRECTORY_SEPARATOR.$cur_invoice['file_name']);
					}
					$q = "DELETE FROM `#__vikbooking_invoices` WHERE `id`=".(int)$d.";";
					$dbo->setQuery($q);
					$dbo->execute();
					$tot_removed++;
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESRMVD', $tot_removed));
		$mainframe->redirect("index.php?option=com_vikbooking&task=invoices");
	}

	function geninvoices() {
		$ids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		if (!(count($ids) > 0)) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}
		$dbo = JFactory::getDBO();
		$pinvoice_num = VikRequest::getInt('invoice_num', '', 'request');
		$pinvoice_num = $pinvoice_num <= 0 ? 1 : $pinvoice_num;
		$pinvoice_suff = VikRequest::getString('invoice_suff', '', 'request');
		$pinvoice_date = VikRequest::getString('invoice_date', '', 'request');
		$pcompany_info = VikRequest::getString('company_info', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcompany_info = strpos($pcompany_info, '<') !== false ? $pcompany_info : nl2br($pcompany_info);
		$pinvoice_send = VikRequest::getInt('invoice_send', '', 'request');
		$pinvoice_send = $pinvoice_send > 0 ? true : false;
		$increment_inv = true;
		$pconfirmgen = VikRequest::getInt('confirmgen', '', 'request');
		//if editing an invoice (re-creating an existing invoice for a booking), do not increment the invoice number
		if (count($ids) == 1) {
			$q = "SELECT `number` FROM `#__vikbooking_invoices` WHERE `idorder`=".(int)$ids[0].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$increment_inv = false;
			}
		}
		//
		$bookings = array();
		$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id` IN (".implode(', ', $ids).") AND `o`.`status`='confirmed' AND `o`.`total` > 0 ORDER BY `o`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$bookings = $dbo->loadAssocList();
		}
		if (!(count($bookings) > 0)) {
			VikError::raiseWarning('', JText::_('VBOGENINVERRNOBOOKINGS'));
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}
		$tot_generated = 0;
		$tot_sent = 0;
		foreach ($bookings as $bkey => $booking) {
			$gen_res = VikBooking::generateBookingInvoice($booking, $pinvoice_num, $pinvoice_suff, $pinvoice_date, $pcompany_info);
			if ($gen_res !== false && $gen_res > 0) {
				$tot_generated++;
				$pinvoice_num++;
				if ($pinvoice_send) {
					$send_res = VikBooking::sendBookingInvoice($gen_res, $booking);
					if ($send_res !== false) {
						$tot_sent++;
					}
				}
			} else {
				VikError::raiseWarning('', JText::sprintf('VBOGENINVERRBOOKING', $booking['id']));
			}
		}
		if ($tot_generated > 0 && $increment_inv === true) {
			$q = "UPDATE `#__vikbooking_config` SET `setting`='".($pinvoice_num - 1)."' WHERE `param`='invoiceinum';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pinvoice_suff)." WHERE `param`='invoicesuffix';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote($pcompany_info)." WHERE `param`='invcompanyinfo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::sprintf('VBOTOTINVOICESGEND', $tot_generated, $tot_sent));
		if ($pconfirmgen > 0) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=invoices&show=".$pconfirmgen);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
		}
	}

	function optionals() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'optionals'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function newoptionals() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoptional'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function editoptional() {
		VikBookingHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageoptional'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function updateoptional() {
		$dbo = JFactory::getDBO();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$poptperperson = VikRequest::getString('optperperson', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getInt('optaliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pifchildren = VikRequest::getString('ifchildren', '', 'request');
		$pifchildren = $pifchildren == "1" ? 1 : 0;
		$pmaxquant = VikRequest::getString('maxquant', '', 'request');
		$pmaxquant = empty($pmaxquant) ? 0 : intval($pmaxquant);
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcevalperchild = VikRequest::getString('forcevalperchild', '', 'request');
		$pforcesummary = VikRequest::getString('forcesummary', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pis_citytax = VikRequest::getString('is_citytax', '', 'request');
		$pis_fee = VikRequest::getString('is_fee', '', 'request');
		$pis_citytax = $pis_citytax == "1" && $pis_fee != "1" ? 1 : 0;
		$pis_fee = $pis_fee == "1" && $pis_citytax == 0 ? 1 : 0;
		$pagefrom = VikRequest::getVar('agefrom', array());
		$pageto = VikRequest::getVar('ageto', array());
		$pagecost = VikRequest::getVar('agecost', array());
		$pagectype = VikRequest::getVar('agectype', array());
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0")."-".($pforcevalperchild == "1" ? "1" : "0")."-".($pforcesummary == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j=1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere=$updpath.$j.$safename;
					} else {
						$j="";
						$pwhere=$updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			($poptperday=="each" ? $poptperday="1" : $poptperday="0");
			$poptperperson=($poptperperson=="each" ? "1" : "0");
			($popthmany=="yes" ? $popthmany="1" : $popthmany="0");
			$ageintervalstr = '';
			if ($pifchildren == 1 && count($pagefrom) > 0 && count($pagecost) > 0 && count($pagefrom) == count($pagecost)) {
				foreach ($pagefrom as $kage => $vage) {
					$afrom = intval($vage);
					$ato = intval($pageto[$kage]);
					$acost = floatval($pagecost[$kage]);
					if (strlen($vage) > 0 && strlen($pagecost[$kage]) > 0) {
						if ($ato < $afrom) $ato = $afrom;
						$ageintervalstr .= $afrom.'_'.$ato.'_'.$acost.(array_key_exists($kage, $pagectype) && strpos($pagectype[$kage], '%') !== false ? '_%'.(strpos($pagectype[$kage], '%b') !== false ? 'b' : '') : '').';;';
					}
				}
				$ageintervalstr = rtrim($ageintervalstr, ';;');
				if (!empty($ageintervalstr)) {
					$pforcesel = 1;
				}
			}
			$q = "UPDATE `#__vikbooking_optionals` SET `name`=".$dbo->quote($poptname).",`descr`=".$dbo->quote($poptdescr).",`cost`=".$dbo->quote($poptcost).",`perday`=".$dbo->quote($poptperday).",`hmany`=".$dbo->quote($popthmany).",".(strlen($picon)>0 ? "`img`='".$picon."'," : "")."`idiva`=".$dbo->quote($poptaliq).", `maxprice`=".$dbo->quote($pmaxprice).", `forcesel`='".$pforcesel."', `forceval`='".$strforceval."', `perperson`='".$poptperperson."', `ifchildren`='".$pifchildren."', `maxquant`='".$pmaxquant."', `ageintervals`='".$ageintervalstr."',`is_citytax`=".$pis_citytax.",`is_fee`=".$pis_fee." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VBOSUCCUPDOPTION'));
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	function createoptionals() {
		$dbo = JFactory::getDBO();
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getFloat('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$poptperperson = VikRequest::getString('optperperson', '', 'request');
		$pmaxprice = VikRequest::getFloat('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptaliq = VikRequest::getInt('optaliq', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pifchildren = VikRequest::getString('ifchildren', '', 'request');
		$pifchildren = $pifchildren == "1" ? 1 : 0;
		$pmaxquant = VikRequest::getString('maxquant', '', 'request');
		$pmaxquant = empty($pmaxquant) ? 0 : intval($pmaxquant);
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcevalperchild = VikRequest::getString('forcevalperchild', '', 'request');
		$pforcesummary = VikRequest::getString('forcesummary', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pis_citytax = VikRequest::getString('is_citytax', '', 'request');
		$pis_fee = VikRequest::getString('is_fee', '', 'request');
		$pis_citytax = $pis_citytax == "1" && $pis_fee != "1" ? 1 : 0;
		$pis_fee = $pis_fee == "1" && $pis_citytax == 0 ? 1 : 0;
		$pagefrom = VikRequest::getVar('agefrom', array());
		$pageto = VikRequest::getVar('ageto', array());
		$pagecost = VikRequest::getVar('agecost', array());
		$pagectype = VikRequest::getVar('agectype', array());
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0")."-".($pforcevalperchild == "1" ? "1" : "0")."-".($pforcesummary == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikBooking::caniWrite(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR) && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists($updpath.$safename)) {
						$j = 1;
						while (file_exists($updpath.$j.$safename)) {
							$j++;
						}
						$pwhere = $updpath.$j.$safename;
					} else {
						$j = "";
						$pwhere = $updpath.$safename;
					}
					VikBooking::uploadFile($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new vikResizer();
							$origmod = $eforj->proportionalImage($pwhere, $updpath.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$poptperday = ($poptperday == "each" ? "1" : "0");
			$poptperperson = ($poptperperson == "each" ? "1" : "0");
			($popthmany == "yes" ? $popthmany = "1" : $popthmany = "0");
			$ageintervalstr = '';
			if ($pifchildren == 1 && count($pagefrom) > 0 && count($pagecost) > 0 && count($pagefrom) == count($pagecost)) {
				foreach ($pagefrom as $kage => $vage) {
					$afrom = intval($vage);
					$ato = intval($pageto[$kage]);
					$acost = floatval($pagecost[$kage]);
					if (strlen($vage) > 0 && strlen($pagecost[$kage]) > 0) {
						if ($ato < $afrom) $ato = $afrom;
						$ageintervalstr .= $afrom.'_'.$ato.'_'.$acost.(array_key_exists($kage, $pagectype) && strpos($pagectype[$kage], '%') !== false ? '_%'.(strpos($pagectype[$kage], '%b') !== false ? 'b' : '') : '').';;';
					}
				}
				$ageintervalstr = rtrim($ageintervalstr, ';;');
				if (!empty($ageintervalstr)) {
					$pforcesel = 1;
				}
			}
			$q = "SELECT `ordering` FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum=1;
			}
			$q = "INSERT INTO `#__vikbooking_optionals` (`name`,`descr`,`cost`,`perday`,`hmany`,`img`,`idiva`,`maxprice`,`forcesel`,`forceval`,`perperson`,`ifchildren`,`maxquant`,`ordering`,`ageintervals`,`is_citytax`,`is_fee`) VALUES(".$dbo->quote($poptname).", ".$dbo->quote($poptdescr).", ".$dbo->quote($poptcost).", ".$dbo->quote($poptperday).", ".$dbo->quote($popthmany).", '".$picon."', ".$dbo->quote($poptaliq).", ".$dbo->quote($pmaxprice).", '".$pforcesel."', '".$strforceval."', '".$poptperperson."', '".$pifchildren."', '".$pmaxquant."', '".$newsortnum."', '".$ageintervalstr."', '".$pis_citytax."', '".$pis_fee."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	function removeoptionals() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `img` FROM `#__vikbooking_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['img']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['img'])) {
						@unlink(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$rows[0]['img']);
					}
				}	
				$q = "DELETE FROM `#__vikbooking_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	function sendcustomsms() {
		$mainframe = JFactory::getApplication();
		$pphone = VikRequest::getString('phone', '', 'request');
		$psmscont = VikRequest::getString('smscont', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikbooking';
		if (!empty($pphone) && !empty($psmscont)) {
			$sms_api = VikBooking::getSMSAPIClass();
			$sms_api_params = VikBooking::getSMSParams();
			if (!empty($sms_api) && file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api) && !empty($sms_api_params)) {
				require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
				$sms_obj = new VikSmsApi(array(), $sms_api_params);
				$response_obj = $sms_obj->sendMessage($pphone, $psmscont);
				if ( !$sms_obj->validateResponse($response_obj) ) {
					VikError::raiseWarning('', $sms_obj->getLog());
				} else {
					$mainframe->enqueueMessage(JText::_('VBSENDSMSOK'));
				}
			} else {
				VikError::raiseWarning('', JText::_('VBSENDSMSERRMISSAPI'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VBSENDSMSERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	function sendcustomemail() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pemailsubj = VikRequest::getString('emailsubj', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pemailcont = VikRequest::getString('emailcont', '', 'request', VIKREQUEST_ALLOWRAW);
		$pemailfrom = VikRequest::getString('emailfrom', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikbooking';
		if (!empty($pemail) && !empty($pemailcont)) {
			$email_attach = null;
			jimport('joomla.filesystem.file');
			$pemailattch = VikRequest::getVar('emailattch', null, 'files', 'array');
			if (isset($pemailattch) && strlen(trim($pemailattch['name']))) {
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pemailattch['name'])));
				$src = $pemailattch['tmp_name'];
				$dest = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest = $dest.$j.$filename;
				if (VikBooking::uploadFile($src, $finaldest)) {
					$email_attach = $finaldest;
				} else {
					VikError::raiseWarning('', 'Error uploading the attachment. Email not sent.');
					$mainframe->redirect($pgoto);
					exit;
				}
			}
			//VBO 1.10 - special tags for the custom email template files and messages
			$orig_mail_cont = $pemailcont;
			if (strpos($pemailcont, '{') !== false && strpos($pemailcont, '}') !== false) {
				$booking = array();
				$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$pbid." LEFT JOIN `#__vikbooking_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikbooking_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$booking = $dbo->loadAssoc();
				}
				$booking_rooms = array();
				$q = "SELECT `or`.*,`r`.`name` AS `room_name` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$booking_rooms = $dbo->loadAssocList();
					if (!empty($booking['lang'])) {
						$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'), array(), $booking['lang']);
					}
				}
				//we use the same parsing function as the one for the Customer SMS Template
				$pemailcont = VikBooking::parseCustomerSMSTemplate($booking, $booking_rooms, null, $pemailcont);
			}
			//
			// allow the use of token {booking_id} in subject
			$pemailsubj = str_replace('{booking_id}', $pbid, $pemailsubj);
			//
			$is_html = (strpos($pemailcont, '<') !== false && strpos($pemailcont, '>') !== false);
			$pemailcont = $is_html ? nl2br($pemailcont) : $pemailcont;
			$vbo_app = new VboApplication();
			$vbo_app->sendMail($pemailfrom, $pemailfrom, $pemail, $pemailfrom, $pemailsubj, $pemailcont, $is_html, 'base64', $email_attach);
			$mainframe->enqueueMessage(JText::_('VBSENDEMAILOK'));
			if ($email_attach !== null) {
				@unlink($email_attach);
			}
			//Booking History
			VikBooking::getBookingHistoryInstance()->setBid($pbid)->store('CE', nl2br($pemailsubj . "\n\n" . $pemailcont));
			//
			//Save email template for future sending
			$config_rec_exists = false;
			$emtpl = array(
				'emailsubj' => $pemailsubj,
				'emailcont' => $orig_mail_cont,
				'emailfrom' => $pemailfrom
			);
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$config_rec_exists = true;
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				$existing_subj = false;
				foreach ($cur_emtpl as $emk => $emv) {
					if (array_key_exists('emailsubj', $emv) && $emv['emailsubj'] == $emtpl['emailsubj']) {
						$cur_emtpl[$emk] = $emtpl;
						$existing_subj = true;
						break;
					}
				}
				if ($existing_subj === false) {
					$cur_emtpl[] = $emtpl;
				}
			} else {
				$cur_emtpl[] = $emtpl;
			}
			if (count($cur_emtpl) > 10) {
				//Max 10 templates to avoid problems with the size of the field and truncated json strings
				$exceed = count($cur_emtpl) - 10;
				for ($tl=0; $tl < $exceed; $tl++) { 
					unset($cur_emtpl[$tl]);
				}
				$cur_emtpl = array_values($cur_emtpl);
			}
			if ($config_rec_exists === true) {
				$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				$q = "INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('customemailtpls', ".$dbo->quote(json_encode($cur_emtpl)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		} else {
			VikError::raiseWarning('', JText::_('VBSENDEMAILERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	function rmcustomemailtpl() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$tplind = VikRequest::getInt('tplind', '', 'request');
		if (empty($oid) || !(strlen($tplind) > 0)) {
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikbooking');
			exit;
		}
		$cur_emtpl = array();
		$q = "SELECT `setting` FROM `#__vikbooking_config` WHERE `param`='customemailtpls';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_emtpl = $dbo->loadResult();
			$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
			$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
		} else {
			VikError::raiseWarning('', 'Missing Templates Record.');
			$mainframe->redirect('index.php?option=com_vikbooking');
			exit;
		}
		if (array_key_exists($tplind, $cur_emtpl)) {
			unset($cur_emtpl[$tplind]);
			$cur_emtpl = count($cur_emtpl) > 0 ? array_values($cur_emtpl) : array();
			$q = "UPDATE `#__vikbooking_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect('index.php?option=com_vikbooking&task=editorder&cid[]='.$oid.'&customemail=1');
		exit;
	}

	function exportcustomers() {
		//we do not set the menu for this view
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'exportcustomers'));
	
		parent::display();

		if (VikBooking::showFooter()) {
			VikBookingHelper::printFooter();
		}
	}

	function csvexportprepare() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'csvexportprepare'));
	
		parent::display();
	}

	function icsexportprepare() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'icsexportprepare'));
	
		parent::display();
	}

	function bookingcheckin() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'bookingcheckin'));
	
		parent::display();
	}

	function gencheckindoc() {
		//modal box, so we do not set menu or footer
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'gencheckindoc'));
	
		parent::display();
	}

	function checkversion() {
		//to be called via ajax
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		$result = array();

		JPluginHelper::importPlugin('e4j');
		if (class_exists('JEventDispatcher')) {
			$dispatcher = JEventDispatcher::getInstance();
			$result = $dispatcher->trigger('checkVersion', array(&$params));
		} else {
			$app = JFactory::getApplication();
			if (method_exists($app, 'triggerEvent')) {
				$result = $app->triggerEvent('checkVersion', array(&$params));
			}
		}

		if (!count($result)) {
			$result = new stdClass;
			$result->status = 0;
		} else {
			$result = $result[0];
		}

		echo json_encode($result);
		exit;
	}

	function updateprogram() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		$result = array();

		JPluginHelper::importPlugin('e4j');
		if (class_exists('JEventDispatcher')) {
			$dispatcher = JEventDispatcher::getInstance();
			$result = $dispatcher->trigger('getVersionContents', array(&$params));
		} else {
			$app = JFactory::getApplication();
			if (method_exists($app, 'triggerEvent')) {
				$result = $app->triggerEvent('getVersionContents', array(&$params));
			}
		}

		if (!count($result) || !$result[0]) {
			if (class_exists('JEventDispatcher')) {
				$result = $dispatcher->trigger('checkVersion', array(&$params));
			} else {
				$app = JFactory::getApplication();
				if (method_exists($app, 'triggerEvent')) {
					$result = $app->triggerEvent('checkVersion', array(&$params));
				}
			}
		}

		if (!count($result) || !$result[0]->status || !$result[0]->response->status) {
			exit('Error, plugin disabled');
		}

		JToolbarHelper::title(JText::_('VBMAINTITLEUPDATEPROGRAM'));

		VikBookingHelper::pUpdateProgram($result[0]->response);
	}

	function updateprogramlaunch() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikbooking';

		JPluginHelper::importPlugin('e4j');

		$json = new stdClass;
		$json->status = false;

		try {

			$result = array();

			if (class_exists('JEventDispatcher')) {
				$dispatcher = JEventDispatcher::getInstance();
				$result = $dispatcher->trigger('doUpdate', array(&$params));
			} else {
				$app = JFactory::getApplication();
				if (method_exists($app, 'triggerEvent')) {
					$result = $app->triggerEvent('doUpdate', array(&$params));
				}
			}
			
			if ( count($result) ) {
				$json->status = (bool) $result[0];
			} else {
				$json->error = 'plugin disabled.';
			}

		} catch(Exception $e) {

			$json->status = false;
			$json->error = $e->getMessage();

		}

		echo json_encode($json);
		exit;
	}

	function invoke_vcm() {
		$oids = VikRequest::getVar('cid', array(0));
		$mainframe = JFactory::getApplication();
		$sync_type = VikRequest::getString('stype', 'new', 'request');
		$sync_type = !in_array($sync_type, array('new', 'modify', 'cancel')) ? 'new' : $sync_type;
		$original_booking_js = VikRequest::getString('origb', '', 'request', VIKREQUEST_ALLOWRAW);
		$return_url = VikRequest::getString('returl', '', 'request');
		$return_url = !empty($return_url) ? urldecode($return_url) : $return_url;
		if (!(count($oids) > 0) || !file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
			exit;
		}

		$vcm_obj = VikBooking::getVcmInvoker();
		$vcm_obj->setOids($oids)->setSyncType($sync_type)->setOriginalBooking($original_booking_js, true);
		$result = $vcm_obj->doSync();

		if ($result === true) {
			$mainframe->enqueueMessage(JText::_('VBCHANNELMANAGERRESULTOK'));
		} else {
			VikError::raiseWarning('', JText::_('VBCHANNELMANAGERRESULTKO').' <a href="index.php?option=com_vikchannelmanager" target="_blank">'.JText::_('VBCHANNELMANAGEROPEN').'</a>');
		}

		if (!empty($return_url)) {
			$mainframe->redirect($return_url);
		} else {
			$mainframe->redirect("index.php?option=com_vikbooking&task=orders");
		}
	}

	function multiphotosupload() {
		jimport('joomla.filesystem.file');
		
		$dbo = JFactory::getDBO();
		$proomid = VikRequest::getInt('roomid', '', 'request');
		
		$resp = array('files' => array());
		$error_messages = array(
			1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
			2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
			3 => 'The uploaded file was only partially uploaded',
			4 => 'No file was uploaded',
			6 => 'Missing a temporary folder',
			7 => 'Failed to write file to disk',
			8 => 'A PHP extension stopped the file upload',
			'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
			'max_file_size' => 'File is too big',
			'min_file_size' => 'File is too small',
			'accept_file_types' => 'Filetype not allowed',
			'max_number_of_files' => 'Maximum number of files exceeded',
			'max_width' => 'Image exceeds maximum width',
			'min_width' => 'Image requires a minimum width',
			'max_height' => 'Image exceeds maximum height',
			'min_height' => 'Image requires a minimum height',
			'abort' => 'File upload aborted',
			'image_resize' => 'Failed to resize image',
			'vbo_type' => 'The file type cannot be accepted',
			'vbo_jupload' => 'The upload has failed. Check the Joomla Configuration',
			'vbo_perm' => 'Error moving the uploaded files. Check your permissions'
		);

		$creativik = new vikResizer();
		$updpath = VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
		$bigsdest = $updpath;
		$thumbsdest = $updpath;
		$dest = $updpath;
		$moreimagestr = '';
		$cur_captions = json_encode(array());

		$q = "SELECT `moreimgs`,`imgcaptions` FROM `#__vikbooking_rooms` WHERE `id`=".$proomid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$photo_data = $dbo->loadAssocList();
			$cur_captions = $photo_data[0]['imgcaptions'];
			$cur_photos = $photo_data[0]['moreimgs'];
			if (!empty($cur_photos)) {
				$moreimagestr .= $cur_photos;
			} 
		}

		$bulkphotos = VikRequest::getVar('bulkphotos', null, 'files', 'array');

		if (is_array($bulkphotos) && count($bulkphotos) > 0 && array_key_exists('name', $bulkphotos) && count($bulkphotos['name']) > 0) {
			foreach ($bulkphotos['name'] as $updk => $photoname) {
				$uploaded_image = array();
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($photoname)));
				$src = $bulkphotos['tmp_name'][$updk];
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest=$dest.$j.$filename;
				$is_error = false;
				$err_key = '';
				if (array_key_exists('error', $bulkphotos) && array_key_exists($updk, $bulkphotos['error']) && !empty($bulkphotos['error'][$updk])) {
					if (array_key_exists($bulkphotos['error'][$updk], $error_messages)) {
						$is_error = true;
						$err_key = $bulkphotos['error'][$updk];
					}
				}
				if (!$is_error) {
					$check = getimagesize($bulkphotos['tmp_name'][$updk]);
					if ($check[2] & imagetypes()) {
						if (VikBooking::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
							//orig img
							$origmod = true;
							VikBooking::uploadFile($finaldest, $bigsdest.'big_'.$j.$filename, true);
							//thumb
							$thumbsize = VikBooking::getThumbSize();
							$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, $thumbsize, $thumbsize);
							if (!$thumb || !$origmod) {
								if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
								if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
								$is_error = true;
								$err_key = 'vbo_perm';
							} else {
								$moreimagestr.=$j.$filename.";;";
							}
							@unlink($finaldest);
						} else {
							$is_error = true;
							$err_key = 'vbo_jupload';
						}
					} else {
						$is_error = true;
						$err_key = 'vbo_type';
					}
				}
				$img = new stdClass();
				if ($is_error) {
					$img->name = '';
					$img->size = '';
					$img->type = '';
					$img->url = '';
					$img->error = array_key_exists($err_key, $error_messages) ? $error_messages[$err_key] : 'Generic Error for Upload';
				} else {
					$img->name = $photoname;
					$img->size = $bulkphotos['size'][$updk];
					$img->type = $bulkphotos['type'][$updk];
					$img->url = VBO_SITE_URI.'resources/uploads/big_'.$j.$filename;
				}
				$resp['files'][] = $img;
			}
		} else {
			$res = new stdClass();
			$res->name = '';
			$res->size = '';
			$res->type = '';
			$res->url = '';
			$res->error = 'No images received for upload';
			$resp['files'][] = $res;
		}
		//Update current extra images string
		$q = "UPDATE `#__vikbooking_rooms` SET `moreimgs`=".$dbo->quote($moreimagestr)." WHERE `id`=".$proomid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$resp['actmoreimgs'] = $moreimagestr;
		//Update current extra images uploaded
		$cur_thumbs = '';
		$morei=explode(';;', $moreimagestr);
		if (@count($morei) > 0) {
			$imgcaptions = json_decode($cur_captions, true);
			$usecaptions = empty($imgcaptions) || is_null($imgcaptions) || !is_array($imgcaptions) || !(count($imgcaptions) > 0) ? false : true;
			$cur_thumbs .= '<ul class="vbo-sortable">';
			foreach ($morei as $ki => $mi) {
				if (!empty($mi)) {
					$cur_thumbs .= '<li class="vbo-editroom-currentphoto">';
					$cur_thumbs .= '<a href="'.VBO_SITE_URI.'resources/uploads/big_'.$mi.'" target="_blank" class="vbomodal"><img src="'.VBO_SITE_URI.'resources/uploads/thumb_'.$mi.'" class="maxfifty"/></a>';
					$cur_thumbs .= '<a class="vbo-toggle-imgcaption" href="javascript: void(0);" onclick="vbOpenImgDetails(\''.$ki.'\', this)"><i class="fa fa-cog"></i></a>';
					$cur_thumbs .= '<div id="vbimgdetbox'.$ki.'" class="vbimagedetbox" style="display: none;"><div class="captionlabel"><span>'.JText::_('VBIMGCAPTION').'</span><input type="text" name="caption'.$ki.'" value="'.($usecaptions === true && isset($imgcaptions[$ki]) ? $imgcaptions[$ki] : "").'" size="40"/></div><input type="hidden" name="imgsorting[]" value="'.$mi.'"/><input class="captionsubmit" type="button" name="updcatpion" value="'.JText::_('VBIMGUPDATE').'" onclick="javascript: updateCaptions();"/><div class="captionremoveimg"><a class="vbimgrm btn btn-danger" href="index.php?option=com_vikbooking&task=removemoreimgs&roomid='.$proomid.'&imgind='.$ki.'" title="'.JText::_('VBREMOVEIMG').'"><i class="icon-remove"></i>'.JText::_('VBREMOVEIMG').'</a></div></div>';
					$cur_thumbs .= '</li>';
				}
			}
			$cur_thumbs .= '</ul>';
			$cur_thumbs .= '<br clear="all"/>';
		}
		$resp['currentthumbs'] = $cur_thumbs;

		echo json_encode($resp);
		exit;
	}

	function loadsmsbalance() {
		//to be called via ajax
		$html = 'Error1 [N/A]';
		$sms_api = VikBooking::getSMSAPIClass();
		if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api)) {
			require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
			$sms_obj = new VikSmsApi(array(), VikBooking::getSMSParams());
			if (method_exists('VikSmsApi', 'estimate')) {
				$array_result = $sms_obj->estimate("+393711271611", "estimate credit");
				if ( $array_result->errorCode != 0 ) {
					$html = 'Error3 ['.$array_result->errorMsg.']';
				} else {
					$html = VikBooking::getCurrencySymb().' '.$array_result->userCredit;
				}
			} else {
				$html = 'Error2 [N/A]';
			}
		}
		echo $html;
		exit;
	}

	function loadsmsparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$sms_api = VikBooking::getSMSAPIClass();
			$sms_params = $sms_api == $phpfile ? VikBooking::getSMSParams(false) : '';
			$html = VikBooking::displaySMSParameters($phpfile, $sms_params);
		}
		echo $html;
		exit;
	}

	function loadcronparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikBooking::displayCronParameters($phpfile);
		}
		echo $html;
		exit;
	}

	function loadpaymentparams() {
		//to be called via ajax
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikBooking::displayPaymentParameters($phpfile);
		}
		echo $html;
		exit;
	}

	function setbookingtag() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$pidorder = VikRequest::getInt('idorder', '', 'request');
		$ptagkey = VikRequest::getInt('tagkey', '', 'request');
		if (!empty($pidorder) && $ptagkey >= 0) {
			$all_tags = VikBooking::loadBookingsColorTags();
			if (array_key_exists($ptagkey, $all_tags)) {
				$q = "SELECT `id` FROM `#__vikbooking_orders` WHERE `id`=".(int)$pidorder.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$newcolortag = json_encode($all_tags[$ptagkey]);
					$q = "UPDATE `#__vikbooking_orders` SET `colortag`=".$dbo->quote($newcolortag)." WHERE `id`=".(int)$pidorder.";";
					$dbo->setQuery($q);
					$dbo->execute();
					$newcolortag = $all_tags[$ptagkey];
					$newcolortag['name'] = JText::_($newcolortag['name']);
					$newcolortag['fontcolor'] = VikBooking::getBestColorContrast($newcolortag['color']);
					echo json_encode($newcolortag);
				} else {
					echo 'e4j.error.Booking ('.$pidorder.') not found';
				}
			} else {
				echo 'e4j.error.Color Tag ('.$ptagkey.') not found';
			}
		} else {
			echo 'e4j.error.Missing Data';
		}
		exit;
	}

	function updatereceiptnum() {
		//to be called via ajax
		$pnewnum = VikRequest::getInt('newnum', '', 'request');
		$pnewnotes = VikRequest::getString('newnotes', '', 'request', VIKREQUEST_ALLOWRAW);
		$poid = VikRequest::getInt('oid', '', 'request');
		if ($pnewnum > 0) {
			VikBooking::getNextReceiptNumber($poid, $pnewnum);
			VikBooking::getReceiptNotes($pnewnotes);
			//Booking History
			VikBooking::getBookingHistoryInstance()->setBid($poid)->store('BR', JText::_('VBOFISCRECEIPTNUM').': '.$pnewnum);
			//
			echo 'e4j.ok';
			exit;
		}
		echo 'e4j.error';
		exit;
	}

	function isroombookable() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$res = array(
			'status' => 0,
			'err' => ''
		);
		$prid = VikRequest::getInt('rid', '', 'request');
		$pfdate = VikRequest::getString('fdate', '', 'request');
		$ptdate = VikRequest::getString('tdate', '', 'request');
		$room_info = array();
		$q = "SELECT * FROM `#__vikbooking_rooms` WHERE `id`=".(int)$prid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$room_info = $dbo->loadAssoc();
		}
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$from_ts = VikBooking::getDateTimestamp($pfdate, $pcheckinh, $pcheckinm);
		$to_ts = VikBooking::getDateTimestamp($ptdate, $pcheckouth, $pcheckoutm);
		if (
			count($room_info) > 0 && 
			(!empty($pfdate) && !empty($ptdate) && !empty($from_ts) && !empty($to_ts)) && 
			VikBooking::roomBookable($room_info['id'], $room_info['units'], $from_ts, $to_ts)) 
		{
			$res['status'] = 1;
		} else {
			if (!(count($room_info) > 0)) {
				$res['err'] = 'Room not found';
			} elseif (empty($pfdate) || empty($ptdate) || empty($from_ts) || empty($to_ts)) {
				$res['err'] = 'Invalid dates';
			} else {
				//not available
				$res['err'] = JText::sprintf('VBOBOOKADDROOMERR', $room_info['name'], $pfdate, $ptdate);
			}
		}

		echo json_encode($res);
		exit;
	}

	function uploadsnapshot() {
		$snap_base = date('Y-m-d_H:i:s').'_'.rand(1000, 9999).'.jpg';
		$snapname = VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR.$snap_base;
		$input_data = file_get_contents('php://input');
		$result = file_put_contents($snapname, $input_data);
		if (!$result) {
			echo "e4j.error.Error (".@strlen($input_data)."): Failed to write data to ".$snapname.", check permissions";
			exit();
		}
		echo $snap_base;
		exit;
	}

	function checkvcmrateschanges() {
		//to be called via ajax
		$session = JFactory::getSession();
		$ret = array('changesCount' => 0, 'changesData' => '');
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		if (!empty($updforvcm) && is_array($updforvcm) && count($updforvcm) > 0) {
			$ret['changesCount'] = $updforvcm['count'];
			$ret['changesData'] = $updforvcm;
		}

		echo json_encode($ret);
		exit;
	}

	function getbookingsinfo() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$booking_infos = array();
		$bookings = array();
		$pidorders = VikRequest::getString('idorders', '', 'request');
		$psubroom = VikRequest::getString('subroom', '', 'request');
		if (!empty($pidorders)) {
			$bookings = explode(',', $pidorders);
			foreach ($bookings as $k => $v) {
				$v = intval(str_replace('-', '', $v));
				if (empty($v)) {
					unset($bookings[$k]);
					continue;
				}
				$bookings[$k] = $v;
			}
		}
		$bookings = array_values($bookings);
		if (!(count($bookings) > 0)) {
			echo 'e4j.error.1 '.addslashes(JText::_('VBOVWGETBKERRMISSDATA'));
			exit;
		}
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf=="%d/%m/%Y") {
			$df='d/m/Y';
		} elseif ($nowdf=="%m/%d/%Y") {
			$df='m/d/Y';
		} else {
			$df='Y/m/d';
		}
		$datesep = VikBooking::getDateSeparator(true);
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id` IN (".implode(', ', $bookings).") AND (`status`='confirmed' OR `status`='standby');";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_infos = $dbo->loadAssocList();
			foreach ($booking_infos as $k => $row) {
				//rooms, amounts and guests information
				$rooms = VikBooking::loadOrdersRoomsData($row['id']);
				$room_names = array();
				$totadults = 0;
				$totchildren = 0;
				foreach ($rooms as $rr) {
					$totadults += $rr['adults'];
					$totchildren += $rr['children'];
					$room_names[] = $rr['room_name'];
				}
				$booking_infos[$k]['status_lbl'] = ($row['status'] != 'confirmed' && $row['status'] != 'standby' ? $row['status'] : ($row['status'] == 'confirmed' ? JText::_('VBCONFIRMED') : JText::_('VBSTANDBY')));
				$booking_infos[$k]['colortag'] = VikBooking::applyBookingColorTag($row);
				if (count($booking_infos[$k]['colortag']) > 0) {
					$booking_infos[$k]['colortag']['name'] = JText::_($booking_infos[$k]['colortag']['name']);
				}
				$booking_infos[$k]['room_names'] = implode(', ', $room_names);
				$booking_infos[$k]['tot_adults'] = $totadults;
				$booking_infos[$k]['tot_children'] = $totchildren;
				$booking_infos[$k]['format_tot'] = VikBooking::numberFormat($row['total']);
				$booking_infos[$k]['format_totpaid'] = VikBooking::numberFormat($row['totpaid']);
				//Rooms Indexes
				$rindexes = array();
				$optindexes = array();
				$subroomdata = !empty($psubroom) ? explode('-', $psubroom) : array();
				foreach ($rooms as $or) {
					if ($row['status'] == "confirmed" && !empty($or['params']) && strlen($or['roomindex'])) {
						$room_params = json_decode($or['params'], true);
						if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
							foreach ($room_params['features'] as $rind => $rfeatures) {
								if ($rind == $or['roomindex']) {
									$ind_str = '';
									foreach ($rfeatures as $fname => $fval) {
										if (strlen($fval)) {
											$ind_str = '#'.$rind.' - '.JText::_($fname).': '.$fval;
											break;
										}
									}
									if (!array_key_exists($or['room_name'], $rindexes)) {
										$rindexes[$or['room_name']] = $ind_str;
									} else {
										$rindexes[$or['room_name']] .= ', '.$ind_str;
									}
									break;
								}
							}
							if (count($subroomdata) && !count($optindexes) && $or['idroom'] == (int)$subroomdata[0]) {
								// build the options for switching the room index for this room
								foreach ($room_params['features'] as $rind => $rfeatures) {
									foreach ($rfeatures as $fname => $fval) {
										if (strlen($fval)) {
											$optindexes[] = '<option value="'.$rind.'"'.($rind == (int)$subroomdata[1] ? ' selected="selected"' : '').'>#'.$rind.' - '.JText::_($fname).': '.$fval.'</option>';
											break;
										}
									}
								}
							}
						}
					}
				}
				if (count($rindexes)) {
					$booking_infos[$k]['rindexes'] = $rindexes;
				}
				if (count($optindexes)) {
					$booking_infos[$k]['optindexes'] = $optindexes;
				}
				//Channel Provenience
				$ota_logo_img = JText::_('VBORDFROMSITE');
				if (!empty($row['channel'])) {
					$channelparts = explode('_', $row['channel']);
					$otachannel = array_key_exists(1, $channelparts) && strlen($channelparts[1]) > 0 ? $channelparts[1] : ucwords($channelparts[0]);
					$ota_logo_img = VikBooking::getVcmChannelsLogo($otachannel);
					if ($ota_logo_img === false) {
						$ota_logo_img = $otachannel;
					} else {
						$ota_logo_img = '<img src="'.$ota_logo_img.'" class="vbo-channelimg-small"/>';
					}
				}
				$booking_infos[$k]['channelimg'] = $ota_logo_img;
				//Customer Details
				$custdata = $row['custdata'];
				$custdata_parts = explode("\n", $row['custdata']);
				if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
					//get the first two fields
					$custvalues = array();
					foreach ($custdata_parts as $custdet) {
						if (strlen($custdet) < 1) {
							continue;
						}
						$custdet_parts = explode(':', $custdet);
						if (count($custdet_parts) >= 2) {
							unset($custdet_parts[0]);
							array_push($custvalues, trim(implode(':', $custdet_parts)));
						}
						if (count($custvalues) > 1) {
							break;
						}
					}
					if (count($custvalues) > 1) {
						$custdata = implode(' ', $custvalues);
					}
				}
				if (strlen($custdata) > 45) {
					$custdata = substr($custdata, 0, 45)." ...";
				}

				$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$cust_country = $dbo->loadAssocList();
					$cust_country = $cust_country[0];
					if (!empty($cust_country['first_name'])) {
						$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
						if (!empty($cust_country['country'])) {
							if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$cust_country['country'].'.png')) {
								$custdata .= '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$cust_country['country'].'.png'.'" title="'.$cust_country['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
							}
						}
					}
				}
				$custdata = JText::_('VBDBTEXTROOMCLOSED') == $row['custdata'] ? '<span class="vbordersroomclosed">'.JText::_('VBDBTEXTROOMCLOSED').'</span>' : $custdata;
				$booking_infos[$k]['cinfo'] = $custdata;
				//Formatted dates
				$booking_infos[$k]['ts'] = date(str_replace("/", $datesep, $df).' H:i', $row['ts']);
				$booking_infos[$k]['checkin'] = date(str_replace("/", $datesep, $df).' H:i', $row['checkin']);
				$booking_infos[$k]['checkout'] = date(str_replace("/", $datesep, $df).' H:i', $row['checkout']);
			}
		}
		if (!(count($booking_infos) > 0)) {
			echo 'e4j.error.2 '.addslashes(JText::_('VBOVWGETBKERRMISSDATA'));
			exit;
		}

		echo json_encode($booking_infos);
		exit;
	}

	function switchRoomIndex() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$bid = VikRequest::getInt('bid', '', 'request');
		$rid = VikRequest::getInt('rid', '', 'request');
		$old_rindex = VikRequest::getInt('old_rindex', '', 'request');
		$new_rindex = VikRequest::getInt('new_rindex', '', 'request');
		if (empty($bid) || empty($rid) || empty($old_rindex) || empty($new_rindex)) {
			echo 'e4j.error.#1 Missing Data';
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_ordersrooms` WHERE `idorder`=".$bid." AND `idroom`=".$rid." AND `roomindex`=".$old_rindex.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			echo 'e4j.error.#2 Record not found';
			exit;
		}
		$rows = $dbo->loadAssocList();
		$q = "UPDATE `#__vikbooking_ordersrooms` SET `roomindex`=".$new_rindex." WHERE `id`=".$rows[0]['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		echo 'e4j.ok';
		exit;
	}

	function searchcustomer() {
		//to be called via ajax
		$kw = VikRequest::getString('kw', '', 'request');
		$nopin = VikRequest::getInt('nopin', '', 'request');
		$cstring = '';
		if (strlen($kw) > 0) {
			$dbo = JFactory::getDBO();
			if ($nopin > 0) {
				//page all bookings
				$q = "SELECT * FROM `#__vikbooking_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC LIMIT 30;";
			} else {
				//page calendar
				$q = "SELECT * FROM `#__vikbooking_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." OR `pin` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC;";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$customers = $dbo->loadAssocList();
				$cust_old_fields = array();
				$cstring_search = '';
				foreach ($customers as $k => $v) {
					$cstring_search .= '<div class="vbo-custsearchres-entry" data-custid="'.$v['id'].'" data-email="'.$v['email'].'" data-phone="'.addslashes($v['phone']).'" data-country="'.$v['country'].'" data-pin="'.$v['pin'].'" data-firstname="'.addslashes($v['first_name']).'" data-lastname="'.addslashes($v['last_name']).'">'."\n";
					$cstring_search .= '<span class="vbo-custsearchres-name hasTooltip" title="'.$v['email'].'">'.$v['first_name'].' '.$v['last_name'].'</span>'."\n";
					if (!($nopin > 0)) {
						$cstring_search .= '<span class="vbo-custsearchres-pin">'.$v['pin'].'</span>'."\n";
					}
					if (!empty($v['country'])) {
						if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$v['country'].'.png')) {
							$cstring_search .= '<span class="vbo-custsearchres-cflag"><img src="'.VBO_ADMIN_URI.'resources/countries/'.$v['country'].'.png'.'" title="'.$v['country'].'" class="vbo-country-flag"/></span>'."\n";
						}
					}
					$cstring_search .= '</div>'."\n";
					if (!empty($v['cfields'])) {
						$oldfields = json_decode($v['cfields'], true);
						if (is_array($oldfields) && count($oldfields)) {
							$cust_old_fields[$v['id']] = $oldfields;
						}
					}
				}
				$cstring = json_encode(array(($nopin > 0 ? '' : $cust_old_fields), $cstring_search));
			}
		}
		echo $cstring;
		exit;
	}

	function sharesignaturelink() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$response = array(
			'status' => 0,
			'error' => 'Generic Error'
		);
		$pbid = VikRequest::getInt('bid', '', 'request');
		$phow = VikRequest::getString('how', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pcustomer = VikRequest::getInt('customer', '', 'request');
		$cpin = VikBooking::getCPinIstance();
		$customer_info = $cpin->getCustomerByID($pcustomer);
		if (!empty($pbid) && !empty($phow) && !empty($pto) && count($customer_info) > 0) {
			$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$pbid." AND `status`='confirmed' AND `checked` > 0;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row = $dbo->loadAssoc();
				$share_link = JURI::root().'index.php?option=com_vikbooking&task=signature&sid='.$row['sid'].'&ts='.$row['ts'];
				$share_message = JText::sprintf('VBOSIGNSHAREMESSAGE', ltrim($customer_info['first_name'].' '.$customer_info['last_name']), $share_link, VikBooking::getFrontTitle());
				if ($phow == 'email') {
					$sender = VikBooking::getSenderMail();
					$vbo_app = new VboApplication();
					$vbo_app->sendMail($sender, $sender, $pto, $sender, JText::_('VBOSIGNSHARESUBJECT'), $share_message, false);
					$response['status'] = 1;
				} elseif ($phow == 'sms') {
					$share_message = JText::sprintf('VBOSIGNSHAREMESSAGESMS', ltrim($customer_info['first_name'].' '.$customer_info['last_name']), $share_link, VikBooking::getFrontTitle());
					$sms_api = VikBooking::getSMSAPIClass();
					$sms_api_params = VikBooking::getSMSParams();
					if (!empty($sms_api) && file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api) && !empty($sms_api_params)) {
						require_once(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'smsapi'.DIRECTORY_SEPARATOR.$sms_api);
						$sms_obj = new VikSmsApi(array(), $sms_api_params);
						$response_obj = $sms_obj->sendMessage($pto, $share_message);
						if ($sms_obj->validateResponse($response_obj)) {
							$response['status'] = 1;
						} else {
							$response['error'] = $sms_obj->getLog();
						}
					} else {
						$response['error'] = 'No SMS Provider Configured';
					}
				} else {
					$response['error'] = 'Invalid Sending Method';
				}
			} else {
				$response['error'] = 'Invalid Booking ID';
			}
		} else {
			$response['error'] = 'Empty values';
		}

		echo json_encode($response);
		exit;
	}

	function dayselectioncount() {
		//to be called via ajax
		$tsinit = VikRequest::getString('dinit', '', 'request');
		$tsend = VikRequest::getString('dend', '', 'request');
		if (strlen($tsinit) > 0 && strlen($tsend) > 0) {
			$ptsinit=VikBooking::getDateTimestamp($tsinit, '0', '0');
			$ptsend=VikBooking::getDateTimestamp($tsend, '23', '59');
			$diff = $ptsend - $ptsinit;
			if ($diff >= 172800) {
				$datef = VikBooking::getDateFormat(true);
				if ($datef=="%d/%m/%Y") {
					$df = 'd-m-Y';
				} else {
					$df = 'Y-m-d';
				}
				//minimum 2 days for excluding some days
				$daysdiff = floor($diff / 86400);
				$infoinit = getdate($ptsinit);
				$select = '';
				$select .= '<div style="display: inline-block;"><select name="excludeday[]" multiple="multiple" size="'.($daysdiff > 8 ? 8 : $daysdiff).'" id="vboexclusion">';
				for($i = 0; $i <= $daysdiff; $i++) {
					$ts = $i > 0 ? mktime(0, 0, 0, $infoinit['mon'], ((int)$infoinit['mday'] + $i), $infoinit['year']) : $ptsinit;
					$infots = getdate($ts);
					$optval = $infots['mon'].'-'.$infots['mday'].'-'.$infots['year'];
					$select .= '<option value="'.$optval.'">'.date($df, $ts).'</option>';
				}
				$select .= '</select></div>';
				//excluded days of the week
				if ($daysdiff >= 14) {
					$select .= '<div style="display: inline-block; margin-left: 40px;"><select name="excludewdays[]" multiple="multiple" size="8" id="excludewdays" onchange="vboExcludeWDays();">';
					$select .= '<optgroup label="'.JText::_('VBOEXCLWEEKD').'">';
					$select .= '<option value="0">'.JText::_('VBSUNDAY').'</option><option value="1">'.JText::_('VBMONDAY').'</option><option value="2">'.JText::_('VBTUESDAY').'</option><option value="3">'.JText::_('VBWEDNESDAY').'</option><option value="4">'.JText::_('VBTHURSDAY').'</option><option value="5">'.JText::_('VBFRIDAY').'</option><option value="6">'.JText::_('VBSATURDAY').'</option>';
					$select .= '</optgroup>';
					$select .= '</select></div>';
				}
				//
				echo $select;
			} else {
				echo '';
			}
		} else {
			echo '';
		}
		exit;
	}

	function createcheckindoc() {
		$cid = VikRequest::getVar('cid', array(0));
		$id = $cid[0];

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vbo_tn = VikBooking::getTranslator();
		$lang = JFactory::getLanguage();
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$psignature = VikRequest::getString('signature', '', 'request', VIKREQUEST_ALLOWRAW);
		$ppad_width = VikRequest::getInt('pad_width', '', 'request');
		$ppad_ratio = VikRequest::getInt('pad_ratio', '', 'request');
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$id." AND `status`='confirmed' AND `checked` > 0;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			$mainframe->redirect('index.php');
			exit;
		}
		$row = $dbo->loadAssoc();
		if (!empty($row['lang'])) {
			if ($lang->getTag() != $row['lang']) {
				$lang->load('com_vikbooking', JPATH_SITE, $row['lang'], true);
				$lang->load('com_vikbooking', JPATH_ADMINISTRATOR, $row['lang'], true);
				$vbo_tn::$force_tolang = $row['lang'];
			}
		}
		$customer = array();
		$q = "SELECT `c`.*,`co`.`idorder`,`co`.`signature`,`co`.`pax_data`,`co`.`comments`,`co`.`checkindoc` FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$customer = $dbo->loadAssoc();
			if (!empty($customer['country'])) {
				if (file_exists(VBO_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$customer['country'].'.png')) {
					$customer['country_img'] = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$customer['country'].'.png'.'" title="'.$customer['country'].'" class="vbo-country-flag vbo-country-flag-left"/>';
				}
			}
		}
		if (!(count($customer) > 0)) {
			VikError::raiseWarning('', JText::_('VBOCHECKINERRNOCUSTOMER'));
			$mainframe->redirect('index.php?option=com_vikbooking&task=newcustomer&checkin=1&bid='.$row['id'].($ptmpl == 'component' ? '&tmpl=component' : ''));
			exit;
		}
		$customer['pax_data'] = !empty($customer['pax_data']) ? json_decode($customer['pax_data'], true) : array();
		//check if the signature has been submitted
		$signature_data = '';
		$cont_type = '';
		if (!empty($psignature)) {
			//check whether the format is accepted
			if (strpos($psignature, 'image/png') !== false || strpos($psignature, 'image/jpeg') !== false || strpos($psignature, 'image/svg') !== false) {
				$parts = explode(';base64,', $psignature);
				$cont_type_parts = explode('image/', $parts[0]);
				$cont_type = $cont_type_parts[1];
				if (!empty($parts[1])) {
					$signature_data = base64_decode($parts[1]);
				}
			}
		}
		if (!empty($signature_data)) {
			//write file
			$sign_fname = $row['id'].'_'.$row['sid'].'_'.$customer['id'].'.'.$cont_type;
			$filepath = VBO_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'idscans' . DIRECTORY_SEPARATOR . $sign_fname;
			$fp = fopen($filepath, 'w+');
			$bytes = fwrite($fp, $signature_data);
			fclose($fp);
			if ($bytes !== false && $bytes > 0) {
				//update the signature in the DB
				$q = "UPDATE `#__vikbooking_customers_orders` SET `signature`=".$dbo->quote($sign_fname)." WHERE `idorder`=".(int)$row['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$customer['signature'] = $sign_fname;
				//resize image for screens with high resolution
				if ($ppad_ratio > 1) {
					$new_width = floor(($ppad_width / 2));
					$creativik = new vikResizer();
					$creativik->proportionalImage($filepath, $filepath, $new_width, $new_width);
				}
				//
			} else {
				VikError::raiseWarning('', JText::_('VBOERRSTORESIGNFILE'));
			}
		}
		//
		//generate PDF for check-in document by parsing the apposite template file
		$booking_rooms = array();
		$q = "SELECT `or`.*,`r`.`name` AS `room_name`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or` LEFT JOIN `#__vikbooking_rooms` `r` ON `r`.`id`=`or`.`idroom` WHERE `or`.`idorder`=".(int)$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$booking_rooms = $dbo->loadAssocList();
			if (!empty($row['lang'])) {
				$vbo_tn->translateContents($booking_rooms, '#__vikbooking_rooms', array('id' => 'idroom', 'room_name' => 'name'), array(), $row['lang']);
			}
		}
		if (!class_exists('TCPDF')) {
			require_once(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
		}
		$usepdffont = file_exists(VBO_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php") ? 'dejavusans' : 'helvetica';
		list($checkintpl, $pdfparams) = VikBooking::loadCheckinDocTmpl($row, $booking_rooms, $customer);
		$checkin_body = VikBooking::parseCheckinDocTemplate($checkintpl, $row, $booking_rooms, $customer);
		$pdffname = $row['id'] . '_' . $row['sid'] . '.pdf';
		$pathpdf = VBO_SITE_PATH . DS . "helpers" . DS . "checkins" . DS . "generated" . DS . $pdffname;
		if (file_exists($pathpdf)) @unlink($pathpdf);
		$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
		$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
		$pdf->SetTitle(JText::_('VBOCHECKINDOCTITLE'));
		//Header for each page of the pdf
		if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
			$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
		}
		//header and footer fonts
		$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
		$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
		//margins
		$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
		$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
		$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
		//
		$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
		$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
		$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
		if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
			$pdf->SetPrintHeader(false);
		}
		if ($pdfparams['show_footer'] == 0) {
			$pdf->SetPrintFooter(false);
		}
		$pdf->AddPage();
		$pdf->writeHTML($checkin_body, true, false, true, false, '');
		$pdf->lastPage();
		$pdf->Output($pathpdf, 'F');
		if (!file_exists($pathpdf)) {
			VikError::raiseWarning('', JText::_('VBOERRGENCHECKINDOC'));
		} else {
			$q = "UPDATE `#__vikbooking_customers_orders` SET `checkindoc`=".$dbo->quote($pdffname)." WHERE `idorder`=".(int)$row['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VBOGENCHECKINDOCSUCCESS'));
		}
		//

		$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id'].($ptmpl == 'component' ? '&tmpl=component' : ''));
		exit;
	}

	function updatebookingcheckin() {
		$cid = VikRequest::getVar('cid', array(0));
		$id = $cid[0];

		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pnewtotpaid = VikRequest::getFloat('newtotpaid', 0, 'request');
		$pguests = VikRequest::getVar('guests', array());
		$pcomments = VikRequest::getString('comments', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcheckin_action = VikRequest::getInt('checkin_action', '', 'request');
		$valid_actions = array(-1, 0, 1, 2);
		if (!in_array($pcheckin_action, $valid_actions)) {
			$mainframe->redirect('index.php');
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`=".(int)$id." AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			$mainframe->redirect('index.php');
			exit;
		}
		$row = $dbo->loadAssoc();
		$q = "SELECT * FROM `#__vikbooking_customers_orders` WHERE `idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			VikError::raiseWarning('', JText::_('VBOCHECKINERRNOCUSTOMER'));
			$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id'].($ptmpl == 'component' ? '&tmpl=component' : ''));
			exit;
		}
		$custorder = $dbo->loadAssoc();
		//update checked status and new total paid
		$q = "UPDATE `#__vikbooking_orders` SET `checked`=".$pcheckin_action."".($pnewtotpaid > 0 ? ', `totpaid`='.$pnewtotpaid : '')." WHERE `id`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		//Booking History
		$hist_type = 'A';
		if ($pcheckin_action < 0) {
			$hist_type = 'Z';
		} elseif ($pcheckin_action == 1) {
			$hist_type = 'B';
		} elseif ($pcheckin_action == 2) {
			$hist_type = 'C';
		}
		VikBooking::getBookingHistoryInstance()->setBid($row['id'])->store('R'.$hist_type);
		//
		//Guests Details
		$guests_details = array();
		list($pax_fields, $pax_fields_attributes) = VikBooking::getPaxFields();
		foreach ($pguests as $ind => $adults) {
			foreach ($adults as $aduind => $details) {
				foreach ($pax_fields as $key => $v) {
					if (isset($details[$key]) && !empty($details[$key])) {
						if (!isset($guests_details[$ind])) {
							$guests_details[$ind] = array();
						}
						if (!isset($guests_details[$ind][$aduind])) {
							$guests_details[$ind][$aduind] = array();
						}
						$guests_details[$ind][$aduind][$key] = $details[$key];
					}
				}
			}
		}
		if (count($guests_details)) {
			$q = "UPDATE `#__vikbooking_customers_orders` SET `pax_data`=".$dbo->quote(json_encode($guests_details))." WHERE `id`=".$custorder['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//'checked' status comments
		$q = "UPDATE `#__vikbooking_customers_orders` SET `comments`=".$dbo->quote($pcomments)." WHERE `id`=".$custorder['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe->enqueueMessage(JText::_('VBOCHECKINSTATUSUPDATED'));
		$mainframe->redirect('index.php?option=com_vikbooking&task=bookingcheckin&cid[]='.$row['id'].($pcheckin_action != $row['checked'] ? '&changed=1' : '').($ptmpl == 'component' ? '&tmpl=component' : ''));
		exit;
	}

	function alterbooking() {
		$dbo = JFactory::getDBO();
		$response = array('esit' => 1, 'message' => '', 'vcm' => '');
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pidorder = intval(str_replace('-', '', $pidorder));
		$poldidroom = VikRequest::getInt('oldidroom', '', 'request');
		$pidroom = VikRequest::getInt('idroom', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if ($pdebug == 1) {
			echo 'e4j.error.'.print_r($_POST, true);
			exit;
		}
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$info_tsto = getdate(strtotime($ptodate));
		$actualtsto = mktime(0, 0, 0, $info_tsto['mon'], ($info_tsto['mday'] + 1), $info_tsto['year']);
		$first = VikBooking::getDateTimestamp(date($df, strtotime($pfromdate)), $pcheckinh, $pcheckinm);
		$second = VikBooking::getDateTimestamp(date($df, $actualtsto), $pcheckouth, $pcheckoutm);
		$ptodate = date('Y-m-d', $second);
		if (!($second > $first)) {
			echo 'e4j.error.1 '.addslashes(JText::_('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		if (!($pidorder > 0) || !($pidroom > 0) || empty($pfromdate) || empty($ptodate)) {
			echo 'e4j.error.2 '.addslashes(JText::_('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		$q = "SELECT * FROM `#__vikbooking_orders` WHERE `id`='".$pidorder."' AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() != 1) {
			echo 'e4j.error.3 '.addslashes(JText::_('VBOVWALTBKERRMISSDATA'));
			exit;
		}
		$ord = $dbo->loadAssocList();
		$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$ord[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$ordersrooms = $dbo->loadAssocList();
		$ord[0]['rooms_info'] = $ordersrooms;
		//Package or custom rate
		$is_package = !empty($ord[0]['pkg']) ? true : false;
		$is_cust_cost = false;
		foreach ($ordersrooms as $kor => $or) {
			if ($is_package !== true && !empty($or['cust_cost']) && $or['cust_cost'] > 0.00) {
				$is_cust_cost = true;
				break;
			}
		}
		//
		$toswitch = array();
		$idbooked = array();
		$rooms_units = array();
		$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$all_rooms = $dbo->loadAssocList();
		foreach ($all_rooms as $rr) {
			$rooms_units[$rr['id']]['name'] = $rr['name'];
			$rooms_units[$rr['id']]['units'] = $rr['units'];
		}
		//Switch room
		if ($poldidroom != $pidroom) {
			foreach ($ordersrooms as $ind => $or) {
				if ($poldidroom == $or['idroom'] && array_key_exists($pidroom, $rooms_units)) {
					//$idbooked is not really needed as switch is never made for the same room id
					$idbooked[$or['idroom']]++;
					//
					$orkey = count($toswitch);
					$toswitch[$orkey]['from'] = $or['idroom'];
					$toswitch[$orkey]['to'] = intval($pidroom);
					$toswitch[$orkey]['record'] = $or;
					break;
				}
			}
		}
		if (count($toswitch) > 0) {
			foreach ($toswitch as $ksw => $rsw) {
				$plusunit = array_key_exists($rsw['to'], $idbooked) ? $idbooked[$rsw['to']] : 0;
				if (!VikBooking::roomBookable($rsw['to'], ($rooms_units[$rsw['to']]['units'] + $plusunit), $ord[0]['checkin'], $ord[0]['checkout'])) {
					unset($toswitch[$ksw]);
					echo 'e4j.error.'.JText::sprintf('VBSWITCHRERR', $rsw['record']['name'], $rooms_units[$rsw['to']]['name']);
					exit;
				}
			}
			if (count($toswitch) > 0) {
				//reset first record rate so that rates can be set again (rates are unset only if the room is switched, if just the dates are different the rates are kept equal as the num nights is the same)
				reset($ordersrooms);
				$q = "UPDATE `#__vikbooking_ordersrooms` SET `idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$ordersrooms[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//
				foreach ($toswitch as $ksw => $rsw) {
					$q = "UPDATE `#__vikbooking_ordersrooms` SET `idroom`=".$rsw['to'].",`idtar`=NULL,`roomindex`=NULL,`room_cost`=NULL WHERE `id`=".$rsw['record']['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$response['message'] .= JText::sprintf('VBOVWALTBKSWITCHROK', $rsw['record']['name'], $rooms_units[$rsw['to']]['name'])."\n";
					//update Notes field for this booking to keep track of the previous room that was assigned
					$prev_room_name = array_key_exists($rsw['from'], $rooms_units) ? $rooms_units[$rsw['from']]['name'] : '';
					if (!empty($prev_room_name)) {
						$new_notes = JText::sprintf('VBOPREVROOMMOVED', $prev_room_name, date($df.' H:i:s'))."\n".$ord[0]['adminnotes'];
						$q = "UPDATE `#__vikbooking_orders` SET `adminnotes`=".$dbo->quote($new_notes)." WHERE `id`=".(int)$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
					//
					if ($ord[0]['status'] == 'confirmed') {
						//update record in _busy
						$q = "SELECT `b`.`id`,`b`.`idroom`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`=" . $rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id']." LIMIT 1;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$cur_busy = $dbo->loadAssocList();
							$q = "UPDATE `#__vikbooking_busy` SET `idroom`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `idroom`=".$cur_busy[0]['idroom']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//if automated updates enabled, keep $response['vcm'] empty
						//Invoke Channel Manager
						if (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
							$response['vcm'] = JText::_('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=overview").'"/><button type="submit" class="btn btn-primary">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button></form>';
						}
						//
					} elseif ($ord[0]['status'] == 'standby') {
						//remove record in _tmplock
						$q = "DELETE FROM `#__vikbooking_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//do not terminate the process when there is a switch, proceed to check the dates.
			}
		}
		//end Switch room
		//Change Dates
		if (date('Y-m-d', $ord[0]['checkin']) != $pfromdate || date('Y-m-d', $ord[0]['checkout']) != $ptodate) {
			$daysdiff = $ord[0]['days'];
			//re-read ordersrooms (as rooms may have been switched)
			$q = "SELECT `or`.*,`r`.`name`,`r`.`idopt`,`r`.`units`,`r`.`fromadult`,`r`.`toadult` FROM `#__vikbooking_ordersrooms` AS `or`,`#__vikbooking_rooms` AS `r` WHERE `or`.`idorder`='".$ord[0]['id']."' AND `or`.`idroom`=`r`.`id` ORDER BY `or`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$ordersrooms = $dbo->loadAssocList();
			//
			$groupdays = VikBooking::getGroupDays($first, $second, $daysdiff);
			$opertwounits = true;
			$units_counter = array();
			foreach ($ordersrooms as $ind => $or) {
				if (!isset($units_counter[$or['idroom']])) {
					$units_counter[$or['idroom']] = -1;
				}
				$units_counter[$or['idroom']]++;
			}
			foreach ($ordersrooms as $ind => $or) {
				$num = $ind + 1;
				$check = "SELECT `b`.`id`,`b`.`checkin`,`b`.`realback`,`ob`.`idorder` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`idroom`='" . $or['idroom'] . "' AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`!='".$ord[0]['id']."';";
				$dbo->setQuery($check);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$busy = $dbo->loadAssocList();
					foreach ($groupdays as $gday) {
						$bfound = 0;
						foreach ($busy as $bu) {
							if ($gday >= $bu['checkin'] && $gday <= $bu['realback']) {
								$bfound++;
							}
						}
						if ($bfound >= ($or['units'] - $units_counter[$or['idroom']]) || !VikBooking::roomNotLocked($or['idroom'], $or['units'], $first, $second)) {
							$opertwounits = false;
						}
					}
				}
			}
			if ($opertwounits !== true) {
				$response['esit'] = 0;
				$response['message'] = JText::_('VBROOMNOTRIT')." ".date($df.' H:i', $first)." ".JText::_('VBROOMNOTCONSTO')." ".date($df.' H:i', $second);
				echo json_encode($response);
				exit;
			}
			//update dates and busy records
			$realback = VikBooking::getHoursRoomAvail() * 3600;
			$realback += $second;
			$q = "UPDATE `#__vikbooking_orders` SET `checkin`='".$first."', `checkout`='".$second."' WHERE `id`='".$ord[0]['id']."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($ord[0]['status'] == 'confirmed') {
				$q = "SELECT `b`.`id` FROM `#__vikbooking_busy` AS `b`,`#__vikbooking_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`='".$ord[0]['id']."';";
				$dbo->setQuery($q);
				$dbo->execute();
				$allbusy = $dbo->loadAssocList();
				foreach ($allbusy as $bb) {
					$q = "UPDATE `#__vikbooking_busy` SET `checkin`='".$first."', `checkout`='".$second."', `realback`='".$realback."' WHERE `id`='".$bb['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				//if automated updates enabled, keep $response['vcm'] empty
				//Invoke Channel Manager
				if (file_exists(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR ."synch.vikbooking.php")) {
					$response['vcm'] = JText::_('VBCHANNELMANAGERINVOKEASK').' <form action="index.php?option=com_vikbooking" method="post"><input type="hidden" name="option" value="com_vikbooking"/><input type="hidden" name="task" value="invoke_vcm"/><input type="hidden" name="stype" value="modify"/><input type="hidden" name="cid[]" value="'.$ord[0]['id'].'"/><input type="hidden" name="origb" value="'.urlencode(json_encode($ord[0])).'"/><input type="hidden" name="returl" value="'.urlencode("index.php?option=com_vikbooking&task=overview").'"/><button type="submit" class="btn btn-primary">'.JText::_('VBCHANNELMANAGERSENDRQ').'</button></form>';
				}
				//
			}
			$response['message'] .= JText::_('RESUPDATED')."\n";
			//
		}
		//end Change Dates
		
		if (count($toswitch) > 0) {
			//TODO: rooms have changed so the new rates must be re-calculated. Maybe they should be calculated in any case, even if just the dates have changed. For the moment the rates are reset
		}

		//Booking History
		VikBooking::getBookingHistoryInstance()->setBid($ord[0]['id'])->store('MB', VikBooking::getLogBookingModification($ord[0]));
		//

		$vcm_autosync = VikBooking::vcmAutoUpdate();
		if ($vcm_autosync > 0 && !empty($response['vcm'])) {
			//unset the vcm property as no buttons should be displayed when in auto-sync
			$response['vcm'] = '';
			$vcm_obj = VikBooking::getVcmInvoker();
			$vcm_obj->setOids(array($ord[0]['id']))->setSyncType('modify')->setOriginalBooking($ord[0]);
			$sync_result = $vcm_obj->doSync();
			if ($sync_result === false) {
				$response['message'] .= JText::_('VBCHANNELMANAGERRESULTKO')." (".$vcm_obj->getError().")\n";
			}
		}

		//in case of error but not empty VCM message, set an error that will be displayed after the mustReload
		if ($response['esit'] < 1 && !empty($response['vcm'])) {
			VikError::raiseNotice('', $response['vcm']);
		}
		//
		
		$response['message'] = nl2br($response['message']);
		echo json_encode($response);
		exit;
	}

	function modroomrateplans() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$pid_room = VikRequest::getInt('id_room', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_room) || empty($pid_price) || empty($ptype) || empty($pfromdate) || empty($ptodate) || !(strtotime($pfromdate) > 0)  || !(strtotime($ptodate) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRMODRPLANS'));
			exit;
		}
		$price_record = array();
		$q = "SELECT * FROM `#__vikbooking_prices` WHERE `id`=".$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$price_record = $dbo->loadAssoc();
		}
		if (!count($price_record) > 0) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRMODRPLANS')).'.';
			exit;
		}
		$current_closed = array();
		if (!empty($price_record['closingd'])) {
			$current_closed = json_decode($price_record['closingd'], true);
		}
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		$all_days = array();
		$output = array();
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$all_days[] = date('Y-m-d', $infostart[0]);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$output[$indkey] = array();
			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if ($ptype == 'close') {
			if (!array_key_exists($pid_room, $current_closed)) {
				$current_closed[$pid_room] = array();
			}
			foreach ($all_days as $daymod) {
				if (!in_array($daymod, $current_closed[$pid_room])) {
					$current_closed[$pid_room][] = $daymod;
				}
			}
		} else {
			//open
			if (array_key_exists($pid_room, $current_closed)) {
				foreach ($all_days as $daymod) {
					if (in_array($daymod, $current_closed[$pid_room])) {
						foreach ($current_closed[$pid_room] as $ck => $cv) {
							if ($daymod == $cv) {
								unset($current_closed[$pid_room][$ck]);
							}
						}
					}
				}
			} else {
				$current_closed[$pid_room] = array();
			}
		}
		if (!count($current_closed[$pid_room]) > 0) {
			unset($current_closed[$pid_room]);
		}
		$q = "UPDATE `#__vikbooking_prices` SET `closingd`=".(count($current_closed) > 0 ? $dbo->quote(json_encode($current_closed)) : "NULL")." WHERE `id`=".(int)$pid_price.";";
		$dbo->setQuery($q);
		$dbo->execute();
		$oldcsscls = $ptype == 'close' ? 'vbo-roverw-rplan-on' : 'vbo-roverw-rplan-off';
		$newcsscls = $ptype == 'close' ? 'vbo-roverw-rplan-off' : 'vbo-roverw-rplan-on';
		foreach ($output as $ok => $ov) {
			$output[$ok] = array('oldcls' => $oldcsscls, 'newcls' => $newcsscls);
		}
		//update session values
		$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
		if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
			$updforvcm['dfrom'] = $updforvcm['dfrom'] > $start_ts ? $start_ts : $updforvcm['dfrom'];
		} else {
			$updforvcm['dfrom'] = $start_ts;
		}
		if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
			$updforvcm['dto'] = $updforvcm['dto'] < $end_ts ? $end_ts : $updforvcm['dto'];
		} else {
			$updforvcm['dto'] = $end_ts;
		}
		if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
			if (!in_array($pid_room, $updforvcm['rooms'])) {
				$updforvcm['rooms'][] = $pid_room;
			}
		} else {
			$updforvcm['rooms'] = array($pid_room);
		}
		if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
			if (array_key_exists($pid_room, $updforvcm['rplans'])) {
				if (!in_array($pid_price, $updforvcm['rplans'][$pid_room])) {
					$updforvcm['rplans'][$pid_room][] = $pid_price;
				}
			} else {
				$updforvcm['rplans'][$pid_room] = array($pid_price);
			}
		} else {
			$updforvcm['rplans'] = array($pid_room => array($pid_price));
		}
		$session->set('vbVcmRatesUpd', $updforvcm);
		//
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($current_closed, true)."\n";
			echo print_r($output, true)."\n\n";
			echo print_r($all_days, true)."\n";
		}
		echo json_encode($output);
		exit;
	}

	function setnewrates() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$updforvcm = $session->get('vbVcmRatesUpd', '');
		$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
		$currencysymb = VikBooking::getCurrencySymb();
		$vbo_df = VikBooking::getDateFormat();
		$df = $vbo_df == "%d/%m/%Y" ? 'd/m/Y' : ($vbo_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y/m/d');
		$pcheckinh = 0;
		$pcheckinm = 0;
		$pcheckouth = 0;
		$pcheckoutm = 0;
		$timeopst = VikBooking::getTimeOpenStore();
		if (is_array($timeopst)) {
			$opent = VikBooking::getHoursMinutes($timeopst[0]);
			$closet = VikBooking::getHoursMinutes($timeopst[1]);
			$pcheckinh = $opent[0];
			$pcheckinm = $opent[1];
			$pcheckouth = $closet[0];
			$pcheckoutm = $closet[1];
		}
		$pid_room = VikRequest::getInt('id_room', '', 'request');
		$pid_price = VikRequest::getInt('id_price', '', 'request');
		$prate = VikRequest::getString('rate', '', 'request');
		$prate = (float)$prate;
		$pvcm = VikRequest::getInt('vcm', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		if (empty($pid_room) || empty($pid_price) || empty($prate) || !($prate > 0) || empty($pfromdate) || empty($ptodate)) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRNEWRATE'));
			exit;
		}
		$roomrates = array();
		//read the rates for the lowest number of nights
		//the old query below used to cause an error #1055 when sql_mode=only_full_group_by
		//$q = "SELECT `r`.*,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$pid_room." AND `idprice`=".(int)$pid_price." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idroom`=".(int)$pid_room." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`idprice` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$q = "SELECT `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` FROM `#__vikbooking_dispcost` AS `r` INNER JOIN (SELECT MIN(`days`) AS `min_days` FROM `#__vikbooking_dispcost` WHERE `idroom`=".(int)$pid_room." AND `idprice`=".(int)$pid_price." GROUP BY `idroom`) AS `r2` ON `r`.`days`=`r2`.`min_days` LEFT JOIN `#__vikbooking_prices` `p` ON `p`.`id`=`r`.`idprice` AND `p`.`id`=".(int)$pid_price." WHERE `r`.`idroom`=".(int)$pid_room." AND `r`.`idprice`=".(int)$pid_price." GROUP BY `r`.`id`,`r`.`idroom`,`r`.`days`,`r`.`idprice`,`r`.`cost`,`p`.`name` ORDER BY `r`.`days` ASC, `r`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$roomrates = $dbo->loadAssocList();
			foreach ($roomrates as $rrk => $rrv) {
				$roomrates[$rrk]['cost'] = round(($rrv['cost'] / $rrv['days']), 2);
				$roomrates[$rrk]['days'] = 1;
			}
		}
		//
		if (!(count($roomrates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRNORATES'));
			exit;
		}
		$roomrates = $roomrates[0];
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikBooking::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikBooking::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikBooking::applySeasonsRoom(array($roomrates), $today_tsin, $today_tsout);
			$current_rates[(date('Y-m-d', $infostart[0]))] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		if (!(count($current_rates) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRNORATES').'.');
			exit;
		}
		$all_days = array_keys($current_rates);
		$season_intervals = array();
		$firstind = 0;
		$firstdaycost = $current_rates[$all_days[0]]['cost'];
		$nextdaycost = false;
		for ($i=1; $i < count($all_days); $i++) {
			$ind = $all_days[$i];
			$nextdaycost = $current_rates[$ind]['cost'];
			if ($firstdaycost != $nextdaycost) {
				$interval = array(
					'from' => $all_days[$firstind],
					'to' => $all_days[($i - 1)],
					'cost' => $firstdaycost
				);
				$season_intervals[] = $interval;
				$firstdaycost = $nextdaycost;
				$firstind = $i;
			}
		}
		if ($nextdaycost === false) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[$firstind],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		} elseif ($firstdaycost == $nextdaycost) {
			$interval = array(
				'from' => $all_days[$firstind],
				'to' => $all_days[($i - 1)],
				'cost' => $firstdaycost
			);
			$season_intervals[] = $interval;
		}
		foreach ($season_intervals as $sik => $siv) {
			if ((float)$siv['cost'] == $prate) {
				unset($season_intervals[$sik]);
			}
		}
		if (!(count($season_intervals) > 0)) {
			echo 'e4j.error.'.addslashes(JText::_('VBRATESOVWERRNORATESMOD'));
			exit;
		}
		foreach ($season_intervals as $sik => $siv) {
			$first = strtotime($siv['from']);
			$second = strtotime($siv['to']);
			if ($second > 0 && $second == $first) {
				$second += 86399;
			}
			if (!($second > $first)) {
				unset($season_intervals[$sik]);
				continue;
			}
			$baseone = getdate($first);
			$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
			$sfrom = $baseone[0] - $basets;
			$basetwo = getdate($second);
			$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
			$sto = $basetwo[0] - $basets;
			//check leap year
			if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
				if ($baseone[0] >= $leapts) {
					$sfrom -= 86400;
				}
			}
			if ($basetwo['year'] % 4 == 0 && ($basetwo['year'] % 100 != 0 || $basetwo['year'] % 400 == 0)) {
				$leapts = mktime(0, 0, 0, 2, 29, $basetwo['year']);
				if ($basetwo[0] >= $leapts) {
					$sto -= 86400;
				}
			}
			//end leap year
			$tieyear = $baseone['year'];
			$ptype = (float)$siv['cost'] > $prate ? "2" : "1";
			$pdiffcost = $ptype == "1" ? ($prate - (float)$siv['cost']) : ((float)$siv['cost'] - $prate);
			$roomstr = "-".$pid_room."-,";
			$pspname = date('Y-m-d H:i').' - '.substr($baseone['month'], 0, 3).' '.$baseone['mday'].($siv['from'] != $siv['to'] ? '/'.($baseone['month'] != $basetwo['month'] ? substr($basetwo['month'], 0, 3).' ' : '').$basetwo['mday'] : '');
			$pval_pcent = 1;
			$pricestr = "-".$pid_price."-,";
			$q = "INSERT INTO `#__vikbooking_seasons` (`type`,`from`,`to`,`diffcost`,`idrooms`,`spname`,`wdays`,`checkinincl`,`val_pcent`,`losoverride`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`,`promominlos`,`occupancy_ovr`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($roomstr).", ".$dbo->quote($pspname).", '', '0', '".$pval_pcent."', '', NULL, ".$tieyear.", ".$dbo->quote($pricestr).", 0, NULL, '', 0, NULL);";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//prepare output by re-calculating the rates in real-time
		$current_rates = array();
		$start_ts = strtotime($pfromdate);
		$end_ts = strtotime($ptodate);
		$infostart = getdate($start_ts);
		while ($infostart[0] > 0 && $infostart[0] <= $end_ts) {
			$today_tsin = VikBooking::getDateTimestamp(date($df, $infostart[0]), $pcheckinh, $pcheckinm);
			$today_tsout = VikBooking::getDateTimestamp(date($df, mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year'])), $pcheckouth, $pcheckoutm);

			$tars = VikBooking::applySeasonsRoom(array($roomrates), $today_tsin, $today_tsout);
			$indkey = $infostart['mday'].'-'.$infostart['mon'].'-'.$infostart['year'].'-'.$pid_price;
			$current_rates[$indkey] = $tars[0];

			$infostart = getdate(mktime(0, 0, 0, $infostart['mon'], ($infostart['mday'] + 1), $infostart['year']));
		}
		//launch channel manager or update session values for VCM
		if ($pvcm > 0) {
			//launch channel manager (from VBO, unlikely through the App, we update one rate plan per request)
			if (!class_exists('VikChannelManager')) {
				require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "lib.vikchannelmanager.php");
				require_once(VCM_SITE_PATH . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "vcm_config.php");
			}
			$vcm_logos = VikBooking::getVcmChannelsLogo('', true);
			$channels_updated = array();
			$channels_bkdown = array();
			$channels_success = array();
			$channels_warnings = array();
			$channels_errors = array();
			//load room details
			$row = array();
			$q = "SELECT `id`,`name`,`units` FROM `#__vikbooking_rooms` WHERE `id`=".(int)$pid_room.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$row = $dbo->loadAssoc();
				$row['channels'] = array();
				//Get the mapped channels for this room
				$q = "SELECT * FROM `#__vikchannelmanager_roomsxref` WHERE `idroomvb`=".(int)$pid_room.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$channels_data = $dbo->loadAssocList();
					foreach ($channels_data as $ch_data) {
						$row['channels'][$ch_data['idchannel']] = $ch_data;
					}
				}
			}
			if (count($row) && isset($row['channels']) && count($row['channels'])) {
				//this room is actually mapped to some channels supporting AV requests
				//load the 'Bulk Action - Rates Upload' cache
				$bulk_rates_cache = VikChannelManager::getBulkRatesCache();
				//we update one rate plan per time, even though we could update all of them with a similar request
				$rates_data = array(
					array(
						'rate_id' => $pid_price,
						'cost' => $prate
					)
				);
				//build the array with the update details
				$update_rows = array();
				foreach ($rates_data as $rk => $rd) {
					$node = $row;
					$setminlos = '';
					$setmaxlos = '';
					//check bulk rates cache to see if the exact rate should be increased for the channels (the exact rate has already been set in VBO at this point of the code)
					if (isset($bulk_rates_cache[$pid_room]) && isset($bulk_rates_cache[$pid_room][$rd['rate_id']])) {
						if ((int)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmod'] > 0 && (float)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodamount'] > 0) {
							if ((int)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodop'] > 0) {
								//Increase rates
								if ((int)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodval'] > 0) {
									//Percentage charge
									$rd['cost'] = $rd['cost'] * (100 + (float)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodamount']) / 100;
								} else {
									//Fixed charge
									$rd['cost'] += (float)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodamount'];
								}
							} else {
								//Lower rates
								if ((int)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodval'] > 0) {
									//Percentage discount
									$disc_op = $rd['cost'] * (float)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodamount'] / 100;
									$rd['cost'] -= $disc_op;
								} else {
									//Fixed discount
									$rd['cost'] -= (float)$bulk_rates_cache[$pid_room][$rd['rate_id']]['rmodamount'];
								}
							}
						}
					}
					//
					$node['ratesinventory'] = array(
						$pfromdate.'_'.$ptodate.'_'.$setminlos.'_'.$setmaxlos.'_1_2_'.$rd['cost'].'_0'
					);
					$node['pushdata'] = array(
						'pricetype' => $rd['rate_id'],
						'defrate' => $roomrates['cost'],
						'rplans' => array(),
						'cur_rplans' => array(),
						'rplanarimode' => array()
					);
					//build push data for each channel rate plan according to the Bulk Rates Cache or to the OTA Pricing
					if (isset($bulk_rates_cache[$pid_room]) && isset($bulk_rates_cache[$pid_room][$rd['rate_id']])) {
						//Bulk Rates Cache available for this room_id and rate_id
						$node['pushdata']['rplans'] = $bulk_rates_cache[$pid_room][$rd['rate_id']]['rplans'];
						$node['pushdata']['cur_rplans'] = $bulk_rates_cache[$pid_room][$rd['rate_id']]['cur_rplans'];
						$node['pushdata']['rplanarimode'] = $bulk_rates_cache[$pid_room][$rd['rate_id']]['rplanarimode'];
					}
					//check the channels mapped for this room and add what was not found in the Bulk Rates Cache, if anything
					foreach ($node['channels'] as $idchannel => $ch_data) {
						if (!isset($node['pushdata']['rplans'][$idchannel])) {
							//this channel was not found in the Bulk Rates Cache. Read data from OTA Pricing
							$otapricing = json_decode($ch_data['otapricing'], true);
							$ch_rplan_id = '';
							if (is_array($otapricing) && isset($otapricing['RatePlan'])) {
								foreach ($otapricing['RatePlan'] as $rpkey => $rpv) {
									//get the first key (rate plan ID) of the RatePlan array from OTA Pricing
									$ch_rplan_id = $rpkey;
									break;
								}
							}
							if (empty($ch_rplan_id)) {
								unset($node['channels'][$idchannel]);
								continue;
							}
							//set channel rate plan data
							$node['pushdata']['rplans'][$idchannel] = $ch_rplan_id;
							if ($idchannel == (int)VikChannelManagerConfig::BOOKING) {
								//Default Pricing is used by default, when no data available
								$node['pushdata']['rplanarimode'][$idchannel] = 'person';
							}
						}
					}
					//add update node
					array_push($update_rows, $node);
				}
				//Invoke the connector for any update request
				$vboConnector = VikChannelManager::getVikBookingConnectorInstance();
				//Update rates on the various channels
				$channels_map = array();
				foreach ($update_rows as $update_row) {
					if (!(count($update_row['channels']) > 0)) {
						continue;
					}
					if (!(count($channels_updated) > 0)) {
						foreach ($update_row['channels'] as $ch) {
							$channels_map[$ch['idchannel']] = ucfirst($ch['channel']);
							$ota_logo_url = is_object($vcm_logos) ? $vcm_logos->setProvenience($ch['channel'])->getLogoURL() : false;
							$channel_logo = $ota_logo_url !== false ? $ota_logo_url : '';
							$channels_updated[$ch['idchannel']] = array(
								'id' 	=> $ch['idchannel'],
								'name' 	=> ucfirst($ch['channel']),
								'logo' 	=> $channel_logo
							);
						}
					}
					//prepare request data
					$channels_ids = array_keys($update_row['channels']);
					$channels_rplans = array();
					foreach ($channels_ids as $ch_id) {
						$ch_rplan = isset($update_row['pushdata']['rplans'][$ch_id]) ? $update_row['pushdata']['rplans'][$ch_id] : '';
						$ch_rplan .= isset($update_row['pushdata']['rplanarimode'][$ch_id]) ? '='.$update_row['pushdata']['rplanarimode'][$ch_id] : '';
						$ch_rplan .= isset($update_row['pushdata']['cur_rplans'][$ch_id]) && !empty($update_row['pushdata']['cur_rplans'][$ch_id]) ? ':'.$update_row['pushdata']['cur_rplans'][$ch_id] : '';
						$channels_rplans[] = $ch_rplan;
					}
					$channels = array(
						implode(',', $channels_ids)
					);
					$chrplans = array(
						implode(',', $channels_rplans)
					);
					$nodes = array(
						implode(';', $update_row['ratesinventory'])
					);
					$rooms = array($pid_room);
					$pushvars = array(
						implode(';', array($update_row['pushdata']['pricetype'], $update_row['pushdata']['defrate']))
					);
					//send the request
					//set the caller to 'VBO' to reduce the sleep time between the requests
					$vboConnector->caller = 'VBO';
					//
					$result = $vboConnector->channelsRatesPush($channels, $chrplans, $nodes, $rooms, $pushvars);
					if ($vc_error = $vboConnector->getError(true)) {
						$channels_errors[] = $vc_error;
						continue;
					}
					//parse the channels update result and compose success, warnings, errors
					$result_pool = json_decode($result, true);
					foreach ($result_pool as $rid => $ch_responses) {
						foreach ($ch_responses as $ch_id => $ch_res) {
							if ($ch_id == 'breakdown' || !is_numeric($ch_id)) {
								//get the rates/dates breakdown of the update request
								$bkdown = $ch_res;
								if (is_array($ch_res)) {
									$bkdown = '';
									foreach ($ch_res as $bk => $bv) {
										$bkparts = explode('-', $bk);
										if (count($bkparts) == 6) {
											//breakdown key is usually composed of two dates in Y-m-d concatenated with another "-".
											$bkdown .= ucwords(JText::_('VBDAYSFROM')).' '.implode('-', array_slice($bkparts, 0, 3)).' - '.ucwords(JText::_('VBDAYSTO')).' '.implode('-', array_slice($bkparts, 3, 3)).': '.$bv."\n";
										} else {
											$bkdown .= $bk.': '.$bv."\n";
										}
									}
									$bkdown = rtrim($bkdown, "\n");
								}
								if (!isset($channels_bkdown[$ch_id])) {
									$channels_bkdown[$ch_id] = $bkdown;
								} else {
									$channels_bkdown[$ch_id] .= "\n".$bkdown;
								}
								continue;
							}
							$ch_id = (int)$ch_id;
							if (substr($ch_res, 0, 6) == 'e4j.OK') {
								//success
								if (!isset($channels_success[$ch_id])) {
									$channels_success[$ch_id] = $channels_map[$ch_id];
								}
							} elseif (substr($ch_res, 0, 11) == 'e4j.warning') {
								//warning
								if (!isset($channels_warnings[$ch_id])) {
									$channels_warnings[$ch_id] = $channels_map[$ch_id].': '.str_replace('e4j.warning.', '', $ch_res);
								} else {
									$channels_warnings[$ch_id] .= "\n".str_replace('e4j.warning.', '', $ch_res);
								}
								//add the channel also to the successful list in case of Warning
								if (!isset($channels_success[$ch_id])) {
									$channels_success[$ch_id] = $channels_map[$ch_id];
								}
							} elseif (substr($ch_res, 0, 9) == 'e4j.error') {
								//error
								if (!isset($channels_errors[$ch_id])) {
									$channels_errors[$ch_id] = $channels_map[$ch_id].': '.str_replace('e4j.error.', '', $ch_res);
								} else {
									$channels_errors[$ch_id] .= "\n".str_replace('e4j.error.', '', $ch_res);
								}
							}
						}
					}
				}
			}
			if (count($channels_updated)) {
				$current_rates['vcm'] = array(
					'channels_updated' => $channels_updated
				);
				//set these property only if not empty
				if (count($channels_bkdown)) {
					$current_rates['vcm']['channels_bkdown'] = $channels_bkdown['breakdown'];
				}
				if (count($channels_success)) {
					$current_rates['vcm']['channels_success'] = $channels_success;
				}
				if (count($channels_warnings)) {
					$current_rates['vcm']['channels_warnings'] = $channels_warnings;
				}
				if (count($channels_errors)) {
					$current_rates['vcm']['channels_errors'] = $channels_errors;
				}
			}
		} else {
			//update session values
			$updforvcm['count'] = array_key_exists('count', $updforvcm) && !empty($updforvcm['count']) ? ($updforvcm['count'] + 1) : 1;
			if (array_key_exists('dfrom', $updforvcm) && !empty($updforvcm['dfrom'])) {
				$updforvcm['dfrom'] = $updforvcm['dfrom'] > $start_ts ? $start_ts : $updforvcm['dfrom'];
			} else {
				$updforvcm['dfrom'] = $start_ts;
			}
			if (array_key_exists('dto', $updforvcm) && !empty($updforvcm['dto'])) {
				$updforvcm['dto'] = $updforvcm['dto'] < $end_ts ? $end_ts : $updforvcm['dto'];
			} else {
				$updforvcm['dto'] = $end_ts;
			}
			if (array_key_exists('rooms', $updforvcm) && is_array($updforvcm['rooms'])) {
				if (!in_array($pid_room, $updforvcm['rooms'])) {
					$updforvcm['rooms'][] = $pid_room;
				}
			} else {
				$updforvcm['rooms'] = array($pid_room);
			}
			if (array_key_exists('rplans', $updforvcm) && is_array($updforvcm['rplans'])) {
				if (array_key_exists($pid_room, $updforvcm['rplans'])) {
					if (!in_array($pid_price, $updforvcm['rplans'][$pid_room])) {
						$updforvcm['rplans'][$pid_room][] = $pid_price;
					}
				} else {
					$updforvcm['rplans'][$pid_room] = array($pid_price);
				}
			} else {
				$updforvcm['rplans'] = array($pid_room => array($pid_price));
			}
		}
		//update session in any case
		$session->set('vbVcmRatesUpd', $updforvcm);
		//
		$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
		if ($pdebug == 1) {
			echo "e4j.error.\n".print_r($roomrates, true)."\n";
			echo print_r($current_rates, true)."\n\n";
			echo print_r($season_intervals, true)."\n";
			echo $pid_room.' - '.$pid_price.' - '.$prate.' - '.$pfromdate.' - '.$ptodate."\n";
		}
		echo json_encode($current_rates);
		exit;
	}

	function icsexportlaunch() {
		$dbo = JFactory::getDBO();
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pstatus = VikRequest::getString('status', '', 'request');
		$validstatus = array('confirmed', 'standby', 'cancelled');
		$filterstatus = '';
		$filterfirst = 0;
		$filtersecond = 0;
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$currencyname = VikBooking::getCurrencyName();
		if (!empty($pstatus) && in_array($pstatus, $validstatus)) {
			$filterstatus = $pstatus;
		}
		if (!empty($pcheckindate)) {
			if (VikBooking::dateIsValid($pcheckindate)) {
				$first=VikBooking::getDateTimestamp($pcheckindate, '0', '0');
				$filterfirst = $first;
			}
		}
		if (!empty($pcheckoutdate)) {
			if (VikBooking::dateIsValid($pcheckoutdate)) {
				$second=VikBooking::getDateTimestamp($pcheckoutdate, '23', '59');
				if ($second > $first) {
					$filtersecond = $second;
				}
			}
		}
		$clause = array();
		if ($filterfirst > 0) {
			$clause[] = "`o`.`checkin` >= ".$filterfirst;
		}
		if ($filtersecond > 0) {
			$clause[] = "`o`.`checkout` <= ".$filtersecond;
		}
		if (!empty($filterstatus)) {
			$clause[] = "`o`.`status` = '".$filterstatus."'";
		}
		$q = "SELECT `o`.*,`or`.`idroom`,`or`.`adults`,`or`.`children`,`r`.`name` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_rooms` `r` ON `or`.`idroom`=`r`.`id` ".(count($clause) > 0 ? "WHERE ".implode(" AND ", $clause)." " : "")."ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$orders = $dbo->loadAssocList();
			$icscontent = "BEGIN:VCALENDAR\n";
			$icscontent .= "VERSION:2.0\n";
			$icscontent .= "PRODID:-//e4j//VikBooking//EN\n";
			$icscontent .= "CALSCALE:GREGORIAN\n";
			$str = "";
			foreach ($orders as $kord => $ord) {
				if (isset($orders[($kord + 1)]) && $orders[($kord + 1)]['id'] == $ord['id']) {
					continue;
				}
				$usecurrencyname = $currencyname;
				$usecurrencyname = !empty($ord['idorderota']) && !empty($ord['chcurrency']) ? $ord['chcurrency'] : $usecurrencyname;
				$statusstr = '';
				if ($ord['status'] == 'confirmed') {
					$statusstr = JText::_('VBCSVSTATUSCONFIRMED');
				} elseif ($ord['status'] == 'standby') {
					$statusstr = JText::_('VBCSVSTATUSSTANDBY');
				} elseif ($ord['status'] == 'cancelled') {
					$statusstr = JText::_('VBCSVSTATUSCANCELLED');
				}
				$uri = JURI::root().'index.php?option=com_vikbooking&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'];
				$ordnumbstr = $ord['id'].(!empty($ord['confirmnumber']) ? ' - '.$ord['confirmnumber'] : '').(!empty($ord['idorderota']) ? ' ('.ucwords($ord['channel']).')' : '').' - '.$statusstr;
				$peoplestr = ($ord['adults'] + $ord['children']).($ord['children'] > 0 ? ' ('.JText::_('VBCSVCHILDREN').': '.$ord['children'].')' : '');
				$totalstring = ($ord['total'] > 0 ? ($usecurrencyname.' '.VikBooking::numberFormat($ord['total'])) : '');
				$totalpaidstring = ($ord['totpaid'] > 0 ? (' ('.VikBooking::numberFormat($ord['totpaid']).')') : '');
				$description = JText::sprintf('VBICSEXPDESCRIPTION', $ordnumbstr."\\n", $peoplestr."\\n", $ord['days']."\\n", $totalstring.$totalpaidstring."\\n", "\\n".str_replace("\n", "\\n", trim($ord['custdata'])));
				$str .= "BEGIN:VEVENT\n";
				$str .= "DTEND:".date('Ymd\THis\Z', $ord['checkout'])."\n";
				$str .= "UID:".uniqid()."\n";
				$str .= "DTSTAMP:".date('Ymd\THis\Z', time())."\n";
				$str .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
				$str .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
				$str .= "SUMMARY:".JText::sprintf('VBICSEXPSUMMARY', date($df, $ord['checkin']))."\n";
				$str .= "DTSTART:".date('Ymd\THis\Z', $ord['checkin'])."\n";
				$str .= "END:VEVENT\n";
			}
			$icscontent .= $str;
			$icscontent .= "END:VCALENDAR\n";
			//download file from buffer
			header("Content-Type: application/octet-stream; ");
			header("Cache-Control: no-store, no-cache");
			header('Content-Disposition: attachment; filename="bookings_export.ics"');
			$f = fopen('php://output', "w");
			fwrite($f, $icscontent);
			fclose($f);
			exit;
		} else {
			VikError::raiseWarning('', JText::_('VBICSEXPNORECORDS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=icsexportprepare&checkindate=".$pcheckindate."&checkoutdate=".$pcheckoutdate."&status=".$pstatus."&tmpl=component");
		}
	}

	function csvexportlaunch() {
		$dbo = JFactory::getDBO();
		$pdatefilt = VikRequest::getString('datefilt', '', 'request');
		$proomfilt = VikRequest::getString('roomfilt', '', 'request');
		$pchfilt = VikRequest::getString('chfilt', '', 'request');
		$ppayfilt = VikRequest::getString('payfilt', '', 'request');
		$pcheckindate = VikRequest::getString('checkindate', '', 'request');
		$pcheckoutdate = VikRequest::getString('checkoutdate', '', 'request');
		$pstatus = VikRequest::getString('status', '', 'request');
		$validstatus = array('confirmed', 'standby', 'cancelled');
		$validdates = array('ts', 'checkin', 'checkout');
		$filterdate = '';
		$filterstatus = '';
		$first = 0;
		$filterfirst = 0;
		$filtersecond = 0;
		$nowdf = VikBooking::getDateFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$datesep = VikBooking::getDateSeparator(true);
		$currencyname = VikBooking::getCurrencyName();
		if (!empty($pstatus) && in_array($pstatus, $validstatus)) {
			$filterstatus = $pstatus;
		}
		if (!empty($pdatefilt) && in_array($pdatefilt, $validdates)) {
			$filterdate = $pdatefilt;
		}
		if (!empty($pcheckindate) && !empty($filterdate)) {
			if (VikBooking::dateIsValid($pcheckindate)) {
				$first=VikBooking::getDateTimestamp($pcheckindate, '0', '0');
				$filterfirst = $first;
			}
		}
		if (!empty($pcheckoutdate) && !empty($filterdate)) {
			if (VikBooking::dateIsValid($pcheckoutdate)) {
				$second=VikBooking::getDateTimestamp($pcheckoutdate, '23', '59');
				if ($second > $first) {
					$filtersecond = $second;
				}
			}
		}
		$clause = array();
		if ($filterfirst > 0) {
			$clause[] = "`o`.`".$filterdate."` >= ".$filterfirst;
		}
		if ($filtersecond > 0) {
			$clause[] = "`o`.`".$filterdate."` <= ".$filtersecond;
		}
		if (!empty($filterstatus)) {
			$clause[] = "`o`.`status` = '".$filterstatus."'";
		}
		if (!empty($pchfilt)) {
			$clause[] = "`o`.`channel` LIKE ".$dbo->quote("%".$pchfilt."%");
		}
		if (!empty($ppayfilt)) {
			$clause[] = "`o`.`idpayment` LIKE '".$ppayfilt."=%'";
		}
		if (!empty($proomfilt)) {
			$clause[] = "`or`.`idroom` = '".(int)$proomfilt."'";
		}
		$q = "SELECT `o`.*,`or`.`idroom`,`or`.`adults`,`or`.`children`,`or`.`idtar`,`or`.`optionals`,`or`.`t_first_name`,`or`.`t_last_name`,`or`.`extracosts`,`r`.`name`,`d`.`idprice`,`p`.`idiva`,`t`.`aliq`,`t`.`breakdown` FROM `#__vikbooking_orders` AS `o` LEFT JOIN `#__vikbooking_ordersrooms` `or` ON `or`.`idorder`=`o`.`id` LEFT JOIN `#__vikbooking_rooms` `r` ON `or`.`idroom`=`r`.`id` LEFT JOIN `#__vikbooking_dispcost` `d` ON `or`.`idtar`=`d`.`id` LEFT JOIN `#__vikbooking_prices` `p` ON `d`.`idprice`=`p`.`id` LEFT JOIN `#__vikbooking_iva` `t` ON `p`.`idiva`=`t`.`id` ".(count($clause) > 0 ? "WHERE ".implode(" AND ", $clause)." " : "")."ORDER BY `o`.`checkin` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$orders = $dbo->loadAssocList();
			//options
			$all_options = array();
			$q = "SELECT * FROM `#__vikbooking_optionals` ORDER BY `#__vikbooking_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$options = $dbo->loadAssocList();
				foreach ($options as $ok => $ov) {
					$all_options[$ov['id']] = $ov;
				}
			}
			//
			$orderscsv = array();
			$orderscsv[] = array(JText::_('VBCSVCHECKIN'), JText::_('VBCSVCHECKOUT'), JText::_('VBCSVNIGHTS'), JText::_('VBCSVROOM'), JText::_('VBCSVPEOPLE'), JText::_('VBCSVCUSTINFO'), JText::_('VBCSVCREATEDBY'), JText::_('VBCSVCUSTMAIL'), JText::_('VBCSVOPTIONS'), JText::_('VBCSVPAYMENTMETHOD'), JText::_('VBCSVORDIDCONFNUMB'), JText::_('VBCSVEXPFILTBSTATUS'), JText::_('VBCSVTOTAL'), JText::_('VBCSVTOTPAID'), JText::_('VBCSVTOTTAXES'));
			foreach ($orders as $kord => $ord) {
				$usecurrencyname = $currencyname;
				$usecurrencyname = !empty($ord['idorderota']) && !empty($ord['chcurrency']) ? $ord['chcurrency'] : $usecurrencyname;
				$peoplestr = ($ord['adults'] + $ord['children']).($ord['children'] > 0 ? ' ('.JText::_('VBCSVCHILDREN').': '.$ord['children'].')' : '');
				$custinfostr = str_replace(",", " ", $ord['custdata']);
				$paystr = '';
				if (!empty($ord['idpayment'])) {
					$payparts = explode('=', $ord['idpayment']);
					$paystr = $payparts[1];
				}
				$ordnumbstr = $ord['id'].' - '.$ord['confirmnumber'].(!empty($ord['idorderota']) ? ' ('.ucwords($ord['channel']).')' : ''); 
				$statusstr = '';
				if ($ord['status'] == 'confirmed') {
					$statusstr = JText::_('VBCSVSTATUSCONFIRMED');
				} elseif ($ord['status'] == 'standby') {
					$statusstr = JText::_('VBCSVSTATUSSTANDBY');
				} elseif ($ord['status'] == 'cancelled') {
					$statusstr = JText::_('VBCSVSTATUSCANCELLED');
				}
				$totalstring = ($ord['total'] > 0 ? $ord['total'].' '.$usecurrencyname : '0.00'.' '.$usecurrencyname);
				$totalpaidstring = ($ord['totpaid'] > 0 ? $ord['totpaid'].' '.$usecurrencyname : '0.00'.' '.$usecurrencyname);
				if (isset($orders[($kord + 1)]) && $orders[($kord + 1)]['id'] == $ord['id']) {
					$totalstring = '';
					$totalpaidstring = '';
				}
				$options_str = '';
				if (!empty($ord['optionals'])) {
					$stepo = explode(";", $ord['optionals']);
					foreach ($stepo as $oo) {
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							if (array_key_exists($stept[0], $all_options)) {
								$actopt = $all_options[$stept[0]];
								$optpcent = false;
								if (!empty($actopt['ageintervals']) && $ord['children'] > 0 && strstr($stept[1], '-') != false) {
									$optagecosts = VikBooking::getOptionIntervalsCosts($actopt['ageintervals']);
									$optagenames = VikBooking::getOptionIntervalsAges($actopt['ageintervals']);
									$optagepcent = VikBooking::getOptionIntervalsPercentage($actopt['ageintervals']);
									$agestept = explode('-', $stept[1]);
									$stept[1] = $agestept[0];
									$chvar = $agestept[1];
									if (array_key_exists(($chvar - 1), $optagepcent) && $optagepcent[($chvar - 1)] > 0) {
										$optpcent = true;
									}
									$actopt['chageintv'] = $chvar;
									$actopt['name'] .= ' ('.$optagenames[($chvar - 1)].')';
									$realcost = (intval($actopt['perday']) == 1 ? (floatval($optagecosts[($chvar - 1)]) * $ord['days'] * $stept[1]) : (floatval($optagecosts[($chvar - 1)]) * $stept[1]));
								} else {
									$realcost = (intval($actopt['perday']) == 1 ? ($actopt['cost'] * $ord['days'] * $stept[1]) : ($actopt['cost'] * $stept[1]));
								}
								if ($actopt['maxprice'] > 0 && $realcost > $actopt['maxprice']) {
									$realcost=$actopt['maxprice'];
									if (intval($actopt['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt['maxprice'] * $stept[1];
									}
								}
								$realcost = $actopt['perperson'] == 1 ? ($realcost * $ord['adults']) : $realcost;
								$tmpopr=VikBooking::sayOptionalsPlusIva($realcost, $actopt['idiva']);
								$options_str .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt['name'].": ".(!$optpcent ? $currencyname : '')." ".VikBooking::numberFormat($tmpopr).($optpcent ? ' %' : '')." \r\n";
							}
						}
					}
				}
				//custom extra costs
				if (!empty($ord['extracosts'])) {
					$cur_extra_costs = json_decode($ord['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$ecplustax = !empty($ecv['idtax']) ? VikBooking::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax']) : $ecv['cost'];
						$options_str .= $ecv['name'].": ".$currencyname." ".VikBooking::numberFormat($ecplustax)." \r\n";
					}
				}
				//
				//taxes
				$taxes_str = '';
				if ($ord['tot_taxes'] > 0.00) {
					$taxes_str .= $usecurrencyname.' '.VikBooking::numberFormat($ord['tot_taxes']);
					if (!empty($ord['aliq']) && !empty($ord['breakdown'])) {
						$tax_breakdown = json_decode($ord['breakdown'], true);
						$tax_breakdown = is_array($tax_breakdown) && count($tax_breakdown) > 0 ? $tax_breakdown : array();
						if (count($tax_breakdown)) {
							foreach ($tax_breakdown as $tbkk => $tbkv) {
								$tax_break_cost = $ord['tot_taxes'] * floatval($tbkv['aliq']) / $ord['aliq'];
								$taxes_str .= "\r\n".$tbkv['name'].": ".$usecurrencyname.' '.VikBooking::numberFormat($tax_break_cost);
							}
						}
					}
				}
				//
				//created by
				$created_by = '';
				if (!empty($ord['ujid'])) {
					$creator = new JUser($ord['ujid']);
					if (property_exists($creator, 'name')) {
						$created_by = $creator->name.' ('.$creator->username.')';
					}
				}
				if (empty($created_by) && !empty($ord['t_first_name'])) {
					$created_by = $ord['t_first_name'].' '.$ord['t_last_name'];
				}
				//
				$orderscsv[] = array(date(str_replace("/", $datesep, $df), $ord['checkin']), date(str_replace("/", $datesep, $df), $ord['checkout']), $ord['days'], $ord['name'], $peoplestr, $custinfostr, $created_by, $ord['custmail'], $options_str, $paystr, $ordnumbstr, $statusstr, $totalstring, $totalpaidstring, $taxes_str);
			}
			header("Content-type: text/csv");
			header("Cache-Control: no-store, no-cache");
			header('Content-Disposition: attachment; filename="bookings_export_'.date('Y-m-d').'.csv"');
			$outstream = fopen("php://output", 'w');
			foreach ($orderscsv as $csvline) {
				fputcsv($outstream, $csvline);
			}
			fclose($outstream);
			exit;
		} else {
			VikError::raiseWarning('', JText::_('VBCSVEXPNORECORDS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=csvexportprepare&checkindate=".$pcheckindate."&checkoutdate=".$pcheckoutdate."&status=".$pstatus."&tmpl=component");
		}
	}

	function exportcustomerslaunch() {
		$cid = VikRequest::getVar('cid', array(0));
		$dbo = JFactory::getDBO();
		$pnotes = VikRequest::getInt('notes', '', 'request');
		$pscanimg = VikRequest::getInt('scanimg', '', 'request');
		$ppin = VikRequest::getInt('pin', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$clauses = array();
		if (count($cid) > 0 && !empty($cid[0])) {
			$clauses[] = "`c`.`id` IN (".implode(', ', $cid).")";
		}
		if (!empty($pcountry)) {
			$clauses[] = "`c`.`country`=".$dbo->quote($pcountry);
		}
		$datescol = '`bk`.`ts`';
		if ($pdatefilt > 0) {
			if ($pdatefilt == 1) {
				$datescol = '`bk`.`ts`';
			} elseif ($pdatefilt == 2) {
				$datescol = '`bk`.`checkin`';
			} elseif ($pdatefilt == 3) {
				$datescol = '`bk`.`checkout`';
			}
		}
		if (!empty($pfromdate)) {
			$from_ts = VikBooking::getDateTimestamp($pfromdate, 0, 0);
			$clauses[] = $datescol.">=".$from_ts;
		}
		if (!empty($ptodate)) {
			$to_ts = VikBooking::getDateTimestamp($ptodate, 23, 59);
			$clauses[] = $datescol."<=".$to_ts;
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by
		$q = "SELECT `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`ischannel`,`c`.`chdata`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,".
			"(SELECT COUNT(*) FROM `#__vikbooking_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings`,".
			"`cy`.`country_3_code`,`cy`.`country_name` ".
			"FROM `#__vikbooking_customers` AS `c` LEFT JOIN `#__vikbooking_countries` `cy` ON `cy`.`country_3_code`=`c`.`country` ".
			"LEFT JOIN `#__vikbooking_customers_orders` `co` ON `co`.`idcustomer`=`c`.`id` ".
			"LEFT JOIN `#__vikbooking_orders` `bk` ON `bk`.`id`=`co`.`idorder`".
			(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." 
			GROUP BY `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`ischannel`,`c`.`chdata`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,`cy`.`country_3_code`,`cy`.`country_name` ".
			"ORDER BY `c`.`last_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!($dbo->getNumRows() > 0)) {
			VikError::raiseWarning('', JText::_('VBONORECORDSCSVCUSTOMERS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
			exit;
		}
		$customers = $dbo->loadAssocList();
		$csvlines = array();
		$csvheadline = array('ID', JText::_('VBCUSTOMERLASTNAME'), JText::_('VBCUSTOMERFIRSTNAME'), JText::_('VBCUSTOMEREMAIL'), JText::_('VBCUSTOMERPHONE'), JText::_('VBCUSTOMERADDRESS'), JText::_('VBCUSTOMERCITY'), JText::_('VBCUSTOMERZIP'), JText::_('VBCUSTOMERCOUNTRY'), JText::_('VBCUSTOMERTOTBOOKINGS'));
		if ($ppin > 0) {
			$csvheadline[] = JText::_('VBCUSTOMERPIN');
		}
		if ($pscanimg > 0) {
			$csvheadline[] = JText::_('VBCUSTOMERDOCTYPE');
			$csvheadline[] = JText::_('VBCUSTOMERDOCNUM');
			$csvheadline[] = JText::_('VBCUSTOMERDOCIMG');
		}
		if ($pnotes > 0) {
			$csvheadline[] = JText::_('VBCUSTOMERNOTES');
		}
		$csvlines[] = $csvheadline;
		foreach ($customers as $customer) {
			$csvcustomerline = array($customer['id'], $customer['last_name'], $customer['first_name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['zip'], $customer['country_name'], $customer['tot_bookings']);
			if ($ppin > 0) {
				$csvcustomerline[] = $customer['pin'];
			}
			if ($pscanimg > 0) {
				$csvcustomerline[] = $customer['doctype'];
				$csvcustomerline[] = $customer['docnum'];
				$csvcustomerline[] = (!empty($customer['docimg']) ? VBO_ADMIN_URI.'resources/idscans/'.$customer['docimg'] : '');
			}
			if ($pnotes > 0) {
				$csvcustomerline[] = $customer['notes'];
			}	
			$csvlines[] = $csvcustomerline;
		}
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="customers_export_'.(!empty($pcountry) ? strtolower($pcountry).'_' : '').date('Y-m-d').'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	function renewsession() {
		$dbo = JFactory::getDBO();
		$q = "TRUNCATE TABLE `#__session`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	function cancelcrons() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=crons");
	}

	function cancelpackages() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=packages");
	}

	function cancelcustomer() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customers");
	}

	function cancelbusyvcm() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikchannelmanager&task=oversight");
	}

	function cancelrestriction() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=restrictions");
	}

	function cancelcoupon() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=coupons");
	}

	function cancelcustomf() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=customf");
	}

	function cancelpayment() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=payments");
	}

	function cancelseason() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=seasons");
	}

	function goconfig() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=config");
	}

	function canceledorder() {
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	function cancelbusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=editorder&cid[]=".$pidorder.($pgoto == 'overv' ? '&goto=overv' : ''));
	}

	function canceloverv() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=overv");
	}

	function cancelcalendar() {
		$pidroom = VikRequest::getString('idroom', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=calendar&cid[]=".$pidroom);
	}

	function canceloptionals() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=optionals");
	}

	function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=rooms");
	}

	function cancelcarat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=carat");
	}

	function cancelcat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=categories");
	}

	function cancelprice() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=prices");
	}

	function canceliva() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikbooking&task=iva");
	}

}
