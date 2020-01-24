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

$roomrows = $this->roomrows;
$rows = $this->rows;
$prices = $this->prices;
$allc = $this->allc;

//header
$idroom = $roomrows['id'];
$name = $roomrows['name'];
if (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$roomrows['img']) && getimagesize(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$roomrows['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Room Image" src="' . VBO_SITE_URI . 'resources/uploads/'.$roomrows['img'].'" />';
} else {
	$img = '<img align="middle" alt="vikbooking logo" src="' . VBO_ADMIN_URI . 'vikbooking.png' . '" />';
}
$fprice = "<div class=\"dailypricesactive\">".JText::_('VBDAILYFARES')."</div>\n";
if (empty($prices)) {
	$fprice .= "<br/><span class=\"err\"><b>".JText::_('VBMSGONE')." <a href=\"index.php?option=com_vikbooking&task=newprice\">".JText::_('VBHERE')."</a></b></span>";
} else {
	$colsp = "2";
	$fprice .= "<form name=\"newd\" method=\"post\" action=\"index.php?option=com_vikbooking\" onsubmit=\"javascript: if (!document.newd.ddaysfrom.value.match(/\S/)){alert('".JText::_('VBMSGTWO')."'); return false;} else {return true;}\">\n<br clear=\"all\"/><div class=\"vbo-insertrates-cont\"><span class=\"vbo-ratestable-lbl\">".JText::_('VBDAYS').": </span><br/><table><tr><td>".JText::_('VBDAYSFROM')." <input type=\"number\" name=\"ddaysfrom\" id=\"ddaysfrom\" value=\"".(!is_array($prices) ? '1' : '')."\" min=\"1\" /></td><td>&nbsp;&nbsp;&nbsp; ".JText::_('VBDAYSTO')." <input type=\"number\" name=\"ddaysto\" id=\"ddaysto\" value=\"".(!is_array($prices) ? '30' : '')."\" min=\"1\" max=\"999\" /></td></tr></table>\n";
	$fprice .= "<br/><span class=\"vbo-ratestable-lbl\">".JText::_('VBDAILYPRICES').": </span><br/><table>\n";
	$currencysymb = VikBooking::getCurrencySymb(true);
	foreach ($prices as $pr) {
		$fprice .= "<tr><td>".$pr['name'].": </td><td>".$currencysymb." <input type=\"number\" min=\"0\" step=\"any\" name=\"dprice".$pr['id']."\" value=\"\" style=\"width: 70px !important;\"/></td>";
		if (!empty($pr['attr'])) {
			$colsp = "4";
			$fprice .= "<td>".$pr['attr']."</td><td><input type=\"text\" name=\"dattr".$pr['id']."\" value=\"\" size=\"10\"/></td>";
		}
		$fprice .= "</tr>\n";
	}
	$fprice .= "<tr><td colspan=\"".$colsp."\" align=\"right\"><input type=\"submit\" class=\"vbsubmitfares\" name=\"newdispcost\" value=\"".JText::_('VBINSERT')."\"/></td></tr></table></div><input type=\"hidden\" name=\"cid[]\" value=\"".$idroom."\"/><input type=\"hidden\" name=\"task\" value=\"tariffs\"/></form>";
}
$chroomsel = "<select name=\"cid[]\" onchange=\"javascript: document.vbchroom.submit();\">\n";
foreach ($allc as $cc) {
	$chroomsel .= "<option value=\"".$cc['id']."\"".($cc['id'] == $idroom ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
}
$chroomsel .= "</select>\n";
$chroomf = "<form name=\"vbchroom\" method=\"post\" action=\"index.php?option=com_vikbooking\"><input type=\"hidden\" name=\"task\" value=\"tariffs\"/>".JText::_('VBSELVEHICLE').": ".$chroomsel."</form>";
echo "<table><tr><td colspan=\"2\" valign=\"top\" align=\"left\"><div class=\"vbadminfaresctitle\">".$name." - ".JText::_('VBINSERTFEE')." <span style=\"float: right; text-transform: none;\">".$chroomf."</span></div></td></tr><tr><td valign=\"top\" align=\"left\">".$img."</td><td valign=\"top\" align=\"left\">".$fprice."</td></tr></table><br/>\n";
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#ddaysfrom').change(function() {
		var fnights = parseInt(jQuery(this).val());
		if (!isNaN(fnights)) {
			jQuery('#ddaysto').attr('min', fnights);
			var tnights = jQuery('#ddaysto').val();
			if (!(tnights.length > 0)) {
				jQuery('#ddaysto').val(fnights);
			} else {
				if (parseInt(tnights) < fnights) {
					jQuery('#ddaysto').val(fnights);
				}
			}
		}
	});
});
</script>

