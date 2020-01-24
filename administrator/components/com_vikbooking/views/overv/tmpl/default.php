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
$arrbusy = $this->arrbusy;
$wmonthsel = $this->wmonthsel;
$tsstart = $this->tsstart;
$lim0 = $this->lim0;
$navbut = $this->navbut;

$vbo_app = new VboApplication();
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
JHtml::_('script', VBO_ADMIN_URI.'resources/js_upload/jquery.stickytableheaders.min.js', false, true, false, false);
$days_labels = array(
		JText::_('VBSUN'),
		JText::_('VBMON'),
		JText::_('VBTUE'),
		JText::_('VBWED'),
		JText::_('VBTHU'),
		JText::_('VBFRI'),
		JText::_('VBSAT')
);
$currencysymb = VikBooking::getCurrencySymb(true);
$pdebug = VikRequest::getInt('e4j_debug', '', 'request');
$session = JFactory::getSession();
$show_type = $session->get('vbUnitsShowType', '');
$mnum = $session->get('vbOvwMnum', '1');
$mnum = intval($mnum);
$cookie = JFactory::getApplication()->input->cookie;
$cookie_uleft = $cookie->get('vboAovwUleft', '', 'string');
$colortags = VikBooking::loadBookingsColorTags();
//View mode - Classic or Tags
$pbmode = $session->get('vbTagsMode', 'classic');
$tags_view_supported = false;
//Rooms Units Distinctive Features
$rooms_features_map = array();
$rooms_features_bookings = array();
$rooms_bids_pools = array();
$bids_checkins = array();
$index_loop = 0;
foreach ($rows as $kr => $room) {
	if ($room['units'] <= 1) {
		$tags_view_supported = true;
	} else {
		if (!empty($room['params']) && $room['units'] <= 50) {
			//sub-room units only if room type has 50 units at most
			$room_params = json_decode($room['params'], true);
			if (is_array($room_params) && array_key_exists('features', $room_params) && @count($room_params['features']) > 0) {
				$rooms_features_map[$room['id']] = array();
				foreach ($room_params['features'] as $rind => $rfeatures) {
					foreach ($rfeatures as $fname => $fval) {
						if (strlen($fval)) {
							$rooms_features_map[$room['id']][$rind] = '#'.$rind.' - '.JText::_($fname).': '.$fval;
							break;
						}
					}
				}
				if (!(count($rooms_features_map[$room['id']]) > 0)) {
					unset($rooms_features_map[$room['id']]);
				} else {
					foreach ($rooms_features_map[$room['id']] as $rind => $indexdata) {
						$clone_room = $room;
						$clone_room['unit_index'] = (int)$rind;
						$clone_room['unit_index_str'] = $indexdata;
						array_splice($rows, ($kr + 1 + $index_loop), 0, array($clone_room));
						$index_loop++;
					}
				}
			}
		}
	}
}
//
if (!$tags_view_supported) {
	$pbmode = 'classic';
}
//
//locked (stand-by) records - new in VBO 1.9
$arrlocked = array();
if (array_key_exists('tmplock', $arrbusy)) {
	if (count($arrbusy['tmplock']) > 0) {
		$arrlocked = $arrbusy['tmplock'];
	}
	unset($arrbusy['tmplock']);
}
//
?>
<script type="text/Javascript">
function vboUnitsLeftOrBooked() {
	var set_to = jQuery('#uleftorbooked').val();
	if (jQuery('.vbo-overview-redday').length) {
		jQuery('.vbo-overview-redday').each(function(){
			jQuery(this).text(jQuery(this).attr('data-'+set_to));
		});
	}
	var nd = new Date();
	nd.setTime(nd.getTime() + (365*24*60*60*1000));
	document.cookie = "vboAovwUleft="+set_to+"; expires=" + nd.toUTCString() + "; path=/";
}
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
} else {
	jQuery.fn.tooltip = function(){};
}
</script>
<form action="index.php?option=com_vikbooking&amp;task=overv" method="post" name="vboverview" class="vbo-avov-form">
	<div class="btn-toolbar vbo-avov-toolbar" id="filter-bar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<?php echo $wmonthsel; ?>
		</div>
		<div class="btn-group pull-left">
			<select name="mnum" onchange="document.vboverview.submit();">
			<?php
			for($i = 1; $i <= 18; $i++) {
				?>
				<option value="<?php echo $i; ?>"<?php echo $i == $mnum ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOVWNUMMONTHS').' '.$i; ?></option>
				<?php
			}
			?>
			</select>
		</div>
	<?php
	if ((count($rows) * $mnum) > 10) {
		$stickyheaders_cmd_on = "jQuery('table.vboverviewtable').stickyTableHeaders({cacheHeaderHeight: true, fixedOffset: jQuery('.navbar')});";
		$stickyheaders_cmd_off = "jQuery('table.vboverviewtable').stickyTableHeaders('destroy');";
		?>
		<div class="btn-group pull-left">
			<select name="stickytheads" onchange="if (parseInt(this.value) > 0) {<?php echo $stickyheaders_cmd_on; ?>} else {<?php echo $stickyheaders_cmd_off; ?>}">
				<option value="1"><?php echo JText::_('VBOVERVIEWSTICKYTHEADON'); ?></option>
				<option value="0"><?php echo JText::_('VBOVERVIEWSTICKYTHEADOFF'); ?></option>
			</select>
		</div>
		<?php
	} else {
		$stickyheaders_cmd_on = '';
		$stickyheaders_cmd_off = '';
	}
	if ($tags_view_supported === true) {
	?>
		<div class="btn-group pull-left">
			<div class="vbo-overview-tagsorclassic">
				<select name="bmode" onchange="document.vboverview.submit();">
					<option value="classic"><?php echo JText::_('VBOAVOVWBMODECLASSIC'); ?></option>
					<option value="tags"<?php echo $pbmode == 'tags' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOAVOVWBMODETAGS'); ?></option>
				</select>
			</div>
		<?php
		if ($pbmode == 'tags' && count($colortags) > 0) {
			?>
			<div class="vbo-overview-tagslegend">
				<span class="vbo-overview-tagslegend-lbl"><?php echo JText::_('VBOAVOVWBMODETAGSLBL'); ?></span>
			<?php
			foreach ($colortags as $ctagk => $ctagv) {
				?>
				<div class="vbo-overview-legend-tag">
					<div class="vbo-colortag-circle hasTooltip" style="background-color: <?php echo $ctagv['color']; ?>;" title="<?php echo JText::_($ctagv['name']); ?>"></div>
				</div>
				<?php
			}
			?>
			</div>
			<?php
		}
		?>
		</div>
	<?php
	}
	?>
		<div class="btn-group pull-right">
			<select name="units_show_type" id="uleftorbooked" onchange="vboUnitsLeftOrBooked();"><option value="units-booked"<?php echo (!empty($cookie_uleft) && $cookie_uleft == 'units-booked' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBOVERVIEWUBOOKEDFILT'); ?></option><option value="units-left"<?php echo $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBOVERVIEWULEFTFILT'); ?></option></select>
		</div>
		<div class="btn-group pull-right vbo-avov-legend">
			<span class="vbo-overview-legend-init"><?php echo JText::_('VBOVERVIEWLEGEND'); ?></span>
			<div class="vbo-overview-legend-red">
				<span class="vbo-overview-legend-box">&nbsp;</span>
				<span class="vbo-overview-legend-title"><?php echo JText::_('VBOVERVIEWLEGRED'); ?></span>
			</div>
			<div class="vbo-overview-legend-yellow">
				<span class="vbo-overview-legend-box">&nbsp;</span>
				<span class="vbo-overview-legend-title"><?php echo JText::_('VBOVERVIEWLEGYELLOW'); ?></span>
			</div>
			<div class="vbo-overview-legend-green">
				<span class="vbo-overview-legend-box">&nbsp;</span>
				<span class="vbo-overview-legend-title"><?php echo JText::_('VBOVERVIEWLEGGREEN'); ?></span>
			</div>
			<div class="vbo-overview-legend-green vbo-overview-legend-dnd">
				<span class="vbo-overview-legend-box"><i class="vboicn-enlarge" style="margin: 1px; display: block; text-align: center;"></i></span>
				<span class="vbo-overview-legend-title"><?php echo JText::_('VBOVERVIEWLEGDND'); ?></span>
			</div>
		</div>
	</div>
