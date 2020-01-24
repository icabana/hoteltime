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

$res=$this->res;
$days=$this->days;
$checkin=$this->checkin;
$checkout=$this->checkout;
$roomsnum=$this->roomsnum;
$arrpeople=$this->arrpeople;
$showchildren=$this->showchildren;
$showchildren = intval($showchildren) == 1 ? true : false;
$js_overcounter=$this->js_overcounter;
$vbo_tn=$this->vbo_tn;
$writeroomnum = array();

$vat_included = VikBooking::ivaInclusa();
$tax_summary = !$vat_included && VikBooking::showTaxOnSummaryOnly() ? true : false;

$vbdateformat = VikBooking::getDateFormat();
if ($vbdateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vbdateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();

//load jQuery lib e jQuery UI
$document = JFactory::getDocument();
if(VikBooking::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery-ui.min.css');
$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
//

$currencysymb = VikBooking::getCurrencySymb();
$pitemid = VikRequest::getInt('Itemid', '', 'request');

$totadults = 0;
$totchildren = 0;

foreach($arrpeople as $aduchild) {
	$totadults += $aduchild['adults'];
	$totchildren += $aduchild['children'];
}

?>
<script type="text/javascript">
var vbdialog_on = false;
jQuery.noConflict();
jQuery(function() {
	jQuery(".vbmodalframe").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"width": "45%",
		"height": "80%",
		"autoScale": true,
		"transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"fitToView" : false,
		"autoSize" : false,
		"type": "iframe" 
	});
	jQuery(document).mouseup(function(e) {
		if(!vbdialog_on) {
			return false;
		}
		var vbdialog_cont = jQuery(".vbdialog-inner");
		if(!vbdialog_cont.is(e.target) && vbdialog_cont.has(e.target).length === 0) {
			vbDialogClose();
		}
	});
});
<?php
if (count($js_overcounter) > 0) {
	echo 'var r_counter = '.json_encode($js_overcounter).";\n";
}else {
	echo 'var r_counter = {};'."\n";
}
?>
var arridroom = new Array();
<?php
$arr_nr = array();
$disp_rooms = array();
for($ir = 1; $ir <= $roomsnum; $ir++) {
	$arr_nr[$ir] = '';
	$nowrooms = array();
	foreach($res[$ir] as $room) {
		$nowrooms[] = '"'.$room[0]['idroom'].'"';
		$disp_rooms[$ir][] = (int)$room[0]['idroom'];
	}
?>
arridroom[<?php echo $ir; ?>] = new Array(<?php echo implode(",", $nowrooms); ?>);
<?php
}
echo 'var sel_rooms = '.json_encode($arr_nr).";\n";
echo 'var disp_rooms = '.json_encode($disp_rooms).";\n";
?>
function vbDialogClose() {
	jQuery("#vbdialog-overlay").fadeOut();
	vbdialog_on = false;
}
function vbDialog(totr, selr, roomnum, idroom) {
	var roomimg = jQuery("#vbroomimg"+roomnum+"_"+idroom).attr("src");
	var roomname = jQuery("#vbroomname"+roomnum+"_"+idroom).text();
	jQuery("#vbdialogrimage").attr("src", roomimg);
	jQuery("#vbdialogrname").text(roomname);
	if(totr == selr) {
		jQuery("#vbdialog-confirm").attr("onclick", "Javasript: vbDialogClose();document.getElementById('vbselectroomform').submit();");
	}else {
		var nextr = selr + 1;
		jQuery("#vbdialog-confirm").attr("onclick", "Javasript: vbDialogClose();jQuery('html,body').animate({ scrollTop: (jQuery('#vbpositionroom"+nextr+"').offset().top - 5) }, { duration: 'slow' });");
	}
	jQuery("#vbdialog-overlay").fadeIn();
	vbdialog_on = true;
}
function vbhasClass(ele,cls) {
	if(ele == null) {
		return false;
	}else {
		return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
	}
}
function vbaddClass(ele,cls) {
	if (!this.vbhasClass(ele,cls)) ele.className += " "+cls;
}
function vbremoveClass(ele,cls) {
	if (vbhasClass(ele,cls)) {
		var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		ele.className=ele.className.replace(reg,' ').replace(/\s+/g,' ').replace(/^\s|\s$/,'');
	}
}
function vbinArray(needle, haystack) {
	var arrpos;
	if (typeof jQuery != 'undefined') {
		arrpos = jQuery.inArray(needle, haystack);
	}else {
		arrpos = haystack.indexOf(needle);
	}
	return arrpos >= 0 ? true : false;
}
function vbSelectRoom(roomnum, idroom) {
	var totrooms = <?php echo $roomsnum; ?>;
	if(r_counter.hasOwnProperty(idroom) && totrooms > 1) {
		if(r_counter[idroom]['used'] >= r_counter[idroom]['unitsavail']) {
			alert('<?php echo addslashes(JText::_('VBERRJSNOUNITS')); ?>');
			return false;
		}else {
			if((r_counter[idroom]['used'] + 1) >= r_counter[idroom]['unitsavail']) {
				var excess = r_counter[idroom]['count'] - r_counter[idroom]['unitsavail'];
				var unselected = new Array();
				for (var x = totrooms; x >= 1; x--) {
					if(sel_rooms[x].length == 0) unselected.push(x);
				}
				for (var x = totrooms; x >= 1 && excess > 0; x--) {
					if(unselected.length == 1 && vbinArray(parseInt(roomnum), unselected) && ((r_counter[idroom]['used'] + 1) == r_counter[idroom]['unitsavail'])) {
						break;
					}
					if(x != roomnum && vbinArray(parseInt(idroom), disp_rooms[x]) && (vbhasClass(document.getElementById('vbcontainer'+x+'_'+idroom), 'room_selected') || document.getElementById('roomopt'+x).value.length == 0)) {
						if (typeof jQuery != 'undefined') {
							jQuery('#vbcontainer'+x+'_'+idroom).fadeOut();
						}else {
							document.getElementById('vbcontainer'+x+'_'+idroom).style.display = 'none';
						}
						document.getElementById('roomopt'+x).value = '';
						vbremoveClass(document.getElementById('vbcontainer'+x+'_'+idroom), 'room_selected');
						excess--;
					}
				}
			}
			if(sel_rooms[roomnum] != idroom && sel_rooms[roomnum].length > 0 && r_counter[sel_rooms[roomnum]]['used'] > 0) {
				for (var x = 1; x <= totrooms; x++) {
					if(x != roomnum && r_counter[sel_rooms[roomnum]]['used'] >= r_counter[sel_rooms[roomnum]]['unitsavail']) {
						if (typeof jQuery != 'undefined') {
							jQuery('#vbcontainer'+x+'_'+sel_rooms[roomnum]).fadeIn();
						}else {
							document.getElementById('vbcontainer'+x+'_'+sel_rooms[roomnum]).style.display = 'block';
						}
					}
				}
				r_counter[sel_rooms[roomnum]]['used']--;
			}
			if(sel_rooms[roomnum] != idroom) {
				r_counter[idroom]['used']++;
			}
			sel_rooms[roomnum] = idroom;
		}
	}
	vbaddClass(document.getElementById('vbcontainer'+roomnum+'_'+idroom), 'room_selected');
	document.getElementById('vbselector'+roomnum+'_'+idroom).innerHTML = '<?php echo addslashes(JText::_('VBSELECTEDR')); ?>';
	for(val in arridroom[roomnum]) {
		if(arridroom[roomnum][val] != idroom) {
			if(vbhasClass(document.getElementById('vbcontainer'+roomnum+'_'+arridroom[roomnum][val]), 'room_selected')) {
				vbremoveClass(document.getElementById('vbcontainer'+roomnum+'_'+arridroom[roomnum][val]), 'room_selected');
				document.getElementById('vbselector'+roomnum+'_'+arridroom[roomnum][val]).innerHTML = '<?php echo addslashes(JText::_('VBSELECTR')); ?>';
			}
		}
	}
	document.getElementById('roomopt'+roomnum).value = idroom;
	var selectedrooms = 0;
	for (var x = 1; x <= totrooms; x++) {
		var roomsel = document.getElementById('roomopt'+x).value;
		if(roomsel.length > 0) {
			selectedrooms++;
		}
	}
	if(totrooms == selectedrooms) {
		document.getElementById('vbsearchmainsbmt').style.display = 'block';
	}
	if (totrooms < 2) {
		//print the dialog message for at least two rooms booked
		document.getElementById('vbselectroomform').submit();
		return true;
	}
	if (typeof jQuery != 'undefined') {
    	vbDialog(totrooms, selectedrooms, roomnum, idroom);  
	}
}
</script>

