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

//VikBooking 1.5
$session = JFactory::getSession();
$channel_disclaimer = false;
$vcmchanneldata = $session->get('vcmChannelData', '');
if (!empty($vcmchanneldata) && is_array($vcmchanneldata) && count($vcmchanneldata) > 0) {
	if (array_key_exists('disclaimer', $vcmchanneldata) && !empty($vcmchanneldata['disclaimer'])) {
		$channel_disclaimer = true;
	}
}
//

$rooms=$this->rooms;
$roomsnum=$this->roomsnum;
$tars=$this->tars;
$prices=$this->prices;
$arrpeople=$this->arrpeople;
$selopt=$this->selopt;
$days=$this->days;
$coupon=$this->coupon;
$first=$this->first;
$second=$this->second;
$payments=$this->payments;
$cfields=$this->cfields;
$customer_details=$this->customer_details;
$countries=$this->countries;
$pkg=$this->pkg;
$vbo_tn=$this->vbo_tn;

$vbo_app = VikBooking::getVboApplication();
$showchildren = VikBooking::showChildrenFront();
$totadults = 0;
$totchildren = 0;
$is_package = is_array($pkg) && count($pkg) > 0 ? true : false;
$pitemid = VikRequest::getInt('Itemid', '', 'request');

$wdays_map = array(
	JText::_('VBWEEKDAYZERO'),
	JText::_('VBWEEKDAYONE'),
	JText::_('VBWEEKDAYTWO'),
	JText::_('VBWEEKDAYTHREE'),
	JText::_('VBWEEKDAYFOUR'),
	JText::_('VBWEEKDAYFIVE'),
	JText::_('VBWEEKDAYSIX')
);

if (VikBooking::tokenForm()) {
	$vikt = uniqid(rand(17, 1717), true);
	$session->set('vikbtoken', $vikt);
	$tok = "<input type=\"hidden\" name=\"viktoken\" value=\"" . $vikt . "\"/>\n";
} else {
	$tok = "";
}

$document = JFactory::getDocument();
if (VikBooking::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);

if (is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (!empty($cf['poplink'])) {
			?>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".vbmodal").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"width": "70%",
		"height": "75%",
		"autoScale": false,
		"transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"type": "iframe"
	});
});
</script>
			<?php
			break;
		}
	}
}
$currencysymb = VikBooking::getCurrencySymb();
$nowdf = VikBooking::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();
$checkinforlink = date($df, $first);
$checkoutforlink = date($df, $second);
$in_info = getdate($first);
$out_info = getdate($second);

$peopleforlink = '';
foreach ($arrpeople as $aduchild) {
	$totadults += $aduchild['adults'];
	$totchildren += $aduchild['children'];
	$peopleforlink .= '&adults[]='.$aduchild['adults'].'&children[]='.$aduchild['children'];
}

$roomoptforlink = '';
foreach ($rooms as $r) {
	$roomoptforlink .= '&roomopt[]='.$r['id'];
}

if (count($this->mod_booking)) {
	//booking modification
	?>
<div class="vbo-booking-modification-helper">
	<div class="vbo-booking-modification-helper-inner">
		<div class="vbo-booking-modification-msg">
			<span><?php echo JText::_('VBOMODBOOKHELPOCONF'); ?></span>
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
	if ($is_package === true) {
		?>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=packageslist'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBOPKGLINK'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=packagedetails&pkgid='.$pkg['id'].(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPROOMSELECTION'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=showprc&checkin='.$first.'&checkout='.$second.'&roomsnum='.$roomsnum.'&days='.$days.'&pkg_id='.$pkg['id'].$peopleforlink.$roomoptforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPOPTIONS'); ?></a></li>
		<?php
	} else {
		?>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&checkin='.$first.'&checkout='.$second.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBSTEPDATES'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=search&checkindate='.urlencode($checkinforlink).'&checkoutdate='.urlencode($checkoutforlink).'&roomsnum='.$roomsnum.$peopleforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPROOMSELECTION'); ?></a></li>
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=showprc&checkin='.$first.'&checkout='.$second.'&roomsnum='.$roomsnum.'&days='.$days.$peopleforlink.$roomoptforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBSTEPOPTIONS'); ?></a></li>
		<?php
	}
	?>
		<li class="vbo-step vbo-step-current"><span><?php echo JText::_('VBSTEPCONFIRM'); ?></span></li>
	</ol>
</div>

<br clear="all"/>

<div class="vbo-results-head vbo-results-head-oconfirm">
	<span class="vbo-results-nights"><i class="fa fa-calendar"></i> <?php echo $days; ?> <?php echo ($days == 1 ? JText::_('VBSEARCHRESNIGHT') : JText::_('VBSEARCHRESNIGHTS')); ?></span>