</form>
<?php
$nowts = getdate($tsstart);
$curts = $nowts;
for ($mind = 1; $mind <= $mnum; $mind++) {
?>
<table class="vboverviewtable vbo-roverview-table">
	<thead>
		<tr class="vboverviewtablerowone">
			<th class="bluedays vbo-overview-month"><?php echo VikBooking::sayMonth($curts['mon'])." ".$curts['year']; ?></th>
		<?php
		$moncurts = $curts;
		$mon = $moncurts['mon'];
		while ($moncurts['mon'] == $mon) {
			echo '<th class="bluedays">'.$moncurts['mday'].'<br/>'.$days_labels[$moncurts['wday']].'</th>';
			$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
		}
		?>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ($rows as $room) {
		$moncurts = $curts;
		$mon = $moncurts['mon'];
		$room_tags_view = $pbmode == 'tags' && $tags_view_supported === true && $room['units'] <= 1 ? true : false;
		$is_subunit = (array_key_exists('unit_index', $room));
		echo '<tr class="vboverviewtablerow'.($is_subunit ? ' vboverviewtablerow-subunit' : '').'"'.($is_subunit ? ' data-subroomid="'.$room['id'].'-'.$room['unit_index'].'"' : '').'>'."\n";
		if ($is_subunit) {
			echo '<td class="roomname subroomname" data-roomid="-'.$room['id'].'"><span class="vbo-overview-subroomunits"><i class="fa fa-bed"></i></span><span class="vbo-overview-subroomname">'.$room['unit_index_str'].'</span></td>';
		} else {
			echo '<td class="roomname" data-roomid="'.$room['id'].'"><span class="vbo-overview-roomunits">'.$room['units'].'</span><span class="vbo-overview-roomname">'.$room['name'].'</span>'.(array_key_exists($room['id'], $rooms_features_map) ? '<span class="vbo-overview-subroom-toggle"><i class="fa fa-chevron-down hasTooltip" style="margin: 0;" title="'.addslashes(JText::_('VBOVERVIEWTOGGLESUBROOM')).'"></i></span>' : '').'</td>';
		}
		$room_bids_pool = array();
		while ($moncurts['mon'] == $mon) {
			$dclass = !array_key_exists('unit_index', $room) ? "notbusy" : "subnotbusy";
			$is_checkin = false;
			$lastbidcheckout = null;
			$dalt = "";
			$bid = "";
			$bids_pool = array();
			$totfound = 0;
			$cur_day_key = date('Y-m-d', $moncurts[0]);
			if (@is_array($arrbusy[$room['id']]) && !array_key_exists('unit_index', $room)) {
				foreach ($arrbusy[$room['id']] as $b) {
					$tmpone = getdate($b['checkin']);
					$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
					$ritts = strtotime($rit);
					$tmptwo = getdate($b['checkout']);
					$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
					$conts = strtotime($con);
					if ($moncurts[0] >= $ritts && $moncurts[0] < $conts) {
						$dclass = "busy";
						$bid = $b['idorder'];
						if (!in_array($bid, $bids_pool)) {
							$bids_pool[] = '-'.$bid.'-';
						}
						if (array_key_exists($room['id'], $rooms_features_map)) {
							if (!array_key_exists($cur_day_key, $room_bids_pool)) {
								$room_bids_pool[$cur_day_key] = array();
							}
							$room_bids_pool[$cur_day_key][] = (int)$bid;
						}
						if ($moncurts[0] == $ritts) {
							$dalt = JText::_('VBPICKUPAT')." ".date('H:i', $b['checkin']);
							$is_checkin = true;
							$lastbidcheckout = $b['checkout'];
							$bids_checkins[$bid] = $cur_day_key;
						} elseif ($moncurts[0] == $conts) {
							$dalt = JText::_('VBRELEASEAT')." ".date('H:i', $b['checkout']);
						}
						$totfound++;
					}
				}
			}
			//locked (stand-by) records - new in VBO 1.9
			if ($room_tags_view === true && array_key_exists($room['id'], $arrlocked) && count($arrlocked[$room['id']]) > 0 && !array_key_exists('unit_index', $room)) {
				foreach ($arrlocked[$room['id']] as $l) {
					$tmpone = getdate($l['checkin']);
					$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
					$ritts = strtotime($rit);
					$tmptwo = getdate($l['checkout']);
					$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
					$conts = strtotime($con);
					if ($moncurts[0] >= $ritts && $moncurts[0] < $conts) {
						$dclass = $dclass == "notbusy" ? "busytmplock" : "busy busytmplock";
						$bid = $l['idorder'];
						if (!in_array($bid, $bids_pool)) {
							if (count($bids_pool) > 0) {
								array_unshift($bids_pool, '-'.$bid.'-');
							} else {
								$bids_pool[] = '-'.$bid.'-';
							}
						}
						if ($moncurts[0] == $ritts) {
							$dalt = JText::_('VBPICKUPAT')." ".date('H:i', $l['checkin']);
						} elseif ($moncurts[0] == $conts) {
							$dalt = JText::_('VBRELEASEAT')." ".date('H:i', $l['checkout']);
						}
						$totfound++;
					}
				}
			}
			//
			$useday = ($moncurts['mday'] < 10 ? "0".$moncurts['mday'] : $moncurts['mday']);
			$dclass .= ($totfound < $room['units'] && $totfound > 0 ? ' vbo-partially' : '');
			$dstyle = '';
			$astyle = '';
			if ($room_tags_view === true && $totfound > 0) {
				$last_bid = intval(str_replace('-', '', $bids_pool[(count($bids_pool) - 1)]));
				$binfo = VikBooking::getBookingInfoFromID($last_bid);
				if (count($binfo) > 0) {
					$bcolortag = VikBooking::applyBookingColorTag($binfo);
					if (count($bcolortag) > 0) {
						$bcolortag['name'] = JText::_($bcolortag['name']);
						$dstyle = " style=\"background-color: ".$bcolortag['color']."; color: ".(array_key_exists('fontcolor', $bcolortag) ? $bcolortag['fontcolor'] : '#ffffff').";\" data-lastbid=\"".$last_bid."\"";
						$astyle = " style=\"color: ".(array_key_exists('fontcolor', $bcolortag) ? $bcolortag['fontcolor'] : '#ffffff').";\"";
						$dclass .= ' vbo-hascolortag';
					}
				}
			}
			if (array_key_exists('unit_index', $room) && array_key_exists($room['id'], $rooms_features_bookings) && array_key_exists($cur_day_key, $rooms_bids_pools[$room['id']]) && array_key_exists($room['unit_index'], $rooms_features_bookings[$room['id']])) {
				foreach ($rooms_bids_pools[$room['id']][$cur_day_key] as $bid) {
					$bid = intval(str_replace('-', '', $bid));
					if (in_array($bid, $rooms_features_bookings[$room['id']][$room['unit_index']])) {
						$room['units'] = 1;
						$totfound = 1;
						$dclass = "subroom-busy";
						$is_checkin = isset($bids_checkins[$bid]) && $bids_checkins[$bid] == $cur_day_key ? true : $is_checkin;
						break;
					}
				}
			}
			$write_units = $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ($room['units'] - $totfound) : $totfound;
			if ($totfound == 1) {
				$write_units = strpos($dclass, "subroom-busy") !== false ? '&bull;' : $write_units;
				$stopdrag = ($mind == $mnum && !is_null($lastbidcheckout) && (int)date('n', $lastbidcheckout) != (int)$mon);
				$dclass .= $is_checkin === true ? ' vbo-checkinday' : '';
				$dlnk = "<a href=\"index.php?option=com_vikbooking&task=editbusy&cid[]=".$bid."&goto=overv\" class=\"".(strpos($dclass, "subroom-busy") === false ? 'vbo-overview-redday' : 'vbo-overview-subredday')."\" data-units-booked=\"".$totfound."\" data-units-left=\"".($room['units'] - $totfound)."\"".$astyle.(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$write_units."</a>";
				$cal = "<td align=\"center\" class=\"".$dclass."\"".$dstyle." data-day=\"".$cur_day_key."\" data-bids=\"".(strpos($dclass, "subroom-busy") !== false ? '-'.$bid.'-' : implode(',', $bids_pool))."\">".($is_checkin === true && !$stopdrag ? '<span class="vbo-draggable-sp" draggable="true">'.$dlnk.'</span>' : $dlnk)."</td>\n";
			} elseif ($totfound > 1) {
				$dlnk = "<a href=\"index.php?option=com_vikbooking&task=choosebusy&idroom=".$room['id']."&ts=".$moncurts[0]."&goto=overv\" class=\"vbo-overview-redday\" data-units-booked=\"".$totfound."\" data-units-left=\"".($room['units'] - $totfound)."\"".$astyle.">".$write_units."</a>";
				$cal = "<td align=\"center\" class=\"".$dclass."\"".$dstyle." data-day=\"".$cur_day_key."\" data-bids=\"".implode(',', $bids_pool)."\">".$dlnk."</td>\n";
			} else {
				$dlnk = $useday;
				$cal = "<td align=\"center\" class=\"".$dclass."\" data-day=\"".$cur_day_key."\" data-bids=\"\">&nbsp;</td>\n";
			}
			echo $cal;
			$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
		}
		if (array_key_exists($room['id'], $rooms_features_map) && !array_key_exists('unit_index', $room) && count($room_bids_pool) > 0) {
			//load bookings for distinctive features when parsing the parent $room array
			$room_indexes_bids = VikBooking::loadRoomIndexesBookings($room['id'], $room_bids_pool);
			if (count($room_indexes_bids) > 0) {
				$rooms_features_bookings[$room['id']] = $room_indexes_bids;
				$rooms_bids_pools[$room['id']] = $room_bids_pool;
			}
			//
		}
		echo '</tr>'."\n";
	}
	?>
	</tbody>
</table>
<?php echo ($mind + 1) <= $mnum ? '<br/>' : ''; ?>
<?php
	$curts = getdate(mktime(0, 0, 0, ($nowts['mon'] + $mind), $nowts['mday'], $nowts['year']));
}
//Prepare modal
?>
<script type="text/javascript">
var hasNewBooking = false;
var last_room_click = '',
	last_date_click = '';
function vboJModalHideCallback() {
	if (hasNewBooking === true) {
		location.reload();
	}
}
function vboCallOpenJModal(identif, baseurl) {
	if (last_room_click && last_room_click.length) {
		baseurl += '&cid[]='+last_room_click;
		last_room_click = '';
	}
	if (last_date_click && last_date_click.length) {
		baseurl += '&checkin='+last_date_click;
		last_date_click = '';
	}
	vboOpenJModal(identif, baseurl);
}
</script>
<?php
echo $vbo_app->getJmodalScript('', 'vboJModalHideCallback();', '');
echo $vbo_app->getJmodalHtml('vbo-new-res', JText::_('VBOSHOWQUICKRES'));
//end Prepare modal
?>
<div class="vbo-ovrv-flt-butn" onclick="vboCallOpenJModal('vbo-new-res', 'index.php?option=com_vikbooking&task=calendar&overv=1&tmpl=component');"><span><i class="vboicn-user-plus"></i> <?php echo JText::_('VBOSHOWQUICKRES'); ?></span></div>
<div class="vbo-info-overlay-block">
	<div class="vbo-info-overlay-loading-dnd">
		<span class="vbo-loading-dnd-head"></span>
		<span class="vbo-loading-dnd-body"></span>
		<span class="vbo-loading-dnd-footer"><?php echo JText::_('VIKLOADING'); ?></span>
		<span id="vbo-dnd-response" class="vbo-loading-dnd-response"></span>
		<canvas id="vbo-dnd-canvas-success" height="250"></canvas>
	</div>
</div>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="overv" />
	<input type="hidden" name="month" value="<?php echo $tsstart; ?>" />
	<input type="hidden" name="mnum" value="<?php echo $mnum; ?>" />
	<?php echo '<br/>'.$navbut; ?>
</form>
<script type="text/Javascript">
var hovtimer;
var hovtip = false;
var isdragging = false;
var vboMessages = {
	"notEnoughCells": "<?php echo addslashes(JText::_('VBOVWDNDERRNOTENCELLS')); ?>",
	"movingBookingId": "<?php echo addslashes(JText::_('VBOVWDNDMOVINGBID')); ?>",
	"switchRoomTo": "<?php echo addslashes(JText::_('VBOVWDNDMOVINGROOM')); ?>",
	"switchDatesTo": "<?php echo addslashes(JText::_('VBOVWDNDMOVINGDATES')); ?>",
	"cancelText": "<?php echo addslashes(JText::_('VBANNULLA')); ?>",
	"loadingTip": "<?php echo addslashes(JText::_('VIKLOADING')); ?>",
	"numRooms": "<?php echo addslashes(JText::_('VBEDITORDERROOMSNUM')); ?>",
	"numAdults": "<?php echo addslashes(JText::_('VBEDITORDERADULTS')); ?>",
	"numNights": "<?php echo addslashes(JText::_('VBDAYS')); ?>",
	"checkinLbl": "<?php echo addslashes(JText::_('VBPICKUPAT')); ?>",
	"checkoutLbl": "<?php echo addslashes(JText::_('VBRELEASEAT')); ?>",
	"numChildren": "<?php echo addslashes(JText::_('VBEDITORDERCHILDREN')); ?>",
	"totalAmount": "<?php echo addslashes(JText::_('VBEDITORDERNINE')); ?>",
	"totalPaid": "<?php echo addslashes(JText::_('VBPEDITBUSYTOTPAID')); ?>",
	"assignUnit": "<?php echo addslashes(JText::_('VBOFEATASSIGNUNIT')); ?>",
	"currencySymb": "<?php echo $currencysymb; ?>"
};
var debug_mode = '<?php echo $pdebug; ?>';
var bctags_count = <?php echo count($colortags); ?>;
var bctags_pool = <?php echo json_encode($colortags); ?>;
<?php
if (count($colortags) > 0) {
	$bctags_tip = '<div class=\"vbo-overview-tip-bctag-subtip-inner\">';
	foreach ($colortags as $ctagk => $ctagv) {
		$bctags_tip .= '<div class=\"vbo-overview-tip-bctag-subtip-circle hasTooltip\" data-ctagkey=\"'.$ctagk.'\" data-ctagcolor=\"'.$ctagv['color'].'\" title=\"'.addslashes(JText::_($ctagv['name'])).'\"><div class=\"vbo-overview-tip-bctag-subtip-circlecont\" style=\"background-color: '.$ctagv['color'].';\"></div></div>';
	}
	$bctags_tip .= '</div>';
	?>
var bctags_tip = "<?php echo $bctags_tip; ?>";
	<?php
}
?>
</script>
<script type="text/Javascript">
/**
 * Render the units view mode
 */
vboUnitsLeftOrBooked();

/* DnD global vars */
var cellspool = [];
var newcellspool = [];
/* DnD Count the number of consecutive cells for this booking */
function countBookingCells(cellobj, bidstart, roomid) {
	var totnights = 1;
	var loop = true;
	var cellelem = cellobj;
	cellspool.push(cellelem);
	while(loop === true) {
		var next = cellelem.next('td');
		if (next === undefined || !next.length) {
			//attempt to go to the month after
			var partable = cellelem.closest('tr').closest('table').nextAll('table.vboverviewtable').first();
			var nextmonth = false;
			if (partable !== undefined && partable.length) {
				partable.find('tr.vboverviewtablerow').each(function() {
					var roomexists = jQuery(this).find('td').first();
					if (roomexists !== undefined && roomexists.length) {
						if (roomexists.attr('data-roomid') == roomid) {
							nextmonth = true;
							next = roomexists.next('td');
							return true;
						}
					}
				});
			}
			if (nextmonth === false) {
				//nothing was found in the month after
				loop = false;
				break;
			}
		}
		cellelem = next;
		var nextbids = cellelem.attr('data-bids');
		if (nextbids.length && nextbids.indexOf(bidstart) >= 0) {
			cellspool.push(cellelem);
			totnights++;
		} else {
			loop = false;
			break;
		}
	}
	return totnights;
}
/* DnD Count the number of consecutive free date-cells for moving the booking onto the landing cell selected for drop */
function countCellFreeNights(landobj, roomid, totnights) {
	var freenights = 1;
	var loop = true;
	var cellelem = landobj;
	newcellspool.push(cellelem);
	while(loop === true) {
		var next = cellelem.next('td');
		if (next === undefined || !next.length) {
			//attempt to go to the month after
			var partable = cellelem.closest('tr').closest('table').nextAll('table.vboverviewtable').first();
			var nextmonth = false;
			if (partable !== undefined && partable.length) {
				partable.find('tr.vboverviewtablerow').each(function() {
					var roomexists = jQuery(this).find('td').first();
					if (roomexists !== undefined && roomexists.length) {
						if (roomexists.attr('data-roomid') == roomid) {
							nextmonth = true;
							next = roomexists.next('td');
							return true;
						}
					}
				});
			}
			if (nextmonth === false) {
				//nothing was found in the month after
				loop = false;
				break;
			}
		}
		cellelem = next;
		if (!cellelem.hasClass('busy') || (cellelem.hasClass('busy') && cellelem.hasClass('vbo-partially'))) {
			//bookings of 1 night can stop here because there is availability for one day
			if (parseInt(totnights) === 1) {
				loop = false;
				break;
			}
			//
			newcellspool.push(cellelem);
			freenights++;
			if (freenights >= totnights) {
				loop = false;
				break;
			}
		} else {
			loop = false;
			break;
		}
	}
	return freenights;
}

/* DnD function to perform the ajax request of the booking modification */
function doAlterBooking(bid, roomid, landrid) {
	var nowdatefrom = jQuery(newcellspool[0]).attr('data-day');
	var nowdateto = jQuery(newcellspool[(newcellspool.length -1)]).attr('data-day');
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikbooking", task: "alterbooking", tmpl: "component", idorder: bid, oldidroom: roomid, idroom: landrid, fromdate: nowdatefrom, todate: nowdateto, e4j_debug: debug_mode }
	}).done(function(res) {
		if (res.indexOf('e4j.error') >= 0 ) {
			console.log('doAlterBooking-- Booking ID: '+bid+' - Old Room ID: '+roomid+' - New Room ID: '+landrid+' - New Date From: '+nowdatefrom+' - New Date To: '+nowdateto);
			console.log(res);
			alert(res.replace("e4j.error.", ""));
			//restore the old cells
			jQuery('td.vbo-dragging-cells-tmp').removeClass('vbo-dragging-cells-tmp');
			jQuery('td.vbo-dragged-cells-tmp').removeClass('vbo-dragged-cells-tmp');
			//
			jQuery(".vbo-info-overlay-block").hide();
		} else {
			//move to the new cells and if there are already bookings on those dates, reload the page without moving the blocks
			var obj_res = JSON.parse(res);
			var mustReload = obj_res.esit < 1 ? true : false;
			//always force reload if booking was made for more than one room
			var samebidcells = jQuery("td.vbo-checkinday[data-bids='"+bid+"']");
			if (samebidcells.length > 1) {
				mustReload = true;
			}
			//
			jQuery(newcellspool).each(function(k, v) {
				var cur_units_booked = parseInt(jQuery(cellspool[k]).find('a').attr('data-units-booked'));
				if ((v.hasClass('busy') && v.attr('data-bids') != jQuery(cellspool[k]).attr('data-bids')) || isNaN(cur_units_booked) || cur_units_booked > 1) {
					mustReload = true;
					return false;
				}
			});
			if (mustReload !== true) {
				/* switch cells */
				var switchmap = [];
				jQuery(cellspool).each(function(k, v) {
					var switchcell = {
						"hcont": v.html(),
						"cl": v.attr('class'),
						"bids": v.attr('data-bids'),
						"tit": v.attr('title')
					};
					switchmap[k] = switchcell;
					v.html("").attr('class', 'notbusy').attr('data-bids', '').attr('title', '');
				});
				jQuery(switchmap).each(function(k, v) {
					jQuery(newcellspool[k]).html(v.hcont).attr('class', v.cl).attr('data-bids', v.bids).attr('title', v.tit);
					// re-bind hover event
					jQuery(newcellspool[k]).hover(function() {
						registerHoveringTooltip(this);
					}, unregisterHoveringTooltip);
					//
				});
				jQuery('td.vbo-dragging-cells-tmp').removeClass('vbo-dragging-cells-tmp');
				jQuery('td.vbo-dragged-cells-tmp').removeClass('vbo-dragged-cells-tmp');
			}
			if (obj_res.esit < 1) {
				//some errors occurred after executing certain functions
				if (obj_res.message.length) {
					alert(obj_res.message);
				}
				document.location.href='index.php?option=com_vikbooking&task=overv';
			} else {
				finalizeDndUpdate(mustReload, obj_res);
			}
		}
	}).fail(function() { 
		alert("Request Failed");
		//restore the old cells
		jQuery('td.vbo-dragging-cells-tmp').removeClass('vbo-dragging-cells-tmp');
		jQuery('td.vbo-dragged-cells-tmp').removeClass('vbo-dragged-cells-tmp');
		//
		jQuery(".vbo-info-overlay-block").hide();
	});
}

