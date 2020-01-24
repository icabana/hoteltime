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

//This is a template file loaded (included) by the main lib in case of errors during the booking process to keep the current query string, without performing any redirect.
//The output of this template file is similar to the default view 'vikbooking' but it takes an error-message variable and an array parameter declared in the main library file.
//This is not a core-file and, even though it does not support overrides, it can be customized as it is never replaced by any update.

if (VikBooking::allowBooking()) {
	$session = JFactory::getSession();
	$dbo = JFactory::getDBO();
	$vbo_tn = VikBooking::getTranslator();
	$is_mobile = VikBooking::detectUserAgent(false, false);
	//vikbooking 1.1
	$calendartype = VikBooking::calendarType();
	$document = JFactory::getDocument();
	//load jQuery lib e jQuery UI
	if (VikBooking::loadJquery()) {
		JHtml::_('jquery.framework', true, true);
		JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
	}
	if ($calendartype == "jqueryui") {
		$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
		//load jQuery UI
		JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
	}
	//load modalframe lib for search suggestions
	$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
	JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
	//
	//vikbooking 1.2
	$restrictions = VikBooking::loadRestrictions();
	$oldroomsnum = $session->get('vbroomsnum', '');
	$oldarrpeople = $session->get('vbarrpeople', '');
	//
	$pcheckin = VikRequest::getInt('checkin', '', 'request');
	$pcheckout = VikRequest::getInt('checkout', '', 'request');
	$sesscheckin = $session->get('vbcheckin', '');
	$sesscheckout = $session->get('vbcheckout', '');
	$pitemid = VikRequest::getInt('Itemid', '', 'request');
	$pval = "";
	$rval = "";
	$vbdateformat = VikBooking::getDateFormat();
	if ($vbdateformat == "%d/%m/%Y") {
		$df = 'd/m/Y';
	}elseif ($vbdateformat == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (!empty($pcheckin) || !empty($sesscheckin)) {
		$pcheckin = !empty($pcheckin) ? $pcheckin : $sesscheckin;
		$dp = date($df, $pcheckin);
		if (VikBooking::dateIsValid($dp)) {
			$pval = $dp;
		}
	}
	if (!empty($pcheckout) || !empty($sesscheckout)) {
		$pcheckout = !empty($pcheckout) ? $pcheckout : $sesscheckout;
		$dr = date($df, $pcheckout);
		if (VikBooking::dateIsValid($dr)) {
			$rval = $dr;
		}
	}
	$selform = "<div class=\"vbdivsearch vbo-search-mainview vbo-search-noresults-cont\"><form action=\"".JRoute::_('index.php?option=com_vikbooking&Itemid='.VikRequest::getString('Itemid', '', 'request'))."\" method=\"get\"><div class=\"vb-search-inner\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikbooking\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	
	$timeopst = VikBooking::getTimeOpenStore();
	if (is_array($timeopst)) {
		$opent = VikBooking::getHoursMinutes($timeopst[0]);
		$closet = VikBooking::getHoursMinutes($timeopst[1]);
		$hcheckin = $opent[0];
		$mcheckin = $opent[1];
		$hcheckout = $closet[0];
		$mcheckout = $closet[1];
	} else {
		$hcheckin = 0;
		$mcheckin = 0;
		$hcheckout = 0;
		$mcheckout = 0;
	}
	
	//vikbooking 1.1
	if($calendartype == "jqueryui") {
		if ($vbdateformat == "%d/%m/%Y") {
			$juidf = 'dd/mm/yy';
		}elseif ($vbdateformat == "%m/%d/%Y") {
			$juidf = 'mm/dd/yy';
		}else {
			$juidf = 'yy/mm/dd';
		}
		//lang for jQuery UI Calendar
		$ldecl = '
jQuery.noConflict();
jQuery(function($){'."\n".'
	$.datepicker.regional["vikbooking"] = {'."\n".'
		closeText: "'.JText::_('VBJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VBJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VBJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VBJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VBMONTHONE').'","'.JText::_('VBMONTHTWO').'","'.JText::_('VBMONTHTHREE').'","'.JText::_('VBMONTHFOUR').'","'.JText::_('VBMONTHFIVE').'","'.JText::_('VBMONTHSIX').'","'.JText::_('VBMONTHSEVEN').'","'.JText::_('VBMONTHEIGHT').'","'.JText::_('VBMONTHNINE').'","'.JText::_('VBMONTHTEN').'","'.JText::_('VBMONTHELEVEN').'","'.JText::_('VBMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VBMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VBJQCALSUN').'", "'.JText::_('VBJQCALMON').'", "'.JText::_('VBJQCALTUE').'", "'.JText::_('VBJQCALWED').'", "'.JText::_('VBJQCALTHU').'", "'.JText::_('VBJQCALFRI').'", "'.JText::_('VBJQCALSAT').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VBJQCALSUN'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALMON'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALTUE'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALWED'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALTHU'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALFRI'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALSAT'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VBJQCALSUN'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALMON'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALTUE'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALWED'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALTHU'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALFRI'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBJQCALSAT'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VBJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikBooking::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikbooking"]);'."\n".'
});
function vbGetDateObject(dstring) {
	var dparts = dstring.split("-");
	return new Date(dparts[0], (parseInt(dparts[1]) - 1), parseInt(dparts[2]), 0, 0, 0, 0);
}
function vbFullObject(obj) {
	var jk;
	for(jk in obj) {
		return obj.hasOwnProperty(jk);
	}
}
var vbrestrctarange, vbrestrctdrange, vbrestrcta, vbrestrctd;';
		$document->addScriptDeclaration($ldecl);
		//
		//VikBooking 1.4
		$totrestrictions = count($restrictions);
		if ($totrestrictions > 0) {
			$wdaysrestrictions = array();
			$wdaystworestrictions = array();
			$wdaysrestrictionsrange = array();
			$wdaysrestrictionsmonths = array();
			$ctarestrictionsrange = array();
			$ctarestrictionsmonths = array();
			$ctdrestrictionsrange = array();
			$ctdrestrictionsmonths = array();
			$monthscomborestr = array();
			$minlosrestrictions = array();
			$minlosrestrictionsrange = array();
			$maxlosrestrictions = array();
			$maxlosrestrictionsrange = array();
			$notmultiplyminlosrestrictions = array();
			foreach($restrictions as $rmonth => $restr) {
				if($rmonth != 'range') {
					if (strlen($restr['wday']) > 0) {
						$wdaysrestrictions[] = "'".($rmonth - 1)."': '".$restr['wday']."'";
						$wdaysrestrictionsmonths[] = $rmonth;
						if (strlen($restr['wdaytwo']) > 0) {
							$wdaystworestrictions[] = "'".($rmonth - 1)."': '".$restr['wdaytwo']."'";
							$monthscomborestr[($rmonth - 1)] = VikBooking::parseJsDrangeWdayCombo($restr);
						}
					}elseif(!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if(!empty($restr['ctad'])) {
							$ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']);
						}
						if(!empty($restr['ctdd'])) {
							$ctdrestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctdd']);
						}
					}
					if ($restr['multiplyminlos'] == 0) {
						$notmultiplyminlosrestrictions[] = $rmonth;
					}
					$minlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['minlos']."'";
					if (!empty($restr['maxlos']) && $restr['maxlos'] > 0 && $restr['maxlos'] > $restr['minlos']) {
						$maxlosrestrictions[] = "'".($rmonth - 1)."': '".$restr['maxlos']."'";
					}
				}else {
					foreach ($restr as $kr => $drestr) {
						if (strlen($drestr['wday']) > 0) {
							$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
							$wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
							$wdaysrestrictionsrange[$kr][2] = $drestr['wday'];
							$wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
							$wdaysrestrictionsrange[$kr][4] = strlen($drestr['wdaytwo']) > 0 ? $drestr['wdaytwo'] : -1;
							$wdaysrestrictionsrange[$kr][5] = VikBooking::parseJsDrangeWdayCombo($drestr);
						}elseif(!empty($drestr['ctad']) || !empty($drestr['ctdd'])) {
							$ctfrom = date('Y-m-d', $drestr['dfrom']);
							$ctto = date('Y-m-d', $drestr['dto']);
							if(!empty($drestr['ctad'])) {
								$ctarestrictionsrange[$kr][0] = $ctfrom;
								$ctarestrictionsrange[$kr][1] = $ctto;
								$ctarestrictionsrange[$kr][2] = explode(',', $drestr['ctad']);
							}
							if(!empty($drestr['ctdd'])) {
								$ctdrestrictionsrange[$kr][0] = $ctfrom;
								$ctdrestrictionsrange[$kr][1] = $ctto;
								$ctdrestrictionsrange[$kr][2] = explode(',', $drestr['ctdd']);
							}
						}
						$minlosrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
						$minlosrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
						$minlosrestrictionsrange[$kr][2] = $drestr['minlos'];
						if (!empty($drestr['maxlos']) && $drestr['maxlos'] > 0 && $drestr['maxlos'] > $drestr['minlos']) {
							$maxlosrestrictionsrange[$kr] = $drestr['maxlos'];
						}
					}
					unset($restrictions['range']);
				}
			}
			
			$resdecl = "
var vbrestrmonthswdays = [".implode(", ", $wdaysrestrictionsmonths)."];
var vbrestrmonths = [".implode(", ", array_keys($restrictions))."];
var vbrestrmonthscombojn = jQuery.parseJSON('".json_encode($monthscomborestr)."');
var vbrestrminlos = {".implode(", ", $minlosrestrictions)."};
var vbrestrminlosrangejn = jQuery.parseJSON('".json_encode($minlosrestrictionsrange)."');
var vbrestrmultiplyminlos = [".implode(", ", $notmultiplyminlosrestrictions)."];
var vbrestrmaxlos = {".implode(", ", $maxlosrestrictions)."};
var vbrestrmaxlosrangejn = jQuery.parseJSON('".json_encode($maxlosrestrictionsrange)."');
var vbrestrwdaysrangejn = jQuery.parseJSON('".json_encode($wdaysrestrictionsrange)."');
var vbrestrcta = jQuery.parseJSON('".json_encode($ctarestrictionsmonths)."');
var vbrestrctarange = jQuery.parseJSON('".json_encode($ctarestrictionsrange)."');
var vbrestrctd = jQuery.parseJSON('".json_encode($ctdrestrictionsmonths)."');
var vbrestrctdrange = jQuery.parseJSON('".json_encode($ctdrestrictionsrange)."');
var vbcombowdays = {};
function vbRefreshCheckout(darrive) {
	if(vbFullObject(vbcombowdays)) {
		var vbtosort = new Array();
		for(var vbi in vbcombowdays) {
			if(vbcombowdays.hasOwnProperty(vbi)) {
				var vbusedate = darrive;
				vbtosort[vbi] = vbusedate.setDate(vbusedate.getDate() + (vbcombowdays[vbi] - 1 - vbusedate.getDay() + 7) % 7 + 1);
			}
		}
		vbtosort.sort(function(da, db) {
			return da > db ? 1 : -1;
		});
		for(var vbnext in vbtosort) {
			if(vbtosort.hasOwnProperty(vbnext)) {
				var vbfirstnextd = new Date(vbtosort[vbnext]);
				jQuery('#checkoutdate').datepicker( 'option', 'minDate', vbfirstnextd );
				jQuery('#checkoutdate').datepicker( 'setDate', vbfirstnextd );
				break;
			}
		}
	}
}
function vbSetMinCheckoutDate () {
	var minlos = ".VikBooking::getDefaultNightsCalendar().";
	var maxlosrange = 0;
	var nowcheckin = jQuery('#checkindate').datepicker('getDate');
	var nowd = nowcheckin.getDay();
	var nowcheckindate = new Date(nowcheckin.getTime());
	vbcombowdays = {};
	if(vbFullObject(vbrestrminlosrangejn)) {
		for (var rk in vbrestrminlosrangejn) {
			if(vbrestrminlosrangejn.hasOwnProperty(rk)) {
				var minldrangeinit = vbGetDateObject(vbrestrminlosrangejn[rk][0]);
				if(nowcheckindate >= minldrangeinit) {
					var minldrangeend = vbGetDateObject(vbrestrminlosrangejn[rk][1]);
					if(nowcheckindate <= minldrangeend) {
						minlos = parseInt(vbrestrminlosrangejn[rk][2]);
						if(vbFullObject(vbrestrmaxlosrangejn)) {
							if(rk in vbrestrmaxlosrangejn) {
								maxlosrange = parseInt(vbrestrmaxlosrangejn[rk]);
							}
						}
						if(rk in vbrestrwdaysrangejn && nowd in vbrestrwdaysrangejn[rk][5]) {
							vbcombowdays = vbrestrwdaysrangejn[rk][5][nowd];
						}
					}
				}
			}
		}
	}
	var nowm = nowcheckin.getMonth();
	if(vbFullObject(vbrestrmonthscombojn) && vbrestrmonthscombojn.hasOwnProperty(nowm)) {
		if(nowd in vbrestrmonthscombojn[nowm]) {
			vbcombowdays = vbrestrmonthscombojn[nowm][nowd];
		}
	}
	if(jQuery.inArray((nowm + 1), vbrestrmonths) != -1) {
		minlos = parseInt(vbrestrminlos[nowm]);
	}
	nowcheckindate.setDate(nowcheckindate.getDate() + minlos);
	jQuery('#checkoutdate').datepicker( 'option', 'minDate', nowcheckindate );
	if(maxlosrange > 0) {
		var diffmaxminlos = maxlosrange - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkoutdate').datepicker( 'option', 'maxDate', maxcheckoutdate );
	}
	if(nowm in vbrestrmaxlos) {
		var diffmaxminlos = parseInt(vbrestrmaxlos[nowm]) - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkoutdate').datepicker( 'option', 'maxDate', maxcheckoutdate );
	}
	if(!vbFullObject(vbcombowdays)) {
		jQuery('#checkoutdate').datepicker( 'setDate', nowcheckindate );
	}else {
		vbRefreshCheckout(nowcheckin);
	}
}";
			
			if(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
				$resdecl .= "
var vbrestrwdays = {".implode(", ", $wdaysrestrictions)."};
var vbrestrwdaystwo = {".implode(", ", $wdaystworestrictions)."};
function vbIsDayDisabled(date) {
	if(!vbIsDayOpen(date) || !vboValidateCta(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
	if(vbFullObject(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if(vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vbrestrwdaysrangejn[rk][2]) {
							if(vbrestrwdaysrangejn[rk][4] == -1 || wd != vbrestrwdaysrangejn[rk][4]) {
								return [false];
							}
						}
					}
				}
			}
		}
	}
	if(vbFullObject(vbrestrwdays)) {
		if(jQuery.inArray((m+1), vbrestrmonthswdays) == -1) {
			return [true];
		}
		if(wd == vbrestrwdays[m]) {
			return [true];
		}
		if(vbFullObject(vbrestrwdaystwo)) {
			if(wd == vbrestrwdaystwo[m]) {
				return [true];
			}
		}
		return [false];
	}
	return [true];
}
function vbIsDayDisabledCheckout(date) {
	if(!vbIsDayOpen(date) || !vboValidateCtd(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
	if(vbFullObject(vbcombowdays)) {
		if(jQuery.inArray(wd, vbcombowdays) != -1) {
			return [true];
		}else {
			return [false];
		}
	}
	if(vbFullObject(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if(vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrwdaysrangejn[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrwdaysrangejn[rk][1]);
					if(date <= wdrangeend) {
						if(wd != vbrestrwdaysrangejn[rk][2] && vbrestrwdaysrangejn[rk][3] == 1) {
							return [false];
						}
					}
				}
			}
		}
	}
	if(vbFullObject(vbrestrwdays)) {
		if(jQuery.inArray((m+1), vbrestrmonthswdays) == -1 || jQuery.inArray((m+1), vbrestrmultiplyminlos) != -1) {
			return [true];
		}
		if(wd == vbrestrwdays[m]) {
			return [true];
		}
		return [false];
	}
	return [true];
}";
			}
			$document->addScriptDeclaration($resdecl);
		}
		//
		$closing_dates = VikBooking::parseJsClosingDates();
		$sdecl = "
var vbclosingdates = jQuery.parseJSON('".json_encode($closing_dates)."');
function vbCheckClosingDatesIn(date) {
	if(!vbIsDayOpen(date) || !vboValidateCta(date)) {
		return [false];
	}
	return [true];
}
function vbCheckClosingDatesOut(date) {
	if(!vbIsDayOpen(date) || !vboValidateCtd(date)) {
		return [false];
	}
	return [true];
}
function vbIsDayOpen(date) {
	if(vbFullObject(vbclosingdates)) {
		for (var cd in vbclosingdates) {
			if(vbclosingdates.hasOwnProperty(cd)) {
				var cdfrom = vbGetDateObject(vbclosingdates[cd][0]);
				var cdto = vbGetDateObject(vbclosingdates[cd][1]);
				if(date >= cdfrom && date <= cdto) {
					return false;
				}
			}
		}
	}
	return true;
}
function vboValidateCta(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vbFullObject(vbrestrctarange)) {
		for (var rk in vbrestrctarange) {
			if(vbrestrctarange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrctarange[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrctarange[rk][1]);
					if(date <= wdrangeend) {
						if(jQuery.inArray('-'+wd+'-', vbrestrctarange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if(vbFullObject(vbrestrcta)) {
		if(vbrestrcta.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrcta[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vboValidateCtd(date) {
	var m = date.getMonth(), wd = date.getDay();
	if(vbFullObject(vbrestrctdrange)) {
		for (var rk in vbrestrctdrange) {
			if(vbrestrctdrange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrctdrange[rk][0]);
				if(date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrctdrange[rk][1]);
					if(date <= wdrangeend) {
						if(jQuery.inArray('-'+wd+'-', vbrestrctdrange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if(vbFullObject(vbrestrctd)) {
		if(vbrestrctd.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrctd[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vbSetGlobalMinCheckoutDate() {
	var nowcheckin = jQuery('#checkindate').datepicker('getDate');
	var nowcheckindate = new Date(nowcheckin.getTime());
	nowcheckindate.setDate(nowcheckindate.getDate() + ".VikBooking::getDefaultNightsCalendar().");
	jQuery('#checkoutdate').datepicker( 'option', 'minDate', nowcheckindate );
	jQuery('#checkoutdate').datepicker( 'setDate', nowcheckindate );
}
jQuery(function(){
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#checkindate').datepicker({
		showOn: 'focus',
		numberOfMonths: ".($is_mobile ? '1' : '2').",".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vbIsDayDisabled,\n" : "\nbeforeShowDay: vbCheckClosingDatesIn,\n")."
		onSelect: function( selectedDate ) {
			".($totrestrictions > 0 ? "vbSetMinCheckoutDate();" : "vbSetGlobalMinCheckoutDate();")."
			vbCalcNights();
		}
	});
	jQuery('#checkindate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#checkindate').datepicker( 'option', 'minDate', '".VikBooking::getMinDaysAdvance()."d');
	jQuery('#checkindate').datepicker( 'option', 'maxDate', '".VikBooking::getMaxDateFuture()."');
	jQuery('#checkoutdate').datepicker({
		showOn: 'focus',
		numberOfMonths: ".($is_mobile ? '1' : '2').",".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vbIsDayDisabledCheckout,\n" : "\nbeforeShowDay: vbCheckClosingDatesOut,\n")."
		onSelect: function( selectedDate ) {
			vbCalcNights();
		}
	});
	jQuery('#checkoutdate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#checkoutdate').datepicker( 'option', 'minDate', '".VikBooking::getMinDaysAdvance()."d');
	jQuery('#checkoutdate').datepicker( 'option', 'maxDate', '".VikBooking::getMaxDateFuture()."');
	jQuery('#checkindate').datepicker( 'option', jQuery.datepicker.regional[ 'vikbooking' ] );
	jQuery('#checkoutdate').datepicker( 'option', jQuery.datepicker.regional[ 'vikbooking' ] );
	jQuery('.vb-cal-img, .vbo-caltrigger').click(function(){
		var jdp = jQuery(this).prev('input.hasDatepicker');
		if(jdp.length) {
			jdp.focus();
		}
	});
});";
		$document->addScriptDeclaration($sdecl);
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkin\"><label for=\"checkindate\">" . JText::_('VBPICKUPROOM') . "</label><div class=\"input-group\"><input type=\"text\" name=\"checkindate\" id=\"checkindate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vbo-caltrigger\"></i></div><input type=\"hidden\" name=\"checkinh\" value=\"".$hcheckin."\"/><input type=\"hidden\" name=\"checkinm\" value=\"".$mcheckin."\"/></div>\n";
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkout\"><label for=\"checkoutdate\">" . JText::_('VBRETURNROOM') . "</label><div class=\"input-group\"><input type=\"text\" name=\"checkoutdate\" id=\"checkoutdate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vbo-caltrigger\"></i></div><input type=\"hidden\" name=\"checkouth\" value=\"".$hcheckout."\"/><input type=\"hidden\" name=\"checkoutm\" value=\"".$mcheckout."\"/></div>\n";
	} else {
		//default Joomla Calendar
		$vbo_app = new VboApplication();
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkin\"><label for=\"checkindate\">" . JText::_('VBPICKUPROOM') . "</label><div class=\"input-group\">" . $vbo_app->getCalendar('', 'checkindate', 'checkindate', $vbdateformat, array ('class' => '','size' => '10','maxlength' => '19'));
		$selform .= "<input type=\"hidden\" name=\"checkinh\" value=\"".$hcheckin."\"/><input type=\"hidden\" name=\"checkinm\" value=\"".$mcheckin."\"/></div></div>\n";
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkout\"><label for=\"checkoutdate\">" . JText::_('VBRETURNROOM') . "</label><div class=\"input-group\">" . $vbo_app->getCalendar('', 'checkoutdate', 'checkoutdate', $vbdateformat, array ('class' => '','size' => '10','maxlength' => '19')); 
		$selform .= "<input type=\"hidden\" name=\"checkouth\" value=\"".$hcheckout."\"/><input type=\"hidden\" name=\"checkoutm\" value=\"".$mcheckout."\"/></div></div>\n";
	}
	//
	//rooms, adults, children
	$showchildren = VikBooking::showChildrenFront();
	//max number of rooms
	$maxsearchnumrooms = VikBooking::getSearchNumRooms();
	if (intval($maxsearchnumrooms) > 1) {
		$roomsel = "<label for=\"vbo-roomsnum\">".JText::_('VBFORMROOMSN')."</label><select id=\"vbo-roomsnum\" name=\"roomsnum\" onchange=\"vbSetRoomsAdults(this.value);\">\n";
		for($r = 1; $r <= $maxsearchnumrooms; $r++) {
			$roomsel .= "<option value=\"".$r."\"".(!empty($oldroomsnum) && $oldroomsnum == $r ? " selected=\"selected\"" : "").">".$r."</option>\n";
		}
		$roomsel .= "</select>\n";
	}else {
		$roomsel = "<input type=\"hidden\" name=\"roomsnum\" value=\"1\">\n";
	}
	//
	//max number of adults per room
	$globnumadults = VikBooking::getSearchNumAdults();
	$adultsparts = explode('-', $globnumadults);
	$adultsel = "<select name=\"adults[]\">";
	for($a = $adultsparts[0]; $a <= $adultsparts[1]; $a++) {
		$adultsel .= "<option value=\"".$a."\"".((is_array($oldarrpeople) && $oldarrpeople[1]['adults'] == $a) || (intval($adultsparts[0]) < 1 && $a == 1) ? " selected=\"selected\"" : "").">".$a."</option>";
	}
	$adultsel .= "</select>";
	//
	//max number of children per room
	$globnumchildren = VikBooking::getSearchNumChildren();
	$childrenparts = explode('-', $globnumchildren);
	$childrensel = "<select name=\"children[]\">";
	for($c = $childrenparts[0]; $c <= $childrenparts[1]; $c++) {
		$childrensel .= "<option value=\"".$c."\"".(is_array($oldarrpeople) && $oldarrpeople[1]['children'] == $c ? " selected=\"selected\"" : "").">".$c."</option>";
	}
	$childrensel .= "</select>";
	//
	$selform .= "<div class=\"vbo-search-num-racblock\">\n";
	$selform .= "	<div class=\"vbo-search-num-rooms\">".$roomsel."</div>\n";
	$selform .= "	<div class=\"vbo-search-num-aduchild-block\" id=\"vbo-search-num-aduchild-block\">\n";
	$selform .= "		<div class=\"vbo-search-num-aduchild-entry\">".(intval($maxsearchnumrooms) > 1 ? "<span class=\"vbo-search-roomnum\">".JText::_('VBFORMNUMROOM')." 1</span>" : "")."\n";
	$selform .= "			<div class=\"vbo-search-num-adults-entry\"><label class=\"vbo-search-num-adults-entry-label\">".JText::_('VBFORMADULTS')."</label><span class=\"vbo-search-num-adults-entry-inp\">".$adultsel."</span></div>\n";
	if($showchildren) {
		$selform .= "		<div class=\"vbo-search-num-children-entry\"><label class=\"vbo-search-num-children-entry-label\">".JText::_('VBFORMCHILDREN')."</label><span class=\"vbo-search-num-children-entry-inp\">".$childrensel."</span></div>\n";
	}
	$selform .= "		</div>\n";
	$selform .= "	</div>\n";
	//the tag <div id=\"vbjstotnights\"></div> will be used by javascript to calculate the nights
	$selform .= "	<div id=\"vbjstotnights\"></div>\n";
	$selform .= "</div>\n";
	if (VikBooking::showCategoriesFront()) {
		$q = "SELECT * FROM `#__vikbooking_categories` ORDER BY `#__vikbooking_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$categories = $dbo->loadAssocList();
			$vbo_tn->translateContents($categories, '#__vikbooking_categories');
			$selform .= "<div class=\"vbo-search-categories\"><label for=\"vbo-formcategories\" class=\"vbformcategories\">" . JText::_('VBROOMCAT') . "</label><select id=\"vbo-formcategories\" name=\"categories\">";
			$selform .= "<option value=\"all\">" . JText::_('VBALLCAT') . "</option>\n";
			foreach ($categories as $cat) {
				$selform .= "<option value=\"" . $cat['id'] . "\">" . $cat['name'] . "</option>\n";
			}
			$selform .= "</select></div>\n";
		}
	}
	$selform .= "<div class=\"vbo-search-submit\"><input type=\"submit\" name=\"search\" value=\"" . JText::_('VBSEARCHBUTTON') . "\" class=\"btn\"/></div>\n";
	$selform .= "</div>\n";
	$selform .= (!empty ($pitemid) ? "<input type=\"hidden\" name=\"Itemid\" value=\"" . $pitemid . "\"/>" : "") . "</form></div>";
	
	?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function vbAddElement() {
		var ni = document.getElementById('vbo-search-num-aduchild-block');
		var numi = document.getElementById('vbroomhelper');
		var num = (document.getElementById('vbroomhelper').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'vb'+num+'racont';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<div class=\'vbo-search-num-aduchild-entry\'><span class=\'vbo-search-roomnum\'><?php echo addslashes(JText::_('VBFORMNUMROOM')); ?> '+ num +'</span><div class=\'vbo-search-num-adults-entry\'><label class=\'vbo-search-num-adults-entry-label\'><?php echo addslashes(JText::_('VBFORMADULTS')); ?></label><span class=\'vbo-search-num-adults-entry-inp\'><?php echo addslashes(str_replace('"', "'", $adultsel)); ?></span></div><?php if($showchildren): ?><div class=\'vbo-search-num-children-entry\'><label class=\'vbo-search-num-children-entry-label\'><?php echo addslashes(JText::_('VBFORMCHILDREN')); ?></label><span class=\'vbo-search-num-adults-entry-inp\'><?php echo addslashes(str_replace('"', "'", $childrensel)); ?></span></div><?php endif; ?></div>';
		ni.appendChild(newdiv);
	}
	function vbSetRoomsAdults(totrooms) {
		var actrooms = parseInt(document.getElementById('vbroomhelper').value);
		var torooms = parseInt(totrooms);
		var difrooms;
		if(torooms > actrooms) {
			difrooms = torooms - actrooms;
			for(var ir=1; ir<=difrooms; ir++) {
				vbAddElement();
			}
		}
		if(torooms < actrooms) {
			for(var ir=actrooms; ir>torooms; ir--) {
				if(ir > 1) {
					var rmra = document.getElementById('vb' + ir + 'racont');
					rmra.parentNode.removeChild(rmra);
				}
			}
			document.getElementById('vbroomhelper').value = torooms;
		}
	}
	function vbCalcNights() {
		var vbcheckin = document.getElementById('checkindate').value;
		var vbcheckout = document.getElementById('checkoutdate').value;
		if(vbcheckin.length > 0 && vbcheckout.length > 0) {
			var vbcheckinp = vbcheckin.split("/");
			var vbcheckoutp = vbcheckout.split("/");
		<?php
		if ($vbdateformat == "%d/%m/%Y") {
			?>
			var vbinmonth = parseInt(vbcheckinp[1]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[0], 10);
			var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[1]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[0], 10);
			var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
			<?php
		}elseif ($vbdateformat == "%m/%d/%Y") {
			?>
			var vbinmonth = parseInt(vbcheckinp[0]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[1], 10);
			var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[0]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[1], 10);
			var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
			<?php
		}else {
			?>
			var vbinmonth = parseInt(vbcheckinp[1]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[2], 10);
			var vbcheckind = new Date(vbcheckinp[0], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[1]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[2], 10);
			var vbcheckoutd = new Date(vbcheckoutp[0], vboutmonth, vboutday);
			<?php
		}
		?>
			var vbdivider = 1000 * 60 * 60 * 24;
			var vbints = vbcheckind.getTime();
			var vboutts = vbcheckoutd.getTime();
			if(vboutts > vbints) {
				//var vbnights = Math.ceil((vboutts - vbints) / (vbdivider));
				var utc1 = Date.UTC(vbcheckind.getFullYear(), vbcheckind.getMonth(), vbcheckind.getDate());
				var utc2 = Date.UTC(vbcheckoutd.getFullYear(), vbcheckoutd.getMonth(), vbcheckoutd.getDate());
				var vbnights = Math.ceil((utc2 - utc1) / vbdivider);
				if(vbnights > 0) {
					document.getElementById('vbjstotnights').innerHTML = '<?php echo addslashes(JText::_('VBJSTOTNIGHTS')); ?>: '+vbnights;
				}else {
					document.getElementById('vbjstotnights').innerHTML = '';
				}
			}else {
				document.getElementById('vbjstotnights').innerHTML = '';
			}
		}else {
			document.getElementById('vbjstotnights').innerHTML = '';
		}
	}
	/* ]]> */
	</script>
	<input type="hidden" id="vbroomhelper" value="1"/>
	<?php
	if (isset($err) && strlen($err)) {
		echo "<p class=\"err\">" . $err . "</p>";
	}
	?>
	<div class="vbo-intro-main"><?php echo VikBooking::getIntroMain(); ?></div>
	<?php
	echo $selform;
	?>
	<div class="vbo-closing-main"><?php echo VikBooking::getClosingMain(); ?></div>
	<?php
	//search suggestions
	if (isset($err_code_info) && count($err_code_info) > 0 && (int)VikBooking::showSearchSuggestions() > 0) {
		if (!empty($pitemid) && !isset($err_code_info['Itemid'])) {
			$err_code_info['Itemid'] = $pitemid;
		}
		?>
	<div id="vbo-search-suggestions"></div>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "<?php echo JRoute::_('index.php?option=com_vikbooking&view=searchsuggestions&tmpl=component', false); ?>",
			data: <?php echo json_encode($err_code_info); ?>
		}).done(function(res) {
			jQuery("#vbo-search-suggestions").html(res);
		}).fail(function() {
			console.log('Failure event when calling the search suggestions view');
		});
	});
	</script>
		<?php
	}
	//
	//echo javascript to fill the date values
	if (!empty ($pval) && !empty ($rval)) {
		if($calendartype == "jqueryui") {
			?>
			<script type="text/javascript">
			jQuery(function(){
				jQuery('#checkindate').val('<?php echo $pval; ?>');
				jQuery('#checkoutdate').val('<?php echo $rval; ?>');
			});
			</script>
			<?php
		}else {
			?>
			<script type="text/javascript">
			document.getElementById('checkindate').value='<?php echo $pval; ?>';
			document.getElementById('checkoutdate').value='<?php echo $rval; ?>';
			</script>
			<?php
		}
	}
	//
	if (!empty($oldroomsnum) && $oldroomsnum > 1 && count($oldarrpeople) > 0) {
		$oldroomscountadults = array();
		$oldroomscountchildren = array();
		for($i = 2; $i <= $oldroomsnum; $i++) {
			$globnumadults = VikBooking::getSearchNumAdults();
			$adultsparts = explode('-', $globnumadults);
			$adultsel = "<select name=\"adults[]\">";
			for($a = $adultsparts[0]; $a <= $adultsparts[1]; $a++) {
				$adultsel .= "<option value=\"".$a."\"".($oldarrpeople[$i]['adults'] == $a ? " selected=\"selected\"" : "").">".$a."</option>";
			}
			$adultsel .= "</select>";
			$oldroomscountadults[$i] = $adultsel;
			$globnumchildren = VikBooking::getSearchNumChildren();
			$childrenparts = explode('-', $globnumchildren);
			$childrensel = "<select name=\"children[]\">";
			for($c = $childrenparts[0]; $c <= $childrenparts[1]; $c++) {
				$childrensel .= "<option value=\"".$c."\"".($oldarrpeople[$i]['children'] == $c ? " selected=\"selected\"" : "").">".$c."</option>";
			}
			$childrensel .= "</select>";
			$oldroomscountchildren[$i] = $childrensel;
		}
		?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function vbAddElementSession() {
		var oldradultsvals = new Array();
		var oldrchildrenvals = new Array();
		<?php
		for($i = 2; $i <= $oldroomsnum; $i++) {
			?>
			oldradultsvals[<?php echo $i; ?>] = "<?php echo addslashes(str_replace('"', "'", $oldroomscountadults[$i])); ?>";
			oldrchildrenvals[<?php echo $i; ?>] = "<?php echo addslashes(str_replace('"', "'", $oldroomscountchildren[$i])); ?>";
			<?php
		}
		?>
		var ni = document.getElementById('vbo-search-num-aduchild-block');
		var numi = document.getElementById('vbroomhelper');
		var num = (document.getElementById('vbroomhelper').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'vb'+num+'racont';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<div class=\'vbo-search-num-aduchild-entry\'><span class=\'vbo-search-roomnum\'><?php echo addslashes(JText::_('VBFORMNUMROOM')); ?> '+ num +'</span><div class=\'vbo-search-num-adults-entry\'><span class=\'vbo-search-num-adults-entry-label\'><?php echo addslashes(JText::_('VBFORMADULTS')); ?></span><span class=\'vbo-search-num-adults-entry-inp\'>'+ oldradultsvals[num] +'</span></div><?php if($showchildren): ?><div class=\'vbo-search-num-children-entry\'><span class=\'vbo-search-num-children-entry-label\'><?php echo addslashes(JText::_('VBFORMCHILDREN')); ?></span><span class=\'vbo-search-num-adults-entry-inp\'>'+ oldrchildrenvals[num] +'</span></div><?php endif; ?></div>';
		ni.appendChild(newdiv);
	}
	function vbSetRoomsAdultsSession(totrooms) {
		var actrooms = parseInt(document.getElementById('vbroomhelper').value);
		var torooms = parseInt(totrooms);
		var difrooms;
		if(torooms > actrooms) {
			difrooms = torooms - actrooms;
			for(var ir=1; ir<=difrooms; ir++) {
				vbAddElementSession();
			}
		}
		if(torooms < actrooms) {
			for(var ir=actrooms; ir>torooms; ir--) {
				if(ir > 1) {
					var rmra = document.getElementById('vb' + ir + 'racont');
					rmra.parentNode.removeChild(rmra);
				}
			}
			document.getElementById('vbroomhelper').value = torooms;
		}
	}
	vbSetRoomsAdultsSession('<?php echo $oldroomsnum; ?>');
	/* ]]> */
	</script>
	<?php
	}
} else {
	echo VikBooking::getDisabledBookingMsg();
}