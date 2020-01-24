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

$room = $this->room;
$busy = $this->busy;
$seasons_cal = $this->seasons_cal;
$promo_season = $this->promo_season;
$vbo_tn = $this->vbo_tn;

$vbo_app = VikBooking::getVboApplication();
$is_mobile = VikBooking::detectUserAgent(false, false);

$session = JFactory::getSession();
$currencysymb = VikBooking::getCurrencySymb();
$showpartlyres = VikBooking::showPartlyReserved();
$showcheckinoutonly = VikBooking::showStatusCheckinoutOnly();
$vbdateformat = VikBooking::getDateFormat();
if ($vbdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vbdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();
$inonout_allowed = true;
$timeopst = VikBooking::getTimeOpenStore();
if (is_array($timeopst)) {
	if ($timeopst[0] < $timeopst[1]) {
		//check-in not allowed on a day where there is already a check out (no arrivals/depatures on the same day)
		$inonout_allowed = false;
	}
}

$carats = VikBooking::getRoomCaratOriz($room['idcarat'], $vbo_tn);
$pitemid = VikRequest::getInt('Itemid', '', 'request');

$document = JFactory::getDocument();
//load jQuery
if (VikBooking::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$navdecl = '
jQuery(document).ready(function() {
	jQuery(".vbomodalframe").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"width": "75%",
		"height": "75%",
	    "autoScale": false,
	    "transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"type": "iframe"
	});
});';
$document->addScriptDeclaration($navdecl);

$imagegallery = false;
if (strlen($room['moreimgs']) > 0) {
	$imagegallery = true;
	$moreimages = explode(';;', $room['moreimgs']);
	$document->addStyleSheet(VBO_SITE_URI.'resources/VikFXThumbSlide.css');
	JHtml::_('script', VBO_SITE_URI.'resources/VikFXThumbSlide.js', false, true, false, false);
	$vikfsimgs = array();
	$imgcaptions = json_decode($room['imgcaptions'], true);
	$usecaptions = empty($imgcaptions) || is_null($imgcaptions) || !is_array($imgcaptions) || !(count($imgcaptions) > 0) ? false : true;
	foreach($moreimages as $iind => $mimg) {
		if (!empty($mimg)) {
			$img_alt = $usecaptions === true && !empty($imgcaptions[$iind]) ? $imgcaptions[$iind] : substr($mimg, 0, strpos($mimg, '.'));
			$vikfsimgs[] = '{image : "'.VBO_SITE_URI.'resources/uploads/big_'.$mimg.'", alt : "'.addslashes($img_alt).'", caption : "'.($usecaptions === true ? addslashes($imgcaptions[$iind]) : "").'"}';
		}
	}
	if (count($vikfsimgs)) {
		$vikfx = '
jQuery.VikFXThumbSlide.set({
	images : [
		'.implode(',', $vikfsimgs).'
	],
	mainImageClass : "vikfx-thumbslide-image",
	fadeContainerClass : "vikfx-thumbslide-fade-container",
	thumbnailContainerClass: "vikfx-thumbslide-thumbnails",
	useNavigationControls: true,
	previousLinkClass : "vikfx-thumbslide-previous-image",
	nextLinkClass : "vikfx-thumbslide-next-image",
	startSlideShowClass : "vikfx-thumbslide-start-slideshow",
	stopSlideShowClass : "vikfx-thumbslide-stop-slideshow"
});
jQuery(document).ready(function () {
	jQuery.VikFXThumbSlide.init(".vikfx-thumbslide-container");
});';
		$document->addScriptDeclaration($vikfx);
	}
}

?>

<div class="vbrdetboxtop">

<div class="vblistroomnamediv">
	<span class="vblistroomname"><?php echo $room['name']; ?></span>
	<span class="vblistroomcat"><?php echo VikBooking::sayCategory($room['idcat'], $vbo_tn); ?></span>
</div>
<div class="vbroomimgdesc">
<?php 
if (!empty ($room['img'])) {
?>
	<div class="vikfx-thumbslide-container">
		<div class="vikfx-thumbslide-fade-container">
			<img src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $room['img']; ?>" alt="<?php echo $room['name']; ?>" class="vikfx-thumbslide-image vblistimg"/>
			<div class="vikfx-thumbslide-caption"></div>
		<?php
		if ($imagegallery === true) {
			?>
			<div class="vikfx-thumbslide-navigation-controls">
				<div class="vikfx-thumbslide-navigation-controls-prevnext">
					<a href="javascript: void(0);" class="vikfx-thumbslide-previous-image"><i class="fa fa-chevron-circle-left"></i></a>
					<a href="javascript: void(0);" class="vikfx-thumbslide-next-image"><i class="fa fa-chevron-circle-right"></i></a>
				</div>
				<div class="vikfx-thumbslide-navigation-controls-stasto">
					<a href="javascript: void(0);" class="vikfx-thumbslide-start-slideshow"><i class="fa fa-play-circle"></i></a>
					<a href="javascript: void(0);" class="vikfx-thumbslide-stop-slideshow"><i class="fa fa-stop-circle"></i></a>
				</div>
			</div>
		<?php
		}
		?>
		</div>
	<?php
	if ($imagegallery === true) {
		?>
		<ul class="vikfx-thumbslide-thumbnails">
		<?php
		foreach($moreimages as $mimg) {
			if (!empty($mimg)) {
			?>
			<li>
				<a href="<?php echo VBO_SITE_URI; ?>resources/uploads/big_<?php echo $mimg; ?>" target="_blank"><img src="<?php echo VBO_SITE_URI; ?>resources/uploads/thumb_<?php echo $mimg; ?>"/></a>
			</li>
			<?php
			}
		}
		?>
		</ul>
		<?php
	}
	?>	
	</div>
<?php
}
?>
</div>

<div class="vbo-rdet-descprice-block">
	<div class="vbo-rdet-desc-cont">