<?php
if ($roomsnum > 1) {
	?>
	<span class="vbo-results-numrooms"><i class="fa fa-bed"></i> <?php echo $roomsnum." ".($roomsnum == 1 ? JText::_('VBSEARCHRESROOM') : JText::_('VBSEARCHRESROOMS')); ?></span>
	<?php
}
?>
	<span class="vbo-results-numadults"><i class="fa fa-male"></i> <?php echo $totadults; ?> <?php echo ($totadults == 1 ? JText::_('VBSEARCHRESADULT') : JText::_('VBSEARCHRESADULTS')); ?></span>
<?php
if ($showchildren && $totchildren > 0) {
	?>
	<span class="vbo-results-numchildren"><i class="fa fa-child"></i> <?php echo $totchildren." ".($totchildren == 1 ? JText::_('VBSEARCHRESCHILD') : JText::_('VBSEARCHRESCHILDREN')); ?></span>
	<?php
}
?>
	<span class="vbo-summary-date"><i class="fa fa-sign-in"></i> <?php echo $wdays_map[$in_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $first); ?></span>
	<span class="vbo-summary-date"><i class="fa fa-sign-out"></i> <?php echo $wdays_map[$out_info['wday']].', '.date(str_replace("/", $datesep, $df).' H:i', $second); ?></span>
</div>

<div class="table-responsive vbo-oconfirm-tblcont">
	<table class="table vbtableorder">
		<tr class="vbtableorderfrow"><td>&nbsp;</td><td align="center"><?php echo JText::_('ORDDD'); ?></td><td align="center"><?php echo JText::_('ORDNOTAX'); ?></td><td align="center"><?php echo JText::_('ORDTAX'); ?></td><td align="center"><?php echo JText::_('ORDWITHTAX'); ?></td></tr>
