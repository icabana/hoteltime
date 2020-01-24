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

$tars=$this->tars;
$rooms=$this->rooms;
$roomsnum=$this->roomsnum;
$arrpeople=$this->arrpeople;
$checkin=$this->checkin;
$checkout=$this->checkout;
$days=$this->days;
$pkg=$this->pkg;
$vbo_tn=$this->vbo_tn;

$vat_included = VikBooking::ivaInclusa();
$tax_summary = !$vat_included && VikBooking::showTaxOnSummaryOnly() ? true : false;
$is_package = false;
//packages may skip some room options
$skip_all_opt = false;
$only_forced_opt = false;
if (is_array($pkg) && count($pkg) > 0) {
	$skip_all_opt = $pkg['showoptions'] == 3 ? true : $skip_all_opt;
	$only_forced_opt = $pkg['showoptions'] == 2 ? true : $only_forced_opt;
	$is_package = true;
}
//
$deftar_costs = array();
$deftar_basecosts = array();
$children_agepcent = false;
$pitemid = VikRequest::getInt('Itemid', '', 'request');

$infocheckin = getdate($checkin);
$infocheckout = getdate($checkout);

$nowdf = VikBooking::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$checkinforlink = date($df, $checkin);
$checkoutforlink = date($df, $checkout);

$discl = VikBooking::getDisclaimer();
$currencysymb = VikBooking::getCurrencySymb();
$showchildren = VikBooking::showChildrenFront();
$totadults = 0;
$totchildren = 0;

$peopleforlink = '';
foreach($arrpeople as $aduchild) {
	$totadults += $aduchild['adults'];
	$totchildren += $aduchild['children'];
	$peopleforlink .= '&adults[]='.$aduchild['adults'].'&children[]='.$aduchild['children'];
}

$document = JFactory::getDocument();
//load jQuery
if(VikBooking::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}

foreach($rooms as $num => $r) {
	if(strlen($r['moreimgs']) > 0) {
		$document->addStyleSheet(VBO_SITE_URI.'resources/VikFXThumbSlide.css');
		JHtml::_('script', VBO_SITE_URI.'resources/VikFXThumbSlide.js', false, true, false, false);
		break;
	}
}

