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

$room = $this->room;
$msg = $this->msg;
$allc = $this->allc;
$payments = $this->payments;
$busy = $this->busy;
$vmode = $this->vmode;

//header
$dbo = JFactory::getDBO();
$vbo_app = new VboApplication();
$vbo_app->loadSelect2();
$document = JFactory::getDocument();
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
$document->addStyleSheet(VBO_ADMIN_URI.'resources/jquery.highlighttextarea.min.css');
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/jquery.highlighttextarea.min.js', false, true, false, false);
$vbo_df = VikBooking::getDateFormat(true);
$juidf = $vbo_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vbo_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');
$pcheckin = VikRequest::getString('checkin', '', 'request');
if (!empty($pcheckin)) {
	$pcheckin = date(str_replace('%', '', $vbo_df), strtotime($pcheckin));
}
$pcheckout = VikRequest::getString('checkout', '', 'request');
if (!empty($pcheckout)) {
	$pcheckout = date(str_replace('%', '', $vbo_df), strtotime($pcheckout));
}
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$poverview = VikRequest::getInt('overv', '', 'request');
$poverview_change = VikRequest::getInt('overview_change', '', 'request');
$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikbooking"] = {'."\n".'
		monthNames: ["'.JText::_('VBMONTHONE').'","'.JText::_('VBMONTHTWO').'","'.JText::_('VBMONTHTHREE').'","'.JText::_('VBMONTHFOUR').'","'.JText::_('VBMONTHFIVE').'","'.JText::_('VBMONTHSIX').'","'.JText::_('VBMONTHSEVEN').'","'.JText::_('VBMONTHEIGHT').'","'.JText::_('VBMONTHNINE').'","'.JText::_('VBMONTHTEN').'","'.JText::_('VBMONTHELEVEN').'","'.JText::_('VBMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VBMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VBMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VBSUNDAY').'", "'.JText::_('VBMONDAY').'", "'.JText::_('VBTUESDAY').'", "'.JText::_('VBWEDNESDAY').'", "'.JText::_('VBTHURSDAY').'", "'.JText::_('VBFRIDAY').'", "'.JText::_('VBSATURDAY').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VBSUNDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBMONDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTUESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBWEDNESDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBTHURSDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBFRIDAY'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VBSATURDAY'), 0, 2, 'UTF-8').'"],'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikBooking::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikbooking"]);'."\n".'
});';
$document->addScriptDeclaration($ldecl);
$fquick = "";
if (strlen($msg) > 0 && intval($msg) > 0) {
	$fquick.="<br/><p class=\"successmade\" style=\"margin-top: -15px;\">".JText::_('VBBOOKMADE')." - <a href=\"index.php?option=com_vikbooking&task=editorder&cid[]=".intval($msg)."\"><i class=\"vboicn-eye\"></i> ".JText::_('VBOVIEWBOOKINGDET')."</a></p>";
	if ($poverview > 0 && $ptmpl == 'component') {
		$poverview_change = 1;
	}

} elseif (strlen($msg) > 0 && $msg == "0") {
	$fquick.="<br/><p class=\"err\" style=\"margin-top: -15px;\">".JText::_('VBBOOKNOTMADE')."</p>";
}
$fquick .= "<form name=\"newb\" method=\"post\" action=\"index.php?option=com_vikbooking\" onsubmit=\"javascript: if (!document.newb.checkindate.value.match(/\S/)){alert('".JText::_('VBMSGTHREE')."'); return false;} if (!document.newb.checkoutdate.value.match(/\S/)){alert('".JText::_('VBMSGFOUR')."'); return false;} return true;\">";

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
$formatparts = explode(':', VikBooking::getNumberFormatData());
$currencysymb = VikBooking::getCurrencySymb(true);
$globnumadults = VikBooking::getSearchNumAdults(true);
$adultsparts = explode('-', $globnumadults);
$seladults = "<select name=\"adults\">\n";
for ($i = $adultsparts[0]; $i <= ((int)$adultsparts[1] * $room['units']); $i++) {
	$seladults .= "<option value=\"".$i."\"".(intval($adultsparts[0]) < 1 && $i == 1 ? " selected=\"selected\"" : "").">".$i."</option>\n";
}
$seladults .= "</select>\n";
$globnumchildren = VikBooking::getSearchNumChildren(true);
$childrenparts = explode('-', $globnumchildren);
$selchildren = "<select name=\"children\">\n";
for ($i = $childrenparts[0]; $i <= ((int)$childrenparts[1] * $room['units']); $i++) {
	$selchildren .= "<option value=\"".$i."\">".$i."</option>\n";
}
$selchildren .= "</select>\n";
$selpayments = '<select name="payment"><option value="">'.JText::_('VBPAYMUNDEFINED').'</option>';
if (is_array($payments) && @count($payments) > 0) {
	foreach ($payments as $pay) {
		$selpayments .= '<option value="'.$pay['id'].'">'.$pay['name'].'</option>';
	}
}
$selpayments .= '</select>';
//Custom Fields
$cfields_cont = '';
$q = "SELECT * FROM `#__vikbooking_custfields` ORDER BY `#__vikbooking_custfields`.`ordering` ASC;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$all_cfields = $dbo->loadAssocList();
	$q = "SELECT * FROM `#__vikbooking_countries` ORDER BY `#__vikbooking_countries`.`country_name` ASC;";
	$dbo->setQuery($q);
	$dbo->execute();
	$all_countries = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
	foreach ($all_cfields as $cfield) {
		if ($cfield['type'] == 'text') {
			$cfields_cont .= '<div class="vbo-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><input type="text" id="cfield'.$cfield['id'].'" data-isemail="'.($cfield['isemail'] == 1 ? '1' : '0').'" data-isnominative="'.($cfield['isnominative'] == 1 ? '1' : '0').'" data-isphone="'.($cfield['isphone'] == 1 ? '1' : '0').'" value="" size="35"/></span></div>'."\n";
		} elseif ($cfield['type'] == 'textarea') {
			$cfields_cont .= '<div class="vbo-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><textarea id="cfield'.$cfield['id'].'" rows="4" cols="35"></textarea></span></div>'."\n";
		} elseif ($cfield['type'] == 'country') {
			$cfields_cont .= '<div class="vbo-calendar-cfield-entry"><label for="cfield'.$cfield['id'].'" data-fieldid="'.$cfield['id'].'">'.JText::_($cfield['name']).'</label><span><select id="cfield'.$cfield['id'].'"><option value=""> </option>'."\n";
			foreach ($all_countries as $country) {
				$cfields_cont .= '<option value="'.$country['country_name'].'" data-ccode="'.$country['country_3_code'].'">'.$country['country_name'].'</option>';
			}
			$cfields_cont .= '</select></span></div>'."\n";
		}
	}
}
//
$wiva = "";
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $kiv => $iv) {
		$wiva .= "<option value=\"".$iv['id']."\" data-aliqid=\"".$iv['id']."\"".($kiv < 1 ? ' selected="selected"' : '').">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']." - ".$iv['aliq']."%")."</option>\n";
	}
}