<?php
$imp = 0;
$totdue = 0;
$saywithout = 0;
$saywith = 0;
$tot_taxes = 0;
$tot_city_taxes = 0;
$tot_fees = 0;
$wop = "";
foreach ($rooms as $num => $r) {
	if ($is_package === true) {
		$pkg_cost = $pkg['pernight_total'] == 1 ? ($pkg['cost'] * $days) : $pkg['cost'];
		$pkg_cost = $pkg['perperson'] == 1 ? ($pkg_cost * ($arrpeople[$num]['adults'] > 0 ? $arrpeople[$num]['adults'] : 1)) : $pkg_cost;
		$tmpimp = VikBooking::sayPackageMinusIva($pkg_cost, $pkg['idiva']);
		$tmptotdue = VikBooking::sayPackagePlusIva($pkg_cost, $pkg['idiva']);
		$base_cost = $pkg_cost;
	} else {
		$tmpimp = VikBooking::sayCostMinusIva($tars[$num][0]['cost'], $tars[$num][0]['idprice']);
		$tmptotdue = VikBooking::sayCostPlusIva($tars[$num][0]['cost'], $tars[$num][0]['idprice']);
		$base_cost = $tars[$num][0]['cost'];
	}
	$imp += $tmpimp;
	$totdue += $tmptotdue;
	if ($tmptotdue == $base_cost) {
		$tot_taxes += ($base_cost - $tmpimp);
	} else {
		$tot_taxes += ($tmptotdue - $base_cost);
	}
	$saywithout = $tmpimp;
	$saywith = $tmptotdue;
	if (is_array($selopt[$num])) {
		foreach ($selopt[$num] as $selo) {
			$wop .= $num . "_" . $selo['id'] . ":" . $selo['quan'] . (array_key_exists('chageintv', $selo) ? '-'.$selo['chageintv'] : '') . ";";
			$realcost = (intval($selo['perday']) == 1 ? ($selo['cost'] * $days * $selo['quan']) : ($selo['cost'] * $selo['quan']));
			if (!empty ($selo['maxprice']) && $selo['maxprice'] > 0 && $realcost > $selo['maxprice']) {
				$realcost = $selo['maxprice'];
				if (intval($selo['hmany']) == 1 && intval($selo['quan']) > 1) {
					$realcost = $selo['maxprice'] * $selo['quan'];
				}
			}
			if ($selo['perperson'] == 1) {
				$realcost = $realcost * $arrpeople[$num]['adults'];
			}
			$imp += VikBooking::sayOptionalsMinusIva($realcost, $selo['idiva']);
			$tmpopr = VikBooking::sayOptionalsPlusIva($realcost, $selo['idiva']);
			$totdue += $tmpopr;
			if ($selo['is_citytax'] == 1) {
				$tot_city_taxes += $tmpopr;
			} elseif ($selo['is_fee'] == 1) {
				$tot_fees += $tmpopr;
			} else {
				if ($tmpopr == $realcost) {
					$tot_taxes += ($realcost - $imp);
				} else {
					$tot_taxes += ($tmpopr - $realcost);
				}
			}
		}
	}
	?>
		<tr class="vbo-oconfirm-roomrow">
			<td align="left">
				<div class="vbo-oconfirm-roomname"><?php echo $r['name']; ?></div>
				<div class="vbo-oconfirm-priceinfo">
				<?php
				if ($is_package === true) {
					echo $pkg['name'];
				} else {
					echo VikBooking::getPriceName($tars[$num][0]['idprice'], $vbo_tn).(!empty($tars[$num][0]['attrdata']) ? "<br/>".VikBooking::getPriceAttr($tars[$num][0]['idprice'], $vbo_tn).": ".$tars[$num][0]['attrdata'] : "");
				}
				?>
				</div>
			</td>
			<td align="center"><?php echo $days; ?></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($saywithout); ?></span></span></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($saywith - $saywithout); ?></span></span></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($saywith); ?></span></span></td>
		</tr>
	<?php
	//write options
	$sf = 2;
	if (is_array($selopt[$num])) {
		foreach ($selopt[$num] as $aop) {
			if (intval($aop['perday']) == 1) {
				$thisoptcost = ($aop['cost'] * $aop['quan']) * $days;
			} else {
				$thisoptcost = $aop['cost'] * $aop['quan'];
			}
			if (!empty ($aop['maxprice']) && $aop['maxprice'] > 0 && $thisoptcost > $aop['maxprice']) {
				$thisoptcost = $aop['maxprice'];
				if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) {
					$thisoptcost = $aop['maxprice'] * $aop['quan'];
				}
			}
			if ($aop['perperson'] == 1) {
				$thisoptcost = $thisoptcost * $arrpeople[$num]['adults'];
			}
			$optwithout = (intval($aop['perday']) == 1 ? VikBooking::sayOptionalsMinusIva($thisoptcost, $aop['idiva']) : VikBooking::sayOptionalsMinusIva($thisoptcost, $aop['idiva']));
			$optwith = (intval($aop['perday']) == 1 ? VikBooking::sayOptionalsPlusIva($thisoptcost, $aop['idiva']) : VikBooking::sayOptionalsPlusIva($thisoptcost, $aop['idiva']));
			$opttax = ($optwith - $optwithout);
		?>
		<tr<?php echo (($sf % 2) == 0 ? " class=\"vbordrowtwo\"" : " class=\"vbordrowone\""); ?>>
			<td><div class="vbo-oconfirm-optname"><?php echo $aop['name'].($aop['quan'] > 1 ? " <small>(x ".$aop['quan'].")</small>" : ""); ?></div></td>
			<td align="center">&nbsp;</td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($optwithout); ?></span></span></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($opttax); ?></span></span></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($optwith); ?></span></span></td>
		</tr>
		<?php
			$sf++;
		}
	}
	//end write options
	if ($roomsnum > 1 && $num < $roomsnum) {
		?>
		<tr class="vbo-oconfirm-tr-separator"><td colspan="5">&nbsp;</td></tr>
		<?php
	}
}

//store Order Total in session for modules
$session->set('vikbooking_ordertotal', $totdue);
//

//vikbooking 1.1
$origtotdue = $totdue;
$usedcoupon = false;
if (is_array($coupon)) {
	//check min tot ord
	$coupontotok = true;
	if (strlen($coupon['mintotord']) > 0) {
		if ($totdue < $coupon['mintotord']) {
			$coupontotok = false;
		}
	}
	if ($coupontotok == true) {
		$usedcoupon = true;
		if ($coupon['percentot'] == 1) {
			//percent value
			$minuscoupon = 100 - $coupon['value'];
			$couponsave = ($totdue - $tot_city_taxes - $tot_fees) * $coupon['value'] / 100;
			$totdue = ($totdue - $tot_taxes - $tot_city_taxes - $tot_fees) * $minuscoupon / 100;
			$tot_taxes = $tot_taxes * $minuscoupon / 100;
			$totdue += ($tot_taxes + $tot_city_taxes + $tot_fees);
		} else {
			//total value
			$couponsave = $coupon['value'];
			$tax_prop = $tot_taxes * $coupon['value'] / $totdue;
			$tot_taxes -= $tax_prop;
			$tot_taxes = $tot_taxes < 0 ? 0 : $tot_taxes;
			$totdue -= $coupon['value'];
			$totdue = $totdue < 0 ? 0 : $totdue;
		}
	} else {
		VikError::raiseWarning('', JText::_('VBCOUPONINVMINTOTORD'));
	}
}
//