<div id="vbdialog-overlay" style="display: none;">
	<a class="vbdialog-overlay-close" href="javascript: void(0);"></a>
	<div class="vbdialog-inner">
		<div class="vbdialog-left">
			<div class="vbdialogrimage"><img id="vbdialogrimage" src=""/></div>
		</div>
		<div class="vbdialog-right">
			<div class="vbdialog-right-top">
				<span class="vbdialog-intro"><?php echo JText::_('VBDIALOGMESSONE'); ?></span>
				<span id="vbdialogrname" class="vbdialogrname"></span>
			</div>
			<div class="vbdialog-right-bottom">
				<button type="button" class="btn" id="vbdialog-cancel" onclick="Javascript: vbDialogClose();"><?php echo JText::_('VBDIALOGBTNCANCEL'); ?></button>
				<button type="button" class="btn" id="vbdialog-confirm" onclick="Javascript: void(0);"><?php echo JText::_('VBDIALOGBTNCONTINUE'); ?></button>
			</div>
		</div>
	</div>
</div>

<?php
if (count($this->mod_booking)) {
	//booking modification
	?>
<div class="vbo-booking-modification-helper">
	<div class="vbo-booking-modification-helper-inner">
		<div class="vbo-booking-modification-msg">
			<span><?php echo JText::_('VBOMODBOOKHELPROOMS'); ?></span>
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
		<li class="vbo-step vbo-step-complete"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&checkin='.$checkin.'&checkout='.$checkout.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBSTEPDATES'); ?></a></li>
		<li class="vbo-step vbo-step-current"><span><?php echo JText::_('VBSTEPROOMSELECTION'); ?></span></li>
		<li class="vbo-step vbo-step-next"><span><?php echo JText::_('VBSTEPOPTIONS'); ?></span></li>
		<li class="vbo-step vbo-step-next"><span><?php echo JText::_('VBSTEPCONFIRM'); ?></span></li>
	</ol>