$fquick.="<fieldset class=\"adminform\"><table cellspacing=\"1\" class=\"admintable table\"><tbody><tr><td width=\"200\" class=\"vbo-config-param-cell\"><strong>".JText::_('VBDATEPICKUP').":</strong> </td><td><div class=\"input-append\"><input type=\"text\" autocomplete=\"off\" name=\"checkindate\" id=\"checkindate\" size=\"10\" /><button type=\"button\" class=\"btn vbodatepicker-trig-icon\"><span class=\"icon-calendar\"></span></button></div> <span style=\"display: inline-block; margin-left: 10px;\">".JText::_('VBAT')." ".($hcheckin < 10 ? '0'.$hcheckin : $hcheckin).":".($mcheckin < 10 ? '0'.$mcheckin : $mcheckin)."</span><input type=\"hidden\" name=\"checkinh\" value=\"".$hcheckin."\"/><input type=\"hidden\" name=\"checkinm\" value=\"".$mcheckin."\"/></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBDATERELEASE').":</strong> </td><td><div class=\"input-append\"><input type=\"text\" autocomplete=\"off\" name=\"checkoutdate\" id=\"checkoutdate\" size=\"10\" /><button type=\"button\" class=\"btn vbodatepicker-trig-icon\"><span class=\"icon-calendar\"></span></button></div> <span style=\"display: inline-block; margin-left: 10px;\">".JText::_('VBAT')." ".($hcheckout < 10 ? '0'.$hcheckout : $hcheckout).":".($mcheckout < 10 ? '0'.$mcheckout : $mcheckout)."</span><span style=\"display: inline-block; margin-left: 25px; font-weight: bold;\" id=\"vbjstotnights\"></span><input type=\"hidden\" name=\"checkouth\" value=\"".$hcheckout."\"/><input type=\"hidden\" name=\"checkoutm\" value=\"".$mcheckout."\"/></td></tr>";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><span class=\"vbcloseroomsp\"><i class=\"fa fa-ban\"></i><label for=\"setclosed\"><strong>".JText::_('VBCLOSEROOM').":</strong></label></span> </td><td><input type=\"checkbox\" name=\"setclosed\" id=\"setclosed\" value=\"1\" onclick=\"javascript: vbCloseRoom();\"/></td></tr>\n";
if ($room['units'] > 1) {
	$num_rooms_vals = range(1, $room['units']);
	$num_rooms_opts = '';
	foreach ($num_rooms_vals as $nrv) {
		$num_rooms_opts .= '<option value="'.$nrv.'">'.$nrv.'</option>'."\n";
	}
	$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBPVIEWROOMSEVEN').":</strong> </td><td><span id=\"vbspannumrooms\"><select name=\"num_rooms\">".$num_rooms_opts."</select></span></td></tr>\n";
} else {
	$fquick.='<input type="hidden" name="num_rooms" value="1"/>';
}
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBQUICKRESGUESTS').":</strong> </td><td><span id=\"vbspanpeople\">".JText::_('VBQUICKADULTS').": ".$seladults." &nbsp;&nbsp; ".JText::_('VBQUICKCHILDREN').": ".$selchildren."</span></td></tr>\n";
$fquick.="<tr".($poverview > 0 ? ' style="display: none;"' : '')."><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBCALBOOKINGSTATUS').":</strong> </td><td><span id=\"vbspanbstat\"><select name=\"newstatus\"><option value=\"confirmed\">".JText::_('VBCONFIRMED')."</option><option value=\"standby\">".JText::_('VBSTANDBY')."</option></select></span></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBCALBOOKINGPAYMENT').":</strong> </td><td><span id=\"vbspanbpay\">".$selpayments."</span></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\">&nbsp;</td><td><span class=\"vbo-assign-customer\" id=\"vbfillcustfields\"><i class=\"fa fa-user-circle\"></i> <span>".JText::_('VBFILLCUSTFIELDS')."</span></span></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBCUSTEMAIL').":</strong> </td><td><span id=\"vbspancmail\"><input type=\"text\" name=\"custmail\" id=\"custmailfield\" value=\"\" size=\"25\"/></span></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBCUSTINFO').":</strong> </td><td><textarea name=\"custdata\" id=\"vbcustdatatxtarea\" rows=\"5\" cols=\"70\" style=\"min-width: 300px;\"></textarea></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\"><strong>".JText::_('VBOROOMCUSTRATEPLANADD').":</strong> </td><td><span id=\"vbspcustcost\">".$currencysymb." <input name=\"cust_cost\" id=\"cust_cost\" value=\"\" onfocus=\"document.getElementById('taxid').style.display = 'inline-block';\" onkeyup=\"vbCalcDailyCost(this.value);\" onchange=\"vbCalcDailyCost(this.value);\" type=\"number\" step=\"any\" style=\"min-width: 75px; margin: 0 5px 0 0;\"><select name=\"taxid\" id=\"taxid\" style=\"display: none; margin: 0;\"><option value=\"\">".JText::_('VBNEWOPTFOUR')."</option>".$wiva."</select><span id=\"avg-daycost\" style=\"display: inline-block; margin-left: 15px; font-weight: bold;\"></span></span></td></tr>\n";
$fquick.="<tr><td class=\"vbo-config-param-cell\">&nbsp;</td><td><button type=\"submit\" id=\"quickbsubmit\" class=\"btn btn-success btn-large\"><i class=\"icon-save\"></i> <span>".JText::_('VBMAKERESERV')."</span></button></td></tr>\n";
$fquick.="</tbody></table></fieldset>";
if ($poverview > 0) {
	$fquick.="<input type=\"hidden\" name=\"overv\" value=\"".$poverview."\" />\n";
}
if ($ptmpl == 'component') {
	$fquick.="<input type=\"hidden\" name=\"tmpl\" value=\"component\" />\n";
}
$fquick.="<input type=\"hidden\" name=\"customer_id\" value=\"\" id=\"customer_id_inpfield\"/><input type=\"hidden\" name=\"countrycode\" value=\"\" id=\"ccode_inpfield\"/><input type=\"hidden\" name=\"t_first_name\" value=\"\" id=\"t_first_name_inpfield\"/><input type=\"hidden\" name=\"t_last_name\" value=\"\" id=\"t_last_name_inpfield\"/><input type=\"hidden\" name=\"phone\" value=\"\" id=\"phonefield\"/><input type=\"hidden\" name=\"task\" value=\"calendar\"/><input type=\"hidden\" name=\"cid[]\" value=\"".$room['id']."\"/><input type=\"hidden\" name=\"option\" value=\"com_vikbooking\" /></form>\n";
//search customer
$search_funct = '<div class="vbo-calendar-cfields-search"><label for="vbo-searchcust" style="display: block;"><strong>'.JText::_('VBOSEARCHEXISTCUST').'</strong></label><span id="vbo-searchcust-loading"><i class="vboicn-hour-glass"></i></span><input type="text" id="vbo-searchcust" autocomplete="off" value="" placeholder="'.JText::_('VBOSEARCHCUSTBY').'" size="35" /><div id="vbo-searchcust-res"></div></div>';
//
//custom fields
$fquick.='<div class="vbo-calendar-cfields-filler-overlay"><a class="vbo-info-overlay-close" href="javascript: void(0);"></a><div class="vbo-calendar-cfields-filler"><h4>'.JText::_('VBCUSTINFO').'</h4>'.$search_funct.'<div class="vbo-calendar-cfields-inner">'.$cfields_cont.'</div><div class="vbo-calendar-cfields-bottom"><button type="button" class="btn" onclick="hideCustomFields();">'.JText::_('VBANNULLA').'</button> <button type="button" class="btn btn-success" onclick="applyCustomFieldsContent();"><i class="icon-edit"></i> '.JText::_('VBAPPLY').'</button></div></div></div>';
//
$fquick.='
<script type="text/javascript">
'.($poverview_change > 0 ? 'window.parent.hasNewBooking = true;'."\n" : '').'
var vbo_glob_sel_nights = 0;
var cfields_overlay = false;
var customers_search_vals = "";
function vbCloseRoom() {
	if (document.getElementById("setclosed").checked == true) {
		document.getElementById("vbspanpeople").style.display = "none";
		if (document.getElementById("vbspannumrooms")) {
			document.getElementById("vbspannumrooms").style.display = "none";
		}
		document.getElementById("vbspanbstat").style.display = "none";
		document.getElementById("vbspcustcost").style.display = "none";
		document.getElementById("vbspancmail").style.display = "none";
		document.getElementById("vbfillcustfields").style.display = "none";
		document.getElementById("vbspanbpay").style.display = "none";
		document.getElementById("vbcustdatatxtarea").value = "'.addslashes(JText::_('VBDBTEXTROOMCLOSED')).'";
		jQuery("#quickbsubmit").removeClass("btn-success").addClass("btn-danger").find("span").text("'.addslashes(JText::_('VBSUBMCLOSEROOM')).'");
	} else {
		document.getElementById("vbspanpeople").style.display = "inline-block";
		if (document.getElementById("vbspannumrooms")) {
			document.getElementById("vbspannumrooms").style.display = "inline-block";
		}
		document.getElementById("vbspanbstat").style.display = "block";
		document.getElementById("vbspcustcost").style.display = "block";
		document.getElementById("vbspancmail").style.display = "block";
		document.getElementById("vbfillcustfields").style.display = "inline-block";
		document.getElementById("vbspanbpay").style.display = "block";
		document.getElementById("vbcustdatatxtarea").value = "";
		jQuery("#quickbsubmit").removeClass("btn-danger").addClass("btn-success").find("span").text("'.addslashes(JText::_('VBMAKERESERV')).'");
	}
}
function showCustomFields() {
	cfields_overlay = true;
	jQuery(".vbo-calendar-cfields-filler-overlay, .vbo-calendar-cfields-filler").fadeIn();
}
function hideCustomFields() {
	cfields_overlay = false;
	jQuery(".vbo-calendar-cfields-filler-overlay").fadeOut();
}
function applyCustomFieldsContent() {
	var cfields_cont = "";
	var cfields_labels = new Array;
	var nominatives = new Array;
	var tot_rows = 1;
	jQuery(".vbo-calendar-cfields-inner .vbo-calendar-cfield-entry").each(function(){
		var cfield_name = jQuery(this).find("label").text();
		var cfield_input = jQuery(this).find("span").find("input");
		var cfield_textarea = jQuery(this).find("span").find("textarea");
		var cfield_select = jQuery(this).find("span").find("select");
		var cfield_cont = "";
		if (cfield_input.length) {
			cfield_cont = cfield_input.val();
			if (cfield_input.attr("data-isemail") == "1" && cfield_cont.length) {
				jQuery("#custmailfield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isphone") == "1") {
				jQuery("#phonefield").val(cfield_cont);
			}
			if (cfield_input.attr("data-isnominative") == "1") {
				nominatives.push(cfield_cont);
			}
		}else if (cfield_textarea.length) {
			cfield_cont = cfield_textarea.val();
		}else if (cfield_select.length) {
			cfield_cont = cfield_select.val();
			if (cfield_cont.length) {
				var country_code = jQuery("option:selected", cfield_select).attr("data-ccode");
				if (country_code.length) {
					jQuery("#ccode_inpfield").val(country_code);
				}
			}
		}
		if (cfield_cont.length) {
			cfields_cont += cfield_name+": "+cfield_cont+"\r\n";
			tot_rows++;
			cfields_labels.push(cfield_name+":");
		}
	});
	if (cfields_cont.length) {
		cfields_cont = cfields_cont.replace(/\r\n+$/, "");
	}
	if (nominatives.length > 1) {
		jQuery("#t_first_name_inpfield").val(nominatives[0]);
		jQuery("#t_last_name_inpfield").val(nominatives[1]);
	}
	jQuery("#vbcustdatatxtarea").val(cfields_cont);
	jQuery("#vbcustdatatxtarea").attr("rows", tot_rows);
	//Highlight Custom Fields Labels
	jQuery("#vbcustdatatxtarea").highlightTextarea({
		words: cfields_labels,
		color: "#ddd",
		id: "vbo-highlight-cfields"
	});
	//end highlight
	hideCustomFields();
}
function vbCalcNights() {
	vbo_glob_sel_nights = 0;
	var vbcheckin = document.getElementById("checkindate").value;
	var vbcheckout = document.getElementById("checkoutdate").value;
	if (vbcheckin.length > 0 && vbcheckout.length > 0) {
		var vbcheckinp = vbcheckin.split("/");
		var vbcheckoutp = vbcheckout.split("/");
		var vbo_df = "'.$vbo_df.'";
		if (vbo_df == "%d/%m/%Y") {
			var vbinmonth = parseInt(vbcheckinp[1]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[0], 10);
			var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[1]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[0], 10);
			var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
		}else if (vbo_df == "%m/%d/%Y") {
			var vbinmonth = parseInt(vbcheckinp[0]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[1], 10);
			var vbcheckind = new Date(vbcheckinp[2], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[0]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[1], 10);
			var vbcheckoutd = new Date(vbcheckoutp[2], vboutmonth, vboutday);
		} else {
			var vbinmonth = parseInt(vbcheckinp[1]);
			vbinmonth = vbinmonth - 1;
			var vbinday = parseInt(vbcheckinp[2], 10);
			var vbcheckind = new Date(vbcheckinp[0], vbinmonth, vbinday);
			var vboutmonth = parseInt(vbcheckoutp[1]);
			vboutmonth = vboutmonth - 1;
			var vboutday = parseInt(vbcheckoutp[2], 10);
			var vbcheckoutd = new Date(vbcheckoutp[0], vboutmonth, vboutday);
		}
		var vbdivider = 1000 * 60 * 60 * 24;
		var vbints = vbcheckind.getTime();
		var vboutts = vbcheckoutd.getTime();
		if (vboutts > vbints) {
			//var vbnights = Math.ceil((vboutts - vbints) / (vbdivider));
			var utc1 = Date.UTC(vbcheckind.getFullYear(), vbcheckind.getMonth(), vbcheckind.getDate());
			var utc2 = Date.UTC(vbcheckoutd.getFullYear(), vbcheckoutd.getMonth(), vbcheckoutd.getDate());
			var vbnights = Math.ceil((utc2 - utc1) / vbdivider);
			if (vbnights > 0) {
				vbo_glob_sel_nights = vbnights;
				document.getElementById("vbjstotnights").innerHTML = "'.addslashes(JText::_('VBDAYS')).': "+vbnights;
			} else {
				document.getElementById("vbjstotnights").innerHTML = "";
			}
		} else {
			document.getElementById("vbjstotnights").innerHTML = "";
		}
	} else {
		document.getElementById("vbjstotnights").innerHTML = "";
	}
}
function vbCalcDailyCost(cur_val) {
	var avg_cost_str = "";
	if (cur_val.length && !isNaN(cur_val) && vbo_glob_sel_nights > 0) {
		var avg_cost = (parseFloat(cur_val) / vbo_glob_sel_nights).toFixed('.(int)$formatparts[0].');
		avg_cost_str = "'.$currencysymb.' "+avg_cost+"/'.addslashes(JText::_('VBDAY')).'";
	}
	document.getElementById("avg-daycost").innerHTML = avg_cost_str;
}
jQuery(document).ready(function(){
	jQuery("#vbfillcustfields").click(function(){
		showCustomFields();
	});
	jQuery(document).mouseup(function(e) {
		if (!cfields_overlay) {
			return false;
		}
		var vbdialogcf_cont = jQuery(".vbo-calendar-cfields-filler");
		if (!vbdialogcf_cont.is(e.target) && vbdialogcf_cont.has(e.target).length === 0) {
			hideCustomFields();
		}
	});
	//Search customer - Start
	var vbocustsdelay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	function vboCustomerSearch(words) {
		jQuery("#vbo-searchcust-res").hide().html("");
		jQuery("#vbo-searchcust-loading").show();
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "searchcustomer", kw: words, tmpl: "component" }
		}).done(function(cont) {
			if (cont.length) {
				var obj_res = JSON.parse(cont);
				customers_search_vals = obj_res[0];
				jQuery("#vbo-searchcust-res").html(obj_res[1]);
			} else {
				customers_search_vals = "";
				jQuery("#vbo-searchcust-res").html("----");
			}
			jQuery("#vbo-searchcust-res").show();
			jQuery("#vbo-searchcust-loading").hide();
		}).fail(function() {
			jQuery("#vbo-searchcust-loading").hide();
			alert("Error Searching.");
		});
	}
	jQuery("#vbo-searchcust").keyup(function(event) {
		vbocustsdelay(function() {
			var keywords = jQuery("#vbo-searchcust").val();
			var chars = keywords.length;
			if (chars > 1) {
				if ((event.which > 96 && event.which < 123) || (event.which > 64 && event.which < 91) || event.which == 13) {
					vboCustomerSearch(keywords);
				}
			} else {
				if (jQuery("#vbo-searchcust-res").is(":visible")) {
					jQuery("#vbo-searchcust-res").hide();
				}
			}
		}, 600);
	});
	//Search customer - End
	//Datepickers - Start
	jQuery("#checkindate").datepicker({
		showOn: "focus",
		dateFormat: "'.$juidf.'",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var nowcheckin = jQuery("#checkindate").datepicker("getDate");
			var nowcheckindate = new Date(nowcheckin.getTime());
			nowcheckindate.setDate(nowcheckindate.getDate() + 1);
			jQuery("#checkoutdate").datepicker( "option", "minDate", nowcheckindate );
			vbCalcNights();
		}
	});
	jQuery("#checkoutdate").datepicker({
		showOn: "focus",
		dateFormat: "'.$juidf.'",
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			vbCalcNights();
		}
	});
	jQuery(".vbodatepicker-trig-icon").click(function(){
		var jdp = jQuery(this).prev("input.hasDatepicker");
		if (jdp.length) {
			jdp.focus();
		}
	});
	//Datepickers - End
	'.(!empty($pcheckin) ? 'jQuery("#checkindate").datepicker("setDate", "'.$pcheckin.'");'."\n" : '').'
	'.(!empty($pcheckout) ? 'jQuery("#checkoutdate").datepicker("setDate", "'.$pcheckout.'");'."\n" : '').'
	'.(!empty($pcheckin) || !empty($pcheckout) ? 'jQuery(".ui-datepicker-current-day").click();'."\n" : '').'
});
jQuery("body").on("click", ".vbo-custsearchres-entry", function() {
	var custid = jQuery(this).attr("data-custid");
	var custemail = jQuery(this).attr("data-email");
	var custphone = jQuery(this).attr("data-phone");
	var custcountry = jQuery(this).attr("data-country");
	var custfirstname = jQuery(this).attr("data-firstname");
	var custlastname = jQuery(this).attr("data-lastname");
	jQuery("#customer_id_inpfield").val(custid);
	if (customers_search_vals.hasOwnProperty(custid)) {
		jQuery.each(customers_search_vals[custid], function(cfid, cfval) {
			var fill_field = jQuery("#cfield"+cfid);
			if (fill_field.length) {
				fill_field.val(cfval);
			}
		});
	} else {
		jQuery("input[data-isnominative=\"1\"]").each(function(k, v) {
			if (k == 0) {
				jQuery(this).val(custfirstname);
				return true;
			}
			if (k == 1) {
				jQuery(this).val(custlastname);
				return true;
			}
			return false;
		});
		jQuery("input[data-isemail=\"1\"]").val(custemail);
		jQuery("input[data-isphone=\"1\"]").val(custphone);
		//Populate main calendar form
		jQuery("#custmailfield").val(custemail);
		jQuery("#t_first_name_inpfield").val(custfirstname);
		jQuery("#t_last_name_inpfield").val(custlastname);
		//
	}
	applyCustomFieldsContent();
	if (custcountry.length) {
		jQuery("#ccode_inpfield").val(custcountry);
	}
	if (custphone.length) {
		jQuery("#phonefield").val(custphone);
	}
});
</script>';
//vikbooking 1.1
$chroomsel = "<select id=\"vbo-calendar-changeroom\" name=\"cid[]\" onchange=\"javascript: document.vbchroom.submit();\">\n";
foreach ($allc as $cc) {
	$chroomsel .= "<option value=\"".$cc['id']."\"".($cc['id'] == $room['id'] ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
}
$chroomsel .= "</select>\n";
if ($ptmpl == 'component') {
	$chroomsel .= "<input type=\"hidden\" name=\"tmpl\" value=\"component\" />\n";
}
$chroomf = "<form name=\"vbchroom\" method=\"post\" action=\"index.php?option=com_vikbooking\"><input type=\"hidden\" name=\"task\" value=\"calendar\"/>".$chroomsel."</form>";
//
echo "<div class=\"vbo-quickres-wrapper\"><table style=\"width: 95%;\"><tr><td valign=\"top\" align=\"left\"><div class=\"vbo-quickres-head\"><h4>".$room['name'].", ".JText::_('VBQUICKBOOK')."</h4> <div class=\"vbo-quickres-head-right\">".$chroomf."</div></div></td></tr><tr><td valign=\"top\" align=\"left\">".$fquick."</td></tr></table></div>\n";
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#vbo-calendar-changeroom").select2();
});
</script>