?>
		<tr class="vbo-oconfirm-tr-separator-total"><td colspan="5">&nbsp;</td></tr>
		<tr class="vbordrowtotal">
			<td><div class="vbo-oconfirm-total-block"><?php echo JText::_('VBTOTAL'); ?></div></td>
			<td align="center">&nbsp;</td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($imp); ?></span></span></td>
			<td align="center"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat(($origtotdue - $imp)); ?></span></span></td>
			<td align="center" class="vbtotalord"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($origtotdue); ?></span></span></td>
		</tr>
	<?php
	if ($usedcoupon == true) {
		?>
		<tr class="vbordrowtotal">
			<td><?php echo JText::_('VBCOUPON'); ?> <?php echo $coupon['code']; ?></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vbtotalord"><span class="vbcurrency">- <span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($couponsave); ?></span></span></td>
		</tr>
		<tr class="vbordrowtotal">
			<td><div class="vbo-oconfirm-total-block"><?php echo JText::_('VBNEWTOTAL'); ?></div></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vbtotalord"><span class="vbcurrency"><span class="vbo_currency"><?php echo $currencysymb; ?></span></span> <span class="vbprice"><span class="vbo_price"><?php echo VikBooking::numberFormat($totdue); ?></span></span></td>
		</tr>
		<?php
	}
	if (count($this->mod_booking) && $this->mod_booking['total'] > 0) {
		//booking modification
		$modbook_tot_diff = abs($this->mod_booking['total'] - $totdue);
		$modbook_diff_op = $this->mod_booking['total'] <= $totdue ? '+' : '-';
		?>
		<tr class="vbordrowtotal vbordrowtotal-prevtot">
			<td><div class="vbo-oconfirm-total-block vbo-oconfirm-previoustotal-block"><?php echo JText::_('VBOMODBOOKPREVTOT'); ?></div></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vbtotalord">
				<span class="vbcurrency">
					<span class="vbo_currency"><?php echo $currencysymb; ?></span>
				</span> 
				<span class="vbprice">
					<span class="vbo_price"><?php echo VikBooking::numberFormat($this->mod_booking['total']); ?></span>
				</span>
			</td>
		</tr>
		<tr class="vbo-oconfirm-tr-separator-total"><td colspan="5">&nbsp;</td></tr>
		<tr class="vbordrowtotal <?php echo $modbook_diff_op == '+' ? 'vbordrowtotal-negative' : 'vbordrowtotal-positive'; ?>">
			<td><div class="vbo-oconfirm-total-block vbo-oconfirm-diffmod-block"><?php echo JText::_('VBOMODBOOKDIFFTOT'); ?></div></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vbtotalord">
				<!-- <span class="vbo-modbook-diffop"><?php echo $modbook_diff_op; ?></span> -->
				<span class="vbcurrency">
					<span class="vbo_currency"><?php echo $currencysymb; ?></span>
				</span> 
				<span class="vbprice">
					<span class="vbo_price"><?php echo VikBooking::numberFormat($modbook_tot_diff); ?></span>
				</span>
			</td>
		</tr>
		<?php
	}
		?>
	</table>
</div>

