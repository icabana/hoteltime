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

$row = $this->row;
$tot_rooms = $this->tot_rooms;
$tot_rooms_options = $this->tot_rooms_options;

$vbo_app = VikBooking::getVboApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikbooking_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"optaliq\"><option value=\"\"> </option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikbooking&task=iva\">".JText::_('VBNOIVAFOUND')."</a>";
}
$currencysymb = VikBooking::getCurrencySymb(true);
if (count($row) && strlen($row['forceval']) > 0) {
	$forceparts = explode("-", $row['forceval']);
	$forcedq = $forceparts[0];
	$forcedqperday = intval($forceparts[1]) == 1 ? true : false;
	$forcedqperchild = intval($forceparts[2]) == 1 ? true : false;
	$forcesummary = intval($forceparts[3]) == 1 ? true : false;
} else {
	$forcedq = "1";
	$forcedqperday = false;
	$forcedqperchild = false;
	$forcesummary = false;
}
$useageintervals = false;
$oldageintervals = '';
if (count($row) && !empty($row['ageintervals'])) {
	$useageintervals = true;
	$ageparts = explode(';;', $row['ageintervals']);
	foreach ($ageparts as $kage => $age) {
		if (empty($age)) {
			continue;
		}
		$interval = explode('_', $age);
		$oldageintervals .= '<p id="old'.$kage.'intv">'.JText::_('VBNEWAGEINTERVALFROM').': <input type="number" min="0" max="30" name="agefrom[]" size="2" value="'.$interval[0].'"/> '.JText::_('VBNEWAGEINTERVALTO').': <input type="number" min="0" max="30" name="ageto[]" size="2" value="'.$interval[1].'"/> '.JText::_('VBNEWAGEINTERVALCOST').': <input type="number" step="any" name="agecost[]" size="4" value="'.$interval[2].'"/> <select name="agectype[]"><option value="">'.$currencysymb.'</option><option value="%"'.(array_key_exists(3, $interval) && strpos($interval[3], '%') !== false && strpos($interval[3], '%b') === false ? ' selected="selected"' : '').'>'.JText::_('VBNEWAGEINTERVALCOSTPCENT').'</option><option value="%b"'.(array_key_exists(3, $interval) && strpos($interval[3], '%b') !== false ? ' selected="selected"' : '').'>'.JText::_('VBOAGEINTVALBCOSTPCENT').'</option></select> <i class="fa fa-minus-circle" onclick="removeAgeInterval(\'old'.$kage.'intv\');" style="font-size: 25px; cursor: pointer;"></i></p>'."\n";
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
function showForceSel() {
	if (document.adminForm.forcesel.checked == true) {
		document.getElementById('forcevalspan').style.display='block';
	} else {
		document.getElementById('forcevalspan').style.display='none';
	}
	return true;
}
function showMaxQuant() {
	if (document.adminForm.opthmany.checked == true) {
		document.getElementById('maxquantblock').style.display='block';
	} else {
		document.getElementById('maxquantblock').style.display='none';
	}
	return true;
}
function showAgeIntervals() {
	if (document.adminForm.ifchildren.checked == true) {
		document.getElementById('ifchildrenextra').style.display='block';
		document.adminForm.optperperson.checked = false;
		if (document.getElementById('myDiv').getElementsByTagName('div').length > 0 || document.getElementById('myDiv').getElementsByTagName('p').length > 0) {
			document.getElementById('optperpersontr').style.display='none';
			document.getElementById('opthmanytr').style.display='none';
			document.getElementById('forceseltr').style.display='none';
		}
	} else {
		document.getElementById('ifchildrenextra').style.display='none';
		if (document.getElementById('optperpersontr').style.display == 'none') {
			document.getElementById('optperpersontr').style.display='table-row';
			document.getElementById('opthmanytr').style.display='table-row';
			document.getElementById('forceseltr').style.display='table-row';
		}
	}
	return true;
}
function addAgeInterval() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreagaintervals');
	var num = (document.getElementById('moreagaintervals').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VBNEWAGEINTERVALFROM')); ?>: <input type=\'number\' min=\'0\' max=\'30\' name=\'agefrom[]\' size=\'2\'/> <?php echo addslashes(JText::_('VBNEWAGEINTERVALTO')); ?>: <input type=\'number\' min=\'0\' max=\'30\' name=\'ageto[]\' size=\'2\'/> <?php echo addslashes(JText::_('VBNEWAGEINTERVALCOST')); ?>: <input type=\'number\' step=\'any\' name=\'agecost[]\' size=\'4\'/> <select name=\'agectype[]\'><option value=\'\'><?php echo addslashes($currencysymb); ?></option><option value=\'%\'><?php echo addslashes(JText::_('VBNEWAGEINTERVALCOSTPCENT')); ?></option><option value=\'%b\'><?php echo addslashes(JText::_('VBOAGEINTVALBCOSTPCENT')); ?></option></select> <i class=\'fa fa-minus-circle\' onclick=\'removeAgeInterval("my'+num+'Div");\' style=\'font-size: 25px; cursor: pointer;\'></i></p>';
	ni.appendChild(newdiv);
	if (document.getElementById('optperpersontr').style.display != 'none') {
		document.getElementById('optperpersontr').style.display='none';
		document.getElementById('opthmanytr').style.display='none';
		document.getElementById('forceseltr').style.display='none';
	}
}
function removeAgeInterval(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
}
</script>
<input type="hidden" value="0" id="moreagaintervals" />

<?php
if (count($row)) {
?>
<div class="vbo-outer-info-message" id="vbo-outer-info-message-opt" style="display: block;" onclick="removeAgeInterval('vbo-outer-info-message-opt');">
	<div class="vbo-info-message-cont">
		<i class="vboicn-info"></i><span><?php echo JText::sprintf('VBOOPTASSTOXROOMS', $tot_rooms_options, $tot_rooms); ?></span>
	</div>
</div>
<?php
}
?>

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTONE'); ?></b> </td>
				<td><input type="text" name="optname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTTWO'); ?></b> </td>
				<td><?php echo $editor->display( "optdescr", (count($row) ? $row['descr'] : ""), '100%', 300, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTTHREE'); ?></b> </td>
				<td><?php echo $currencysymb; ?> <input type="number" step="any" name="optcost" value="<?php echo count($row) ? $row['cost'] : ''; ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTFOUR'); ?></b> </td>
				<td><?php echo $wiva; ?></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTFIVE'); ?></b> </td>
				<td><input type="checkbox" name="optperday" value="each"<?php echo (count($row) && intval($row['perday']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="optperpersontr">
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTPERPERSON'); ?></b> </td>
				<td><input type="checkbox" name="optperperson" value="each"<?php echo (count($row) && intval($row['perperson']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTEIGHT'); ?></b> </td>
				<td><?php echo $currencysymb; ?> <input type="number" step="any" name="maxprice" value="<?php echo count($row) ? $row['maxprice'] : ''; ?>" size="4"/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWOPTIFCHILDREN'); ?></b> </td>
				<td>
					<input type="checkbox" name="ifchildren" value="1" onclick="showAgeIntervals();"<?php echo (count($row) && intval($row['ifchildren']) == 1 ? " checked=\"checked\"" : ""); ?>/>
					<div id="ifchildrenextra" style="display: <?php echo ($useageintervals === true && strlen($oldageintervals) > 0 ? "block" : "none"); ?>;">
						<p style="display: block; font-weight: bold;"><?php echo JText::_('VBNEWOPTIFAGEINTERVAL'); ?></p>
						<div id="myDiv" class="vbo-dyninpnum-cont" style="display: block;"><?php echo $oldageintervals; ?></div>
						<a href="javascript: void(0);" onclick="addAgeInterval();"><i class="fa fa-plus-circle"></i> <?php echo JText::_('VBADDAGEINTERVAL'); ?></a>
					</div>
				</td>
			</tr>
			<tr id="opthmanytr">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWOPTSIX'); ?></b> </td>
				<td>
					<input type="checkbox" name="opthmany" value="yes" onclick="showMaxQuant();"<?php echo (count($row) && intval($row['hmany']) == 1 ? " checked=\"checked\"" : ""); ?>/> 
					<span id="maxquantblock" style="display: <?php echo (count($row) && intval($row['hmany']) == 1 ? "block" : "none"); ?>;">
						<?php echo JText::_('VBNEWOPTMAXQUANTSEL'); ?> 
						<input type="number" min="0" name="maxquant" value="<?php echo count($row) ? $row['maxquant'] : '0'; ?>" size="4"/>
					</span>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTSEVEN'); ?></b> </td>
				<td>
					<?php echo (count($row) && !empty($row['img']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/'.$row['img'].'" class="vbomodal" target="_blank">'.$row['img'].'</a> &nbsp;' : ""); ?>
					<input type="file" name="optimg" size="35"/>
					<br/>
					<label for="autoresize" style="display: inline-block;"><?php echo JText::_('VBNEWOPTNINE'); ?></label> 
					<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
					<span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="50" size="3"/> px</span>
				</td>
			</tr>
			<tr id="forceseltr">
				<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWOPTFORCESEL'); ?></b> </td>
				<td>
					<input type="checkbox" name="forcesel" value="1" onclick="showForceSel();"<?php echo (count($row) && intval($row['forcesel']) == 1 ? " checked=\"checked\"" : ""); ?>/> 
					<span id="forcevalspan" style="display: <?php echo (count($row) && intval($row['forcesel']) == 1 ? "block" : "none"); ?>;">
						<?php echo JText::_('VBNEWOPTFORCEVALT'); ?> 
						<input type="number" min="0" step="any" name="forceval" value="<?php echo $forcedq; ?>" size="4"/>
						<br/>
						<?php echo JText::_('VBNEWOPTFORCEVALTPDAY'); ?> 
						<input type="checkbox" name="forcevalperday" value="1"<?php echo ($forcedqperday == true ? " checked=\"checked\"" : ""); ?>/>
						<br/>
						<?php echo JText::_('VBNEWOPTFORCEVALPERCHILD'); ?> 
						<input type="checkbox" name="forcevalperchild" value="1"<?php echo ($forcedqperchild == true ? " checked=\"checked\"" : ""); ?>/>
						<br/>
						<br/>
						<?php echo JText::_('VBNEWOPTFORCESUMMARY'); ?> 
						<input type="checkbox" name="forcesummary" value="1"<?php echo ($forcesummary == true ? " checked=\"checked\"" : ""); ?>/>
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="vbexplaination"><?php echo JText::_('VBOPTHELPCITYTAXFEE'); ?></div>
				</td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTISCITYTAX'); ?></b> </td>
				<td><input type="checkbox" name="is_citytax" value="1"<?php echo (count($row) && $row['is_citytax'] == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr>
				<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWOPTISFEE'); ?></b> </td>
				<td><input type="checkbox" name="is_fee" value="1"<?php echo (count($row) && $row['is_fee'] == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikbooking">
</form>

<script type="text/javascript">
if (document.adminForm.ifchildren.checked == true && document.getElementById('myDiv').getElementsByTagName('p').length > 0) {
	document.getElementById('optperpersontr').style.display='none';
	document.getElementById('opthmanytr').style.display='none';
	document.getElementById('forceseltr').style.display='none';
}
</script>