/* DnD function to animate a success checkmark. The function may refresh the page once complete as this could be launched when there are multiple units for the rooms */
function finalizeDndUpdate(mustReload, obj_res) {
	jQuery('#vbo-dnd-response').html(obj_res.message+' '+obj_res.vcm);
	jQuery('.vbo-loading-dnd-footer').hide();
	var start = 100;
	var mid = 145;
	var end = 250;
	var width = 22;
	var leftX = start;
	var leftY = start;
	var rightX = mid - (width / 2.7);
	var rightY = mid + (width / 2.7);
	var animationSpeed = 20;
	var closingdelay = 700;
	var ctx = document.getElementById('vbo-dnd-canvas-success').getContext('2d');
	ctx.lineWidth = width;
	ctx.strokeStyle = 'rgba(0, 150, 0, 1)';
	for (var i = start; i < mid; i++) {
		var drawLeft = window.setTimeout(function () {
			ctx.beginPath();
			ctx.moveTo(start, start);
			ctx.lineTo(leftX, leftY);
			ctx.stroke();
			leftX++;
			leftY++;
		}, 1 + (i * animationSpeed) / 3);
	}
	for (var i = mid; i < end; i++) {
		var drawRight = window.setTimeout(function () {
			ctx.beginPath();
			ctx.moveTo(leftX, leftY);
			ctx.lineTo(rightX, rightY);
			ctx.stroke();
			rightX++;
			rightY--;
		}, 1 + (i * animationSpeed) / 3);
	}
	//hide modal window
	window.setTimeout(function () {
		if (obj_res.vcm.length) {
			var vcmbtn = '<br clear="all"/><button type="button" class="btn btn-danger" onclick="'+(mustReload === true ? 'document.location.href=\'index.php?option=com_vikbooking&task=overv\'' : 'closeEsitDialog();')+'">'+vboMessages.cancelText+'</button>';
			jQuery('#vbo-dnd-response').append(vcmbtn);
		} else {
			if (mustReload === true) {
				document.location.href='index.php?option=com_vikbooking&task=overv';
			} else {
				jQuery('.vbo-info-overlay-block').fadeOut(400, function(){
					//clear/reset canvas in case of previous drawing and response text
					ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
					jQuery('#vbo-dnd-response').html("");
					//
				});
			}
		}
	}, closingdelay + (i * animationSpeed) / 3);
	//
}