foreach($rooms as $num => $r) {
	if(strlen($r['moreimgs']) > 0) {
		$moreimages = explode(';;', $r['moreimgs']);
		$vikfsimgs = array();
		foreach($moreimages as $mimg) {
			if (!empty($mimg)) {
				$vikfsimgs[] = '{image : "'.VBO_SITE_URI.'resources/uploads/big_'.$mimg.'", alt : "'.substr($mimg, 0, strpos($mimg, '.')).'"}';
			}
		}
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

if (count($this->mod_booking)) {
	//booking modification
	?>
<div class="vbo-booking-modification-helper">
	<div class="vbo-booking-modification-helper-inner">
		<div class="vbo-booking-modification-msg">
			<span><?php echo JText::_('VBOMODBOOKHELPSHOWPRC'); ?></span>
		</div>
		<div class="vbo-booking-modification-canc">
			<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=cancelmodification&sid='.$this->mod_booking['sid'].'&id='.$this->mod_booking['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>">
				<i class="fa fa-times-circle"></i>
				<?php echo JText::_('VBOMODBOOKCANCMOD'); ?>
			</a>
		</div>
	</div>
</div>
	<?php
}

?>

<div class="vbstepsbarcont">
	<ol class="vbo-stepbar" data-vbosteps="4">
	<?php
	if($is_package === true) {
		?>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=packageslist'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBOPKGLINK'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=packagedetails&pkgid='.$pkg['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPROOMSELECTION'); ?></a></li>
		<?php
	} else {
		?>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&checkin='.$checkin.'&checkout='.$checkout.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBSTEPDATES'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=search&checkindate='.urlencode($checkinforlink).'&checkoutdate='.urlencode($checkoutforlink).'&roomsnum='.$roomsnum.$peopleforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPROOMSELECTION'); ?></a></li>
		<?php
	}
	?>
		<li class="vbo-step vbo-step-current"><span><?php echo JText::_('VBSTEPOPTIONS'); ?></span></li>
		<li class="vbo-step vbo-step-next"><span><?php echo JText::_('VBSTEPCONFIRM'); ?></span></li>
	</ol>
</div>

<div class="vbo-results-head vbo-results-head-showprc">
	<div class="vbo-results-nights">
		<i class="fa fa-calendar"></i>
		<div class="vbo-results-head-det">
			<span class="vbo-results-head-det-lbl"><?php echo JText::_('VBSEARCHRESNIGHTS'); ?></span>
			<span class="vbo-results-head-det-val"><?php echo $days; ?></span>
		</div>
	</div>
<?php
if ($roomsnum > 1) {
	?>
	<div class="vbo-results-numrooms">
		<i class="fa fa-bed"></i>
		<div class="vbo-results-head-det">
			<span class="vbo-results-head-det-lbl"><?php echo JText::_('VBSEARCHRESROOMS'); ?></span>
			<span class="vbo-results-head-det-val"><?php echo $roomsnum; ?></span>
		</div>
	</div>
	<?php
}
?>
	<div class="vbo-results-numadults">
		<i class="fa fa-male"></i>
		<div class="vbo-results-head-det">
			<span class="vbo-results-head-det-lbl"><?php echo JText::_('VBSEARCHRESADULTS'); ?></span>
			<span class="vbo-results-head-det-val"><?php echo $totadults; ?></span>
		</div>
	</div>
<?php
if ($showchildren && $totchildren > 0) {
	?>
	<div class="vbo-results-numchildren">
		<i class="fa fa-child"></i>
		<div class="vbo-results-head-det">
			<span class="vbo-results-head-det-lbl"><?php echo JText::_('VBSEARCHRESCHILDREN'); ?></span>
			<span class="vbo-results-head-det-val"><?php echo $totchildren; ?></span>
		</div>
	</div>
	<?php
}
?>
	<div class="vbcheckinroom">
		<i class="fa fa-sign-in"></i>
		<div class="vbo-results-head-det">
			<span class="vbcheckinroom-lbl"><?php echo JText::_('VBCHECKINONTHE'); ?></span>
			<span class="vbcheckinroom-dt"><?php echo JText::sprintf('VBCHECKINOUTOF', VikBooking::sayDayMonth($infocheckin['mday']), VikBooking::sayMonth($infocheckin['mon'])); ?></span>
		</div>
	</div>
	<div class="vbcheckoutroom">
		<i class="fa fa-sign-out"></i>
		<div class="vbo-results-head-det">
			<span class="vbcheckoutroom-lbl"><?php echo JText::_('VBCHECKOUTONTHE'); ?></span>
			<span class="vbcheckoutroom-dt"><?php echo JText::sprintf('VBCHECKINOUTOF', VikBooking::sayDayMonth($infocheckout['mday']), VikBooking::sayMonth($infocheckout['mon'])); ?></span>
		</div>
	</div>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post">
	<input type="hidden" name="option" value="com_vikbooking"/>
	<input type="hidden" name="task" value="oconfirm"/>
<div class="vbo-showprc-wrapper">
<?php
foreach($rooms as $num => $r) {
	$optionals = "";
	$carats = VikBooking::getRoomCaratOriz($r['idcarat'], $vbo_tn);
	if (!empty ($r['idopt'])) {
		$optionals = VikBooking::getRoomOptionals($r['idopt'], $vbo_tn);
	}
	?>
	<div class="room_container">
		<div class="vbo-showprc-room-head">
		<?php
		if ($roomsnum > 1) {
			?>
			<div class="vbshowprcroomnum"><?php echo JText::_('VBSEARCHROOMNUM'); ?> <?php echo $num; ?></div>
			<?php
		}
		?>
			<div class="vbo-showprc-staydetails">
				<div class="vbo-showprc-staydetails-party">
					<i class="fa fa-users"></i> 
					<?php echo $arrpeople[$num]['adults']; ?> <?php echo ($arrpeople[$num]['adults'] == 1 ? JText::_('VBSEARCHRESADULT') : JText::_('VBSEARCHRESADULTS')); ?><?php echo ($showchildren && $arrpeople[$num]['children'] > 0 ? ", ".$arrpeople[$num]['children']." ".($arrpeople[$num]['children'] == 1 ? JText::_('VBSEARCHRESCHILD') : JText::_('VBSEARCHRESCHILDREN')) : ""); ?>
				</div>
				<div class="vbo-showprc-staydetails-nights">
					<i class="fa fa-calendar"></i> 
					<?php echo (intval($tars[$num][0]['days']) == 1 ? "1 ".JText::_('VBDAY') : $tars[$num][0]['days']." ".JText::_('VBDAYS')); ?>
				</div>
			</div>
		</div>
		<div class="vbo-showprc-room-block<?php echo $roomsnum > 1 ? ' vbo-showprc-room-block-multi' : ''; ?>">
			<div class="vbo-showprc-roomname"><?php echo $r['name']; ?></div>
			<div class="vbroomimgdesc">
				<div class="vikfx-thumbslide-container">
					<div class="vikfx-thumbslide-fade-container">
						<img alt="<?php echo $r['name']; ?>" src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $r['img']; ?>" class="vikfx-thumbslide-image vblistimg"/>
					<?php
					if (strlen($r['moreimgs']) > 0) {
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
		if (strlen($r['moreimgs']) > 0) {
			$moreimages = explode(';;', $r['moreimgs']);
			?>
					<ul class="vikfx-thumbslide-thumbnails">
			<?php
			foreach ($moreimages as $mimg) {
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
				<div class="room_description_box">
				<?php
				//BEGIN: Joomla Content Plugins Rendering
				if (class_exists('JEventDispatcher')) {
					JPluginHelper::importPlugin('content');
					$myItem = JTable::getInstance('content');
					$params = array();
					$dispatcher = JEventDispatcher::getInstance();
					$myItem->text = $r['info'];
					$dispatcher->trigger('onContentPrepare', array('com_vikbooking.showprc', &$myItem, &$params, 0));
					$r['info'] = $myItem->text;
				}
				//END: Joomla Content Plugins Rendering
				echo $r['info'];
				?>
				</div>
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
	
			<div class="room_prices">
				<h4><?php echo JText::_('VBPRICE'); ?></h4>
				<div class="vbo-showprc-rateplans-wrapper">
					<div class="vbo-showprc-pricetable">
		<?php
		foreach ($tars[$num] as $k => $t) {
			if ($is_package === true) {
				//do not print the regular prices if a package was requested.
				break;
			}
			$priceinfo = VikBooking::getPriceInfo($t['idprice'], $vbo_tn);
			$priceinfostr = '';
			$cancpolicy = '';
			//
			$priceinfostr = '<div class="vbpricedetails">';
			if ($priceinfo['breakfast_included'] == 1) {
				$priceinfostr .= '<span class="vbprice_breakfast">'.JText::_('VBBREAKFASTINCLUDED').'</span>';
			}
			if ($priceinfo['free_cancellation'] == 1) {
				if ((int)$priceinfo['canc_deadline'] > 0) {
					$priceinfostr .= '<span class="vbprice_freecanc">'.JText::sprintf('VBFREECANCELLATIONWITHIN', $priceinfo['canc_deadline']).'</span>';
				} else {
					$priceinfostr .= '<span class="vbprice_freecanc">'.JText::_('VBFREECANCELLATION').'</span>';
				}
				if (!empty($priceinfo['canc_policy'])) {
					$priceinfostr .= '<span class="vbo-cancpolicy-trig" onclick="var cancelem=document.getElementById(\'vbo-cancpolicy-cont'.$priceinfo['id'].'\').style.display;if(cancelem == \'block\'){document.getElementById(\'vbo-cancpolicy-cont'.$priceinfo['id'].'\').style.display = \'none\';}else{document.getElementById(\'vbo-cancpolicy-cont'.$priceinfo['id'].'\').style.display = \'block\';}"><i class="fa fa-question-circle"></i></span>';
					$cancpolicy = '<div class="vbo-cancpolicy-cont" id="vbo-cancpolicy-cont'.$priceinfo['id'].'" style="display: none;">'.(strpos($priceinfo['canc_policy'], '<') !== false ? $priceinfo['canc_policy'] : nl2br($priceinfo['canc_policy'])).'</div>';
				}
			} else {
				$priceinfostr .= '<span class="vbprice_freecanc vbprice_freecanc_no">'.JText::_('VBONONREFUNDRATE').'</span>';
			}
			$priceinfostr .= '</div>';
			$priceinfostr .= $cancpolicy;
			//
			$rplan_cost = ($tax_summary ? $t['cost'] : VikBooking::sayCostPlusIva($t['cost'], $t['idprice']));
			$room_basecost = isset($t['room_base_cost']) ? $t['room_base_cost'] : $t['cost'];
			$rplan_basecost = ($tax_summary ? $room_basecost : VikBooking::sayCostPlusIva($room_basecost, $t['idprice']));
			if (!array_key_exists($num, $deftar_costs)) {
				$deftar_costs[$num] = $rplan_cost;
			}
			if (!array_key_exists($num, $deftar_basecosts)) {
				$deftar_basecosts[$num] = $rplan_basecost;
			}
			?>
						<div class="vbo-showprc-price-entry">
							<div class="vbo-showprc-price-entry-radio">
								<input type="radio" class="vbo-radio" data-roomnum="<?php echo $num; ?>" data-ratecost="<?php echo $rplan_cost; ?>" data-ratecostbase="<?php echo $rplan_basecost; ?>" name="priceid<?php echo $num; ?>" id="pid<?php echo $num.$t['idprice']; ?>" value="<?php echo $t['idprice']; ?>"<?php echo ($k==0 ? " checked=\"checked\"" : ""); ?>/>
							</div>
							<div class="vbo-showprc-price-entry-rateplan">
								<label for="pid<?php echo $num.$t['idprice']; ?>"><?php echo $priceinfo['name']; ?></label>
							<?php
							if(strlen($t['attrdata'])) {
								?>
								<div class="vbo-showprc-price-entry-rateattribute">
									<span><?php echo VikBooking::getPriceAttr($t['idprice'], $vbo_tn); ?></span>
									<?php echo $t['attrdata']; ?>
								</div>
								<?php
							}
							?>
								<?php echo $priceinfostr; ?>
							</div>
							<div class="vbo-showprc-price-entry-cost">
								<span class="vbo_currency"><?php echo $currencysymb; ?></span>
								<span class="vbo_price"><?php echo VikBooking::numberFormat($rplan_cost); ?></span>
							</div>
						</div>
			<?php
		}
		if ($is_package === true) {
			$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $days) : $pkg['cost'];
			$base_pkg_cost = ($tax_summary ? $pkg_cost : VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']));
			if (!array_key_exists($num, $deftar_costs)) {
				$deftar_costs[$num] = $base_pkg_cost;
			}
			if (!array_key_exists($num, $deftar_basecosts)) {
				$deftar_basecosts[$num] = $base_pkg_cost;
			}
			$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
			?>
						<div class="vbo-showprc-price-entry vbo-showprc-price-pkg">
							<div class="vbo-showprc-price-entry-radio">
								<input type="radio" class="vbo-radio" data-roomnum="<?php echo $num; ?>" data-ratecost="<?php echo $base_pkg_cost; ?>" data-ratecostbase="<?php echo $base_pkg_cost; ?>" name="priceid<?php echo $num; ?>" id="pid<?php echo $num.$pkg['id']; ?>" value="<?php echo $pkg['id']; ?>" checked="checked"/>
							</div>
							<div class="vbo-showprc-price-entry-rateplan">
								<label for="pid<?php echo $num.$pkg['id']; ?>"><?php echo $pkg['name']; ?></label>
							</div>
							<div class="vbo-showprc-price-entry-cost">
								<span class="vbo_currency"><?php echo $currencysymb; ?></span>
								<span class="vbo_price"><?php echo ($tax_summary ? VikBooking::numberFormat($pkg_cost) : VikBooking::numberFormat(VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']))); ?></span>
							</div>
						</div>
			<?php
		}
		?>
					</div>
		<?php
		//BEGIN: Children Age Intervals
		if (!empty($r['idopt']) && is_array($optionals)) {
			list($optionals, $ageintervals) = VikBooking::loadOptionAgeIntervals($optionals);
			if (is_array($ageintervals) && count($ageintervals) > 0 && $arrpeople[$num]['children'] > 0 && $skip_all_opt !== true) {
				$chageselect = '<select name="optid'.$num.$ageintervals['id'].'[]">'."\n";
				$intervals = explode(';;', $ageintervals['ageintervals']);
				foreach ($intervals as $kintv => $intv) {
					if (empty($intv)) continue;
					$intvparts = explode('_', $intv);
					$intvparts[2] = intval($ageintervals['perday']) == 1 ? ($intvparts[2] * $tars[$num][0]['days']) : $intvparts[2];
					if (!empty ($ageintervals['maxprice']) && $ageintervals['maxprice'] > 0 && $intvparts[2] > $ageintervals['maxprice']) {
						$intvparts[2] = $ageintervals['maxprice'];
					}
					$intvparts[2] = $tax_summary ? $intvparts[2] : VikBooking::sayOptionalsPlusIva($intvparts[2], $ageintervals['idiva']);
					$pricestr = floatval($intvparts[2]) >= 0 ? '+ '.VikBooking::numberFormat($intvparts[2]) : '- '.VikBooking::numberFormat($intvparts[2]);
					$pcent_interval = false;
					$opt_suffix = '';
					if (array_key_exists(3, $intvparts) && strpos($intvparts[3], '%') !== false && array_key_exists($num, $deftar_costs) && floatval($intvparts[2]) >= 0) {
						$children_agepcent = true;
						$pcent_interval = true;
						if (strpos($intvparts[3], '%b') !== false) {
							//VBO 1.10 - percentage value of room base cost
							$opt_suffix = 'base';
							$pcent_cost = $deftar_basecosts[$num] * (float)$intvparts[2] / 100;
							$pricestr = '+ '.VikBooking::numberFormat($pcent_cost);
						} else {
							//percentage value of adults tariff
							$pcent_cost = $deftar_costs[$num] * (float)$intvparts[2] / 100;
							$pricestr = '+ '.VikBooking::numberFormat($pcent_cost);
						}
					}
					$chageselect .= '<option value="'.($kintv + 1).'"'.($pcent_interval === true ? ' data-pcentintvroom="'.$num.'" data-ratetype="'.$opt_suffix.'" data-pcentintv="'.floatval($intvparts[2]).'" data-ageintv="'.$intvparts[0].' - '.$intvparts[1].'"' : '').'>'.$intvparts[0].' - '.$intvparts[1].' ('.$pricestr.' '.$currencysymb.')'.'</option>'."\n";
				}
				$chageselect .= '</select>'."\n";
				?>
					<div class="vbageintervals">
				<?php echo $ageintervals['descr']; ?>
						<ul>
				<?php
				for ($ch = 1; $ch <= $arrpeople[$num]['children']; $ch++) {
					?>
							<li><?php echo JText::_('VBSEARCHRESCHILD').' #'.$ch; ?>: <?php echo $chageselect; ?></li>
					<?php
				}
				?>
						</ul>
					</div>
		<?php
			}
		}
		//END: Children Age Intervals
		?>
				</div>
			</div>
	
	<?php
	if (!empty($r['idopt']) && is_array($optionals) && $skip_all_opt !== true) {
	?>
			<div class="room_options">
				<h4><?php echo JText::_('VBACCOPZ'); ?></h4>
				<div class="vbo-showprc-optionstable">
		<?php
		$arrforcesummary = array();
		foreach ($optionals as $k => $o) {
			$showoptional = true;
			if (intval($o['ifchildren']) == 1 && $arrpeople[$num]['children'] < 1) {
				$showoptional = false;
			}
			if($only_forced_opt === true && intval($o['forcesel']) != 1) {
				$showoptional = false;
			}
			if ($showoptional !== true) {
				continue;
			}
			$optcost = intval($o['perday']) == 1 ? ($o['cost'] * $tars[$num][0]['days']) : $o['cost'];
			if (!empty ($o['maxprice']) && $o['maxprice'] > 0 && $optcost > $o['maxprice']) {
				$optcost = $o['maxprice'];
			}
			if ($o['perperson'] == 1) {
				$optcost = $optcost * $arrpeople[$num]['adults'];
			}
			$optcost = $optcost * 1;
			//vikbooking 1.1
			$forcesummary = false;
			if(intval($o['forcesel']) == 1) {
				$forcedquan = 1;
				$forceperday = false;
				$forceperchild = false;
				if(strlen($o['forceval']) > 0) {
					$forceparts = explode("-", $o['forceval']);
					$forcedquan = intval($forceparts[0]);
					$forceperday = intval($forceparts[1]) == 1 ? true : false;
					$forceperchild = intval($forceparts[2]) == 1 ? true : false;
					$forcesummary = intval($forceparts[3]) == 1 ? true : false;
				}
				$setoptquan = $forceperday == true ? ($forcedquan * $tars[$num][0]['days']) : $forcedquan;
				$setoptquan = $forceperchild == true ? ($setoptquan * $arrpeople[$num]['children']) : $setoptquan;
				if ($forcesummary === true) {
					$optquaninp = "<input type=\"hidden\" name=\"optid".$num.$o['id']."\" value=\"".$setoptquan."\"/>";
					$arrforcesummary[] = $optquaninp;
				} else {
					if (intval($o['hmany']) == 1) {
						$optquaninp = "<input type=\"hidden\" name=\"optid".$num.$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vboptionforcequant\"><small>x</small> ".$setoptquan."</span>";
					} else {
						$optquaninp = "<input type=\"hidden\" name=\"optid".$num.$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vboptionforcequant\"><small>x</small> ".$setoptquan."</span>";
					}
				}
			} else {
				if (intval($o['hmany']) == 1) {
					if (intval($o['maxquant']) > 0) {
						$optquaninp = "<select name=\"optid".$num.$o['id']."\">\n";
						for ($ojj = 0; $ojj <= intval($o['maxquant']); $ojj++) {
							$optquaninp .= "<option value=\"".$ojj."\">".$ojj."</option>\n";
						}
						$optquaninp .= "</select>\n";
					} else {
						$optquaninp = "<input type=\"number\" min=\"0\" step=\"any\" name=\"optid".$num.$o['id']."\" value=\"0\" size=\"5\"/>";
					}
				} else {
					$optquaninp = "<input type=\"checkbox\" name=\"optid".$num.$o['id']."\" value=\"1\"/>";
				}
			}
			//
			if ($forcesummary === false) {
				?>
					<div class="vbo-showprc-option-entry">
						<div class="vbo-showprc-option-entry-img">
							<?php echo (!empty($o['img']) ? '<img class="maxthirty" src="'.VBO_SITE_URI.'resources/uploads/'.$o['img'].'"/>' : '&nbsp;'); ?>
						</div>
						<div class="vbo-showprc-option-entry-name">
							<?php echo $o['name']; ?>
						<?php
						if(strlen(strip_tags( trim($o['descr'] )))) {
							?>
							<div class="vbo-showprc-option-entry-descr">
								<?php echo $o['descr']; ?>
							</div>
							<?php
						}
						?>
						</div>
						<div class="vbo-showprc-option-entry-cost">
							<span class="vbo_currency"><?php echo $currencysymb; ?></span>
							<span class="vbo_price"><?php echo $tax_summary ? VikBooking::numberFormat($optcost) : VikBooking::numberFormat(VikBooking::sayOptionalsPlusIva($optcost, $o['idiva'])); ?></span>
						</div>
						<div class="vbo-showprc-option-entry-input">
							<?php echo $optquaninp; ?>
						</div>
					</div>
					<?php
			}
		}
		?>
				</div>
		<?php
		if (count($arrforcesummary) > 0) {
			echo implode("\n", $arrforcesummary);
		}
		?>
			</div>
	<?php
	}
	?>
		</div>

	</div>
	<input type="hidden" name="roomid[]" value="<?php echo $r['id']; ?>"/>
	<?php
}
?>
</div>
<?php

foreach ($arrpeople as $indroom => $aduch) {
	?>
	<input type="hidden" name="adults[]" value="<?php echo $aduch['adults']; ?>"/>
	<?php
	if ($showchildren) {
		?>
		<input type="hidden" name="children[]" value="<?php echo $aduch['children']; ?>"/>
		<?php	
	}
}
	?>
	<input type="hidden" id="roomsnum" name="roomsnum" value="<?php echo $roomsnum; ?>"/>
	<input type="hidden" name="days" value="<?php echo $tars[1][0]['days']; ?>"/>
	<input type="hidden" name="checkin" value="<?php echo $checkin; ?>"/>
	<input type="hidden" name="checkout" value="<?php echo $checkout; ?>"/>
	<?php
	if (!empty ($pitemid)) {
		?>
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
		<?php
	}
	if ($is_package === true) {
		?>
		<input type="hidden" name="pkg_id" value="<?php echo $pkg['id']; ?>"/>
		<?php
	}
	if ($is_package === true && !empty($pkg['benefits'])) {
	?>
	<div class="vbo-pkg-showprc-benefits"><?php echo $pkg['benefits']; ?></div>
	<?php
	}
	if (strlen($discl)) {
	?>
	<div class="room_disclaimer"><?php echo $discl; ?></div>
	<?php
	}
	if ($is_package === true && !empty($pkg['conditions'])) {
	?>
	<div class="room_disclaimer vbo-pkg-showprc-conditions"><?php echo $pkg['conditions']; ?></div>
	<?php
	}
	?>
	<div class="room_buttons_box">
		<input type="submit" name="goon" value="<?php echo JText::_('VBBOOKNOW'); ?>" class="booknow"/>
		<div class="goback">
		<?php
		if ($is_package === true) {
			?>
			<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=packagedetails&pkgid='.$pkg['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBBACK'); ?></a>
			<?php
		} else {
			?>
			<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=search&checkindate='.urlencode($checkinforlink).'&checkoutdate='.urlencode($checkoutforlink).'&roomsnum='.$roomsnum.$peopleforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBBACK'); ?></a>
			<?php
		}
		?>
		</div>
	</div>
	
</form>
<?php
if ($children_agepcent === true) :
	$formatvals = VikBooking::getNumberFormatData();
	$formatparts = explode(':', $formatvals);
?>
<script type="text/javascript">
Number.prototype.numFormat = function(c, d, t) {
	var n = this, 
		c = isNaN(c = Math.abs(c)) ? 2 : c, 
		d = d == undefined ? "." : d, 
		t = t == undefined ? "," : t, 
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
		j = (j = i.length) > 3 ? j % 3 : 0;
	return (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};
jQuery(document).ready(function() {
	jQuery(".vbo-radio").change(function() {
		var rnum = jQuery(this).attr("data-roomnum");
		var rcost = parseFloat(jQuery(this).attr("data-ratecost"));
		var rcostbase = parseFloat(jQuery(this).attr("data-ratecostbase"));
		if(!isNaN(rcost) && jQuery("option[data-pcentintvroom='"+rnum+"']").length > 0) {
			jQuery("option[data-pcentintvroom='"+rnum+"']").each(function() {
				var usecost = rcost;
				var ratetype = jQuery(this).attr('data-ratetype');
				if (ratetype == 'base') {
					usecost = rcostbase;
				}
				var pcentval = parseFloat(jQuery(this).attr("data-pcentintv"));
				var agesval = jQuery(this).attr("data-ageintv");
				if(!isNaN(pcentval) && agesval.length) {
					var intvcost = usecost * pcentval / 100;
					jQuery(this).html(agesval+" (+ "+(intvcost).numFormat(<?php echo $formatparts[0]; ?>, '<?php echo $formatparts[1]; ?>', '<?php echo $formatparts[2]; ?>')+" <?php echo $currencysymb; ?>)");
				}
			});
		}
	});
});
</script>
<?php
endif;
?>