<?php
//BEGIN: Joomla Content Plugins Rendering
if (class_exists('JEventDispatcher')) {
	JPluginHelper::importPlugin('content');
	$myItem = JTable::getInstance('content');
	$objparams = array();
	$dispatcher = JEventDispatcher::getInstance();
	$myItem->text = $room['info'];
	$dispatcher->trigger('onContentPrepare', array('com_vikbooking.roomdetails', &$myItem, &$objparams, 0));
	$room['info'] = $myItem->text;
}
//END: Joomla Content Plugins Rendering
echo $room['info'];
if ((bool)VikBooking::getRoomParam('reqinfo', $room['params'])) {
	//Request Information form
	$reqinfotoken = rand(1, 999);
	$session->set('vboreqinfo'.$room['id'], $reqinfotoken);
	$cur_user = JFactory::getUser();
	$cur_email = '';
	if (property_exists($cur_user, 'email') && !empty($cur_user->email)) {
		$cur_email = $cur_user->email;
	}
	?>
		<div class="vbo-reqinfo-cont">
			<span><a href="Javascript: void(0);" onclick="vboShowRequestInfo();" class="vbo-reqinfo-opener"><?php echo JText::_('VBOROOMREQINFOBTN'); ?></a></span>
		</div>
		<div id="vbdialog-overlay" style="display: none;">
			<a class="vbdialog-overlay-close" href="javascript: void(0);"></a>
			<div class="vbdialog-inner vbdialog-reqinfo">
				<h3><?php echo JText::sprintf('VBOROOMREQINFOTITLE', $room['name']); ?></h3>
				<form action="<?php echo JRoute::_('index.php?option=com_vikbooking&task=reqinfo'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" onsubmit="return vboValidateReqInfo();">
					<input type="hidden" name="roomid" value="<?php echo $room['id']; ?>" />
					<input type="hidden" name="reqinfotoken" value="<?php echo $reqinfotoken; ?>" />
					<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
					<div class="vbdialog-reqinfo-formcont">
						<div class="vbdialog-reqinfo-formentry">
							<label for="reqname"><?php echo JText::_('VBOROOMREQINFONAME'); ?></label>
							<input type="text" name="reqname" id="reqname" value="" placeholder="<?php echo JText::_('VBOROOMREQINFONAME'); ?>" />
						</div>
						<div class="vbdialog-reqinfo-formentry">
							<label for="reqemail"><?php echo JText::_('VBOROOMREQINFOEMAIL'); ?></label>
							<input type="text" name="reqemail" id="reqemail" value="<?php echo $cur_email; ?>" placeholder="<?php echo JText::_('VBOROOMREQINFOEMAIL'); ?>" />
						</div>
						<div class="vbdialog-reqinfo-formentry">
							<label for="reqmess"><?php echo JText::_('VBOROOMREQINFOMESS'); ?></label>
							<textarea name="reqmess" id="reqmess" placeholder="<?php echo JText::_('VBOROOMREQINFOMESS'); ?>"></textarea>
						</div>
					<?php
					if (count($this->terms_fields)) {
						if (!empty($this->terms_fields['poplink'])) {
							$fname = "<a href=\"" . $this->terms_fields['poplink'] . "\" id=\"vbof\" rel=\"{handler: 'iframe', size: {x: 750, y: 600}}\" target=\"_blank\" class=\"vbomodalframe\">" . JText::_($this->terms_fields['name']) . "</a>";
						} else {
							$fname = "<label id=\"vbof\" for=\"vbof-inp\" style=\"display: inline-block;\">" . JText::_($this->terms_fields['name']) . "</label>";
						}
						?>
						<div class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-ckbox">
							<?php echo $fname; ?>
							<input type="checkbox" name="vbof" id="vbof-inp" value="<?php echo JText::_('VBYES'); ?>"/>
						</div>
						<?php
					} else {
						?>
						<div class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-ckbox">
							<label id="vbof" for="vbof-inp" style="display: inline-block;"><?php echo JText::_('ORDER_TERMSCONDITIONS'); ?></label>
							<input type="checkbox" name="vbof" id="vbof-inp" value="<?php echo JText::_('VBYES'); ?>"/>
						</div>
						<?php
					}
					if ($vbo_app->isCaptcha()) {
						/**
						 * @joomlaonly  reCaptcha is only for Joomla
						 */
						?>
						<div class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formentry-captcha">
							<div><?php echo $vbo_app->reCaptcha(); ?></div>
						</div>
						<?php
					}
					?>
						<div class="vbdialog-reqinfo-formentry vbdialog-reqinfo-formsubmit">
							<button type="submit" class="btn"><?php echo JText::_('VBOROOMREQINFOSEND'); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<script type="text/javascript">
		var vbdialog_on = false;
		function vboShowRequestInfo() {
			jQuery("#vbdialog-overlay").fadeIn();
			vbdialog_on = true;
		}
		function vboHideRequestInfo() {
			jQuery("#vbdialog-overlay").fadeOut();
			vbdialog_on = false;
		}
		jQuery(function() {
			jQuery(document).mouseup(function(e) {
				if (!vbdialog_on) {
					return false;
				}
				var vbdialog_cont = jQuery(".vbdialog-inner");
				if (!vbdialog_cont.is(e.target) && vbdialog_cont.has(e.target).length === 0) {
					vboHideRequestInfo();
				}
			});
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27 && vbdialog_on) {
					vboHideRequestInfo();
				}
			});
		});
		function vboValidateReqInfo() {
			if (document.getElementById('vbof-inp').checked) {
				return true;
			}
			alert('<?php echo addslashes(JText::_('VBOROOMREQINFOMISSFIELD')); ?>');
			return false;
		}
		</script>
	<?php
	//
}
?>
	</div>
<?php
$custprice = VikBooking::getRoomParam('custprice', $room['params']);
$custpricetxt = VikBooking::getRoomParam('custpricetxt', $room['params']);
$custpricetxt = empty($custpricetxt) ? '' : JText::_($custpricetxt);
$custpricesubtxt = VikBooking::getRoomParam('custpricesubtxt', $room['params']);
if ($room['cost'] > 0 || !empty($custprice)) {
?>
	<div class="vb_detcostroomdet">
		<div class="vb_detcostroom">
			<div class="vblistroomnamedivprice">
				<div class="vblistroomname">
					<span class="vbliststartfromrdet"><?php echo JText::_('VBLISTSFROM'); ?></span>
					<span class="room_cost"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo (!empty($custprice) ? VikBooking::numberFormat($custprice) : VikBooking::numberFormat($room['cost'])); ?></span></span>
				<?php
				if (!empty($custpricetxt)) {
					?>
					<span class="roomcustcostlabel"><?php echo $custpricetxt; ?></span>
					<?php
				}
				if (!empty($custpricesubtxt)) {
					?>
					<div class="roomcustcost-subtxt"><?php echo $custpricesubtxt; ?></div>
					<?php
				}
				?>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
</div>

<?php 
if (!empty($carats)) {
	?>
	<div class="room_carats">
		<h3 class="vbtith3"><?php echo JText::_('VBCHARACTERISTICS'); ?></h3>
		<?php echo $carats; ?>
	</div>
	<?php
}
?>
</div>
<br clear="all"/>

<?php

if (count($seasons_cal) > 0) {
	//Seasons Calendar
	$price_types_show = intval(VikBooking::getRoomParam('seasoncal_prices', $room['params'])) == 1 ? false : true;
	$los_show = intval(VikBooking::getRoomParam('seasoncal_restr', $room['params'])) == 1 ? true : false;
?>
<div class="vbo-seasonscalendar-cont">
<h4><?php echo JText::_('VBOSEASONSCALENDAR'); ?></h4>
<div class="table-responsive">
<table class="table vbo-seasons-calendar-table">
	<tr class="vbo-seasons-calendar-nightsrow">
		<td>&nbsp;</td>
	<?php
	foreach ($seasons_cal['offseason'] as $numnights => $ntars) {
		?>
		<td><span><?php echo JText::sprintf(($numnights > 1 ? 'VBOSEASONCALNUMNIGHTS' : 'VBOSEASONCALNUMNIGHT'), $numnights); ?></span></td>
		<?php
	}
	?>
	</tr>
	<tr class="vbo-seasons-calendar-offseasonrow">
		<td>
			<span class="vbo-seasons-calendar-offseasonname"><?php echo JText::_('VBOSEASONSCALOFFSEASONPRICES'); ?></span>
		</td>
	<?php
	foreach ($seasons_cal['offseason'] as $numnights => $tars) {
		?>
		<td>
			<div class="vbo-seasons-calendar-offseasoncosts">
				<?php
				foreach ($tars as $tar) {
					?>
				<div class="vbo-seasons-calendar-offseasoncost">
					<?php
					if ($price_types_show) {
					?>
					<span class="vbo-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
					<?php
					}
					?>
					<span class="vbo-seasons-calendar-pricecost">
						<span class="vbo_currency"><?php echo $currencysymb; ?></span><span class="vbo_price"><?php echo VikBooking::numberFormat($tar['cost']); ?></span>
					</span>
				</div>
					<?php
					if (!$price_types_show) {
						break;
					}
				}
				?>
			</div>
		</td>
		<?php
	}
	?>
	</tr>
	<?php
	foreach ($seasons_cal['seasons'] as $s_id => $s) {
		$restr_diff_nights = array();
		if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions'])) {
			$restr_diff_nights = VikBooking::compareSeasonRestrictionsNights($seasons_cal['restrictions'][$s_id]);
		}
		?>
	<tr class="vbo-seasons-calendar-seasonrow">
		<td>
			<div class="vbo-seasons-calendar-seasondates">
				<span class="vbo-seasons-calendar-seasonfrom"><?php echo date(str_replace("/", $datesep, $df), $s['from_ts']); ?></span>
				<span class="vbo-seasons-calendar-seasondates-separe">-</span>
				<span class="vbo-seasons-calendar-seasonto"><?php echo date(str_replace("/", $datesep, $df), $s['to_ts']); ?></span>
			</div>
			<span class="vbo-seasons-calendar-seasonname"><?php echo $s['spname']; ?></span>
		<?php
		if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && count($restr_diff_nights) == 0) {
			//Season Restrictions
			$season_restrictions = array();
			foreach ($seasons_cal['restrictions'][$s_id] as $restr) {
				$season_restrictions = $restr;
				break;
			}
			?>
			<div class="vbo-seasons-calendar-restrictions">
			<?php
			if ($season_restrictions['minlos'] > 1) {
				?>
				<span class="vbo-seasons-calendar-restriction-minlos"><?php echo JText::_('VBORESTRMINLOS'); ?><span class="vbo-seasons-calendar-restriction-minlos-badge"><?php echo $season_restrictions['minlos']; ?></span></span>
				<?php
			}
			if (array_key_exists('maxlos', $season_restrictions) && $season_restrictions['maxlos'] > 1) {
				?>
				<span class="vbo-seasons-calendar-restriction-maxlos"><?php echo JText::_('VBORESTRMAXLOS'); ?><span class="vbo-seasons-calendar-restriction-maxlos-badge"><?php echo $season_restrictions['maxlos']; ?></span></span>
				<?php
			}
			if (array_key_exists('wdays', $season_restrictions) && count($season_restrictions['wdays']) > 0) {
				?>
				<div class="vbo-seasons-calendar-restriction-wdays">
					<label><?php echo JText::_((count($season_restrictions['wdays']) > 1 ? 'VBORESTRARRIVWDAYS' : 'VBORESTRARRIVWDAY')); ?></label>
				<?php
				foreach ($season_restrictions['wdays'] as $wday) {
					?>
					<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay($wday); ?></span>
					<?php
				}
				?>
				</div>
				<?php
			} elseif ((array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) || (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0)) {
				if (array_key_exists('cta', $season_restrictions) && count($season_restrictions['cta']) > 0) {
					?>
				<div class="vbo-seasons-calendar-restriction-wdays vbo-seasons-calendar-restriction-cta">
					<label><?php echo JText::_('VBORESTRWDAYSCTA'); ?></label>
					<?php
					foreach ($season_restrictions['cta'] as $wday) {
						?>
					<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay(str_replace('-', '', $wday)); ?></span>
						<?php
					}
					?>
				</div>
					<?php
				}
				if (array_key_exists('ctd', $season_restrictions) && count($season_restrictions['ctd']) > 0) {
					?>
				<div class="vbo-seasons-calendar-restriction-wdays vbo-seasons-calendar-restriction-ctd">
					<label><?php echo JText::_('VBORESTRWDAYSCTD'); ?></label>
					<?php
					foreach ($season_restrictions['ctd'] as $wday) {
						?>
					<span class="vbo-seasons-calendar-restriction-wday"><?php echo VikBooking::sayWeekDay(str_replace('-', '', $wday)); ?></span>
						<?php
					}
					?>
				</div>
					<?php
				}
			}
			?>
			</div>
			<?php
		}
		?>
		</td>
		<?php
		if (array_key_exists($s_id, $seasons_cal['season_prices']) && count($seasons_cal['season_prices'][$s_id]) > 0) {
			foreach ($seasons_cal['season_prices'][$s_id] as $numnights => $tars) {
				$show_day_cost = true;
				if ($los_show && array_key_exists($s_id, $seasons_cal['restrictions']) && array_key_exists($numnights, $seasons_cal['restrictions'][$s_id])) {
					if ($seasons_cal['restrictions'][$s_id][$numnights]['allowed'] === false) {
						$show_day_cost = false;
					}
				}
				?>
		<td>
			<?php
			if ($show_day_cost) {
			?>
			<div class="vbo-seasons-calendar-seasoncosts">
				<?php
				foreach ($tars as $tar) {
					//print the types of price that are not being modified by this special price with opacity
					$not_affected = (!array_key_exists('origdailycost', $tar));
					//
					?>
				<div class="vbo-seasons-calendar-seasoncost<?php echo ($not_affected ? ' vbo-seasons-calendar-seasoncost-notaffected' : ''); ?>">
					<?php
					if ($price_types_show) {
					?>
					<span class="vbo-seasons-calendar-pricename"><?php echo $tar['name']; ?></span>
					<?php
					}
					?>
					<span class="vbo-seasons-calendar-pricecost">
						<span class="vbo_currency"><?php echo $currencysymb; ?></span><span class="vbo_price"><?php echo VikBooking::numberFormat($tar['cost']); ?></span>
					</span>
				</div>
					<?php
					if (!$price_types_show) {
						break;
					}
				}
				?>
			</div>
			<?php
			} else {
				?>
				<div class="vbo-seasons-calendar-seasoncosts-disabled"></div>
				<?php
			}
			?>
		</td>
				<?php
			}
		}
		?>
	</tr>
		<?php
	}
	?>