/* DnD function that can be called by those with VCM that have disabled the automated updates. Simply closes the modal window */
function closeEsitDialog() {
	jQuery('.vbo-info-overlay-block').fadeOut(400, function(){
		//clear/reset canvas in case of previous drawing and response text
		var ctx = document.getElementById('vbo-dnd-canvas-success').getContext('2d');
		ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
		jQuery('#vbo-dnd-response').html("");
		//
	});
}

/* Hover Tooltip functions */
function registerHoveringTooltip(that) {
	if (hovtip) {
		return false;
	}
	if (hovtimer) {
		clearTimeout(hovtimer);
		hovtimer = null;
	}
	var elem = jQuery(that);
	var cellheight = elem.outerHeight();
	var celldata = new Array();
	if (elem.hasClass('subroom-busy')) {
		celldata.push(elem.parent('tr').attr('data-subroomid'));
		celldata.push(elem.attr('data-day'));
	}
	hovtimer = setTimeout(function() {
		if (isdragging) {
			// prevent tooltip from popping up if dragging
			unregisterHoveringTooltip();
			return;
		}
		hovtip = true;
		jQuery(
			"<div class=\"vbo-overview-tipblock\">"+
				"<div class=\"vbo-overview-tipinner\"><span class=\"vbo-overview-tiploading\">"+vboMessages.loadingTip+"</span></div>"+
			"</div>"
		).appendTo(elem);
		jQuery(".vbo-overview-tipblock").css("bottom", "+="+cellheight);
		loadTooltipBookings(elem.attr('data-bids'), celldata);
	}, 900);
}
function unregisterHoveringTooltip() {
	clearTimeout(hovtimer);
	hovtimer = null;
}
function adjustHoveringTooltip() {
	setTimeout(function() {
		var difflim = 35;
		var otop = jQuery(".vbo-overview-tipblock").offset().top;
		if (otop < difflim) {
			jQuery(".vbo-overview-tipblock").css("bottom", "-="+(difflim - otop));
		}
	}, 100);
}
function hideVboTooltip() {
	jQuery('.vbo-overview-tipblock').remove();
	hovtip = false;
}
function loadTooltipBookings(bids, celldata) {
	if (!bids || bids === undefined || !bids.length) {
		hideVboTooltip();
		return false;
	}
	var subroomdata = celldata.length ? celldata[0] : '';
	//ajax request
	var jqxhr = jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { option: "com_vikbooking", task: "getbookingsinfo", tmpl: "component", idorders: bids, subroom: subroomdata }
	}).done(function(res) {
		if (res.indexOf('e4j.error') >= 0 ) {
			console.log(res);
			alert(res.replace("e4j.error.", ""));
			//restore
			hideVboTooltip();
			//
		} else {
			var obj_res = JSON.parse(res);
			jQuery('.vbo-overview-tiploading').remove();
			var container = jQuery('.vbo-overview-tipinner');
			jQuery(obj_res).each(function(k, v) {
				var bcont = "<div class=\"vbo-overview-tip-bookingcont\">";
				bcont += "<div class=\"vbo-overview-tip-bookingcont-left\">";
				bcont += "<div class=\"vbo-overview-tip-bid\"><span class=\"vbo-overview-tip-lbl\">ID</span><span class=\"vbo-overview-tip-cnt\">"+v.id+"</span></div>";
				bcont += "<div class=\"vbo-overview-tip-bstatus\"><span class=\"vbo-overview-tip-lbl\"><?php echo addslashes(JText::_('VBPVIEWORDERSEIGHT')); ?></span><span class=\"vbo-overview-tip-cnt\"><div class=\"label "+(v.status == 'confirmed' ? 'label-success' : 'label-warning')+"\">"+v.status_lbl+"</div></span></div>";
				bcont += "<div class=\"vbo-overview-tip-bdate\"><span class=\"vbo-overview-tip-lbl\"><?php echo addslashes(JText::_('VBPVIEWORDERSONE')); ?></span><span class=\"vbo-overview-tip-cnt\"><a href=\"index.php?option=com_vikbooking&task=editbusy&goto=overv&cid[]="+v.id+"\">"+v.ts+"</a></span></div>";
				if (bctags_count > 0) {
					var bctag_title = '';
					var bctag_color = '#ffffff';
					if (v.colortag.hasOwnProperty('color')) {
						bctag_color = v.colortag.color;
						bctag_title = v.colortag.name;
					}
					bcont += "<div class=\"vbo-overview-tip-bctag-wrap\"><div class=\"vbo-overview-tip-bctag\" data-bid=\""+v.id+"\" data-ctagcolor=\""+bctag_color+"\" style=\"background-color: "+bctag_color+"; color: "+v.colortag.fontcolor+";\" title=\""+bctag_title+"\"><i class=\"vboicn-price-tags\"></i></div></div>";
				}
				bcont += "</div>";
				bcont += "<div class=\"vbo-overview-tip-bookingcont-right\">";
				bcont += "<div class=\"vbo-overview-tip-bcustomer\"><span class=\"vbo-overview-tip-lbl\"><?php echo addslashes(JText::_('VBOCUSTOMER')); ?></span><span class=\"vbo-overview-tip-cnt\">"+v.cinfo+"</span></div>";
				if (v.roomsnum > 1) {
					bcont += "<div class=\"vbo-overview-tip-brooms\"><span class=\"vbo-overview-tip-lbl\">"+vboMessages.numRooms+"</span><span class=\"vbo-overview-tip-cnt\">"+v.room_names+"</span></div>";
				}
				bcont += "<div class=\"vbo-overview-tip-bguests\"><span class=\"vbo-overview-tip-lbl\">"+vboMessages.numNights+"</span><span class=\"vbo-overview-tip-cnt hasTooltip\" title=\""+vboMessages.checkinLbl+" "+v.checkin+" - "+vboMessages.checkoutLbl+" "+v.checkout+"\">"+v.days+", "+vboMessages.numAdults+": "+v.tot_adults+(v.tot_children > 0 ? ", "+vboMessages.numChildren+": "+v.tot_children : "")+"</span></div>";
				if (v.hasOwnProperty('rindexes')) {
					for (var rindexk in v.rindexes) {
						if (v.rindexes.hasOwnProperty(rindexk)) {
							bcont += "<div class=\"vbo-overview-tip-brindexes\"><span class=\"vbo-overview-tip-lbl\">"+rindexk+"</span><span class=\"vbo-overview-tip-cnt\">"+v.rindexes[rindexk]+"</span></div>";
						}
					}
				}
				if (v.hasOwnProperty('channelimg')) {
					bcont += "<div class=\"vbo-overview-tip-bprovenience\"><span class=\"vbo-overview-tip-lbl\"><?php echo addslashes(JText::_('VBPVIEWORDERCHANNEL')); ?></span><span class=\"vbo-overview-tip-cnt\">"+v.channelimg+"</span></div>";
				}
				if (v.hasOwnProperty('optindexes') && celldata.length) {
					var subroomids = celldata[0].split('-');
					bcont += "<div class=\"vbo-overview-tip-optindexes\"><span class=\"vbo-overview-tip-lbl\"> </span><span class=\"vbo-overview-tip-cnt\"><select onchange=\"vboMoveSubunit('"+v.id+"', '"+subroomids[0]+"', '"+subroomids[1]+"', this.value, '"+celldata[1]+"');\">"+v.optindexes+"</select></span></div>";
				}
				bcont += "<div class=\"vbo-overview-tip-bookingcont-total\">";
				bcont += "<div class=\"vbo-overview-tip-btot\"><span class=\"vbo-overview-tip-lbl\">"+vboMessages.totalAmount+"</span><span class=\"vbo-overview-tip-cnt\">"+vboMessages.currencySymb+" "+v.format_tot+"</span></div>";
				if (v.totpaid > 0.00) {
					bcont += "<div class=\"vbo-overview-tip-btot\"><span class=\"vbo-overview-tip-lbl\">"+vboMessages.totalPaid+"</span><span class=\"vbo-overview-tip-cnt\">"+vboMessages.currencySymb+" "+v.format_totpaid+"</span></div>";
				}
				var getnotes = v.adminnotes;
				if (getnotes !== null && getnotes.length) {
					bcont += "<div class=\"vbo-overview-tip-notes\"><span class=\"vbo-overview-tip-lbl\"><span class=\"vbo-overview-tip-notes-inner\"><i class=\"vboicn-info hasTooltip\" title=\""+getnotes+"\"></i></span></span></div>";
				}
				bcont += "</div>";
				bcont += "</div>";
				bcont += "</div>";
				container.append(bcont);
			});
			// adjust the position so that it won't go under other contents
			adjustHoveringTooltip()
			//
			jQuery(".hasTooltip").tooltip();
		}
	}).fail(function() { 
		alert("Request Failed");
		//restore
		hideVboTooltip();
		//
	});
	//
}