<div class="vbo-oconfirm-middlep">
<?php
//vikbooking 1.1
if (VikBooking::couponsEnabled() && !is_array($coupon) && $is_package !== true && !(count($this->mod_booking) > 0)) {
	?>
	<div class="vbo-coupon-outer">
		<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="post" class="vbo-coupon-form">
			<div class="vbentercoupon">
				<span class="vbhaveacoupon"><?php echo JText::_('VBHAVEACOUPON'); ?></span><input type="text" name="couponcode" value="" size="20" class="vbinputcoupon"/><input type="submit" class="vbsubmitcoupon" name="applyacoupon" value="<?php echo JText::_('VBSUBMITCOUPON'); ?>"/>
			</div>
			<input type="hidden" name="task" value="oconfirm"/>
			<input type="hidden" name="days" value="<?php echo $days; ?>"/>
			<input type="hidden" name="roomsnum" value="<?php echo $roomsnum; ?>"/>
			<input type="hidden" name="checkin" value="<?php echo $first; ?>"/>
			<input type="hidden" name="checkout" value="<?php echo $second; ?>"/>
			<?php
			foreach($rooms as $num => $r) {
				echo '<input type="hidden" name="priceid'.$num.'" value="'.$prices[$num].'"/>'."\n";
				echo '<input type="hidden" name="roomid[]" value="'.$r['id'].'"/>'."\n";
				echo '<input type="hidden" name="adults[]" value="'.$arrpeople[$num]['adults'].'"/>'."\n";
				echo '<input type="hidden" name="children[]" value="'.$arrpeople[$num]['children'].'"/>'."\n";
				if (is_array($selopt[$num])) {
					foreach ($selopt[$num] as $aop) {
						echo '<input type="hidden" name="optid'.$num.$aop['id'].(!empty($aop['ageintervals']) && array_key_exists('chageintv', $aop) ? '[]' : '').'" value="'.(!empty($aop['ageintervals']) && array_key_exists('chageintv', $aop) ? $aop['chageintv'] : $aop['quan']).'"/>'."\n";
					}
				}
			}
			?>
		</form>
	</div>
	<?php
}
//Customers PIN
if (VikBooking::customersPinEnabled() && !VikBooking::userIsLogged() && !(count($customer_details) > 0)) {
	?>
	<div class="vbo-enterpin-block">
		<div class="vbo-enterpin-top">
			<span><span><?php echo JText::_('VBRETURNINGCUSTOMER'); ?></span><?php echo JText::_('VBENTERPINCODE'); ?></span>
			<input type="text" id="vbo-pincode-inp" value="" size="6"/>
			<button type="button" class="btn vbo-pincode-sbmt"><?php echo JText::_('VBAPPLYPINCODE'); ?></button>
		</div>
		<div class="vbo-enterpin-response"></div>
	</div>
	<script>
	jQuery(document).ready(function() {
		jQuery(".vbo-pincode-sbmt").click(function() {
			var pin_code = jQuery("#vbo-pincode-inp").val();
			jQuery(this).prop('disabled', true);
			jQuery(".vbo-enterpin-response").hide();
			jQuery.ajax({
				type: "POST",
				url: "<?php echo JRoute::_('index.php?option=com_vikbooking&task=validatepin&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>",
				data: { pin: pin_code }
			}).done(function(res) {
				var pinobj = jQuery.parseJSON(res);
				if (pinobj.hasOwnProperty('success')) {
					jQuery(".vbo-enterpin-top").hide();
					jQuery(".vbo-enterpin-response").removeClass("vbo-enterpin-error").addClass("vbo-enterpin-success").html("<span class=\"vbo-enterpin-welcome\"><?php echo addslashes(JText::_('VBWELCOMEBACK')); ?></span><span class=\"vbo-enterpin-customer\">"+pinobj.first_name+" "+pinobj.last_name+"</span>").fadeIn();
					jQuery.each(pinobj.cfields, function(k, v) {
						if (jQuery("#vbf-inp"+k).length) {
							jQuery("#vbf-inp"+k).val(v);
						}						
					});
					var user_country = pinobj.country;
					if (jQuery(".vbf-countryinp").length && user_country.length) {
						jQuery(".vbf-countryinp option").each(function(i){
							var opt_country = jQuery(this).val();
							if (opt_country.substring(0, 3) == user_country) {
								jQuery(this).prop("selected", true);
								return false;
							}
						});
					}
				} else {
					jQuery(".vbo-enterpin-response").addClass("vbo-enterpin-error").html("<p><?php echo addslashes(JText::_('VBINVALIDPINCODE')); ?></p>").fadeIn();
					jQuery(".vbo-pincode-sbmt").prop('disabled', false);
				}
			}).fail(function(){
				alert('Error validating the PIN. Request failed.');
				jQuery(".vbo-pincode-sbmt").prop('disabled', false);
			});
		});
	});
	</script>
	<?php
}
?>
</div>	

		<script type="text/javascript">
		function validateEmail(email) { 
		    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		    return re.test(email);
		}
  		function checkvbFields() {
  			var vbvar = document.vb;
			<?php

if (@ is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			if ($cf['type'] == "text" || $cf['type'] == "textarea" || $cf['type'] == "date" || $cf['type'] == "country") {
			?>
			if (!vbvar.vbf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='';
			}
			<?php
				if ($cf['isemail'] == 1) {
				?>
			if (!validateEmail(vbvar.vbf<?php echo $cf['id']; ?>.value)) {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='';
			}
				<?php
				}
			} elseif ($cf['type'] == "select") {
			?>
			if (!vbvar.vbf<?php echo $cf['id']; ?>.value.match(/\S/)) {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			} else {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='';
			}
			<?php

			} elseif ($cf['type'] == "checkbox") {
				//checkbox
			?>
			if (vbvar.vbf<?php echo $cf['id']; ?>.checked) {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='';
			} else {
				document.getElementById('vbf<?php echo $cf['id']; ?>').style.color='#ff0000';
				return false;
			}
			<?php

			}
		}
	}
}
?>
  			return true;
  		}
  		function validateVbSubmit() {
  			if (!checkvbFields()) {
  				var vbalert_cont = document.getElementById('vbo-alert-container-confirm');
  				if (vbalert_cont !== null) {
  					vbalert_cont.style.display = 'block';
  					vbalert_cont.style.opacity = '1';
  					setTimeout(vbHideAlertFillin, 10000);
  				}
  				return false;
  			}
  			return true;
  		}
  		function vbHideAlertFillin() {
  			var vbalert_cont = document.getElementById('vbo-alert-container-confirm');
  			if (vbalert_cont !== null) {
  				vbalert_cont.style.opacity = '0';
        		setTimeout(function(){ vbalert_cont.style.display = 'none'; }, 600);
  			}
  		}
		</script>
		
	<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" name="vb" method="post" onsubmit="javascript: return validateVbSubmit();">
	<?php