<?php
//calendar content

$ptmpl = VikRequest::getString('tmpl', '', 'request');
$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
?>
<div class="vbo-avcalendars-wrapper">
	<div class="vbo-avcalendars-roomphoto">
	<?php
	if (file_exists(VBO_SITE_PATH.DS.'resources'.DS.'uploads'.DS.$room['img'])) {
		$img = '<img alt="Room Image" src="' . VBO_SITE_URI . 'resources/uploads/'.$room['img'].'" />';
	} else {
		$img = '<img alt="Vik Booking Logo" src="' . VBO_ADMIN_URI . 'vikbooking.png' . '" />';
	}
	echo $img;
	?>
	</div>
<?php
$check = false;
if (empty($busy)) {
	echo "<p class=\"warn\">".JText::_('VBNOFUTURERES')."</p>";
} else {
	$check = true;
	?>
	<p>
		<a class="vbmodelink<?php echo $vmode == 3 ? ' vbmodelink-active' : ''; ?>" href="index.php?option=com_vikbooking&amp;task=calendar&amp;cid[]=<?php echo $room['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=3"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VBTHREEMONTHS'); ?></span></a>
		<a class="vbmodelink<?php echo $vmode == 6 ? ' vbmodelink-active' : ''; ?>" href="index.php?option=com_vikbooking&amp;task=calendar&amp;cid[]=<?php echo $room['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=6"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VBSIXMONTHS'); ?></span></a>
		<a class="vbmodelink<?php echo $vmode == 12 ? ' vbmodelink-active' : ''; ?>" href="index.php?option=com_vikbooking&amp;task=calendar&amp;cid[]=<?php echo $room['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=12"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VBTWELVEMONTHS'); ?></span></a>
		<a class="vbmodelink<?php echo $vmode == 24 ? ' vbmodelink-active' : ''; ?>" href="index.php?option=com_vikbooking&amp;task=calendar&amp;cid[]=<?php echo $room['id'].($ptmpl == 'component' ? '&tmpl=component' : ''); ?>&amp;vmode=24"><i class="fa fa-calendar"></i> <span><?php echo JText::_('VBTWOYEARS'); ?></span></a>
	</p>
	<?php
}
?>
	<div class="table-responsive">
	<table class="table" align="center"><tr>