/**
 * Move a subunit group of cells to another subroom row. Triggered from the Hover Tooltip
 */
function vboMoveSubunit(bid, rid, old_rindex, new_rindex, dday) {
	if (confirm(vboMessages.assignUnit+new_rindex.replace('#', '')+'?')) {
		// check if movement can be made
		var cur_tr = jQuery('tr.vboverviewtablerow-subunit[data-subroomid="'+rid+'-'+old_rindex+'"]');
		if (!cur_tr || !cur_tr.length) {
			console.error('could not find the parent row of the subunit cells');
			return false;
		}
		var maincell = cur_tr.find('td.subroom-busy[data-day="'+dday+'"]');
		if (!maincell || !maincell.length) {
			console.error('could not find the main cell of the subunit to move');
			return false;
		}
		var dest_tr = jQuery('tr.vboverviewtablerow-subunit[data-subroomid="'+rid+'-'+new_rindex+'"]');
		if (!dest_tr || !dest_tr.length) {
			console.error('could not find the destination row for the subunit cells');
			return false;
		}
		var targetbids = maincell.attr('data-bids');
		if (targetbids.indexOf('-'+bid+'-') < 0) {
			console.error('given bid does not match with cell bids', bid, targetbids);
			return false;
		}
		var firstcell = maincell;
		var loop = true;
		while (loop === true) {
			var prevsib = firstcell.prev();
			if (prevsib && prevsib.length && prevsib.attr('data-bids').length && prevsib.attr('data-bids').indexOf('-'+bid+'-') >= 0) {
				firstcell = prevsib;
			} else {
				loop = false;
			}
		}
		dday = firstcell.attr('data-day');
		var destcell = dest_tr.find('td.subnotbusy[data-day="'+dday+'"]');
		if (!destcell || !destcell.length) {
			console.error('could not find the first free destination cell');
			return false;
		}
		// check if all dates are free before making movements. Redundant, but useful.
		var freedates = true;
		var copyfirstcell = firstcell;
		var copydday = dday;
		loop = true;
		while (loop === true) {
			var nextsib = copyfirstcell.next();
			if (nextsib && nextsib.length && nextsib.attr('data-bids').length && nextsib.attr('data-bids').indexOf('-'+bid+'-') >= 0) {
				copyfirstcell = nextsib;
				copydday = copyfirstcell.attr('data-day');
				var copydestcell = dest_tr.find('td.subnotbusy[data-day="'+copydday+'"]');
				if (!copydestcell || !copydestcell.length) {
					console.error('could not find the next free destination cell for '+copydday);
					freedates = false;
					loop = false;
				}
			} else {
				loop = false;
			}
		}
		if (freedates === false) {
			return false;
		}
		// ajax request
		jQuery('.vbo-overview-tipblock').css('opacity', '0.6');
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "switchRoomIndex", tmpl: "component", bid: bid, rid: rid, old_rindex: old_rindex, new_rindex: new_rindex }
		}).done(function(res) {
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				// restore loading opacity in container
				jQuery('.vbo-overview-tipblock').css('opacity', '1');
			} else {
				// hide the tooltip
				hideVboTooltip();
				// move the cells
				loop = true;
				while (loop === true) {
					destcell.attr('class', firstcell.attr('class'))
						.attr('data-bids', firstcell.attr('data-bids'))
						.html(firstcell.html());
					// re-bind hover event
					jQuery(destcell).hover(function() {
						registerHoveringTooltip(this);
					}, unregisterHoveringTooltip);
					firstcell.attr('class', 'subnotbusy')
						.attr('data-bids', '')
						.html('&nbsp;');
					var nextsib = firstcell.next();
					if (nextsib && nextsib.length && nextsib.attr('data-bids').length && nextsib.attr('data-bids').indexOf('-'+bid+'-') >= 0) {
						firstcell = nextsib;
						dday = firstcell.attr('data-day');
						destcell = dest_tr.find('td.subnotbusy[data-day="'+dday+'"]');
						if (!destcell || !destcell.length) {
							console.error('could not find the next free destination cell');
							loop = false;
						}
					} else {
						loop = false;
					}
				}
			}
		}).fail(function() {
			alert("Request Failed");
			// restore loading opacity in container
			jQuery('.vbo-overview-tipblock').css('opacity', '1');
		});
		return true;
	}
	return false;
}

