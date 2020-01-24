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
$lim0 = $this->lim0;
$navbut = $this->navbut;
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$pfiltercustomer = VikRequest::getString('filtercustomer', '', 'request');
?>
<form action="index.php?option=com_vikbooking&amp;task=customers" method="post" name="customersform">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
		<div class="btn-group pull-left input-append">
			<input type="text" name="filtercustomer" id="filtercustomer" value="<?php echo $pfiltercustomer; ?>" size="40" placeholder="<?php echo JText::_( 'VBCUSTOMERFIRSTNAME' ).', '.JText::_( 'VBCUSTOMERLASTNAME' ).', '.JText::_( 'VBCUSTOMEREMAIL' ).', '.JText::_( 'VBCUSTOMERPIN' ); ?>"/>
			<button type="button" class="btn btn-secondary" onclick="document.customersform.submit();"><i class="icon-search"></i></button>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn btn-secondary" onclick="document.getElementById('filtercustomer').value='';document.customersform.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
	</div>
	<input type="hidden" name="task" value="customers" />
	<input type="hidden" name="option" value="com_vikbooking" />
</form>
<?php
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOCUSTOMERS'); ?></p>
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
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "id" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=first_name&amp;vbordersort=<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "first_name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERFIRSTNAME').($orderby == "first_name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "first_name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=last_name&amp;vbordersort=<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "last_name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERLASTNAME').($orderby == "last_name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "last_name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=email&amp;vbordersort=<?php echo ($orderby == "email" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "email" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "email" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMEREMAIL').($orderby == "email" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "email" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=phone&amp;vbordersort=<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "phone" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERPHONE').($orderby == "phone" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "phone" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=country&amp;vbordersort=<?php echo ($orderby == "country" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "country" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "country" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERCOUNTRY').($orderby == "country" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "country" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=pin&amp;vbordersort=<?php echo ($orderby == "pin" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "pin" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "pin" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERPIN').($orderby == "pin" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "pin" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=customers&amp;vborderby=tot_bookings&amp;vbordersort=<?php echo ($orderby == "tot_bookings" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "tot_bookings" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "tot_bookings" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCUSTOMERTOTBOOKINGS').($orderby == "tot_bookings" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "tot_bookings" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">&nbsp;</th>
			<th class="title center" width="75">&nbsp;</th>
		</tr>
		</thead>
	<?php
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$country_flag = '';
		if (!empty($row['country']) && !empty($row['country_full_name'])) {
			if (file_exists(VBO_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
				$country_flag = '<img src="'.VBO_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country_full_name'].'" class="vbo-country-flag"/>';
			}
		}
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editcustomer&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editcustomer&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['first_name']; ?></a><?php echo intval($row['ischannel']) > 0 ? ' <i class="vboicn-airplane" title="'.addslashes(JText::_('VBOCUSTOMERISCHANNEL')).'"></i>' : ''; ?></td>
			<td><?php echo $row['last_name']; ?></td>
			<td><?php echo $row['email']; ?></td>
			<td><?php echo $row['phone']; ?></td>
			<td class="center"><?php echo empty($country_flag) ? $row['country'] : $country_flag; ?></td>
			<td class="center"><?php echo $row['pin']; ?></td>
			<td class="center"><?php echo $row['tot_bookings']; ?></td>
			<td class="center"><?php echo ($row['tot_bookings'] > 0 ? '<a href="index.php?option=com_vikbooking&task=orders&cust_id='.$row['id'].'" class="btn hasTooltip" title="'.JText::_('VBMENUSEVEN').'"><i class="icon-eye"></i></a>' : ''); ?></td>
			<td class="center"><?php echo (!empty($row['phone']) ? '<button type="button" class="btn hasTooltip" onclick="vboToggleSendSMS(\''.$row['phone'].'\', \''.addslashes($row['first_name']).'\');" title="'.JText::_('VBSENDSMSACTION').'"><i class="vboicn-bubbles"></i></button>' : ''); ?></td>
		 </tr>
		  <?php
		$kk = 1 - $kk;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="customers" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-content-sendsms">
		<h4><?php echo JText::_('VBSENDSMSACTION') ?>: <span id="smstophone-lbl"></span></h4>
		<form action="index.php?option=com_vikbooking" method="post">
			<div class="vbo-calendar-cfield-entry">
				<label for="smscont"><?php echo JText::_('VBSENDSMSCUSTCONT') ?></label>
				<span><textarea name="smscont" id="smscont" style="width: 99%; min-width: 99%;max-width: 99%; height: 35%;"></textarea></span>
			</div>
			<div class="vbo-calendar-cfields-bottom">
				<button type="submit" class="btn"><i class="vboicn-bubbles"></i><?php echo JText::_('VBSENDSMSACTION') ?></button>
			</div>
			<input type="hidden" name="phone" id="smstophone" value="" />
			<input type="hidden" name="goto" value="<?php echo urlencode('index.php?option=com_vikbooking&task=customers&limitstart='.$lim0); ?>" />
			<input type="hidden" name="task" value="sendcustomsms" />
		</form>
	</div>
</div>
<script type="text/javascript">
var vbo_overlay_on = false;
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
function vboToggleSendSMS(phone, firstname) {
	jQuery("#smstophone").val(phone);
	jQuery("#smstophone-lbl").text(firstname+" "+phone);
	jQuery(".vbo-info-overlay-block").fadeToggle(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}
jQuery(document).ready(function(){
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			jQuery(".vbo-info-overlay-block").fadeOut();
			vbo_overlay_on = false;
		}
	});
});
</script>
<?php
}