if (@ is_array($cfields)) {
	?>
		<div class="vbcustomfields">
	<?php
	$currentUser = JFactory::getUser();
	$useremail = !empty($currentUser->email) ? $currentUser->email : "";
	$useremail = array_key_exists('email', $customer_details) ? $customer_details['email'] : $useremail;
	$nominatives = array();
	if (count($customer_details) > 0) {
		$nominatives[] = $customer_details['first_name'];
		$nominatives[] = $customer_details['last_name'];
	}
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			$isreq = "<span class=\"vbrequired\"><sup>*</sup></span> ";
		} else {
			$isreq = "";
		}
		if (!empty ($cf['poplink'])) {
			$fname = "<a href=\"" . $cf['poplink'] . "\" id=\"vbf" . $cf['id'] . "\" target=\"_blank\" class=\"vbmodal\">" . JText::_($cf['name']) . "</a>";
		} else {
			$fname = "<label id=\"vbf" . $cf['id'] . "\" for=\"vbf-inp" . $cf['id'] . "\">" . JText::_($cf['name']) . "</label>";
		}
		if ($cf['type'] == "text") {
			$def_textval = '';
			if ($cf['isemail'] == 1) {
				$def_textval = $useremail;
			} elseif ($cf['isphone'] == 1) {
				if (array_key_exists('phone', $customer_details)) {
					$def_textval = $customer_details['phone'];
				}
			} elseif ($cf['isnominative'] == 1) {
				if (count($nominatives) > 0) {
					$def_textval = array_shift($nominatives);
				}
			} elseif (array_key_exists('cfields', $customer_details)) {
				if (array_key_exists($cf['id'], $customer_details['cfields'])) {
					$def_textval = $customer_details['cfields'][$cf['id']];
				}
			}
			?>
			<div class="vbo-oconfirm-cfield-entry">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<input type="text" name="vbf<?php echo $cf['id']; ?>" id="vbf-inp<?php echo $cf['id']; ?>" value="<?php echo $def_textval; ?>" size="40" class="vbinput"/>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "textarea") {
			$def_textval = '';
			if (array_key_exists($cf['id'], $customer_details['cfields'])) {
				$def_textval = $customer_details['cfields'][$cf['id']];
			}
			?>
			<div class="vbo-oconfirm-cfield-entry vbo-oconfirm-cfield-entry-textarea">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<textarea name="vbf<?php echo $cf['id']; ?>" id="vbf-inp<?php echo $cf['id']; ?>" rows="5" cols="30" class="vbtextarea"><?php echo $def_textval; ?></textarea>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "date") {
			?>
			<div class="vbo-oconfirm-cfield-entry">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<?php echo $vbo_app->getCalendar('', 'vbf'.$cf['id'], 'vbf-inp'.$cf['id'], VikBooking::getDateFormat(), array('class'=>'vbinput', 'size'=>'10',  'maxlength'=>'19')); ?>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "country" && is_array($countries)) {
			$usercountry = '';
			if (array_key_exists('country', $customer_details)) {
				$usercountry = !empty($customer_details['country']) ? substr($customer_details['country'], 0, 3) : '';
			}
			$countries_sel = '<select name="vbf'.$cf['id'].'" class="vbf-countryinp"><option value=""></option>'."\n";
			foreach ($countries as $country) {
				$countries_sel .= '<option value="'.$country['country_3_code'].'::'.$country['country_name'].'"'.($country['country_3_code'] == $usercountry ? ' selected="selected"' : '').'>'.$country['country_name'].'</option>'."\n";
			}
			$countries_sel .= '</select>';
			?>
			<div class="vbo-oconfirm-cfield-entry">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<?php echo $countries_sel; ?>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "select") {
			$answ = explode(";;__;;", $cf['choose']);
			$wcfsel = "<select name=\"vbf" . $cf['id'] . "\">\n";
			foreach ($answ as $aw) {
				if (!empty ($aw)) {
					$wcfsel .= "<option value=\"" . $aw . "\">" . $aw . "</option>\n";
				}
			}
			$wcfsel .= "</select>\n";
			?>
			<div class="vbo-oconfirm-cfield-entry">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<?php echo $wcfsel; ?>
				</div>
			</div>
			<?php
		} elseif ($cf['type'] == "separator") {
			$cfsepclass = strlen(JText::_($cf['name'])) > 30 ? "vbseparatorcflong" : "vbseparatorcf";
			?>
			<div class="vbo-oconfirm-cfield-entry vbo-oconfirm-cfield-entry-separator">
				<div class="vbo-oconfirm-cfield-separator <?php echo $cfsepclass; ?>">
					<?php echo JText::_($cf['name']); ?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="vbo-oconfirm-cfield-entry vbo-oconfirm-cfield-entry-checkbox">
				<div class="vbo-oconfirm-cfield-label">
					<?php echo $isreq; ?>
					<?php echo $fname; ?>
				</div>
				<div class="vbo-oconfirm-cfield-input">
					<input type="checkbox" name="vbf<?php echo $cf['id']; ?>" id="vbf-inp<?php echo $cf['id']; ?>" value="<?php echo JText::_('VBYES'); ?>"/>
				</div>
			</div>
			<?php
		}
	}
	?>
		</div>
	<?php
}
?>
		<input type="hidden" name="days" value="<?php echo $days; ?>"/>
  		<input type="hidden" name="roomsnum" value="<?php echo $roomsnum; ?>"/>
  		<input type="hidden" name="checkin" value="<?php echo $first; ?>"/>
  		<input type="hidden" name="checkout" value="<?php echo $second; ?>"/>
  		<input type="hidden" name="totdue" value="<?php echo $totdue; ?>"/>
  		<?php
  		if ($is_package === true) {
  			echo '<input type="hidden" name="pkg_id" value="'.$pkg['id'].'"/>'."\n";
  		}
  		foreach ($rooms as $num => $r) {
  			if ($is_package !== true) {
  				echo '<input type="hidden" name="prtar[]" value="'.$tars[$num][0]['id'].'"/>'."\n";
  			}
  			echo '<input type="hidden" name="priceid[]" value="'.$prices[$num].'"/>'."\n";
  			echo '<input type="hidden" name="rooms[]" value="'.$r['id'].'"/>'."\n";
  			echo '<input type="hidden" name="adults[]" value="'.$arrpeople[$num]['adults'].'"/>'."\n";
  			echo '<input type="hidden" name="children[]" value="'.$arrpeople[$num]['children'].'"/>'."\n";
  		}
  		?>
		
		<input type="hidden" name="optionals" value="<?php echo $wop; ?>"/>
		
		<?php
		if ($usedcoupon == true && is_array($coupon) && $is_package !== true) {
			?>
		<input type="hidden" name="couponcode" value="<?php echo $coupon['code']; ?>"/>
			<?php
		}
		?>
		<?php echo $tok; ?>
		<input type="hidden" name="task" value="saveorder"/>
		<br clear="all" />
		<?php