<?php
//page content

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOTARFOUND'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikbooking">
	</form>
	<?php
} else {
	$mainframe = JFactory::getApplication();
	$lim = $mainframe->getUserStateFromRequest("com_vikbooking.limit", 'limit', 15, 'int');
	$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
	$allpr = array();
	$tottar = array();
	foreach ($rows as $r) {
		if (!array_key_exists($r['idprice'], $allpr)) {
			$allpr[$r['idprice']]=VikBooking::getPriceAttr($r['idprice']);
		}
		$tottar[$r['days']][] = $r;
	}
	$prord = array();
	$prvar = '';
	foreach ($allpr as $kap => $ap) {
		$prord[] = $kap;
		$prvar .= "<th class=\"title center\" width=\"150\">".VikBooking::getPriceName($kap).(!empty($ap) ? " - ".$ap : "")."</th>\n";
	}
	$totrows = count($tottar);
	$tottar = array_slice($tottar, $lim0, $lim, true);
	?>
<script type="text/javascript">
function vbRateSetTask(event) {
	event.preventDefault();
	document.getElementById('vbtarmod').value = '1';
	document.getElementById('vbtask').value = 'rooms';
	document.adminForm.submit();
}
</script>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th class="title left" width="100" style="text-align: left;"><?php echo JText::_( 'VBPVIEWTARONE' ); ?></th>
			<?php echo $prvar; ?>
			<th width="20" class="title right" style="text-align: right;">
				<input type="submit" name="modtar" value="<?php echo JText::_( 'VBPVIEWTARTWO' ); ?>" onclick="vbRateSetTask(event);" class="btn" /> &nbsp; <input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
		</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	foreach ($tottar as $kt => $vt) {
		?>
		<tr class="row<?php echo $k; ?>">
			<td class="left"><?php echo $kt; ?></td>
		<?php
		$multiid = "";
		foreach ($prord as $ord) {
			$thereis = false;
			foreach ($vt as $kkkt => $vvv) {
				if ($vvv['idprice'] == $ord) {
					$multiid .= $vvv['id'].";";
					echo "<td class=\"center\"><input type=\"number\" min=\"0\" step=\"any\" name=\"cost".$vvv['id']."\" value=\"".$vvv['cost']."\" style=\"width: 70px !important;\"/>".(!empty($vvv['attrdata'])? " - <input type=\"text\" name=\"attr".$vvv['id']."\" value=\"".$vvv['attrdata']."\" size=\"10\"/>" : "")."</td>\n";
					$thereis = true;
					break;
				}
			}
			
			if (!$thereis) {
				echo "<td></td>\n";
			}
			unset($thereis);
			
		}
		?>
		<td class="right" style="text-align: right;"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $multiid; ?>" onclick="Joomla.isChecked(this.checked);"></td>
		</tr>
		<?php
		unset($multiid);
		$k = 1 - $k;
		$i++;
	}
	?>
	</table>
</div>
	<input type="hidden" name="roomid" value="<?php echo $roomrows['id']; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $roomrows['id']; ?>" />
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" id="vbtask" value="tariffs" />
	<input type="hidden" name="tarmod" id="vbtarmod" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php
	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $totrows, $lim0, $lim );
	$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
	echo $navbut;
	?>
</form>
<?php
}