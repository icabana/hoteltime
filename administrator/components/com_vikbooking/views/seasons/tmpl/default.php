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

$rows = $this->rows;
$roomsel = $this->roomsel;
$all_rooms = $this->all_rooms;
$pricesel = $this->pricesel;
$all_prices = $this->all_prices;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$session = JFactory::getSession();
$updforvcm = $session->get('vbVcmRatesUpd', '');
$updforvcm = empty($updforvcm) || !is_array($updforvcm) ? array() : $updforvcm;
$vcm_exists = VikBooking::vcmAutoUpdate();
$pidroom = VikRequest::getInt('idroom', '', 'request');
$pidprice = VikRequest::getInt('idprice', '', 'request');
	?>
	<form action="index.php?option=com_vikbooking" method="post" name="seasonsform">
		<div class="vbo-ratesoverview-roomsel-block">
			<div class="vbo-ratesoverview-roomsel-entry">
				<label for="idroom"><?php echo JText::_('VBRATESOVWROOM'); ?></label>
				<?php echo $roomsel; ?>
			</div>
		</div>
		<div class="vbo-ratesoverview-roomsel-block">
			<div class="vbo-ratesoverview-roomsel-entry">
				<label for="idprice"><?php echo JText::_('VBRATESOVWPRICETYPE'); ?></label>
				<?php echo $pricesel; ?>
			</div>
		</div>
		<div style="float: right;">
			<a class="btn btn-primary" href="index.php?option=com_vikbooking&amp;task=ratesoverv&amp;cid[]=<?php echo $pidroom; ?>#tabcal"><?php echo JText::_('VBOGOTOROVERVCAL'); ?></a>
		<?php
		if ($vcm_exists > 0 && count($updforvcm) > 0) {
			?>
			<a class="btn btn-primary hasTooltip" href="index.php?option=com_vikchannelmanager&amp;task=ratespush&amp;vbosess=1" title="<?php echo addslashes(JText::sprintf('VBRATESOVWVCMRCHANGED', $updforvcm['count'])); ?>"><i class="vboicn-notification"></i> <?php echo JText::_('VBRATESOVWVCMRCHANGEDOPEN'); ?></a>
			<?php
		}
		?>
		</div>
		<input type="hidden" name="task" value="seasons" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<br clear="all" />
	<?php
	if (empty($rows)) {
		?>
		<p class="warn"><?php echo JText::_('VBNOSEASONS'); ?></p>
		<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikbooking" />
		</form>
		<?php
	} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="30">
				<a href="index.php?option=com_vikbooking&amp;task=seasons&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidroom) ? '&idroom='.$pidroom : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
				ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "id" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="150">
				<a href="index.php?option=com_vikbooking&amp;task=seasons&amp;vborderby=spname&amp;vbordersort=<?php echo ($orderby == "spname" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidroom) ? '&idroom='.$pidroom : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "spname" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "spname" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPSHOWSEASONSPNAME').($orderby == "spname" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "spname" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=seasons&amp;vborderby=from&amp;vbordersort=<?php echo ($orderby == "from" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidroom) ? '&idroom='.$pidroom : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "from" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "from" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPSHOWSEASONSONE').($orderby == "from" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "from" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=seasons&amp;vborderby=to&amp;vbordersort=<?php echo ($orderby == "to" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidroom) ? '&idroom='.$pidroom : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "to" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "to" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPSHOWSEASONSTWO').($orderby == "to" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "to" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="130" align="center"><?php echo JText::_( 'VBPSHOWSEASONSWDAYS' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBOSEASONAFFECTEDROOMS' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBOSPTYPESPRICE' ); ?></th>
			<th class="title center" width="90" align="center"><?php echo JText::_( 'VBOISPROMOTION' ); ?></th>
			<th class="title center" width="100" align="center"><?php echo JText::_( 'VBPSHOWSEASONSTHREE' ); ?></th>
			<th class="title center" width="100" align="center">
				<a href="index.php?option=com_vikbooking&amp;task=seasons&amp;vborderby=diffcost&amp;vbordersort=<?php echo ($orderby == "diffcost" && $ordersort == "ASC" ? "DESC" : "ASC").(!empty($pidroom) ? '&idroom='.$pidroom : '').(!empty($pidprice) ? '&idprice='.$pidprice : ''); ?>" class="<?php echo ($orderby == "diffcost" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "diffcost" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPSHOWSEASONSFOUR').($orderby == "diffcost" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "diffcost" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
		</tr>
		</thead>
	<?php
	$currencysymb = VikBooking::getCurrencySymb(true);
	$nowdf = VikBooking::getDateFormat(true);
	if ($nowdf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($nowdf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	$datesep = VikBooking::getDateSeparator(true);
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$sfrom = "";
		$sto = "";
		if ($row['from'] > 0 || $row['to'] > 0) {
			$nowyear=!empty($row['year']) ? $row['year'] : date('Y');
			list($sfrom, $sto) = VikBooking::getSeasonRangeTs($row['from'], $row['to'], $nowyear);
			if (!empty($sfrom) && !empty($sto)) {
				$sfrom = date(str_replace("/", $datesep, $df), $sfrom);
				$sto = date(str_replace("/", $datesep, $df), $sto);
			}
		}
		$actwdays = explode(';', $row['wdays']);
		$wdaysmatch = array('0' => JText::_('VBSUNDAY'), '1' => JText::_('VBMONDAY'), '2' => JText::_('VBTUESDAY'), '3' => JText::_('VBWEDNESDAY'), '4' => JText::_('VBTHURSDAY'), '5' => JText::_('VBFRIDAY'), '6' => JText::_('VBSATURDAY'));
		$wdaystr = "";
		if (@count($actwdays) > 0) {
			foreach($actwdays as $awd) {
				if (strlen($awd) > 0) {
					$wdaystr .= substr($wdaysmatch[$awd], 0, 3).' ';
				}
			}
		}
		$aff_rooms = 0;
		$room_tips = array();
		$srooms = explode(',', $row['idrooms']);
		foreach ($srooms as $sroom) {
			$srid = intval(str_replace('-', '', $sroom));
			if (!empty($sroom) && $srid > 0) {
				$aff_rooms++;
				if (array_key_exists($srid, $all_rooms)) {
					$room_tips[] = $all_rooms[$srid];
				}
			}
		}
		$tpstr = '----';
		if (!empty($row['idprices'])) {
			$all_price_names = array();
			$prparts = explode(',', $row['idprices']);
			foreach ($prparts as $espriceid) {
				$tpriceid = intval(str_replace('-', '', trim($espriceid)));
				if (!empty($tpriceid) && array_key_exists($tpriceid, $all_prices)) {
					$all_price_names[] = $all_prices[$tpriceid];
				}
			}
			if (count($all_price_names) > 0) {
				if (count($all_price_names) == count($all_prices)) {
					$tpstr = JText::_('VBRATESOVWAFFALLPRICETYPE');
				} else {
					$tpstr = '<span class="vbo-smallersp">'.implode(', ', $all_price_names).'</span>';
				}
			}
		}
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editseason&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editseason&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['spname']; ?></a></td>
			<td class="center"><?php echo $sfrom; ?></td>
			<td class="center"><?php echo $sto; ?></td>
			<td class="center"><?php echo $wdaystr; ?></td>
			<td class="center"><span<?php echo count($room_tips) > 0 ? ' title="'.addslashes(implode(', ', $room_tips)).'" style="padding: 0 3px;"' : ''; ?>><?php echo $aff_rooms; ?></span></td>
			<td class="center"><?php echo $tpstr; ?></td>
			<td class="center"><?php echo ($row['promo'] == 1 ? '<i class="fa fa-check vbo-icn-img" style="color: #099909;"></i>' : '----'); ?></td>
			<td class="center"><?php echo (intval($row['type']) == 1 ? JText::_('VBPSHOWSEASONSFIVE') : JText::_('VBPSHOWSEASONSSIX')); ?></td>
			<td class="center"><?php echo (intval($row['val_pcent']) == 1 ? $currencysymb.' ' : ''); ?><?php echo $row['diffcost']; ?><?php echo (intval($row['val_pcent']) == 1 ? '' : ' %'); ?></td>
		</tr>	
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="seasons" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="idroom" value="<?php echo $pidroom; ?>" />
	<input type="hidden" name="idprice" value="<?php echo $pidprice; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
	}