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

$package = $this->package;
$rooms = $this->rooms;

$vbo_app = new VboApplication();
JHTML::_('behavior.tooltip');
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$df = VikBooking::getDateFormat(true);
$excldf = 'Y-m-d';
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
	$excldf = 'd-m-Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikBooking::getCurrencySymb(true);
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"aliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($package) && $package['idiva']==$iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikbooking&task=iva\">".JText::_('VBNOIVAFOUND')."</a>";
}
$actexcludedays = "";
if (count($package)) {
	$diff = $package['dto'] - $package['dfrom'];
	$oldexcluded = !empty($package['excldates']) ? explode(";", $package['excldates']) : array();
	if ($diff >= 172800) {
		$daysdiff = floor($diff / 86400);
		$infoinit = getdate($package['dfrom']);
		$actexcludedays .= '<select name="excludeday[]" multiple="multiple" size="'.($daysdiff > 8 ? 8 : $daysdiff).'">';
		for ($i = 0; $i <= $daysdiff; $i++) {
			$ts = $i > 0 ? mktime(0, 0, 0, $infoinit['mon'], ((int)$infoinit['mday'] + $i), $infoinit['year']) : $package['dfrom'];
			$infots = getdate($ts);
			$optval = $infots['mon'].'-'.$infots['mday'].'-'.$infots['year'];
			$actexcludedays .= '<option value="'.$optval.'"'.(in_array($optval, $oldexcluded) ? ' selected="selected"' : '').'>'.date($excldf, $ts).'</option>';
		}
		$actexcludedays .= '</select>';
	}
}
$vbo_app->prepareModalBox();
?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function vboExcludeWDays() {
	var excludewdays = document.getElementById('excludewdays');
	var vboexclusion = document.getElementById('vboexclusion');
	var weekday = '0';
	var setnew = false;
	var curdate = '';
	var curwday = '0';
	for(i = 0; i < excludewdays.length; i++) {
		weekday = parseInt(excludewdays.options[i].value);
		setnew = excludewdays.options[i].selected == false ? false : true;
		for(j = 0; j < vboexclusion.length; j++) {
			curdate = vboexclusion.options[j].value;
			var dateparts = curdate.split("-");
			var dobj = new Date(dateparts[2], (parseInt(dateparts[0]) - 1), dateparts[1]);
			curwday = parseInt(dobj.getDay());
			if (weekday == curwday) {
				vboexclusion.options[j].selected = setnew;
			}
		}
	}
}
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".vbo-select-all").click(function(){
		jQuery(this).next("select").find("option").prop('selected', true);
	});
	jQuery('#vbo-pkg-calcexcld').click(function(){
		var fdate = jQuery('#from').val();
		var tdate = jQuery('#to').val();
		if (fdate.length && tdate.length) {
			jQuery('#vbo-pkg-excldates-td').html('');
			var jqxhr = jQuery.ajax({
				type: "POST",
				url: "index.php",
				data: { option: "com_vikbooking", task: "dayselectioncount", dinit: fdate, dend: tdate, tmpl: "component" }
			}).done(function(cont) {
				if (cont.length) {
					jQuery("#vbo-pkg-excldates-td").html(cont);
				} else {
					jQuery('#vbo-pkg-excldates-td').html('----');
				}
			}).fail(function() {
				alert("Error Calculating the dates for exclusion");
			});
		} else {
			jQuery('#vbo-pkg-excldates-td').html('----');
		}
	});
<?php
if (count($package)) :
?>
	jQuery("#from").val("<?php echo date($usedf, $package['dfrom']); ?>").attr('data-alt-value', "<?php echo date($usedf, $package['dfrom']); ?>");
	jQuery("#to").val("<?php echo date($usedf, $package['dto']); ?>").attr('data-alt-value', "<?php echo date($usedf, $package['dto']); ?>");