if (is_array($payments) && !(count($this->mod_booking) > 0)) {
	?>
	<div class="vbo-oconfirm-paymentopts">
		<h4 class="vbchoosepayment"><?php echo JText::_('VBCHOOSEPAYMENT'); ?></h4>
		<ul style="list-style-type: none;">
	<?php
	$non_ref_rates_found = VikBooking::findNonRefundableRates($tars);
	foreach ($payments as $pk => $pay) {
		if ($pay['hidenonrefund'] > 0 && $non_ref_rates_found) {
			continue;
		}
		$rcheck = $pk == 0 ? " checked=\"checked\"" : "";
		$saypcharge = "";
		if ($pay['charge'] > 0.00) {
			$decimals = $pay['charge'] - (int)$pay['charge'];
			if ($decimals > 0.00) {
				$okchargedisc = VikBooking::numberFormat($pay['charge']);
			} else {
				$okchargedisc = number_format($pay['charge'], 0);
			}
			$saypcharge .= " (".($pay['ch_disc'] == 1 ? "+" : "-");
			$saypcharge .= ($pay['val_pcent'] == 1 ? "<span class=\"vbcurrency\">".$currencysymb."</span> " : "")."<span class=\"vbprice\">" . $okchargedisc . "</span>" . ($pay['val_pcent'] == 1 ? "" : " <span class=\"vbcurrency\">%</span>");
			$saypcharge .= ")";
		}
		?>
			<li>
				<input type="radio" name="gpayid" value="<?php echo $pay['id']; ?>" id="gpay<?php echo $pay['id']; ?>"<?php echo $rcheck; ?>/>
				<label for="gpay<?php echo $pay['id']; ?>"><?php echo $pay['name'].$saypcharge; ?></label>
		<?php
		$pay_img_name = '';
		if (strpos($pay['file'], '.') !== false) {
			$fparts = explode('.', $pay['file']);
			$pay_img_name = array_shift($fparts);
		}
		if (!empty($pay_img_name) && file_exists(VBO_ADMIN_PATH.DS.'payments'.DS.$pay_img_name.'.png')) {
			?>
				<span class="vbo-payment-image">
					<label for="gpay<?php echo $pay['id']; ?>"><img src="<?php echo VBO_ADMIN_URI; ?>payments/<?php echo $pay_img_name; ?>.png" alt="<?php echo $pay['name']; ?>"/></label>
				</span>
			<?php
		}
		?>
			</li>
		<?php
	}
	?>
		</ul>
	<?php
	//choose deposit (Pay Entire Amount = OFF, Deposit Booking Days in Advance <= Days within Checkin, Customer Choice of Deposit = ON, Deposit Amount > 0, Deposit Non-Refundable=true, Deposit < 100 if %)
	//deposit is always disabled for booking modification
	$dep_amount = VikBooking::getAccPerCent();
	$dep_amount = VikBooking::calcDepositOverride($dep_amount, $days);
	$dep_type = VikBooking::getTypeDeposit();
	$dep_nonrefund_allowed = VikBooking::allowDepositFromRates($tars);
	if (!(count($this->mod_booking) > 0) && !VikBooking::payTotal() && VikBooking::depositAllowedDaysAdv($second) && VikBooking::depositCustomerChoice() && $dep_amount > 0 && $dep_nonrefund_allowed && ($dep_type == "fixed" || ($dep_type != "fixed" && $dep_amount < 100))) {
		$dep_amount = ($dep_amount - abs($dep_amount)) > 0.00 ? VikBooking::numberFormat($dep_amount) : $dep_amount;
		$dep_string = $dep_type == "fixed" ? $currencysymb.' '.$dep_amount : $dep_amount.'%';
		?>
		<div class="vbo-oconfirm-choosedeposit">
			<h4 class="vbchoosepayment"><?php echo JText::_('VBCHOOSEDEPOSIT'); ?></h4>
			<div class="vbo-oconfirm-choosedeposit-inner">
				<div class="vbo-oconfirm-choosedeposit-payfull">
					<input type="radio" name="nodep" value="1" id="nodepone" checked="checked" />
					<label for="nodepone"><?php echo JText::_('VBCHOOSEDEPOSITPAYFULL'); ?></label>
				</div>
				<div class="vbo-oconfirm-choosedeposit-paydeposit">
					<input type="radio" name="nodep" value="0" id="nodeptwo" />
					<label for="nodeptwo"><?php echo JText::sprintf('VBCHOOSEDEPOSITPAYDEPOF', $dep_string); ?></label>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
		<div class="vboconfirmbottom">
			<input type="submit" name="saveorder" value="<?php echo count($this->mod_booking) ? JText::_('VBOMODBOOKCONFIRMBTN') : JText::_('VBORDCONFIRM'); ?>" class="booknow"/>
			<div class="goback">
				<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&task=showprc&checkin='.$first.'&checkout='.$second.'&roomsnum='.$roomsnum.'&days='.$days.($is_package === true ? '&pkg_id='.$pkg['id'] : '').$peopleforlink.$roomoptforlink.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>"><?php echo JText::_('VBBACK'); ?></a>
			</div>
		</div>
		
		<?php
		if (!empty ($pitemid)) {
			?>
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
			<?php
		}
		?>
	</form>

	<div class="vbo-alert-container-confirm" id="vbo-alert-container-confirm" style="display: none;">
		<span class="vbo-alert-close" onclick="vbHideAlertFillin();">&times;</span><?php echo JText::_('VBOALERTFILLINALLF'); ?>
	</div>

<?php
if ($channel_disclaimer === true) {
	?>
	<script type="text/javascript">
	function vbCloseDisclaimerBox() {
		return (elem=document.getElementById("vb_ch_disclaimer_box")).parentNode.removeChild(elem);
	}
	</script>
	<div class="vb_ch_disclaimer_box" id="vb_ch_disclaimer_box">
		<div class="vb_ch_disclaimer_box_inner">
			<div class="vb_ch_disclaimer_text">
				<?php echo JText::_($vcmchanneldata['disclaimer']); ?>
			</div>
			<div class="vb_ch_disclaimer_closebtn">
				<a href="javascript: void(0);" onclick="vbCloseDisclaimerBox();"><?php echo JText::_('VBOKDISCLAIMER'); ?></a>
			</div>
		</div>
	</div>
	<?php
}
?>