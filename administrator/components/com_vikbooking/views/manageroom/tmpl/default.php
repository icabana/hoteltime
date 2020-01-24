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
$cats = $this->cats;
$carats = $this->carats;
$optionals = $this->optionals;
$adultsdiff = $this->adultsdiff;

JHtml::_('jquery.framework', true, true);
JHtml::_('script', VBO_SITE_URI.'resources/jquery-ui.sortable.min.js', false, true, false, false);
$vbo_app = new VboApplication();
$currencysymb = VikBooking::getCurrencySymb(true);
$arrcats = array();
$arrcarats = array();
$arropts = array();
$oldcats = count($row) ? explode(";", $row['idcat']) : array();
foreach ($oldcats as $oc) {
	if (!empty($oc)) {
		$arrcats[$oc] = $oc;
	}
}
$oldcarats = count($row) ? explode(";", $row['idcarat']) : array();
foreach ($oldcarats as $ocr) {
	if (!empty($ocr)) {
		$arrcarats[$ocr] = $ocr;
	}
}
$oldopts = count($row) ? explode(";", $row['idopt']) : array();
foreach ($oldopts as $oopt) {
	if (!empty($oopt)) {
		$arropts[$oopt] = $oopt;
	}
}
if (is_array($cats)) {
	$wcats = "<tr><td width=\"200\" class=\"vbo-config-param-cell\"> <b>".JText::_('VBNEWROOMONE')."</b> </td><td>";
	$wcats .= "<select name=\"ccat[]\" multiple=\"multiple\" size=\"".(count($cats) + 1)."\">";
	foreach ($cats as $cat) {
		$wcats .= "<option value=\"".$cat['id']."\"".(array_key_exists($cat['id'], $arrcats) ? " selected=\"selected\"" : "").">".$cat['name']."</option>\n";
	}
	$wcats .= "</select></td></tr>\n";
} else {
	$wcats = "";
}
if (is_array($carats)) {
	$wcarats = "<tr><td width=\"200\" class=\"vbo-config-param-cell\"> <b>".JText::_('VBNEWROOMTHREE').":</b> </td><td>";
	$wcarats .= "<div class=\"vbo-roomentries-cont\">";
	$nn = 0;
	foreach ($carats as $kcarat => $carat) {
		$wcarats .= "<div class=\"vbo-roomentry-cont\"><input type=\"checkbox\" name=\"ccarat[]\" id=\"carat".$kcarat."\" value=\"".$carat['id']."\"".(array_key_exists($carat['id'], $arrcarats) ? " checked=\"checked\"" : "")."/> <label for=\"carat".$kcarat."\">".$carat['name']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$wcarats .= "</div>\n<div class=\"vbo-roomentries-cont\">";
		}
	}
	$wcarats .= "</div>\n";
	$wcarats .= "</td></tr>\n";
} else {
	$wcarats = "";
}
if (is_array($optionals)) {
	$woptionals = "<tr><td width=\"200\" class=\"vbo-config-param-cell\"> <b>".JText::_('VBNEWROOMFOUR').":</b> </td><td>";
	$woptionals .= "<div class=\"vbo-roomentries-cont\">";
	$nn = 0;
	foreach ($optionals as $kopt => $optional) {
		$woptionals .= "<div class=\"vbo-roomentry-cont\"><input type=\"checkbox\" name=\"coptional[]\" id=\"opt".$kopt."\" value=\"".$optional['id']."\"".(array_key_exists($optional['id'], $arropts) ? " checked=\"checked\"" : "")."/> <label for=\"opt".$kopt."\">".$optional['name']." ".(empty($optional['ageintervals']) ? $currencysymb."".$optional['cost'] : "")."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$woptionals .= "</div>\n<div class=\"vbo-roomentries-cont\">";
		}
	}
	$woptionals .= "</div>\n";
	$woptionals .= "</td></tr>\n";
} else {
	$woptionals = "";
}
//more images
$morei = count($row) ? explode(';;', $row['moreimgs']) : array();
$totmorei = count($morei);
$actmoreimgs = "";
if ($totmorei > 0) {
	$notemptymoreim = false;
	$imgcaptions = json_decode($row['imgcaptions'], true);
	$usecaptions = empty($imgcaptions) || !is_array($imgcaptions) || !(count($imgcaptions) > 0) ? false : true;
	foreach ($morei as $ki => $mi) {
		if (!empty($mi)) {
			$notemptymoreim = true;
			$actmoreimgs .= '<li class="vbo-editroom-currentphoto">';
			$actmoreimgs .= '<a href="'.VBO_SITE_URI.'resources/uploads/big_'.$mi.'" target="_blank" class="vbomodal"><img src="'.VBO_SITE_URI.'resources/uploads/thumb_'.$mi.'" class="maxfifty"/></a>';
			$actmoreimgs .= '<a class="vbo-toggle-imgcaption" href="javascript: void(0);" onclick="vbOpenImgDetails(\''.$ki.'\', this)"><i class="fa fa-cog"></i></a>';
			$actmoreimgs .= '<div id="vbimgdetbox'.$ki.'" class="vbimagedetbox" style="display: none;"><div class="captionlabel"><span>'.JText::_('VBIMGCAPTION').'</span><input type="text" name="caption'.$ki.'" value="'.($usecaptions === true ? $imgcaptions[$ki] : "").'" size="40"/></div><input type="hidden" name="imgsorting[]" value="'.$mi.'"/><input class="captionsubmit" type="button" name="updcatpion" value="'.JText::_('VBIMGUPDATE').'" onclick="javascript: updateCaptions();"/><div class="captionremoveimg"><a class="vbimgrm btn btn-danger" href="index.php?option=com_vikbooking&task=removemoreimgs&roomid='.$row['id'].'&imgind='.$ki.'" title="'.JText::_('VBREMOVEIMG').'"><i class="icon-remove"></i>'.JText::_('VBREMOVEIMG').'</a></div></div>';
			$actmoreimgs .= '</li>';
		}
	}
	if ($notemptymoreim) {
		$actmoreimgs .= '<br clear="all"/>';
	}
}
//end more images
//num adults charges/discounts only if the max numb of adults allowed is > than 1 and the minimum is less than the maximum 
$writeadultsdiff = false;
if (count($row) && $row['toadult'] > 1 && $row['fromadult'] < $row['toadult']) {
	$writeadultsdiff = true;
	$stradultsdiff = "";
	$startadind = $row['fromadult'] > 0 ? $row['fromadult'] : 1;
	$parseadultsdiff = array();
	if (@is_array($adultsdiff)) {
		foreach ($adultsdiff as $adiff) {
			$parseadultsdiff[$adiff['adults']] = $adiff;
		}
	}
	for ($adi = $startadind; $adi <= $row['toadult']; $adi++) {
		$stradultsdiff .= "<p>";
		$stradultsdiff .= JText::sprintf('VBADULTSDIFFNUM', $adi)." <select name=\"adultsdiffchdisc[]\"><option value=\"1\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['chdisc'] == 1 ? " selected=\"selected\"" : "").">".JText::_('VBADULTSDIFFCHDISCONE')."</option><option value=\"2\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['chdisc'] == 2 ? " selected=\"selected\"" : "").">".JText::_('VBADULTSDIFFCHDISCTWO')."</option></select>\n";
		$stradultsdiff .= "<input type=\"number\" step=\"any\" name=\"adultsdiffval[]\" value=\"".(array_key_exists($adi, $parseadultsdiff) ? $parseadultsdiff[$adi]['value'] : "")."\" size=\"3\" style=\"width: 40px;\"/><input type=\"hidden\" name=\"adultsdiffnum[]\" value=\"".$adi."\"/>\n";
		$stradultsdiff .= "<select name=\"adultsdiffvalpcent[]\"><option value=\"1\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['valpcent'] == 1 ? " selected=\"selected\"" : "").">".$currencysymb."</option><option value=\"2\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['valpcent'] == 2 ? " selected=\"selected\"" : "").">%</option></select>\n";
		$stradultsdiff .= "<select name=\"adultsdiffpernight[]\"><option value=\"0\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['pernight'] == 0 ? " selected=\"selected\"" : "").">".JText::_('VBADULTSDIFFONTOTAL')."</option><option value=\"1\"".(array_key_exists($adi, $parseadultsdiff) && $parseadultsdiff[$adi]['pernight'] == 1 ? " selected=\"selected\"" : "").">".JText::_('VBADULTSDIFFONPERNIGHT')."</option></select>\n";
		$stradultsdiff .= "</p>\n";
	}
}
//
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
?>
<script type="text/javascript">
//Code to debug the size of the form to be submitted in case it will exceed the PHP post_max_size
/*
Joomla.submitbutton = function(task) {
	console.log(jQuery("#adminForm").not("[type='file']").serialize().length);
	Joomla.submitform(task, document.adminForm);
}
*/
//
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function showResizeSelMore() {
	if (document.adminForm.autoresizemore.checked == true) {
		document.getElementById('resizeselmore').style.display='block';
	} else {
		document.getElementById('resizeselmore').style.display='none';
	}
	return true;
}
function addMoreImages() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreimagescounter');
	var num = (document.getElementById('moreimagescounter').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id', divIdName);
	newdiv.setAttribute('class', 'vbo-first-imgup');
	newdiv.innerHTML = '<input type=\'file\' name=\'cimgmore[]\' size=\'35\'/> <span><?php echo addslashes(JText::_('VBIMGCAPTION')); ?></span> <input type=\'text\' name=\'cimgcaption[]\' size=\'30\' value=\'\'/><br/>';
	ni.appendChild(newdiv);
}
function vbPlusMinus(what, how) {
	var inp = document.getElementById(what);
	var actval = inp.value;
	var newval = 0;
	if (how == 'plus') {
		newval = parseInt(actval) + 1;
	} else {
		if (parseInt(actval) >= 1) {
			newval = parseInt(actval) - 1;
		}
	}
	inp.value = newval;
	<?php
	if ($writeadultsdiff == true) {
		?>
		var origfrom = <?php echo $row['fromadult']; ?>;
		var origto = <?php echo $row['toadult']; ?>;
		if (what == 'fromadult') {
			if (newval == origfrom) {
				document.getElementById('vbadultsdiffsavemess').style.display = 'none';
				document.getElementById('vbadultsdiffbox').style.display = 'block';
			} else {
				document.getElementById('vbadultsdiffbox').style.display = 'none';
				document.getElementById('vbadultsdiffsavemess').style.display = 'block';
			}
		}
		if (what == 'toadult') {
			if (newval == origto) {
				document.getElementById('vbadultsdiffsavemess').style.display = 'none';
				document.getElementById('vbadultsdiffbox').style.display = 'block';
			} else {
				document.getElementById('vbadultsdiffbox').style.display = 'none';
				document.getElementById('vbadultsdiffsavemess').style.display = 'block';
			}
		}
		<?php
	}
	?>
	if (what == 'toadult' || what == 'tochild') {
		vbMaxTotPeople();
	}
	if (what == 'fromadult' || what == 'fromchild') {
		vbMinTotPeople();
	}
	return true;
}
function vbMaxTotPeople() {
	var toadu = document.getElementById('toadult').value;
	var tochi = document.getElementById('tochild').value;
	document.getElementById('totpeople').value = parseInt(toadu) + parseInt(tochi);
	return true;
}
function vbMinTotPeople() {
	var fadu = document.getElementById('fromadult').value;
	var fchi = document.getElementById('fromchild').value;
	document.getElementById('mintotpeople').value = parseInt(fadu) + parseInt(fchi);
	return true;
}
function togglePriceCalendarParam() {
	if (parseInt(document.getElementById('pricecal').value) == 1) {
		document.getElementById('defcalcostp').style.display = 'table-row';
		jQuery('.param-pricecal').addClass("vbroomparampactive");
	} else {
		document.getElementById('defcalcostp').style.display = 'none';
		jQuery('.param-pricecal').removeClass("vbroomparampactive");
	}
}
function toggleSeasonalCalendarParam() {
	if (parseInt(document.getElementById('seasoncal').value) > 0) {
		jQuery('.param-seasoncal').addClass("vbroomparampactive").show();
	} else {
		jQuery('.param-seasoncal').removeClass("vbroomparampactive");
		jQuery('.param-seasoncal').each(function(k, v) {
			if (k > 0) {
				jQuery(this).hide();
			}
		});
	}
}
var vbo_details_on = false;
function vbOpenImgDetails(key, el) {
	if (vbo_details_on === true) {
		jQuery('.vbimagedetbox').not('#vbimgdetbox'+key).hide();
		jQuery('.vbo-toggle-imgcaption.vbo-toggle-imgcaption-on').removeClass('vbo-toggle-imgcaption-on');
	}
	if (document.getElementById('vbimgdetbox'+key).style.display == 'none') {
		document.getElementById('vbimgdetbox'+key).style.display = 'block';
		jQuery(el).addClass('vbo-toggle-imgcaption-on');
		vbo_details_on = true;
	} else {
		document.getElementById('vbimgdetbox'+key).style.display = 'none';
		jQuery(el).removeClass('vbo-toggle-imgcaption-on');
		vbo_details_on = false;
	}
}
function updateCaptions() {
	var ni = document.adminForm;
	var newdiv = document.createElement('div');
	newdiv.innerHTML = '<input type=\'hidden\' name=\'updatecaption\' value=\'1\'/>';
	ni.appendChild(newdiv);
	document.adminForm.task.value='updateroom';
	document.adminForm.submit();
}
/* Start - Room Disctinctive Features */
var cur_units = <?php echo count($row) ? $row['units'] : '1'; ?>;
jQuery(document).ready(function() {
	jQuery(".vbo-sortable").sortable({
		helper: 'clone'
	});
	jQuery(".vbo-sortable").disableSelection();
	jQuery('#vbo-distfeatures-toggle').click(function() {
		jQuery(this).toggleClass('btn-primary');
		jQuery('.vbo-distfeatures-cont').fadeToggle();
	});
	jQuery('#room_units').change(function() {
		var to_units = parseInt(jQuery(this).val());
		if (to_units > 1) {
			jQuery('.param-multiunits').show();
			jQuery('.vbo-distfeature-row').css('display', 'table-row');
		} else {
			jQuery('.param-multiunits').hide();
			jQuery('.vbo-distfeature-row').css('display', 'none');
		}
		if (to_units > cur_units) {
			var diff_units = (to_units - cur_units);
			for (var i = 1; i <= diff_units; i++) {
				var unit_html = "<div class=\"vbo-runit-features-cont\" id=\"runit-features-"+(i + cur_units)+"\">"+
								"	<span class=\"vbo-runit-num\"><?php echo addslashes(JText::_('VBODISTFEATURERUNIT')); ?>"+(i + cur_units)+"</span>"+
								"	<div class=\"vbo-runit-features\">"+
								"		<div class=\"vbo-runit-feature\">"+
								"			<input type=\"text\" name=\"feature-name"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VBODISTFEATURETXT'); ?>\"/>"+
								"			<input type=\"hidden\" name=\"feature-lang"+(i + cur_units)+"[]\" value=\"\"/>"+
								"			<input type=\"text\" name=\"feature-value"+(i + cur_units)+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VBODISTFEATUREVAL'); ?>\"/>"+
								"			<span class=\"vbo-feature-remove\"><i class=\"fa fa-minus-circle\"></i></span>"+
								"		</div>"+
								"		<span class=\"vbo-feature-add btn\"><i class=\"icon-new\"></i><?php echo addslashes(JText::_('VBODISTFEATUREADD')); ?></span>"+
								"	</div>"+
								"</div>";
				jQuery('.vbo-distfeatures-cont').append(unit_html);
			}
			cur_units = to_units;
		}else if (to_units < cur_units) {
			for (var i = cur_units; i > to_units; i--) {
				jQuery('#runit-features-'+i).remove();
			}
			cur_units = to_units;
		}
	});
});
jQuery(document.body).on('click', '.vbo-feature-add', function() {
	var cfeature_id = jQuery(this).parent('div').parent('div').attr('id').split('runit-features-');
	if (cfeature_id[1].length) {
		jQuery(this).before("<div class=\"vbo-runit-feature\">"+
							"	<input type=\"text\" name=\"feature-name"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VBODISTFEATURETXT'); ?>\"/>"+
							"	<input type=\"hidden\" name=\"feature-lang"+cfeature_id[1]+"[]\" value=\"\"/>"+
							"	<input type=\"text\" name=\"feature-value"+cfeature_id[1]+"[]\" value=\"\" size=\"20\" placeholder=\"<?php echo JText::_('VBODISTFEATUREVAL'); ?>\"/>"+
							"	<span class=\"vbo-feature-remove\"><i class=\"fa fa-minus-circle\"></i></span>"+
							"</div>"
							);
	}
});
jQuery(document.body).on('click', '.vbo-feature-remove', function() {
	jQuery(this).parent('div').remove();
});
/* End - Room Disctinctive Features */
</script>
<?php
$vbo_app->prepareModalBox('.vbomodal', '', true);
?>
<input type="hidden" value="0" id="moreimagescounter" />

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	
	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBOROOMLEGUNITOCC'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMFIVE'); ?></b> </td>
					<td><input type="text" name="cname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMEIGHT'); ?></b> </td>
					<td><?php echo $vbo_app->printYesNoButtons('cavail', JText::_('VBYES'), JText::_('VBNO'), ((count($row) && intval($row['avail']) == 1) || !count($row) ? 'yes' : 0), 'yes', 0); ?></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMNINE'); ?></b> </td>
					<td><input type="number" min="1" name="units" id="room_units" value="<?php echo count($row) ? $row['units'] : '1'; ?>" size="3" onfocus="this.select();" /></td>
				</tr>
				<?php
				$room_features = count($row) ? VikBooking::getRoomParam('features', $row['params']) : array(1 => VikBooking::getDefaultDistinctiveFeatures());
				if (!is_array($room_features)) {
					$room_features = array();
				}
				if (!(count($room_features) > 0)) {
					$default_features = VikBooking::getDefaultDistinctiveFeatures();
					for ($i = 1; $i <= $row['units']; $i++) {
						$room_features[$i] = $default_features;
					}
				}
				?>
				<tr class="vbo-distfeature-row" style="display: <?php echo count($row) && $row['units'] > 1 ? 'table-row' : 'none'; ?>;">
					<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBOROOMUNITSDISTFEAT'); ?></b> </td>
					<td>
						<div class="vbo-distfeatures-toggle-cont">
							<span id="vbo-distfeatures-toggle" class="btn btn-primary"><i class="icon-eye"></i><?php echo JText::_('VBOROOMUNITSDISTFEATTOGGLE'); ?></span>
						</div>
						<div class="vbo-distfeatures-cont">
						<?php
						$unitslim = count($row) ? $row['units'] : 1;
						for ($i=1; $i <= $unitslim; $i++) {
							?>
							<div class="vbo-runit-features-cont" id="runit-features-<?php echo $i; ?>">
								<span class="vbo-runit-num"><?php echo JText::_('VBODISTFEATURERUNIT'); ?><?php echo $i; ?></span>
								<div class="vbo-runit-features">
							<?php
							if (array_key_exists($i, $room_features)) {
								foreach ($room_features[$i] as $fkey => $fval) {
									?>
									<div class="vbo-runit-feature">
										<input type="text" name="feature-name<?php echo $i; ?>[]" value="<?php echo JText::_($fkey); ?>" size="20"/>
										<input type="hidden" name="feature-lang<?php echo $i; ?>[]" value="<?php echo $fkey; ?>"/>
										<input type="text" name="feature-value<?php echo $i; ?>[]" value="<?php echo $fval; ?>" size="20"/>
										<span class="vbo-feature-remove"><i class="fa fa-minus-circle"></i></span>
									</div>
									<?php
								}
							}
							?>
									<span class="vbo-feature-add btn"><i class="icon-new"></i><?php echo JText::_('VBODISTFEATUREADD'); ?></span>
								</div>
							</div>
							<?php
						}
						?>
						</div>
					</td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMADULTS'); ?></b> </td>
					<td><div class="vbplusminuscont"><?php echo JText::_('VBNEWROOMMIN'); ?> <input type="number" min="0" id="fromadult" name="fromadult" value="<?php echo count($row) ? $row['fromadult'] : '1'; ?>" size="4" onchange="vbMinTotPeople();" style="width: 40px;"/></div><div onclick="vbPlusMinus('fromadult', 'plus');" class="vbplusminus"><i class="fa fa-plus-circle"></i></div><div onclick="vbPlusMinus('fromadult', 'minus');" class="vbminus vbplusminus"><i class="fa fa-minus-circle"></i></div><br clear="all"/><div class="vbplusminuscont"><?php echo JText::_('VBNEWROOMMAX'); ?> <input type="number" min="0" id="toadult" name="toadult" value="<?php echo count($row) ? $row['toadult'] : '1'; ?>" size="3" onchange="vbMaxTotPeople();" style="width: 40px;"/></div><div onclick="vbPlusMinus('toadult', 'plus');" class="vbplusminus"><i class="fa fa-plus-circle"></i></div><div onclick="vbPlusMinus('toadult', 'minus');" class="vbminus vbplusminus"><i class="fa fa-minus-circle"></i></div><br clear="all"/></td>
				</tr>
			<?php
			if ($writeadultsdiff == true) {
				?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMADULTSDIFF'); ?></b><br/><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWROOMADULTSDIFF'), 'content' => JText::_('VBNEWROOMADULTSDIFFHELP'))); ?> </td>
					<td><div id="vbadultsdiffsavemess" style="display: none; width: 50%;"><i><?php echo JText::_('VBNEWROOMNOTCHANGENUMMESS'); ?></i></div><div id="vbadultsdiffbox" style="display: block;"><?php echo $stradultsdiff; ?></div></td>
				</tr>
				<?php
			} else {
				?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMADULTSDIFF'); ?></b><br/><?php echo $vbo_app->createPopover(array('title' => JText::_('VBNEWROOMADULTSDIFF'), 'content' => JText::_('VBNEWROOMADULTSDIFFHELP'))); ?> </td>
					<td><div style="display: block; width: 50%;"><i><?php echo JText::_('VBNEWROOMADULTSDIFFBEFSAVE'); ?></i></div></td>
				</tr>
				<?php
			}
			?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMCHILDREN'); ?></b> </td>
					<td><div class="vbplusminuscont"><?php echo JText::_('VBNEWROOMMIN'); ?> <input type="number" min="0" id="fromchild" name="fromchild" value="<?php echo count($row) ? $row['fromchild'] : '0'; ?>" size="3" onchange="vbMinTotPeople();" style="width: 40px;"/></div><div onclick="vbPlusMinus('fromchild', 'plus');" class="vbplusminus"><i class="fa fa-plus-circle"></i></div><div onclick="vbPlusMinus('fromchild', 'minus');" class="vbminus vbplusminus"><i class="fa fa-minus-circle"></i></div><br clear="all"/><div class="vbplusminuscont"><?php echo JText::_('VBNEWROOMMAX'); ?> <input type="number" min="0" id="tochild" name="tochild" value="<?php echo count($row) ? $row['tochild'] : '0'; ?>" size="3" onchange="vbMaxTotPeople();" style="width: 40px;"/></div><div onclick="vbPlusMinus('tochild', 'plus');" class="vbplusminus"><i class="fa fa-plus-circle"></i></div><div onclick="vbPlusMinus('tochild', 'minus');" class="vbminus vbplusminus"><i class="fa fa-minus-circle"></i></div><br clear="all"/></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBMAXTOTPEOPLE'); ?></b> </td>
					<td><input type="number" min="1" name="totpeople" id="totpeople" value="<?php echo count($row) ? $row['totpeople'] : '1'; ?>" size="3" style="width: 40px;"/> <i><?php echo JText::_('VBMAXTOTPEOPLEDESC'); ?></i></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBMINTOTPEOPLE'); ?></b> </td>
					<td><input type="number" min="1" name="mintotpeople" id="mintotpeople" value="<?php echo count($row) ? $row['mintotpeople'] : '1'; ?>" size="3" style="width: 40px;"/> <i><?php echo JText::_('VBMINTOTPEOPLEDESC'); ?></i></td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBOROOMLEGPHOTODESC'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMSIX'); ?></b> </td>
					<td><?php echo (count($row) && !empty($row['img']) && file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/'.$row['img'].'" class="vbomodal" target="_blank">'.$row['img'].'</a> &nbsp;' : ""); ?><input type="file" name="cimg" size="35"/><br/><label style="display: inline;" for="autoresize"><?php echo JText::_('VBNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="250" size="3"/> px</span></td>
				</tr>
			<?php
			if (count($row)) {
			?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBMOREIMAGES'); ?></b><br/><a href="javascript: void(0);" onclick="addMoreImages();" class="btn"><i class="icon-new"></i><?php echo JText::_('VBADDIMAGES'); ?></a><div class="vbo-bulkupload-cont"><div class="vbo-bulkupload-inner"><a href="javascript: void(0);" onclick="showBulkUpload();" class="btn"><i class="icon-image"></i><?php echo JText::_('VBOBULKUPLOAD'); ?></a></div></div></td>
					<td><div class="vbo-rmphotos-cont"><a class="btn btn-danger" href="index.php?option=com_vikbooking&amp;task=removemoreimgs&amp;roomid=<?php echo $row['id']; ?>&amp;imgind=-1" onclick="return confirm('<?php echo addslashes(JText::_('VBORMALLPHOTOS')); ?>?');"><i class="icon-cancel"></i><?php echo JText::_('VBORMALLPHOTOS'); ?></a></div><div class="vbo-editroom-currentphotos"><ul class="vbo-sortable"><?php echo $actmoreimgs; ?></ul></div><div class="vbo-first-imgup"><input type="file" name="cimgmore[]" size="35"/> <span><?php echo JText::_('VBIMGCAPTION'); ?></span> <input type="text" name="cimgcaption[]" size="30" value=""/></div><div id="myDiv" style="display: block;"></div><label style="display: inline;" for="autoresizemore"><?php echo JText::_('VBRESIZEIMAGES'); ?></label> <input type="checkbox" id="autoresizemore" name="autoresizemore" value="1" onclick="showResizeSelMore();"/> <span id="resizeselmore" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizetomore" value="600" size="3"/> px</span></td>
				</tr>
			<?php
			} else {
				?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBMOREIMAGES'); ?></b><br/><a class="btn" href="javascript: void(0);" onclick="addMoreImages();"><i class="icon-new"></i><?php echo JText::_('VBADDIMAGES'); ?></a><p class="vbo-small-p-info"><?php echo JText::_('VBOBULKUPLOADAFTERSAVE'); ?></p></td>
					<td><input type="file" name="cimgmore[]" size="35"/> <span><?php echo JText::_('VBIMGCAPTION'); ?></span> <input type="text" name="cimgcaption[]" size="30" value=""/><div id="myDiv" style="display: block;"></div><label style="display: inline;" for="autoresizemore"><?php echo JText::_('VBRESIZEIMAGES'); ?></label> <input type="checkbox" id="autoresizemore" name="autoresizemore" value="1" onclick="showResizeSelMore();"/> <span id="resizeselmore" style="display: none;">&nbsp;<?php echo JText::_('VBNEWOPTTEN'); ?>: <input type="text" name="resizetomore" value="600" size="3"/> px</span></td>
				</tr>
				<?php
			}
			?>
				<tr>
					<td width="200" class="vbo-config-param-cell"> <b><?php echo JText::_('VBNEWROOMSMALLDESC'); ?></b> </td>
					<td><textarea name="smalldesc" rows="6" cols="50"><?php echo count($row) ? $row['smalldesc'] : ''; ?></textarea></td>
				</tr>
				<tr>
					<td width="200" class="vbo-config-param-cell" style="vertical-align: top !important;"> <b><?php echo JText::_('VBNEWROOMSEVEN'); ?></b> </td>
					<td><?php echo $editor->display( "cdescr", (count($row) ? $row['info'] : ""), '100%', 300, 70, 20 ); ?></td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBOROOMLEGCARATCATOPT'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<?php echo $wcats; ?>
				<?php echo $wcarats; ?>
				<?php echo $woptionals; ?>
			</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VBNEWROOMPARAMS'); ?></legend>
		<table cellspacing="1" class="admintable table">
			<tbody>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="lastavail"><?php echo JText::_('VBPARAMLASTAVAIL'); ?></label> </td>
					<td><input type="text" name="lastavail" id="lastavail" value="<?php echo count($row) ? VikBooking::getRoomParam('lastavail', $row['params']) : ''; ?>" size="2"/><span><?php echo JText::_('VBPARAMLASTAVAILHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="custprice"><?php echo JText::_('VBPARAMCUSTPRICE'); ?></label> </td>
					<td><input type="text" name="custprice" id="custprice" value="<?php echo count($row) ? VikBooking::getRoomParam('custprice', $row['params']) : ''; ?>" size="5"/><span><?php echo JText::_('VBPARAMCUSTPRICEHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="custpricetxt"><?php echo JText::_('VBPARAMCUSTPRICETEXT'); ?></label> </td>
					<td><input type="text" name="custpricetxt" id="custpricetxt" value="<?php echo count($row) ? VikBooking::getRoomParam('custpricetxt', $row['params']) : ''; ?>" size="9"/><span><?php echo JText::_('VBPARAMCUSTPRICETEXTHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="custpricesubtxt"><?php echo JText::_('VBPARAMCUSTPRICESUBTEXT'); ?></label> </td>
					<td><input type="text" name="custpricesubtxt" id="custpricesubtxt" value="<?php echo count($row) ? htmlentities(VikBooking::getRoomParam('custpricesubtxt', $row['params'])) : ''; ?>" size="31"/><span><?php echo JText::_('VBPARAMCUSTPRICESUBTEXTHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="reqinfo"><?php echo JText::_('VBORPARAMREQINFO'); ?></label> </td>
					<td><?php echo $vbo_app->printYesNoButtons('reqinfo', JText::_('VBYES'), JText::_('VBNO'), (count($row) && intval(VikBooking::getRoomParam('reqinfo', $row['params'])) == 1 ? 1 : 0), 1, 0); ?></td>
				</tr>
				<?php
				$paramshowpeople = count($row) ? VikBooking::getRoomParam('maxminpeople', $row['params']) : '';
				?>
				<tr class="vbroomparamp">
					<td width="200" class="vbo-config-param-cell"> <label for="maxminpeople"><?php echo JText::_('VBPARAMSHOWPEOPLE'); ?></label> </td>
					<td><select name="maxminpeople" id="maxminpeople"><option value="0"><?php echo JText::_('VBPARAMSHOWPEOPLENO'); ?></option><option value="1"<?php echo ($paramshowpeople == "1" ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMSHOWPEOPLEADU'); ?></option><option value="2"<?php echo ($paramshowpeople == "2" ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMSHOWPEOPLECHI'); ?></option><option value="3"<?php echo ($paramshowpeople == "3" ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMSHOWPEOPLEADUTOT'); ?></option><option value="4"<?php echo ($paramshowpeople == "4" ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMSHOWPEOPLECHITOT'); ?></option><option value="5"<?php echo ($paramshowpeople == "5" ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMSHOWPEOPLEALLTOT'); ?></option></select></td>
				</tr>
				<tr class="vbroomparamp param-pricecal">
					<td width="200" class="vbo-config-param-cell"> <label for="pricecal"><?php echo JText::_('VBPARAMPRICECALENDAR'); ?></label> </td>
					<td><select name="pricecal" id="pricecal" onchange="togglePriceCalendarParam();"><option value="0"><?php echo JText::_('VBPARAMPRICECALENDARDISABLED'); ?></option><option value="1"<?php echo (count($row) && intval(VikBooking::getRoomParam('pricecal', $row['params'])) == 1 ? ' selected="selected"' : ''); ?>><?php echo JText::_('VBPARAMPRICECALENDARENABLED'); ?></option></select><span><?php echo JText::_('VBPARAMPRICECALENDARHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp param-pricecal" id="defcalcostp" style="display: <?php echo (count($row) && intval(VikBooking::getRoomParam('pricecal', $row['params'])) == 1 ? 'table-row' : 'none'); ?>;">
					<td width="200" class="vbo-config-param-cell"> <label for="defcalcost"><?php echo JText::_('VBPARAMDEFCALCOST'); ?></label> </td>
					<td><input type="text" name="defcalcost" id="defcalcost" size="4" value="<?php echo count($row) ? VikBooking::getRoomParam('defcalcost', $row['params']) : ''; ?>" placeholder="50.00"/><span><?php echo JText::_('VBPARAMDEFCALCOSTHELP'); ?></span></td>
				</tr>
				<?php
				$season_cal = count($row) ? VikBooking::getRoomParam('seasoncal', $row['params']) : 0;
				$season_cal_prices = count($row) ? VikBooking::getRoomParam('seasoncal_prices', $row['params']) : 0;
				$season_cal_restr = count($row) ? VikBooking::getRoomParam('seasoncal_restr', $row['params']) : 0;
				?>
				<tr class="vbroomparamp param-seasoncal">
					<td width="200" class="vbo-config-param-cell"> <label for="seasoncal"><?php echo JText::_('VBPARAMSEASONCALENDAR'); ?></label> </td>
					<td><select name="seasoncal" id="seasoncal" onchange="toggleSeasonalCalendarParam();"><option value="0"><?php echo JText::_('VBPARAMSEASONCALENDARDISABLED'); ?></option><optgroup label="<?php echo JText::_('VBPARAMSEASONCALENDARENABLED'); ?>"><option value="1"<?php echo intval($season_cal) == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMSEASONCALENDARENABLEDALL'); ?></option><option value="2"<?php echo intval($season_cal) == 2 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMSEASONCALENDARENABLEDCHARGEDISC'); ?></option><option value="3"<?php echo intval($season_cal) == 3 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMSEASONCALENDARENABLEDCHARGE'); ?></option></optgroup></select></td>
				</tr>
				<tr class="vbroomparamp param-seasoncal" style="display: <?php echo (intval($season_cal) > 0 ? 'table-row' : 'none'); ?>;">
					<td width="200" class="vbo-config-param-cell"> <label for="seasoncal_nights"><?php echo JText::_('VBPARAMSEASONCALNIGHTS'); ?></label> </td>
					<td><input type="text" name="seasoncal_nights" id="seasoncal_nights" size="10" value="<?php echo count($row) ? VikBooking::getRoomParam('seasoncal_nights', $row['params']) : ''; ?>" placeholder="1, 3, 7, 14"/><span><?php echo JText::_('VBPARAMSEASONCALNIGHTSHELP'); ?></span></td>
				</tr>
				<tr class="vbroomparamp param-seasoncal" style="display: <?php echo (intval($season_cal) > 0 ? 'table-row' : 'none'); ?>;">
					<td width="200" class="vbo-config-param-cell"> <label for="seasoncal_prices"><?php echo JText::_('VBPARAMSEASONCALENDARPRICES'); ?></label> </td>
					<td><select name="seasoncal_prices" id="seasoncal_prices"><option value="0"><?php echo JText::_('VBPARAMSEASONCALENDARPRICESANY'); ?></option><option value="1"<?php echo intval($season_cal_prices) == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMSEASONCALENDARPRICESLOW'); ?></option></select></td>
				</tr>
				<tr class="vbroomparamp param-seasoncal" style="display: <?php echo (intval($season_cal) > 0 ? 'table-row' : 'none'); ?>;">
					<td width="200" class="vbo-config-param-cell"> <label for="seasoncal_restr"><?php echo JText::_('VBPARAMSEASONCALENDARLOS'); ?></label> </td>
					<td><select name="seasoncal_restr" id="seasoncal_restr"><option value="0"><?php echo JText::_('VBPARAMSEASONCALENDARLOSHIDE'); ?></option><option value="1"<?php echo intval($season_cal_restr) == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMSEASONCALENDARLOSSHOW'); ?></option></select></td>
				</tr>
				<?php
				$multi_units = count($row) ? VikBooking::getRoomParam('multi_units', $row['params']) : 0;
				?>
				<tr class="vbroomparamp param-multiunits" style="display: <?php echo ($row['units'] > 0 ? 'table-row' : 'none'); ?>;">
					<td width="200" class="vbo-config-param-cell"> <label for="multi_units"><?php echo JText::_('VBPARAMROOMMULTIUNITS'); ?></label> </td>
					<td><select name="multi_units" id="multi_units"><option value="0"><?php echo JText::_('VBPARAMROOMMULTIUNITSDISABLE'); ?></option><option value="1"<?php echo intval($multi_units) == 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMROOMMULTIUNITSENABLE'); ?></option></select><span><?php echo JText::_('VBPARAMROOMMULTIUNITSHELP'); ?></span></td>
				</tr>
				<?php
				$custptitle = count($row) ? VikBooking::getRoomParam('custptitle', $row['params']) : '';
				$custptitlew = count($row) ? VikBooking::getRoomParam('custptitlew', $row['params']) : '';
				$metakeywords = count($row) ? VikBooking::getRoomParam('metakeywords', $row['params']) : '';
				$metadescription = count($row) ? VikBooking::getRoomParam('metadescription', $row['params']) : '';
				?>
				<tr class="vbroomparamp param-sef vbroomparampactive">
					<td width="200" class="vbo-config-param-cell"> <label for="sefalias"><?php echo JText::_('VBROOMSEFALIAS'); ?></label> </td>
					<td><input type="text" id="sefalias" name="sefalias" value="<?php echo count($row) ? $row['alias'] : ''; ?>" placeholder="double-room-superior"/></td>
				</tr>
				<tr class="vbroomparamp param-sef vbroomparampactive">
					<td width="200" class="vbo-config-param-cell"> <label for="custptitle"><?php echo JText::_('VBPARAMPAGETITLE'); ?></label> </td>
					<td><input type="text" id="custptitle" name="custptitle" value="<?php echo $custptitle; ?>"/> <span><select name="custptitlew"><option value="before"<?php echo $custptitlew == 'before' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMPAGETITLEBEFORECUR'); ?></option><option value="after"<?php echo $custptitlew == 'after' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMPAGETITLEAFTERCUR'); ?></option><option value="replace"<?php echo $custptitlew == 'replace' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VBPARAMPAGETITLEREPLACECUR'); ?></option></select></span></td>
				</tr>
				<tr class="vbroomparamp param-sef vbroomparampactive">
					<td width="200" class="vbo-config-param-cell"> <label for="metakeywords"><?php echo JText::_('VBPARAMKEYWORDSMETATAG'); ?></label> </td>
					<td><textarea name="metakeywords" id="metakeywords" rows="3" cols="40"><?php echo $metakeywords; ?></textarea></td>
				</tr>
				<tr class="vbroomparamp param-sef vbroomparampactive">
					<td width="200" class="vbo-config-param-cell"> <label for="metadescription"><?php echo JText::_('VBPARAMDESCRIPTIONMETATAG'); ?></label> </td>
					<td><textarea name="metadescription" id="metadescription" rows="4" cols="40"><?php echo $metadescription; ?></textarea></td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<input type="hidden" name="actmoreimgs" id="actmoreimgs" value="<?php echo $row['moreimgs']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikbooking">
</form>

<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content">
		<!-- The fileinput-button span is used to style the file input field as button -->
		<span class="btn btn-success fileinput-button">
			<i class="icon-new"></i>
			<span><?php echo JText::_('VBOSELORDRAGFILES'); ?></span>
			<!-- The file input field used as target for the file upload widget -->
			<input id="fileupload" type="file" name="bulkphotos[]" multiple>
		</span>
		<br>
		<br>
		<!-- The global progress bar -->
		<div id="progress" class="progress">
			<div class="progress-bar"></div>
		</div>
		<!-- The container for the uploaded files -->
		<div id="files" class="files"></div>
		<br clear="all"/>
		<div class="vbo-upload-done">
			<button type="button" class="btn" onclick="vboCloseModal();"><i class="icon-save"></i><?php echo JText::_('VBOUPLOADFILEDONE'); ?></button>
		</div>
	</div>
</div>
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.ui.widget.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/load-image.all.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.fileupload-image.js"></script>
<!-- The File Upload validation plugin -->
<script src="<?php echo VBO_ADMIN_URI; ?>resources/js_upload/jquery.fileupload-validate.js"></script>

<script type="text/javascript">
togglePriceCalendarParam();toggleSeasonalCalendarParam();
var vbo_overlay_on = false;
function showBulkUpload() {
	jQuery(".vbo-info-overlay-block").fadeIn();
	vbo_overlay_on = true;
}
function vboCloseModal() {
	jQuery(".vbo-info-overlay-block").fadeOut(400, function() {
		jQuery(this).attr("class", "vbo-info-overlay-block");
	});
	vbo_overlay_on = false;
}
jQuery(document).ready(function(){
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			vboCloseModal();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27) {
			if (vbo_overlay_on) {
				vboCloseModal();
			}
			if (vbo_details_on) {
				vbo_details_on = false;
				jQuery('.vbimagedetbox').hide();
			}
		}
	});
});
jQuery(function () {
	'use strict';
	var url = 'index.php?option=com_vikbooking&task=multiphotosupload&roomid=<?php echo count($row) ? $row['id'] : '0'; ?>',
		uploadButton = jQuery('<button/>')
			.addClass('btn btn-primary')
			.prop('disabled', true)
			.text('Processing...')
			.on('click', function () {
				var $this = jQuery(this),
					data = $this.data();
				$this
					.off('click')
					.text('Abort')
					.on('click', function () {
						$this.remove();
						data.abort();
					});
				data.submit().always(function () {
					$this.remove();
				});
			});
	jQuery('#fileupload').fileupload({
		url: url,
		dataType: 'json',
		autoUpload: true,
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		maxFileSize: 999000,
		disableImageResize: true,
		previewMaxWidth: 100,
		previewMaxHeight: 100,
		previewCrop: true
	}).on('fileuploadadd', function (e, data) {
		data.context = jQuery('<div/>').addClass('vbo-upload-photo').appendTo('#files');
		jQuery.each(data.files, function (index, file) {
			var node = jQuery('<p/>')
					.append(jQuery('<span/>').text(file.name));
			if (!index) {
				node
					.append('<br>')
					.append(uploadButton.clone(true).data(data));
			}
			node.appendTo(data.context);
		});
	}).on('fileuploadprocessalways', function (e, data) {
		var index = data.index,
			file = data.files[index],
			node = jQuery(data.context.children()[index]);
		if (file.preview) {
			node
				.prepend('<br>')
				.prepend(file.preview);
		}
		if (file.error) {
			node
				.append('<br>')
				.append(jQuery('<span class="text-danger"/>').text(file.error));
		}
		if (index + 1 === data.files.length) {
			data.context.find('button')
				.text('Upload')
				.prop('disabled', !!data.files.error);
		}
	}).on('fileuploadprogressall', function (e, data) {
		var progress = parseInt(data.loaded / data.total * 100, 10);
		jQuery('#progress .progress-bar').css(
			'width',
			progress + '%'
		);
		if (progress > 99) {
			jQuery('#progress .progress-bar').addClass("progress-bar-success");
		} else {
			if (jQuery('#progress .progress-bar').hasClass("progress-bar-success")){
				jQuery('#progress .progress-bar').removeClass("progress-bar-success");
			} 
		}
	}).on('fileuploaddone', function (e, data) {
		jQuery.each(data.result.files, function (index, file) {
			if (file.url) {
				var link = jQuery('<a>')
					.attr('target', '_blank')
					.attr('class', 'vbomodal')
					.prop('href', file.url);
				jQuery(data.context.children()[index])
					.wrap(link);
				data.context.find('button')
					.hide();
				jQuery('.vbo-upload-done')
					.fadeIn();
			} else if (file.error) {
				var error = jQuery('<span class="text-danger"/>').text(file.error);
				jQuery(data.context.children()[index])
					.append('<br>')
					.append(error);
			} else {
				jQuery(data.context.children()[index])
					.append('<br>')
					.append('Generic Error.');
			}
		});
		if (data.result.hasOwnProperty('actmoreimgs')) {
			jQuery('#actmoreimgs').val(data.result.actmoreimgs);
		}
		if (data.result.hasOwnProperty('currentthumbs')) {
			jQuery('.vbo-editroom-currentphotos').html(data.result.currentthumbs);
		}
		if (typeof reloadFancybox === 'function') {
			reloadFancybox();
		}
	}).on('fileuploadfail', function (e, data) {
		jQuery.each(data.files, function (index) {
			var error = jQuery('<span class="text-danger"/>').text('File upload failed.');
			jQuery(data.context.children()[index])
				.append('<br>')
				.append(error);
		});
	}).prop('disabled', !jQuery.support.fileInput)
		.parent().addClass(jQuery.support.fileInput ? undefined : 'disabled');
});
</script>