</table>
</div>
</div>
<?php
//End Seasons Calendar
}

$numcalendars = VikBooking::numCalendars();
$closing_dates = VikBooking::parseJsClosingDates();
$cal_closing_dates = $closing_dates;
if (count($cal_closing_dates) > 0) {
	foreach ($cal_closing_dates as $ccdk => $ccdv) {
		if (!(count($ccdv) == 2)) {
			continue;
		}
		$cal_closing_dates[$ccdk][0] = strtotime($ccdv[0]);
		$cal_closing_dates[$ccdk][1] = strtotime($ccdv[1]);
	}
}
$push_disabled_in = array();
$push_disabled_out = array();

if ($numcalendars > 0) {
	$pmonth = VikRequest::getInt('month', '', 'request');
	$arr=getdate();
	$mon=$arr['mon'];
	$realmon=($mon < 10 ? "0".$mon : $mon);
	$year=$arr['year'];
	$day=$realmon."/01/".$year;
	$dayts=strtotime($day);
	$validmonth=false;
	if ($pmonth > 0 && $pmonth >= $dayts) {
		$validmonth=true;
	}
	$moptions="";
	for ($i=0; $i < 12; $i++) {
		$moptions.="<option value=\"".$dayts."\"".($validmonth && $pmonth == $dayts ? " selected=\"selected\"" : "").">".VikBooking::sayMonth($arr['mon'])." ".$arr['year']."</option>\n";
		$next=$arr['mon'] + 1;
		$dayts=mktime(0, 0, 0, ($next < 10 ? "0".$next : $next), 01, $arr['year']);
		$arr=getdate($dayts);
	}
	?>

<div id="vbo-bookingpart-init"></div>

<div class="vbo-availcalendars-cont">

	<h4><?php echo JText::_('VBOAVAILABILITYCALENDAR'); ?></h4>
	
	<form action="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$room['id'].'&Itemid='.$pitemid); ?>" method="post" name="vbmonths">
		<select name="month" onchange="javascript: document.vbmonths.submit();" class="vbselectm"><?php echo $moptions; ?></select>
		<input type="hidden" name="checkin" id="checkin-hidden" value="" />
		<input type="hidden" name="promo" id="promo-hidden" value="" />
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
	</form>
	
	<div class="vblegendediv">
	
		<span class="vblegenda"><div class="vblegfree">&nbsp;</div> <?php echo JText::_('VBLEGFREE'); ?></span>
	<?php
	if ($showpartlyres) {
		?>
		<span class="vblegenda"><div class="vblegwarning">&nbsp;</div> <?php echo JText::_('VBLEGWARNING'); ?></span>
		<?php
	}
	if ($showcheckinoutonly) {
		?>
		<span class="vblegenda"><span class="vblegbusycheckout">&nbsp;</span> <?php echo JText::_('VBLEGBUSYCHECKOUT'); ?></span>
		<span class="vblegenda"><span class="vblegbusycheckin">&nbsp;</span> <?php echo JText::_('VBLEGBUSYCHECKIN'); ?></span>
		<?php
	}
	?>
		<span class="vblegenda"><div class="vblegbusy">&nbsp;</div> <?php echo JText::_('VBLEGBUSY'); ?></span>
		
	</div>
	
	<?php
	$check=false;
	if (@is_array($busy)) {
		$check=true;
	}
	if ($validmonth) {
		$arr=getdate($pmonth);
		$mon=$arr['mon'];
		$realmon=($mon < 10 ? "0".$mon : $mon);
		$year=$arr['year'];
		$day=$realmon."/01/".$year;
		$dayts=strtotime($day);
		$newarr=getdate($dayts);
	} else {
		$arr=getdate();
		$mon=$arr['mon'];
		$realmon=($mon < 10 ? "0".$mon : $mon);
		$year=$arr['year'];
		$day=$realmon."/01/".$year;
		$dayts=strtotime($day);
		$newarr=getdate($dayts);
	}
	//price calendar
	$veryfirst = $newarr[0];
	$untilmonth = (int)$newarr['mon'] + intval(($numcalendars - 1));
	$addyears = $untilmonth > 12 ? intval(($untilmonth / 12)) : 0;
	$monthop = $addyears > 0 ? ($addyears * 12) : 0;
	$untilmonth = $untilmonth > 12 ? ($untilmonth - $monthop) : $untilmonth;
	$verylast = mktime(23, 59, 59, $untilmonth, date('t', mktime(0, 0, 0, $untilmonth, 1, ($newarr['year'] + $addyears))), ($newarr['year'] + $addyears));
	$priceseasons = array();
	$usepricecal = false;
	if (intval(VikBooking::getRoomParam('pricecal', $room['params'])) == 1) {
		$assumedays = floor((($verylast - $veryfirst) / (60 * 60 * 24)));
		$assumedays++;
		$assumedailycost = VikBooking::getRoomParam('defcalcost', $room['params']);
		$assumeprice = $assumedailycost * $assumedays;
		$parserates = array(0 => array('id' => -1, 'idroom' => $room['id'], 'days' => $assumedays, 'idprice' => -1, 'cost' => $assumeprice, 'attrdata' => ''));
		$priceseasons = VikBooking::applySeasonsRoom($parserates, $veryfirst, $verylast);
		$usepricecal = true;
		?>
		<p class="vbpricecalwarning"><?php echo JText::_('VBPRICECALWARNING'); ?></p>
		<?php
	}
	//
	$firstwday = (int)VikBooking::getFirstWeekDay();
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
	?>
	<div class="vbcalsblock">
	<?php
	$previousdayclass="";
	for ($jj = 1; $jj <= $numcalendars; $jj++) {
		$d_count = 0;
		$cal="";
		?>
		<div class="vbcaldivcont">
		<table class="<?php echo ($usepricecal === true ? 'vbcalprice' : 'vbcal'); ?>">
		<tr class="vbcaltrmonth"><td colspan="7" align="center"><strong><?php echo VikBooking::sayMonth($newarr['mon'])." ".$newarr['year']; ?></strong></td></tr>
		<tr class="vbcaldays">
		<?php
		for($i = 0; $i < 7; $i++) {
			$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
			echo '<td>'.$days_labels[$d_ind].'</td>';
		}
		?>
		</tr>
		<tr class="<?php echo $usepricecal === true ? 'vbcalnumdaysprice' : 'vbcalnumdays'; ?>">
		<?php
		for ($i=0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) {
			$cal.="<td align=\"center\">&nbsp;</td>";
		}
		while ($newarr['mon']==$mon) {
			if ($d_count > 6) {
				$d_count = 0;
				$cal.="</tr>\n<tr class=\"".($usepricecal === true ? 'vbcalnumdaysprice' : 'vbcalnumdays')."\">";
			}
			$dclass="vbtdfree";
			$dalt="";
			$bid="";
			$totfound=0;
			if ($check) {
				$ischeckinday = false;
				$ischeckoutday = false;
				foreach ($busy as $b){
					$tmpone=getdate($b['checkin']);
					$rit=($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
					$ritts=strtotime($rit);
					$tmptwo=getdate($b['checkout']);
					$con=($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
					$conts=strtotime($con);
					if ($newarr[0]>=$ritts && $newarr[0] == $conts) {
						$ischeckoutday = true;
					}
					//if ($newarr[0]>=$ritts && $newarr[0]<=$conts) {
					if ($newarr[0]>=$ritts && $newarr[0]<$conts) {
						$totfound++;
						if ($newarr[0] == $ritts) {
							$ischeckinday = true;
						}
					}
				}
				if ($totfound >= $room['units']) {
					$dclass="vbtdbusy";
					$push_disabled_in[] = '"'.date('Y-m-d', $newarr[0]).'"';
					if (!$ischeckinday || $previousdayclass == "vbtdbusy" || $previousdayclass == "vbtdbusy vbtdbusyforcheckin") {
						$push_disabled_out[] = '"'.date('Y-m-d', $newarr[0]).'"';
					}
					if ($ischeckinday && $showcheckinoutonly && !$usepricecal && $inonout_allowed && $previousdayclass != "vbtdbusy" && $previousdayclass != "vbtdbusy vbtdbusyforcheckin") {
						$dclass="vbtdbusy vbtdbusyforcheckin";
					} elseif ($ischeckinday && !$usepricecal && !$inonout_allowed && $previousdayclass != "vbtdbusy" && $previousdayclass != "vbtdbusy vbtdbusyforcheckin") {
						//check-out not allowed on a day where someone is already checking-in
						$dclass="vbtdbusy";
					}
				} elseif ($totfound > 0) {
					if ($showpartlyres) {
						$dclass="vbtdwarning";
					}
				} else {
					if ($ischeckoutday && !$usepricecal && $showcheckinoutonly && $inonout_allowed && !($room['units'] > 1)) {
						$dclass="vbtdbusy vbtdbusyforcheckout";
					} elseif ($ischeckoutday && !$usepricecal && !$inonout_allowed && !($room['units'] > 1)) {
						$dclass="vbtdbusy";
					}
				}
			}
			if (count($cal_closing_dates)) {
				foreach ($cal_closing_dates as $closed_interval) {
					if ($newarr[0] >= $closed_interval[0] && $newarr[0] <= $closed_interval[1]) {
						$dclass="vbtdbusy";
						break;
					}
				}
			}
			$previousdayclass=$dclass;
			$useday=($newarr['mday'] < 10 ? "0".$newarr['mday'] : $newarr['mday']);
			//price calendar
			$useday = $usepricecal === true ? '<div class="vbcalpricedaynum"><span>'.$useday.'</span></div>' : $useday;
			if ($usepricecal === true) {
				$todaycost = $assumedailycost;
				if (array_key_exists('affdayslist', $priceseasons[0]) && array_key_exists($newarr['wday'].'-'.$newarr['mday'].'-'.$newarr['mon'], $priceseasons[0]['affdayslist'])) {
					$todaycost = $priceseasons[0]['affdayslist'][$newarr['wday'].'-'.$newarr['mday'].'-'.$newarr['mon']];
				}
				$writecost = ($todaycost - intval($todaycost)) > 0.00 ? VikBooking::numberFormat($todaycost) : number_format($todaycost, 0);
				$useday .= '<div class="vbcalpricedaycost"><div><span class="vbo_currency">'.$currencysymb.'</span> <span class="vbo_price">'.$writecost.'</span></div></div>';
			}
			//
			if ($totfound == 1) {
				$cal.="<td align=\"center\" class=\"".$dclass."\" data-daydate=\"".date($df, $newarr[0])."\">".$useday."</td>\n";
			} elseif ($totfound > 1) {
				$cal.="<td align=\"center\" class=\"".$dclass."\" data-daydate=\"".date($df, $newarr[0])."\">".$useday."</td>\n";
			} else {
				$cal.="<td align=\"center\" class=\"".$dclass."\" data-daydate=\"".date($df, $newarr[0])."\">".$useday."</td>\n";
			}
			$next=$newarr['mday'] + 1;
			$dayts=mktime(0, 0, 0, ($newarr['mon'] < 10 ? "0".$newarr['mon'] : $newarr['mon']), ($next < 10 ? "0".$next : $next), $newarr['year']);
			$newarr=getdate($dayts);
			$d_count++;
		}
		
		for($i=$d_count; $i <= 6; $i++){
			$cal.="<td align=\"center\">&nbsp;</td>";
		}
		
		echo $cal;
		?>
		</tr>
		</table>
		</div>
		<?php
		if ($mon==12) {
			$mon=1;
			$year+=1;
			$dayts=mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
		} else {
			$mon+=1;
			$dayts=mktime(0, 0, 0, ($mon < 10 ? "0".$mon : $mon), 01, $year);
		}
		$newarr=getdate($dayts);
		
		if (($jj % 3)==0) {
			echo "";
		}
	}
	?>
	</div>
</div>
	<?php
}
?>

<div class="vbo-seldates-cont">
	<h4><?php echo JText::_('VBSELECTPDDATES'); ?></h4>

<?php
$paramshowpeople = intval(VikBooking::getRoomParam('maxminpeople', $room['params']));
if ($paramshowpeople > 0) {
	$maxadustr = ($room['fromadult'] != $room['toadult'] ? $room['fromadult'].' - '.$room['toadult'] : $room['toadult']);
	$maxchistr = ($room['fromchild'] != $room['tochild'] ? $room['fromchild'].' - '.$room['tochild'] : $room['tochild']);
	$maxtotstr = ($room['mintotpeople'] != $room['totpeople'] ? $room['mintotpeople'].' - '.$room['totpeople'] : $room['totpeople']);
	?>
	<div class="vbmaxminpeopleroom">
	<?php
	if ($paramshowpeople == 1) {
		?>
		<div class="vbmaxadultsdet"><span class="vbmaximgdet"><i class="fa fa-male"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMADULTS'); ?></span><span class="vbmaxnumberdet"><?php echo $maxadustr; ?></span></div>
		<?php
	} elseif ($paramshowpeople == 2) {
		?>
		<div class="vbmaxchildrendet"><span class="vbmaximgdet"><i class="fa fa-child"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMCHILDREN'); ?></span><span class="vbmaxnumberdet"><?php echo $maxchistr; ?></span></div>
		<?php
	} elseif ($paramshowpeople == 3) {
		?>
		<div class="vbmaxadultsdet"><span class="vbmaximgdet"><i class="fa fa-male"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMADULTS'); ?></span><span class="vbmaxnumberdet"><?php echo $maxadustr; ?></span></div>
		<div class="vbmaxtotdet"><span class="vbmaximgdet"><i class="fa fa-users"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBMAXTOTPEOPLE'); ?></span><span class="vbmaxnumberdet"><?php echo $maxtotstr; ?></span></div>
		<?php
	} elseif ($paramshowpeople == 4) {
		?>
		<div class="vbmaxchildrendet"><span class="vbmaximgdet"><i class="fa fa-child"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMCHILDREN'); ?></span><span class="vbmaxnumberdet"><?php echo $maxchistr; ?></span></div>
		<div class="vbmaxtotdet"><span class="vbmaximgdet"><i class="fa fa-users"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBMAXTOTPEOPLE'); ?></span><span class="vbmaxnumberdet"><?php echo $maxtotstr; ?></span></div>
		<?php
	} elseif ($paramshowpeople == 5) {
		?>
		<div class="vbmaxadultsdet"><span class="vbmaximgdet"><i class="fa fa-male"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMADULTS'); ?></span><span class="vbmaxnumberdet"><?php echo $maxadustr; ?></span></div>
		<div class="vbmaxchildrendet"><span class="vbmaximgdet"><i class="fa fa-child"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBFORMCHILDREN'); ?></span><span class="vbmaxnumberdet"><?php echo $maxchistr; ?></span></div>
		<div class="vbmaxtotdet"><span class="vbmaximgdet"><i class="fa fa-users"></i></span><span class="vbmaxlabeldet"><?php echo JText::_('VBMAXTOTPEOPLE'); ?></span><span class="vbmaxnumberdet"><?php echo $maxtotstr; ?></span></div>
		<?php
	}
	?>
	</div>
	<?php
}

if (VikBooking::allowBooking()) {
	//vikbooking 1.1
	$calendartype = VikBooking::calendarType();
	$document = JFactory::getDocument();
	//load jQuery UI
	if ($calendartype == "jqueryui") {
		$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
		//load jQuery UI
		JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
	}
	//
	//vikbooking 1.2
	$restrictions = VikBooking::loadRestrictions(true, array($room['id']));
	//
	//vikbooking 1.5 channel manager
	$ch_start_date = VikRequest::getString('start_date', '', 'request');
	$ch_end_date = VikRequest::getString('end_date', '', 'request');
	$ch_num_adults = VikRequest::getInt('num_adults', '', 'request');
	$ch_num_children = VikRequest::getInt('num_children', '', 'request');
	$arr_adults = VikRequest::getVar('adults', array());
	$ch_num_adults = empty($ch_num_adults) && !empty($arr_adults[0]) ? $arr_adults[0] : $ch_num_adults;
	$arr_children = VikRequest::getVar('children', array());
	$ch_num_children = empty($ch_num_children) && !empty($arr_children[0]) ? $arr_children[0] : $ch_num_children;
	//
	$promo_checkin = VikRequest::getString('checkin', '', 'request');
	$ispromo = count($promo_season) > 0 ? $promo_season['id'] : 0;
	
	$selform = "<div class=\"vbdivsearch\"><form action=\"".JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''))."\" method=\"get\"><div class=\"vb-search-inner\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikbooking\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"roomdetail\" value=\"".$room['id']."\"/>\n";
	
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
	if ($calendartype == "jqueryui") {
		if ($vbdateformat == "%d/%m/%Y") {
			$juidf = 'dd/mm/yy';
		} elseif ($vbdateformat == "%m/%d/%Y") {
			$juidf = 'mm/dd/yy';
		} else {
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
		if ($totrestrictions > 0) {
			foreach($restrictions as $rmonth => $restr) {
				if ($rmonth != 'range') {
					if (strlen($restr['wday']) > 0) {
						$wdaysrestrictions[] = "'".($rmonth - 1)."': '".$restr['wday']."'";
						$wdaysrestrictionsmonths[] = $rmonth;
						if (strlen($restr['wdaytwo']) > 0) {
							$wdaystworestrictions[] = "'".($rmonth - 1)."': '".$restr['wdaytwo']."'";
							$monthscomborestr[($rmonth - 1)] = VikBooking::parseJsDrangeWdayCombo($restr);
						}
					} elseif (!empty($restr['ctad']) || !empty($restr['ctdd'])) {
						if (!empty($restr['ctad'])) {
							$ctarestrictionsmonths[($rmonth - 1)] = explode(',', $restr['ctad']);
						}
						if (!empty($restr['ctdd'])) {
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
				} else {
					foreach ($restr as $kr => $drestr) {
						if (strlen($drestr['wday']) > 0) {
							$wdaysrestrictionsrange[$kr][0] = date('Y-m-d', $drestr['dfrom']);
							$wdaysrestrictionsrange[$kr][1] = date('Y-m-d', $drestr['dto']);
							$wdaysrestrictionsrange[$kr][2] = $drestr['wday'];
							$wdaysrestrictionsrange[$kr][3] = $drestr['multiplyminlos'];
							$wdaysrestrictionsrange[$kr][4] = strlen($drestr['wdaytwo']) > 0 ? $drestr['wdaytwo'] : -1;
							$wdaysrestrictionsrange[$kr][5] = VikBooking::parseJsDrangeWdayCombo($drestr);
						} elseif (!empty($drestr['ctad']) || !empty($drestr['ctdd'])) {
							$ctfrom = date('Y-m-d', $drestr['dfrom']);
							$ctto = date('Y-m-d', $drestr['dto']);
							if (!empty($drestr['ctad'])) {
								$ctarestrictionsrange[$kr][0] = $ctfrom;
								$ctarestrictionsrange[$kr][1] = $ctto;
								$ctarestrictionsrange[$kr][2] = explode(',', $drestr['ctad']);
							}
							if (!empty($drestr['ctdd'])) {
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
	if (vbFullObject(vbcombowdays)) {
		var vbtosort = new Array();
		for(var vbi in vbcombowdays) {
			if (vbcombowdays.hasOwnProperty(vbi)) {
				var vbusedate = darrive;
				vbtosort[vbi] = vbusedate.setDate(vbusedate.getDate() + (vbcombowdays[vbi] - 1 - vbusedate.getDay() + 7) % 7 + 1);
			}
		}
		vbtosort.sort(function(da, db) {
			return da > db ? 1 : -1;
		});
		for(var vbnext in vbtosort) {
			if (vbtosort.hasOwnProperty(vbnext)) {
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
	if (vbFullObject(vbrestrminlosrangejn)) {
		for (var rk in vbrestrminlosrangejn) {
			if (vbrestrminlosrangejn.hasOwnProperty(rk)) {
				var minldrangeinit = vbGetDateObject(vbrestrminlosrangejn[rk][0]);
				if (nowcheckindate >= minldrangeinit) {
					var minldrangeend = vbGetDateObject(vbrestrminlosrangejn[rk][1]);
					if (nowcheckindate <= minldrangeend) {
						minlos = parseInt(vbrestrminlosrangejn[rk][2]);
						if (vbFullObject(vbrestrmaxlosrangejn)) {
							if (rk in vbrestrmaxlosrangejn) {
								maxlosrange = parseInt(vbrestrmaxlosrangejn[rk]);
							}
						}
						if (rk in vbrestrwdaysrangejn && nowd in vbrestrwdaysrangejn[rk][5]) {
							vbcombowdays = vbrestrwdaysrangejn[rk][5][nowd];
						}
					}
				}
			}
		}
	}
	var nowm = nowcheckin.getMonth();
	if (vbFullObject(vbrestrmonthscombojn) && vbrestrmonthscombojn.hasOwnProperty(nowm)) {
		if (nowd in vbrestrmonthscombojn[nowm]) {
			vbcombowdays = vbrestrmonthscombojn[nowm][nowd];
		}
	}
	if (jQuery.inArray((nowm + 1), vbrestrmonths) != -1) {
		minlos = parseInt(vbrestrminlos[nowm]);
	}
	nowcheckindate.setDate(nowcheckindate.getDate() + minlos);
	jQuery('#checkoutdate').datepicker( 'option', 'minDate', nowcheckindate );
	if (maxlosrange > 0) {
		var diffmaxminlos = maxlosrange - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkoutdate').datepicker( 'option', 'maxDate', maxcheckoutdate );
	}
	if (nowm in vbrestrmaxlos) {
		var diffmaxminlos = parseInt(vbrestrmaxlos[nowm]) - minlos;
		var maxcheckoutdate = new Date(nowcheckindate.getTime());
		maxcheckoutdate.setDate(maxcheckoutdate.getDate() + diffmaxminlos);
		jQuery('#checkoutdate').datepicker( 'option', 'maxDate', maxcheckoutdate );
	}
	if (!vbFullObject(vbcombowdays)) {
		jQuery('#checkoutdate').datepicker( 'setDate', nowcheckindate );
	} else {
		vbRefreshCheckout(nowcheckin);
	}
}";
			
			if (count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0) {
				//VikBooking 1.5
				$dfull_in = '';
				$dfull_out = '';
				if (count($push_disabled_in) > 0) {
					$dfull_in = "
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vbfulldays_in) != -1) {
		return [false];
	}
	";
				}
				if (count($push_disabled_out) > 0) {
					$dfull_out = "
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vbfulldays_out) != -1) {
		return [false];
	}
	// exclude days after a fully booked day, because a date selection cannot contain a fully booked day in between.
	var exclude_after = false;
	var last_fully_booked = null;
	var nowcheckin = jQuery('#checkindate').datepicker('getDate');
	if (nowcheckin && vbfulldays_out.length) {
		var nowcheckindate = new Date(nowcheckin.getTime());
		nowcheckindate.setHours(0);
		nowcheckindate.setMinutes(0);
		nowcheckindate.setSeconds(0);
		nowcheckindate.setMilliseconds(0);
		for (var i in vbfulldays_out) {
			var nowfullday = new Date(vbfulldays_out[i]);
			nowfullday.setHours(0);
			nowfullday.setMinutes(0);
			nowfullday.setSeconds(0);
			nowfullday.setMilliseconds(0);
			exclude_after = (nowcheckindate <= nowfullday);
			if (exclude_after) {
				// selected check-in date is before a fully booked day
				last_fully_booked = nowfullday;
				break;
			}
		}
	}
	if (exclude_after) {
		date.setHours(0);
		date.setMinutes(0);
		date.setSeconds(0);
		date.setMilliseconds(0);
		if (date > last_fully_booked) {
			// current day for display is after a fully booked day, with a selected check-in day before a fully booked day. Disable it.
			return [false];
		}
	}
	//
	";
				}
				//
				$resdecl .= "
var vbrestrwdays = {".implode(", ", $wdaysrestrictions)."};
var vbrestrwdaystwo = {".implode(", ", $wdaystworestrictions)."};
".(count($push_disabled_in) > 0 ? "var vbfulldays_in = [".implode(", ", $push_disabled_in)."];" : "")."
".(count($push_disabled_out) > 0 ? "var vbfulldays_out = [".implode(", ", $push_disabled_out)."];" : "")."
function vbIsDayDisabled(date) {
	if (!vbIsDayOpen(date) || !vboValidateCta(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if (vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrwdaysrangejn[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrwdaysrangejn[rk][1]);
					if (date <= wdrangeend) {
						if (wd != vbrestrwdaysrangejn[rk][2]) {
							if (vbrestrwdaysrangejn[rk][4] == -1 || wd != vbrestrwdaysrangejn[rk][4]) {
								return [false];
							}
						}
					}
				}
			}
		}
	}
".(count($push_disabled_in) > 0 ? $dfull_in : '')."
	if (vbFullObject(vbrestrwdays)) {
		if (jQuery.inArray((m+1), vbrestrmonthswdays) == -1) {
			return [true];
		}
		if (wd == vbrestrwdays[m]) {
			return [true];
		}
		if (vbFullObject(vbrestrwdaystwo)) {
			if (wd == vbrestrwdaystwo[m]) {
				return [true];
			}
		}
		return [false];
	}
	return [true];
}
function vbIsDayDisabledCheckout(date) {
	if (!vbIsDayOpen(date) || !vboValidateCtd(date)) {
		return [false];
	}
	var m = date.getMonth(), wd = date.getDay();
".(count($push_disabled_out) > 0 ? $dfull_out : '')."
	if (vbFullObject(vbcombowdays)) {
		if (jQuery.inArray(wd, vbcombowdays) != -1) {
			return [true];
		} else {
			return [false];
		}
	}
	if (vbFullObject(vbrestrwdaysrangejn)) {
		for (var rk in vbrestrwdaysrangejn) {
			if (vbrestrwdaysrangejn.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrwdaysrangejn[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrwdaysrangejn[rk][1]);
					if (date <= wdrangeend) {
						if (wd != vbrestrwdaysrangejn[rk][2] && vbrestrwdaysrangejn[rk][3] == 1) {
							return [false];
						}
					}
				}
			}
		}
	}
	if (vbFullObject(vbrestrwdays)) {
		if (jQuery.inArray((m+1), vbrestrmonthswdays) == -1 || jQuery.inArray((m+1), vbrestrmultiplyminlos) != -1) {
			return [true];
		}
		if (wd == vbrestrwdays[m]) {
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
		//VikBooking 1.5
		if (count($push_disabled_in) > 0) {
			$full_in_decl = "
var vbfulldays_in = [".implode(", ", $push_disabled_in)."];
function vbIsDayFull(date) {
	if (!vbIsDayOpen(date) || !vboValidateCta(date)) {
		return [false];
	}
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vbfulldays_in) == -1) {
		return [true];
	}
	return [false];
}";
			$document->addScriptDeclaration($full_in_decl);
		}
		if (count($push_disabled_out) > 0) {
			$full_out_decl = "
var vbfulldays_out = [".implode(", ", $push_disabled_out)."];
function vbIsDayFullOut(date) {
	if (!vbIsDayOpen(date) || !vboValidateCtd(date)) {
		return [false];
	}
	var actd = jQuery.datepicker.formatDate('yy-mm-dd', date);
	if (jQuery.inArray(actd, vbfulldays_out) == -1) {
		// exclude days after a fully booked day, because a date selection cannot contain a fully booked day in between.
		var exclude_after = false;
		var last_fully_booked = null;
		var nowcheckin = jQuery('#checkindate').datepicker('getDate');
		if (nowcheckin && vbfulldays_out.length) {
			var nowcheckindate = new Date(nowcheckin.getTime());
			nowcheckindate.setHours(0);
			nowcheckindate.setMinutes(0);
			nowcheckindate.setSeconds(0);
			nowcheckindate.setMilliseconds(0);
			for (var i in vbfulldays_out) {
				var nowfullday = new Date(vbfulldays_out[i]);
				nowfullday.setHours(0);
				nowfullday.setMinutes(0);
				nowfullday.setSeconds(0);
				nowfullday.setMilliseconds(0);
				exclude_after = (nowcheckindate <= nowfullday);
				if (exclude_after) {
					// selected check-in date is before a fully booked day
					last_fully_booked = nowfullday;
					break;
				}
			}
		}
		if (exclude_after) {
			date.setHours(0);
			date.setMinutes(0);
			date.setSeconds(0);
			date.setMilliseconds(0);
			if (date > last_fully_booked) {
				// current day for display is after a fully booked day, with a selected check-in day before a fully booked day. Disable it.
				return [false];
			}
		}
		//
		return [true];
	}
	return [false];
}";
			$document->addScriptDeclaration($full_out_decl);
		}
		//
		$sdecl = "
var vbclosingdates = jQuery.parseJSON('".json_encode($closing_dates)."');
function vbCheckClosingDates(date) {
	if (!vbIsDayOpen(date)) {
		return [false];
	}
	return [true];
}
function vbIsDayOpen(date) {
	if (vbFullObject(vbclosingdates)) {
		for (var cd in vbclosingdates) {
			if (vbclosingdates.hasOwnProperty(cd)) {
				var cdfrom = vbGetDateObject(vbclosingdates[cd][0]);
				var cdto = vbGetDateObject(vbclosingdates[cd][1]);
				if (date >= cdfrom && date <= cdto) {
					return false;
				}
			}
		}
	}
	return true;
}
function vboCheckClosingDatesIn(date) {
	var isdayopen = vbIsDayOpen(date) && vboValidateCta(date);
	return [isdayopen];
}
function vboCheckClosingDatesOut(date) {
	var isdayopen = vbIsDayOpen(date) && vboValidateCtd(date);
	return [isdayopen];
}
function vboValidateCta(date) {
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject(vbrestrctarange)) {
		for (var rk in vbrestrctarange) {
			if (vbrestrctarange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrctarange[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrctarange[rk][1]);
					if (date <= wdrangeend) {
						if (jQuery.inArray('-'+wd+'-', vbrestrctarange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if (vbFullObject(vbrestrcta)) {
		if (vbrestrcta.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrcta[m]) >= 0) {
			return false;
		}
	}
	return true;
}
function vboValidateCtd(date) {
	var m = date.getMonth(), wd = date.getDay();
	if (vbFullObject(vbrestrctdrange)) {
		for (var rk in vbrestrctdrange) {
			if (vbrestrctdrange.hasOwnProperty(rk)) {
				var wdrangeinit = vbGetDateObject(vbrestrctdrange[rk][0]);
				if (date >= wdrangeinit) {
					var wdrangeend = vbGetDateObject(vbrestrctdrange[rk][1]);
					if (date <= wdrangeend) {
						if (jQuery.inArray('-'+wd+'-', vbrestrctdrange[rk][2]) >= 0) {
							return false;
						}
					}
				}
			}
		}
	}
	if (vbFullObject(vbrestrctd)) {
		if (vbrestrctd.hasOwnProperty(m) && jQuery.inArray('-'+wd+'-', vbrestrctd[m]) >= 0) {
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
		numberOfMonths: ".($is_mobile ? '1' : '2').",".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vbIsDayDisabled,\n" : (count($push_disabled_in) > 0 ? "\nbeforeShowDay: vbIsDayFull,\n" : "\nbeforeShowDay: vboCheckClosingDatesIn,\n"))."
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
		numberOfMonths: ".($is_mobile ? '1' : '2').",".(count($wdaysrestrictions) > 0 || count($wdaysrestrictionsrange) > 0 ? "\nbeforeShowDay: vbIsDayDisabledCheckout,\n" : (count($push_disabled_out) > 0 ? "\nbeforeShowDay: vbIsDayFullOut,\n" : "\nbeforeShowDay: vboCheckClosingDatesOut,\n"))."
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
		if (jdp.length) {
			jdp.focus();
		}
	});
});";
		$document->addScriptDeclaration($sdecl);
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkin\"><label for=\"checkindate\">" . JText::_('VBPICKUPROOM') . "</label><div class=\"input-group\"><input type=\"text\" name=\"checkindate\" id=\"checkindate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vbo-caltrigger\"></i></div><input type=\"hidden\" name=\"checkinh\" value=\"".$hcheckin."\"/><input type=\"hidden\" name=\"checkinm\" value=\"".$mcheckin."\"/></div>\n";
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkout\"><label for=\"checkoutdate\">" . JText::_('VBRETURNROOM') . "</label><div class=\"input-group\"><input type=\"text\" name=\"checkoutdate\" id=\"checkoutdate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><i class=\"fa fa-calendar vbo-caltrigger\"></i></div><input type=\"hidden\" name=\"checkouth\" value=\"".$hcheckout."\"/><input type=\"hidden\" name=\"checkoutm\" value=\"".$mcheckout."\"/></div>\n";
	} else {
		//default Joomla Calendar
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkin\"><label for=\"checkindate\">" . JText::_('VBPICKUPROOM') . "</label><div class=\"input-group\">" . $vbo_app->getCalendar('', 'checkindate', 'checkindate', $vbdateformat, array ('class' => '','size' => '10','maxlength' => '19'));
		$selform .= "<input type=\"hidden\" name=\"checkinh\" value=\"".$hcheckin."\"/><input type=\"hidden\" name=\"checkinm\" value=\"".$mcheckin."\"/></div></div>\n";
		$selform .= "<div class=\"vbo-search-inpblock vbo-search-inpblock-checkout\"><label for=\"checkoutdate\">" . JText::_('VBRETURNROOM') . "</label><div class=\"input-group\">" . $vbo_app->getCalendar('', 'checkoutdate', 'checkoutdate', $vbdateformat, array ('class' => '','size' => '10','maxlength' => '19')); 
		$selform .= "<input type=\"hidden\" name=\"checkouth\" value=\"".$hcheckout."\"/><input type=\"hidden\" name=\"checkoutm\" value=\"".$mcheckout."\"/></div></div>\n";
	}
	//
	//rooms, adults, children
	$showchildren = VikBooking::showChildrenFront();
	//max number of rooms
	$multi_units = (int)VikBooking::getRoomParam('multi_units', $room['params']);
	if ($multi_units === 1 && $room['units'] > 1) {
		$maxsearchnumrooms = (int)VikBooking::getSearchNumRooms();
		$maxsearchnumrooms = $room['units'] > $maxsearchnumrooms ? $maxsearchnumrooms : $room['units'];
		$roomsel = "<label for=\"vbo-detroomsnum\">".JText::_('VBFORMROOMSN')."</label><select id=\"vbo-detroomsnum\" name=\"roomsnum\" onchange=\"vbSetRoomsAdults(this.value);\">\n";
		for($r = 1; $r <= $maxsearchnumrooms; $r++) {
			$roomsel .= "<option value=\"".$r."\">".$r."</option>\n";
		}
		$roomsel .= "</select>\n";
	} else {
		$roomsel = "<input type=\"hidden\" name=\"roomsnum\" value=\"1\">\n";
	}
	//
	//max number of adults per room
	$adultsel = "<select name=\"adults[]\">";
	for($a = $room['fromadult']; $a <= $room['toadult']; $a++) {
		$adultsel .= "<option value=\"".$a."\"".((!empty($ch_num_adults) && $ch_num_adults == $a) || (empty($ch_num_adults) && $a == $room['toadult']) ? " selected=\"selected\"" : "").">".$a."</option>";
	}
	$adultsel .= "</select>";
	//
	//max number of children per room
	$childrensel = "<select name=\"children[]\">";
	for($c = $room['fromchild']; $c <= $room['tochild']; $c++) {
		$childrensel .= "<option value=\"".$c."\"".(!empty($ch_num_children) && $ch_num_children == $c ? " selected=\"selected\"" : "").">".$c."</option>";
	}
	$childrensel .= "</select>";
	//

	$selform .= "<div class=\"vbo-search-num-racblock\">\n";
	$selform .= "	<div class=\"vbo-search-num-rooms\">".$roomsel."</div>\n";
	$selform .= "	<div class=\"vbo-search-num-aduchild-block\" id=\"vbo-search-num-aduchild-block\">\n";
	$selform .= "		<div class=\"vbo-search-num-aduchild-entry\"><span class=\"vbo-search-roomnum\">".JText::_('VBFORMNUMROOM')." 1</span>\n";
	$selform .= "			<div class=\"vbo-search-num-adults-entry\"><label class=\"vbo-search-num-adults-entry-label\">".JText::_('VBFORMADULTS')."</label><span class=\"vbo-search-num-adults-entry-inp\">".$adultsel."</span></div>\n";
	if ($showchildren) {
		$selform .= "		<div class=\"vbo-search-num-children-entry\"><label class=\"vbo-search-num-children-entry-label\">".JText::_('VBFORMCHILDREN')."</label><span class=\"vbo-search-num-children-entry-inp\">".$childrensel."</span></div>\n";
	}
	$selform .= "		</div>\n";
	$selform .= "	</div>\n";
	//the tag <div id=\"vbjstotnights\"></div> will be used by javascript to calculate the nights
	$selform .= "	<div id=\"vbjstotnights\"></div>\n";
	$selform .= "</div>\n";
	$selform .= "<div class=\"vbo-search-submit\"><input type=\"submit\" name=\"search\" value=\"" . JText::_('VBBOOKTHISROOM') . "\" class=\"btn vbdetbooksubmit\"/></div>\n";
	$selform .= "</div>\n";
	$selform .= (!empty ($pitemid) ? "<input type=\"hidden\" name=\"Itemid\" value=\"" . $pitemid . "\"/>" : "") . "</form></div>";
	
	?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function vbCalcNights() {
		var vbcheckin = document.getElementById('checkindate').value;
		var vbcheckout = document.getElementById('checkoutdate').value;
		if (vbcheckin.length > 0 && vbcheckout.length > 0) {
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
		} elseif ($vbdateformat == "%m/%d/%Y") {
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
		} else {
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
			if (vboutts > vbints) {
				//var vbnights = Math.ceil((vboutts - vbints) / (vbdivider));
				var utc1 = Date.UTC(vbcheckind.getFullYear(), vbcheckind.getMonth(), vbcheckind.getDate());
				var utc2 = Date.UTC(vbcheckoutd.getFullYear(), vbcheckoutd.getMonth(), vbcheckoutd.getDate());
				var vbnights = Math.ceil((utc2 - utc1) / vbdivider);
				if (vbnights > 0) {
					document.getElementById('vbjstotnights').innerHTML = '<?php echo addslashes(JText::_('VBJSTOTNIGHTS')); ?>: '+vbnights;
				} else {
					document.getElementById('vbjstotnights').innerHTML = '';
				}
			} else {
				document.getElementById('vbjstotnights').innerHTML = '';
			}
		} else {
			document.getElementById('vbjstotnights').innerHTML = '';
		}
	}
	function vbAddElement() {
		var ni = document.getElementById('vbo-search-num-aduchild-block');
		var numi = document.getElementById('vbroomdethelper');
		var num = (document.getElementById('vbroomdethelper').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'vb'+num+'detracont';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<div class=\'vbo-search-num-aduchild-entry\'><span class=\'vbo-search-roomnum\'><?php echo addslashes(JText::_('VBFORMNUMROOM')); ?> '+ num +'</span><div class=\'vbo-search-num-adults-entry\'><label class=\'vbo-search-num-adults-entry-label\'><?php echo addslashes(JText::_('VBFORMADULTS')); ?></label><span class=\'vbo-search-num-adults-entry-inp\'><?php echo addslashes(str_replace('"', "'", $adultsel)); ?></span></div><?php if ($showchildren): ?><div class=\'vbo-search-num-children-entry\'><label class=\'vbo-search-num-children-entry-label\'><?php echo addslashes(JText::_('VBFORMCHILDREN')); ?></label><span class=\'vbo-search-num-adults-entry-inp\'><?php echo addslashes(str_replace('"', "'", $childrensel)); ?></span></div><?php endif; ?></div>';
		ni.appendChild(newdiv);
	}
	function vbSetRoomsAdults(totrooms) {
		var actrooms = parseInt(document.getElementById('vbroomdethelper').value);
		var torooms = parseInt(totrooms);
		var difrooms;
		if (torooms > actrooms) {
			difrooms = torooms - actrooms;
			for(var ir=1; ir<=difrooms; ir++) {
				vbAddElement();
			}
		}
		if (torooms < actrooms) {
			for(var ir=actrooms; ir>torooms; ir--) {
				if (ir > 1) {
					var rmra = document.getElementById('vb' + ir + 'detracont');
					rmra.parentNode.removeChild(rmra);
				}
			}
			document.getElementById('vbroomdethelper').value = torooms;
		}
	}
	<?php
	$scroll_booking = false;
	//vikbooking 1.5 channel manager
	if (!empty($ch_start_date) && !empty($ch_end_date)) {
		$ch_ts_startdate = strtotime($ch_start_date);
		$ch_ts_enddate = strtotime($ch_end_date);
		if ($ch_ts_startdate > time() && $ch_ts_startdate < $ch_ts_enddate) {
			?>
	jQuery(document).ready(function(){
		document.getElementById('checkindate').value = '<?php echo date($df, $ch_ts_startdate); ?>';
		document.getElementById('checkoutdate').value = '<?php echo date($df, $ch_ts_enddate); ?>';
		vbCalcNights();
	});
			<?php
		}
	} elseif (!empty($promo_checkin) && intval($promo_checkin) > 0 && $calendartype == "jqueryui") {
		$scroll_booking = $promo_checkin > mktime(0, 0, 0, date("n"), date("j"), date("Y")) ? true : $scroll_booking;
		$min_nights = 1;
		if (count($promo_season) > 0 && $scroll_booking) {
			if ($promo_season['promominlos'] > 1) {
				$min_nights = $promo_season['promominlos'];
				$promo_end_ts = $promo_checkin + ($min_nights * 86400);
				if ((bool)date('I', $promo_checkin) !== (bool)date('I', $promo_end_ts)) {
					if ((bool)$promo_checkin === true) {
						$promo_end_ts += 3600;
					} else {
						$promo_end_ts -= 3600;
					}
				}
			}
		}
		?>
	jQuery(document).ready(function(){
		jQuery("#checkin-hidden").val("<?php echo $promo_checkin; ?>");
		jQuery("#checkindate").datepicker("setDate", new Date(<?php echo date('Y', $promo_checkin); ?>, <?php echo ((int)date('n', $promo_checkin) - 1); ?>, <?php echo date('j', $promo_checkin); ?>));
		<?php
		if ($min_nights > 1) {
			?>
		jQuery("#promo-hidden").val("<?php echo $promo_season['id']; ?>");
		jQuery("#checkoutdate").datepicker("option", "minDate", new Date(<?php echo date('Y', $promo_end_ts); ?>, <?php echo ((int)date('n', $promo_end_ts) - 1); ?>, <?php echo date('j', $promo_end_ts); ?>));
			<?php
		}
		?>
		jQuery(".ui-datepicker-current-day").click();
	});
		<?php
	}
	if ($ispromo > 0 || $scroll_booking === true) {
		?>
	jQuery(document).ready(function(){
		jQuery('html,body').animate({ scrollTop: (jQuery("#vbo-bookingpart-init").offset().top - 5) }, { duration: 'slow' });
	});
		<?php
	}
	//
	?>
	jQuery(document).ready(function(){
		jQuery("td.vbtdfree, td.vbtdwarning, td.vbtdbusyforcheckout").click(function() {
			if (jQuery("#checkindate").length) {
				var tdday = jQuery(this).attr("data-daydate");
				jQuery("#checkindate").datepicker("setDate", tdday);
				jQuery('html,body').animate({
					scrollTop: (jQuery("#vbo-bookingpart-init").offset().top - 5)
				}, 600, function() {
					jQuery("#checkindate").focus();
				});
			}
		});
	});
	/* ]]> */
	</script>
	<input type="hidden" id="vbroomdethelper" value="1"/>
	<div class="vbo-intro-main"><?php echo VikBooking::getIntroMain(); ?></div>
	<div class="vbo-room-details-booking-wrapper">
	<?php
	echo $selform;
	if (count($promo_season) > 0 && !empty($promo_season['promotxt'])) {
		?>
		<div class="vbo-room-details-booking-promo">
		<?php echo $promo_season['promotxt']; ?>
		</div>
		<?php
	}
	?>
	</div>
	<div class="vbo-closing-main"><?php echo VikBooking::getClosingMain(); ?></div>
	<?php
} else {
	echo VikBooking::getDisabledBookingMsg();
}
?>
</div>