<?php
endif;
?>
});
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<fieldset class="adminform">
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGNAME'); ?></b> </td>
					<td><input type="text" name="name" value="<?php echo count($package) ? $package['name'] : ''; ?>" size="50"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGALIAS'); ?></b> </td>
					<td><input type="text" name="alias" value="<?php echo count($package) ? $package['alias'] : ''; ?>" placeholder="<?php echo JFilterOutput::stringURLSafe(JText::_('VBNEWPKGNAME')); ?>" size="50"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGIMG'); ?></b> </td>
					<td><?php echo (count($package) && !empty($package['img']) && file_exists(VBO_SITE_PATH.DS.'resources'.DS.'uploads'.DS.'big_'.$package['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/big_'.$package['img'].'" class="vbomodal" target="_blank">'.$package['img'].'</a> &nbsp;' : ""); ?><input type="file" name="img" size="35"/><br/><label style="display: inline;" for="autoresize"><?php echo JText::_('VBNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="500" size="3"/> px</span></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGDFROM'); ?></b> </td>
					<td><?php echo $vbo_app->getCalendar('', 'from', 'from', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGDTO'); ?></b> </td>
					<td><?php echo $vbo_app->getCalendar('', 'to', 'to', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGEXCLDATES'); ?></b> </td>
					<td><button type="button" class="btn" id="vbo-pkg-calcexcld"><i class="icon-refresh"></i></button><div id="vbo-pkg-excldates-td"><?php echo $actexcludedays; ?></div></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGROOMS'); ?></b> </td>
					<td>
						<span class="vbo-select-all"><?php echo JText::_('VBOSELECTALL'); ?></span>
						<select name="rooms[]" multiple="multiple" size="<?php echo (count($rooms) > 6 ? '6' : count($rooms)); ?>">
						<?php
						foreach ($rooms as $rk => $rv) {
							?>
							<option value="<?php echo $rv['id']; ?>"<?php echo (array_key_exists('selected', $rv) ? ' selected="selected"' : ''); ?>><?php echo $rv['name']; ?></option>
							<?php
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGMINLOS'); ?></b> </td>
					<td><input type="number" name="minlos" id="minlos" value="<?php echo count($package) ? $package['minlos'] : '1'; ?>" min="1" size="5"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGMAXLOS'); ?></b> </td>
					<td><input type="number" name="maxlos" id="maxlos" value="<?php echo count($package) ? $package['maxlos'] : '0'; ?>" min="0" size="5"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGCOST'); ?></b> </td>
					<td><input type="number" step="any" min="0" name="cost" id="cost" value="<?php echo count($package) ? $package['cost'] : ''; ?>" style="min-width: 60px;"/> <?php echo $currencysymb; ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTFOUR'); ?></b> </td>
					<td><?php echo $wiva; ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGCOSTTYPE'); ?></b> </td>
					<td><select name="pernight_total"><option value="1"<?php echo (count($package) && $package['pernight_total'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGCOSTTYPEPNIGHT'); ?></option><option value="2"<?php echo (count($package) && $package['pernight_total'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGCOSTTYPETOTAL'); ?></option></select></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGCOSTTYPEPPERSON'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('perperson', JText::_('VBYES'), JText::_('VBNO'), (count($package) ? (int)$package['perperson'] : 0), 1, 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGSHOWOPT'); ?></b> </td>
					<td><select name="showoptions"><option value="1"<?php echo (count($package) && $package['showoptions'] == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGSHOWOPTALL'); ?></option><option value="2"<?php echo (count($package) && $package['showoptions'] == 2 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGSHOWOPTOBL'); ?></option><option value="3"<?php echo (count($package) && $package['showoptions'] == 3 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBNEWPKGHIDEOPT'); ?></option></select></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGSHORTDESCR'); ?></b> </td>
					<td><textarea name="shortdescr" rows="4" cols="60"><?php echo count($package) ? $package['shortdescr'] : ''; ?></textarea></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGDESCR'); ?></b> </td>
					<td><?php echo $editor->display( "descr", (count($package) ? $package['descr'] : ""), '100%', 300, 70, 20 ); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGCONDS'); ?></b> </td>
					<td><?php echo $editor->display( "conditions", (count($package) ? $package['conditions'] : ""), '100%', 300, 70, 20 ); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWPKGBENEFITS'); ?></b> </td>
					<td><textarea name="benefits" placeholder="<?php echo JText::_('VBNEWPKGBENEFITSHELP'); ?>" rows="3" cols="60"><?php echo count($package) ? $package['benefits'] : ''; ?></textarea></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
<?php
if (count($package)) :
?>
	<input type="hidden" name="whereup" value="<?php echo $package['id']; ?>">
<?php
endif;
?>
</form>