</div>

<br clear="all"/>

<div class="vbo-results-head">
	<span class="vbo-results-checkin"><i class="fa fa-sign-in"></i> <?php echo date(str_replace("/", $datesep, $df), $checkin); ?></span>
	<span class="vbo-results-nights"><i class="fa fa-calendar"></i> <?php echo $days; ?> <?php echo ($days == 1 ? JText::_('VBSEARCHRESNIGHT') : JText::_('VBSEARCHRESNIGHTS')); ?></span>
<?php
if($roomsnum > 1) {
	?>
	<span class="vbo-results-numrooms"><i class="fa fa-bed"></i> <?php echo $roomsnum." ".($roomsnum == 1 ? JText::_('VBSEARCHRESROOM') : JText::_('VBSEARCHRESROOMS')); ?></span>
	<?php
}
?>
	<span class="vbo-results-numadults"><i class="fa fa-male"></i> <?php echo $totadults; ?> <?php echo ($totadults == 1 ? JText::_('VBSEARCHRESADULT') : JText::_('VBSEARCHRESADULTS')); ?></span>
<?php
if($showchildren && $totchildren > 0) {
	?>
	<span class="vbo-results-numchildren"><i class="fa fa-child"></i> <?php echo $totchildren." ".($totchildren == 1 ? JText::_('VBSEARCHRESCHILD') : JText::_('VBSEARCHRESCHILDREN')); ?></span>
	<?php
}
?>
	<span class="vbo-results-chdates">
		<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&checkin='.$checkin.'&checkout='.$checkout.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBCHANGEDATES'); ?></a>
	</span>