/**
 * jQuery ready state
 */
jQuery(document).ready(function() {
	/* calculate padding to increase draggable area and avoid "display: table" for the parent TD and "display: table-cell" for the draggable SPAN with 100% width and height, middle aligns */
	jQuery("td.vbo-checkinday span").each(function(k, v){
		var parentheight = jQuery(this).closest('td').height();
		var spheight = jQuery(this).height();
		var padsp = Math.floor((parentheight - spheight) / 2);
		jQuery(this).css({"padding": padsp+"px 0"});
	});
	/* end padding calculation */
	/* DnD Start */
	/* DnD Event dragstart */
	jQuery(document.body).on("dragstart", "td.vbo-checkinday span", function(e) {
		// start dragging and prevent tooltip from popping up
		isdragging = true;
		//
		if (hovtip === true) {
			hideVboTooltip();
		}
		if (jQuery(this).hasClass("busytmplock")) {
			return false;
		}
		cellspool = [];
		var dt = e.originalEvent.dataTransfer;
		var parentcell = jQuery(this).closest('td');
		var bidstart = parentcell.attr('data-bids');
		var checkind = parentcell.attr('data-day');
		var roomid = parentcell.parent('tr').find('td').first().attr('data-roomid');
		if (!bidstart.length || !checkind.length || !roomid.length) {
			return false;
		}
		var totnights = countBookingCells(parentcell, bidstart, roomid);
		jQuery(this).addClass('vbo-dragging-sp').attr('id', 'vbo-dragging-elem');
		dt.setData('Id', 'vbo-dragging-elem');
		dt.setData('Checkin', checkind);
		dt.setData('Roomid', roomid);
		dt.setData('Bid', bidstart);
		dt.setData('Nights', totnights);
	});
	/* DnD Events dragenter dragover drop */
	jQuery(document.body).on("dragenter dragover drop", "td.notbusy, td.busy", function(e) {
		e.preventDefault();
		if (e.type === 'drop') {
			newcellspool = [];
			var dt = e.originalEvent.dataTransfer;
			var cid = dt.getData('Id');
			var checkind = dt.getData('Checkin');
			var roomid = dt.getData('Roomid');
			var bid = dt.getData('Bid');
			var totnights = dt.getData('Nights');
			/* check if drop is allowed */
			var landrid = jQuery(this).closest('tr').find('td').first().attr('data-roomid');
			if (landrid === undefined || !landrid.length) {
				return false;
			}
			if (jQuery(this).hasClass('busy') && !jQuery(this).hasClass('vbo-partially')) {
				if (bid != jQuery(this).attr('data-bids')) {
					//landed on a occupied date but not for the same booking ID so drop is not allowed on this day
					return false;
				}
			}
			var freenights = countCellFreeNights(jQuery(this), landrid, totnights);
			if (freenights < totnights) {
				alert(vboMessages.notEnoughCells.replace('%s', freenights).replace('%d', totnights));
				return false;
			}
			/* make request */
			var nowdatefrom = jQuery(newcellspool[0]).attr('data-day');
			var nowdateto = jQuery(newcellspool[(newcellspool.length -1)]).attr('data-day');
			/* populate temporary class for the new cells */
			jQuery(cellspool).each(function(k, v){
				v.addClass('vbo-dragging-cells-tmp');
			});
			//bookings for multiple rooms should add the same dragging class also to the booking for the other rooms
			var samebidcells = jQuery("td.vbo-checkinday[data-bids='"+bid+"']");
			if (samebidcells.length > 1) {
				jQuery("td[data-bids='"+bid+"']").each(function(k, v) {
					if (!jQuery(v).hasClass('vbo-dragging-cells-tmp')) {
						jQuery(v).addClass('vbo-dragging-cells-tmp');
					}
				});
			}
			//
			jQuery(newcellspool).each(function(k, v){
				v.addClass('vbo-dragged-cells-tmp');
			});
			/* populate and showing loading message */
			jQuery('.vbo-loading-dnd-head').text(vboMessages.movingBookingId.replace('%d', bid));
			var movingmess = '';
			if (roomid != landrid) {
				movingmess += vboMessages.switchRoomTo.replace('%s', jQuery("td[data-roomid='"+landrid+"']").first().find('span.vbo-overview-roomname').text() );
			}
			if (nowdatefrom != jQuery(cellspool[0]).attr('data-day') || nowdateto != jQuery(cellspool[(cellspool.length -1)]).attr('data-day')) {
				if (roomid != landrid) {
					movingmess += ', ';
				}
				movingmess += vboMessages.switchDatesTo.replace('%s', nowdatefrom+' - '+nowdateto);
			}
			hideVboTooltip();
			jQuery('.vbo-loading-dnd-body').text(movingmess);
			jQuery('.vbo-info-overlay-block').fadeIn();
			jQuery('.vbo-loading-dnd-footer').show();
			/* fire the Ajax request after 1.5 seconds just for giving visibility to the loading message */
			setTimeout(function() {
				doAlterBooking(bid, roomid, landrid);
			}, 1500);
			/*
			//we do not need to copy just the dragged element so detaching it and appending it to the landing cell is useless
			var de = jQuery('#'+cid).detach();
			de.appendTo(jQuery(this));
			jQuery('#'+cid).attr('id', '').removeClass('vbo-dragging-sp');
			*/
		}
	});
	/* DnD Event dragend: remove class, attribute and dataTransfer Data if drag ends on the same position as dragstart or onto an invalid date */
	jQuery(document.body).on("dragend", "td.vbo-checkinday span", function(e) {
		// stop dragging to restore tooltip functions
		isdragging = false;
		//
		var dt = e.originalEvent.dataTransfer;
		var cid = dt.getData('Id');
		if (cid !== undefined && cid.length > 0) {
			jQuery('#'+cid).attr('id', '').removeClass('vbo-dragging-sp');
		} else {
			//Safari fix
			jQuery('.vbo-dragging-sp').removeClass('vbo-dragging-sp');
		}
	});
	/* DnD End */
	/* Show New Reservation Form - Start */
	jQuery('td.busy, td.notbusy').dblclick(function() {
		var curday = jQuery(this).attr('data-day');
		var roomid = jQuery(this).parent('tr').find('td.roomname').attr('data-roomid');
		roomid = roomid && roomid.length ? roomid : '';
		last_room_click = '';
		last_date_click = '';
		vboCallOpenJModal('vbo-new-res', 'index.php?option=com_vikbooking&task=calendar&cid[]='+roomid+'&checkin='+curday+'&overv=1&tmpl=component');
	});
	jQuery('td.busy, td.notbusy').click(function() {
		if (jQuery(this).hasClass('busy') && !jQuery(this).hasClass('vbo-partially')) {
			return;
		}
		var curday = jQuery(this).attr('data-day');
		var roomid = jQuery(this).parent('tr').find('td.roomname').attr('data-roomid');
		roomid = roomid && roomid.length ? roomid : '';
		last_room_click = roomid;
		last_date_click = curday;
		jQuery('.vbo-ovrv-flt-butn').addClass('vbo-ovrv-flt-butn-shake');
		setTimeout(function() {
			jQuery('.vbo-ovrv-flt-butn').removeClass('vbo-ovrv-flt-butn-shake');
		}, 1000);
	});
	/* Show New Reservation Form - End */
	/* Hover Tooltip Start */
	jQuery('td.busy, td.busytmplock, td.subroom-busy').hover(function() {
		registerHoveringTooltip(this);
	}, unregisterHoveringTooltip);
	jQuery(document.body).on('click', '.vbo-overview-tip-bctag', function() {
		if (!jQuery(this).parent().find(".vbo-overview-tip-bctag-subtip").length) {
			jQuery(".vbo-overview-tip-bctag-subtip").remove();
			var cur_color = jQuery(this).attr("data-ctagcolor");
			var cur_bid = jQuery(this).attr("data-bid");
			jQuery(this).after("<div class=\"vbo-overview-tip-bctag-subtip\">"+bctags_tip+"</div>");
			jQuery(this).parent().find(".vbo-overview-tip-bctag-subtip").find(".vbo-overview-tip-bctag-subtip-circle[data-ctagcolor='"+cur_color+"']").addClass("vbo-overview-tip-bctag-activecircle").css('border-color', cur_color);
			jQuery(this).parent().find(".vbo-overview-tip-bctag-subtip").find(".vbo-overview-tip-bctag-subtip-circle").attr('data-bid', cur_bid);
			jQuery(".vbo-overview-tip-bctag-subtip .hasTooltip").tooltip();
		} else {
			jQuery(".vbo-overview-tip-bctag-subtip").remove();
		}
	});
	var applying_tag = false;
	jQuery(document.body).on('click', '.vbo-overview-tip-bctag-subtip-circle', function() {
		if (applying_tag === true) {
			return false;
		}
		applying_tag = true;
		var clickelem = jQuery(this);
		var ctagkey = clickelem.attr('data-ctagkey');
		var bid = clickelem.attr('data-bid');
		//set opacity to circles as loading
		jQuery('.vbo-overview-tip-bctag-subtip-circle').css('opacity', '0.6');
		//
		var jqxhr = jQuery.ajax({
			type: "POST",
			url: "index.php",
			data: { option: "com_vikbooking", task: "setbookingtag", tmpl: "component", idorder: bid, tagkey: ctagkey }
		}).done(function(res) {
			applying_tag = false;
			if (res.indexOf('e4j.error') >= 0 ) {
				console.log(res);
				alert(res.replace("e4j.error.", ""));
				//restore loading opacity in circles
				jQuery('.vbo-overview-tip-bctag-subtip-circle').css('opacity', '1');
			} else {
				var obj_res = JSON.parse(res);
				var last_bid = jQuery(clickelem).closest(".vbo-hascolortag").attr('data-lastbid');
				<?php if ($pbmode == 'tags') { echo 'jQuery(".vbo-hascolortag[data-lastbid=\'"+last_bid+"\']").each(function(k, v) { jQuery(this).css("background-color", obj_res.color).find("a").first().css("color", obj_res.fontcolor); });'; } ?>
				hideVboTooltip();
			}
		}).fail(function() {
			applying_tag = false;
			alert("Request Failed");
			//restore loading opacity in circles
			jQuery('.vbo-overview-tip-bctag-subtip-circle').css('opacity', '1');
		});
	});
	jQuery(document).keydown(function(e) {
		if ( e.keyCode == 27 && hovtip === true ) {
			hideVboTooltip();
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!hovtip) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-overview-tipblock");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			hideVboTooltip();
			return true;
		}
		if (jQuery(".vbo-overview-tip-bctag-subtip").length) {
			var vbo_overlay_subtip_cont = jQuery(".vbo-overview-tip-bctag-wrap");
			if (!vbo_overlay_subtip_cont.is(e.target) && vbo_overlay_subtip_cont.has(e.target).length === 0) {
				jQuery(".vbo-overview-tip-bctag-subtip").remove();
				return true;
			}
		}
	});
	/* Hover Tooltip End */
	/* Toggle Sub-units Start */
	jQuery(".vbo-overview-subroom-toggle").click(function() {
		var roomid = jQuery(this).parent("td").attr("data-roomid");
		if (jQuery(this).hasClass("vbo-overview-subroom-toggle-active")) {
			jQuery("td.roomname[data-roomid='"+roomid+"']").find("span.vbo-overview-subroom-toggle").removeClass("vbo-overview-subroom-toggle-active").find("i.fa").removeClass("fa-chevron-up").addClass("fa-chevron-down");
			jQuery("td.subroomname[data-roomid='-"+roomid+"']").parent("tr").hide();
		} else {
			jQuery("td.roomname[data-roomid='"+roomid+"']").find("span.vbo-overview-subroom-toggle").addClass("vbo-overview-subroom-toggle-active").find("i.fa").removeClass("fa-chevron-down").addClass("fa-chevron-up");
			jQuery("td.subroomname[data-roomid='-"+roomid+"']").parent("tr").show();
		}
	});
	/* Toggle Sub-units End */
	<?php if ((count($rows) * $mnum) > 10) { echo $stickyheaders_cmd_on; } ?>
});
</script>