<?php
$arr = getdate();
$mon = $arr['mon'];
$realmon = ($mon < 10 ? "0".$mon : $mon);
$year = $arr['year'];
$day = $realmon."/01/".$year;
$dayts = strtotime($day);
$newarr = getdate($dayts);

$firstwday = (int)VikBooking::getFirstWeekDay(true);
$days_labels = array(
		JText::_('VBSUN'),
		JText::_('VBMON'),
		JText::_('VBTUE'),
		JText::_('VBWED'),
		JText::_('VBTHU'),
		JText::_('VBFRI'),
		JText::_('VBSAT')
);
$days_indexes = array();
for ($i = 0; $i < 7; $i++) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

for ($jj = 1; $jj <= $vmode; $jj++) {
	$d_count = 0;
	echo "<td valign=\"top\">";
	$cal="";
	?>
	<table class="vbadmincaltable">
	<tr class="vbadmincaltrmon"><td colspan="7" align="center"><?php echo VikBooking::sayMonth($newarr['mon'])." ".$newarr['year']; ?></td></tr>
	<tr class="vbadmincaltrmdays">
	<?php
	for ($i = 0; $i < 7; $i++) {
		$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
		echo '<td>'.$days_labels[$d_ind].'</td>';
	}
	?>
	</tr>
	<tr>
	<?php
	for ($i=0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) {
		$cal .= "<td align=\"center\">&nbsp;</td>";
	}
	while ($newarr['mon'] == $mon) {
		if ($d_count > 6) {
			$d_count = 0;
			$cal .= "</tr>\n<tr>";
		}
		$dclass = "free";
		$dalt = "";
		$bid = "";
		$totfound = 0;
		if ($check) {
			foreach ($busy as $b) {
				$tmpone = getdate($b['checkin']);
				$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
				$ritts = strtotime($rit);
				$tmptwo = getdate($b['checkout']);
				$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
				$conts = strtotime($con);
				if ($newarr[0] >= $ritts && $newarr[0] < $conts) {
					$dclass = "busy";
					$bid = $b['idorder'];
					if ($newarr[0] == $ritts) {
						$dalt = JText::_('VBPICKUPAT')." ".date('H:i', $b['checkin']);
					} elseif ($newarr[0] == $conts) {
						$dalt = JText::_('VBRELEASEAT')." ".date('H:i', $b['checkout']);
					}
					$totfound++;
				}
			}
		}
		$useday = ($newarr['mday'] < 10 ? "0".$newarr['mday'] : $newarr['mday']);
		if ($totfound > 0 && $totfound < $room['units']) {
			$dclass .= " vbo-partially";
		}
		if ($totfound == 1) {
			$dlnk = "<a href=\"index.php?option=com_vikbooking&task=editbusy&cid[]=".$bid."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$dlnk."</td>\n";
		} elseif ($totfound > 1) {
			$dlnk = "<a href=\"index.php?option=com_vikbooking&task=choosebusy&idroom=".$room['id']."&ts=".$newarr[0]."\"".($ptmpl == 'component' ? ' target="_blank"' : '').">".$useday."</a>";
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
		} else {
			$dlnk = $useday;
			$cal .= "<td align=\"center\" data-daydate=\"".date($df, $newarr[0])."\" class=\"".$dclass."\">".$dlnk."</td>\n";
		}
		$next = $newarr['mday'] + 1;
		$dayts = mktime(0, 0, 0, ($newarr['mon'] < 10 ? "0".$newarr['mon'] : $newarr['mon']), ($next < 10 ? "0".$next : $next), $newarr['year']);
		$newarr = getdate($dayts);
		$d_count++;
	}
	
	for ($i = $d_count; $i <= 6; $i++) {
		$cal.="<td align=\"center\">&nbsp;</td>";
	}
	
	echo $cal;
	?>
	</tr>
	</table>
	<?php
	echo "</td>";
	if ($mon == 12) {
		$mon = 1;
		$year += 1;
		$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
	} else {
		$mon += 1;
		$dayts = mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
	}
	$newarr = getdate($dayts);
	
	if (($jj % 4) == 0 && $vmode > 4) {
		echo "</tr>\n<tr>";
	}
}

?>
	</tr>
	</table>
	</div>
</div>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('td.free').click(function() {
		var indate = jQuery('#checkindate').val();
		var outdate = jQuery('#checkoutdate').val();
		var clickdate = jQuery(this).attr('data-daydate');
		if (!(indate.length > 0)) {
			jQuery('#checkindate').val(clickdate);
		}else if (!(outdate.length > 0) && clickdate != indate) {
			jQuery('#checkoutdate').val(clickdate);
		} else {
			jQuery('#checkindate').val(clickdate);
			jQuery('#checkoutdate').val('');
		}
	});
});
</script>
<br clear="all" />