</div>
<?php

foreach ($res as $indroom => $rooms) {
	foreach($rooms as $room) {
		if ($roomsnum > 1 && !in_array($indroom, $writeroomnum)) {
			$writeroomnum[] = $indroom;
			?>
			<div id="vbpositionroom<?php echo $indroom; ?>"></div>
			<div class="vbsearchproominfo">
				<span class="vbsearchnroom"><?php echo JText::_('VBSEARCHROOMNUM'); ?> <?php echo $indroom; ?></span>
				<span class="vbsearchroomparty"><i class="fa fa-users"></i> <?php echo $arrpeople[$indroom]['adults']; ?> <?php echo ($arrpeople[$indroom]['adults'] == 1 ? JText::_('VBSEARCHRESADULT') : JText::_('VBSEARCHRESADULTS')); ?> <?php echo ($showchildren && $arrpeople[$indroom]['children'] > 0 ? ", ".$arrpeople[$indroom]['children']." ".($arrpeople[$indroom]['children'] == 1 ? JText::_('VBSEARCHRESCHILD') : JText::_('VBSEARCHRESCHILDREN')) : ""); ?></span>
			</div>
			<?php
		}
		//set a different class to the main div in case the rooms usage is for less people than the capacity
		$rdiffusage = array_key_exists('diffusage', $room[0]) && $arrpeople[$indroom]['adults'] < $room[0]['toadult'] ? true : false;
		$has_promotion = array_key_exists('promotion', $room[0]) ? true : false;
		$maindivclass = $rdiffusage ? "room_resultdiffusage" : "room_result";
		$carats = VikBooking::getRoomCaratOriz($room[0]['idcarat'], $vbo_tn);
		//BEGIN: Joomla Content Plugins Rendering
		if (class_exists('JEventDispatcher')) {
			JPluginHelper::importPlugin('content');
			$myItem = JTable::getInstance('content');
			$dispatcher = JEventDispatcher::getInstance();
			$myItem->text = $room[0]['smalldesc'];
			$dispatcher->trigger('onContentPrepare', array('com_vikbooking.search', &$myItem, &$params, 0));
			$room[0]['smalldesc'] = $myItem->text;
		}
		//END: Joomla Content Plugins Rendering
		$saylastavail = false;
		$showlastavail = (int)VikBooking::getRoomParam('lastavail', $room[0]['params']);
		if(!empty($showlastavail) && $showlastavail > 0) {
			if($room[0]['unitsavail'] <= $showlastavail) {
				$saylastavail = true;
			}
		}
		?>
		<div class="room_item <?php echo $maindivclass; ?><?php echo $has_promotion === true ? ' vbo-promotion-price' : ''; ?>" id="vbcontainer<?php echo $indroom.'_'.$room[0]['idroom']; ?>">
			<div class="vblistroomblock">
				<div class="vbimglistdiv">
					<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=searchdetails&roomid='.$room[0]['idroom'].'&checkin='.$checkin.'&checkout='.$checkout.'&adults='.$arrpeople[$indroom]['adults'].'&children='.$arrpeople[$indroom]['children'].'&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" class="vbmodalframe" target="_blank"><img class="vblistimg" alt="<?php echo $room[0]['name']; ?>" id="vbroomimg<?php echo $indroom.'_'.$room[0]['idroom']; ?>" src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $room[0]['img']; ?>"/></a>
					<div class="vbmodalrdetails"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=searchdetails&roomid='.$room[0]['idroom'].'&checkin='.$checkin.'&checkout='.$checkout.'&adults='.$arrpeople[$indroom]['adults'].'&children='.$arrpeople[$indroom]['children'].'&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" class="vbmodalframe" target="_blank">+</a></div>
				</div>
				<div class="vbo-info-room">
					<div class="vbdescrlistdiv">
						<span class="vbrowcname" id="vbroomname<?php echo $indroom.'_'.$room[0]['idroom']; ?>"><?php echo $room[0]['name']; ?></span>
						<div class="vbrowcdescr"><?php echo $room[0]['smalldesc']; ?></div>
					</div>
				<?php
				if (!empty($carats)) {
					?>
					<div class="roomlist_carats">
						<?php echo $carats; ?>
					</div>
					<?php
				}
				?>
				<?php
				if($has_promotion === true && !empty($room[0]['promotion']['promotxt'])) {
					?>
					<div class="vbo-promotion-block">
						<?php echo $room[0]['promotion']['promotxt']; ?>
					</div>
					<?php
				}
				?>
				</div>
			</div>
				<div class="vbcontdivtot">
					<div class="vbdivtot">
					<div class="vbdivtotinline">
					<div class="vbsrowprice">
						<div class="vbrowroomcapacity">
					<?php
					for($i = 1; $i <= $room[0]['toadult']; $i++) {
						if ($i <= $arrpeople[$indroom]['adults']) {
							?>
							<i class="fa fa-male"></i>
							<?php
						}else {
							?>
							<i class="fa fa-male vbo-empty-personicn"></i>
							<?php
						}
					}
					?>
						</div>
						<div class="vbsrowpricediv"><span class="room_cost"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo $tax_summary ? VikBooking::numberFormat($room[0]['cost']) : VikBooking::numberFormat(VikBooking::sayCostPlusIva($room[0]['cost'], $room[0]['idprice'])); ?></span></span></div>
					<?php
					if ($saylastavail === true) {
						?>
						<span class="vblastavail"><?php echo JText::sprintf('VBLASTUNITSAVAIL', $room[0]['unitsavail']); ?></span>
						<?php
					}
					?>
					</div>
					<div class="vbselectordiv"><div id="vbselector<?php echo $indroom.'_'.$room[0]['idroom']; ?>" class="vbselectr-result" onclick="vbSelectRoom('<?php echo $indroom; ?>', '<?php echo $room[0]['idroom']; ?>');"><?php echo JText::_('VBSELECTR'); ?></div></div>
					</div>
					</div>
				</div>
				
		</div>
		<?php
	}
}
?>
		<div class="goback">
			<a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=vikbooking&checkin='.$checkin.'&checkout='.$checkout.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>"><?php echo JText::_('VBCHANGEDATES'); ?></a>
		</div>
		
		<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'.(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" method="get" id="vbselectroomform">
			<input type="hidden" name="option" value="com_vikbooking"/>
			<input type="hidden" name="task" value="showprc"/>
			<input type="hidden" id="roomsnum" name="roomsnum" value="<?php echo $roomsnum; ?>"/>
			<?php
			for($ir = 1; $ir <= $roomsnum; $ir++) {
				?>
				<input type="hidden" id="roomopt<?php echo $ir; ?>" name="roomopt[]" value=""/>
				<?php
			}
			foreach($arrpeople as $indroom => $aduch) {
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
  			<input type="hidden" name="days" value="<?php echo $days; ?>"/>
  			<input type="hidden" name="checkin" value="<?php echo $checkin; ?>"/>
  			<input type="hidden" name="checkout" value="<?php echo $checkout; ?>"/>
  			<?php
			if (!empty ($pitemid)) {
			?>
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
			<?php
			}
			?>
			
			<div id="vbsearchmainsbmt" class="vbsearchmainsbmt" style="display: none;">
				<input type="submit" name="continue" value="<?php echo JText::_('VBSEARCHCONTINUESUBM'); ?>" class="vbsubmit"/>
			</div>
		
		</form>