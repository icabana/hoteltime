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

$nowdf = VikBooking::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator(true);
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOCRONS'); ?></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
} else {
	$document = JFactory::getDocument();
	$document->addStyleSheet(VBO_SITE_URI.'resources/jquery.fancybox.css');
	JHtml::_('script', VBO_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="50">
				<a href="index.php?option=com_vikbooking&amp;task=crons&amp;vborderby=id&amp;vbordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "id" ? "vbo-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "id" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="200">
				<a href="index.php?option=com_vikbooking&amp;task=crons&amp;vborderby=cron_name&amp;vbordersort=<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "cron_name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "cron_name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCRONNAME').($orderby == "cron_name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "cron_name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="100"><?php echo JText::_( 'VBCRONCLASS' ); ?></th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=crons&amp;vborderby=last_exec&amp;vbordersort=<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_exec" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "last_exec" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBCRONLASTEXEC').($orderby == "last_exec" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "last_exec" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="50"><?php echo JText::_( 'VBCRONPUBLISHED' ); ?></th>
			<th class="title center" width="150"><?php echo JText::_( 'VBCRONACTIONS' ); ?></th>
			<th class="title center" width="100">&nbsp;</th>
		</tr>
		</thead>
	<?php
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editcron&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editcron&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['cron_name']; ?></a></td>
			<td class="center"><?php echo $row['class_file']; ?></td>
			<td class="center"><?php echo !empty($row['last_exec']) ? date(str_replace("/", $datesep, $df).' H:i:s', $row['last_exec']) : '----'; ?></td>
			<td class="center">
			<?php
			if (intval($row['published']) > 0) {
				?>
				<i class="fa fa-check vbo-icn-img" style="color: #099909;"></i>
				<?php
			} else {
				?>
				<i class="fa fa-times-circle vbo-icn-img" style="color: #ff0000;"></i>
				<?php
			}
			?>
			</td>
			<td class="center"><button type="button" class="btn vbo-getcmd" data-cronid="<?php echo $row['id']; ?>" data-cronname="<?php echo addslashes($row['cron_name']); ?>" data-cronclass="<?php echo $row['class_file']; ?>"><i class="vboicn-terminal"></i><?php echo JText::_('VBCRONGETCMD'); ?></button> &nbsp;&nbsp; <button type="button" class="btn vbo-exec" data-cronid="<?php echo $row['id']; ?>"><i class="vboicn-power-cord"></i><?php echo JText::_('VBCRONACTION'); ?></button></td>
			<td class="center"><button type="button" class="btn vbo-logs" data-cronid="<?php echo $row['id']; ?>"><i class="vboicn-file-text"></i><?php echo JText::_('VBCRONLOGS'); ?></button></td>
		</tr>
		<?php
		$kk = 1 - $kk;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="crons" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-content-getcmd">
		<h3><i class="vboicn-terminal"></i><?php echo JText::_('VBCRONGETCMD') ?>: <span id="crongetcmd-lbl"></span></h3>
		<blockquote class="vbo-crongetcmd-help"><?php echo JText::_('VBCRONGETCMDHELP') ?></blockquote>
		<h4><?php echo JText::_('VBCRONGETCMDINSTSTEPS') ?></h4>
		<ol>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPONE') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPTWO') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPTHREE') ?></li>
			<li><?php echo JText::_('VBCRONGETCMDINSTSTEPFOUR') ?></li>
		</ol>
		<p><?php echo JText::_('VBCRONGETCMDINSTPATH'); ?></p>
		<p><span class="label label-info">/usr/bin/php <?php echo JPATH_SITE.DS; ?><span class="crongetcmd-php"></span>.php</span></p>
		<p><i class="vboicn-warning"></i><?php echo JText::_('VBCRONGETCMDINSTURL'); ?></p>
		<p><span class="label"><?php echo JURI::root(); ?><span class="crongetcmd-php"></span>.php</span></p>
		<br/>
		<form action="index.php?option=com_vikbooking" method="post">
			<button type="submit" class="btn"><i class="vboicn-download"></i><?php echo JText::_('VBCRONGETCMDGETFILE') ?></button>
			<input type="hidden" name="cron_id" id="cronid-inp" value="" />
			<input type="hidden" name="cron_name" id="cronname-inp" value="" />
			<input type="hidden" name="task" value="downloadcron" />
		</form>
	</div>
</div>
<script type="text/javascript">
var vbo_overlay_on = false;
jQuery(document).ready(function() {
	jQuery(".vbo-getcmd").click(function() {
		var cronid = jQuery(this).attr("data-cronid");
		var cronname = jQuery(this).attr("data-cronname");
		jQuery("#crongetcmd-lbl").text(cronname);
		var cronclass = jQuery(this).attr("data-cronclass");
		jQuery("#cronid-inp").val(cronid);
		var cronnamephp = cronname.replace(/\s/g, "").toLowerCase();
		jQuery("#cronname-inp").val(cronnamephp);
		jQuery(".crongetcmd-php").text(cronnamephp);
		jQuery(".vbo-info-overlay-block").fadeToggle(400, function() {
			if (jQuery(".vbo-info-overlay-block").is(":visible")) {
				vbo_overlay_on = true;
			} else {
				vbo_overlay_on = false;
			}
		});
	});
	jQuery(".vbo-logs").click(function() {
		var cron_id = jQuery(this).attr("data-cronid");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": "index.php?option=com_vikbooking&task=cronlogs&cron_id="+cron_id+"&tmpl=component",
			"width": "75%",
			"height": "75%",
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			//"padding": 0,
			"type": "iframe"
		});
	});
	jQuery(".vbo-exec").click(function() {
		var cron_id = jQuery(this).attr("data-cronid");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": "index.php?option=com_vikbooking&task=cron_exec&cronkey=<?php echo VikBooking::getCronKey(); ?>&cron_id="+cron_id+"&tmpl=component",
			"width": "75%",
			"height": "75%",
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			//"padding": 0,
			"type": "iframe"
		});
	});
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