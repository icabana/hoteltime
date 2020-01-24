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

$report_objs = $this->report_objs;

$vbo_app = new VboApplication();
$vbo_app->loadSelect2();
$preport = VikRequest::getString('report', '', 'request');
$pkrsort = VikRequest::getString('krsort', '', 'request');
$pkrorder = VikRequest::getString('krorder', '', 'request');
$execreport = VikRequest::getString('execreport', '', 'request');
$execreport = !empty($execreport);
$pexportreport = VikRequest::getInt('exportreport', 0, 'request');
$report_obj = null;
if ($pexportreport > 0 && $execreport) {
	//if report requested, call the exportCSV() method before outputting any HMTL code
	foreach ($report_objs as $obj) {
		if ($obj->getFileName() == $preport) {
			if (method_exists($obj, 'customExport')) {
				$obj->customExport($pexportreport);
				break;
			}
			if (method_exists($obj, 'exportCSV')) {
				$obj->exportCSV();
				break;
			}
		}
	}
}
?>
<div class="vbo-info-overlay-block">
	<a class="vbo-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vbo-info-overlay-content vbo-info-overlay-report"></div>
</div>
<div class="vbo-reports-container">
	<form name="adminForm" action="index.php?option=com_vikbooking&task=pmsreports" method="post" enctype="multipart/form-data" id="adminForm">
		<div class="vbo-reports-filters-outer">
			<div class="vbo-reports-filters-main">
				<select id="choose-report" name="report" onchange="document.adminForm.submit();">
					<option value=""></option>
				<?php
				foreach ($report_objs as $obj) {
					$opt_active = false;
					if ($obj->getFileName() == $preport) {
						//get current report object
						$report_obj = $obj;
						//
						$opt_active = true;
					}
					?>
					<option value="<?php echo $obj->getFileName(); ?>"<?php echo $opt_active ? ' selected="selected"' : ''; ?>><?php echo $obj->getName(); ?></option>
					<?php
				}
				?>
				</select>
			</div>
		<?php
		$report_filters = $report_obj !== null ? $report_obj->getFilters() : array();
		if (count($report_filters)) {
			?>
			<div class="vbo-reports-filters-report">
			<?php
			foreach ($report_filters as $filt) {
				?>
				<div class="vbo-report-filter-wrap">
				<?php
				if (isset($filt['label']) && !empty($filt['label'])) {
					?>
					<div class="vbo-report-filter-lbl">
						<span><?php echo $filt['label']; ?></span>
					</div>
					<?php
				}
				if (isset($filt['html']) && !empty($filt['html'])) {
					?>
					<div class="vbo-report-filter-val">
						<?php echo $filt['html']; ?>
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
		if ($report_obj !== null) {
			?>
			<div class="vbo-reports-filters-launch">
				<input type="submit" class="btn" name="execreport" value="<?php echo JText::_('VBOREPORTLOAD'); ?>" />
			</div>
			<?php
			if ($execreport && property_exists($report_obj, 'exportAllowed') && $report_obj->exportAllowed) {
			?>
			<div class="vbo-reports-filters-export">
				<a href="JavaScript: void(0);" onclick="vboDoExport();" class="vbcsvexport"><i class="fa fa-table"></i> <span><?php echo JText::_('VBOREPORTCSVEXPORT'); ?></span></a>
			</div>
			<?php
			} elseif ($execreport && property_exists($report_obj, 'customExport')) {
			?>
			<div class="vbo-reports-filters-export">
				<?php echo $report_obj->customExport; ?>
			</div>
			<?php
			}
		}
		?>
		</div>
		<div id="vbo_hidden_fields"></div>
		<input type="hidden" name="krsort" value="<?php echo $pkrsort; ?>" />
		<input type="hidden" name="krorder" value="<?php echo $pkrorder; ?>" />
		<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>" />
		<input type="hidden" name="task" value="pmsreports" />
		<input type="hidden" name="option" value="com_vikbooking" />
	</form>
<?php
if ($report_obj !== null && $execreport) {
	//execute the report
	$res = $report_obj->getReportData();
	?>
	<div class="vbo-reports-output">
	<?php
	if (!$res) {
		//error generating the report
		?>
		<p class="err"><?php echo $report_obj->getError(); ?></p>
		<?php
	} else {
		//display the report and set default ordering and sorting
		if (empty($pkrsort) && property_exists($report_obj, 'defaultKeySort')) {
			$pkrsort = $report_obj->defaultKeySort;
		}
		if (empty($pkrorder) && property_exists($report_obj, 'defaultKeyOrder')) {
			$pkrorder = $report_obj->defaultKeyOrder;
		}
		if (strlen($report_obj->getWarning())) {
			//warning message should not stop the report from rendering
			?>
			<p class="warn"><?php echo $report_obj->getWarning(); ?></p>
			<?php
		}
		?>
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
					<?php
					foreach ($report_obj->getReportCols() as $col) {
						$col_cont = (isset($col['tip']) ? $vbo_app->createPopover(array('title' => $col['label'], 'content' => $col['tip'])) : '').$col['label'];
						?>
						<th<?php echo isset($col['attr']) && count($col['attr']) ? ' '.implode(' ', $col['attr']) : ''; ?>>
						<?php
						if (isset($col['sortable'])) {
							$krorder = $pkrsort == $col['key'] && $pkrorder == 'DESC' ? 'ASC' : 'DESC';
							?>
							<a href="JavaScript: void(0);" onclick="vboSetFilters({krsort: '<?php echo $col['key']; ?>', krorder: '<?php echo $krorder; ?>'}, true);" class="<?php echo $pkrsort == $col['key'] ? 'vbo-list-activesort' : ''; ?>">
								<span><?php echo $col_cont; ?></span>
								<i class="fa <?php echo $pkrsort == $col['key'] && $krorder == 'DESC' ? 'fa-sort-asc' : ($pkrsort == $col['key'] ? 'fa-sort-desc' : 'fa-sort'); ?>"></i>
							</a>
							<?php
						} else {
							?>
							<span><?php echo $col_cont; ?></span>
							<?php
						}
						?>
						</th>
						<?php
					}
					?>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($report_obj->getReportRows() as $row) {
					?>
					<tr>
					<?php
					foreach ($row as $cell) {
						?>
						<td<?php echo isset($cell['attr']) && count($cell['attr']) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
							<span><?php echo isset($cell['callback']) && is_callable($cell['callback']) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
						</td>
						<?php
					}
					?>
					</tr>
					<?php
				}
				?>
				</tbody>
			<?php
			if (count($report_obj->getReportFooterRow())) {
				?>
				<tfoot>
				<?php
				foreach ($report_obj->getReportFooterRow() as $row) {
					?>
					<tr>
					<?php
					foreach ($row as $cell) {
						?>
						<td<?php echo isset($cell['attr']) && count($cell['attr']) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
							<span><?php echo isset($cell['callback']) && is_callable($cell['callback']) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
						</td>
						<?php
					}
					?>
					</tr>
					<?php
				}
				?>
				</tfoot>
				<?php
			}
			?>
			</table>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
?>
</div>
<script type="text/javascript">
function vboSetFilters(obj, dosubmit) {
	if (typeof obj != "object") {
		console.log("arg is not an object");
		return;
	}
	for (var p in obj) {
		if (!obj.hasOwnProperty(p)) {
			continue;
		}
		var elem = document.adminForm[p];
		if (elem) {
			document.adminForm[p].value = obj[p];
		} else {
			document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' name='"+p+"' value='"+obj[p]+"' />";
		}
	}
	if (!obj.hasOwnProperty('execreport')) {
		document.getElementById("vbo_hidden_fields").innerHTML += "<input type='hidden' name='execreport' value='1' />";
	}
	if (dosubmit) {
		document.adminForm.submit();
	}
}
function vboDoExport() {
	document.adminForm.target = '_blank';
	document.adminForm.action += '&tmpl=component';
	vboSetFilters({exportreport: '1'}, true);
	setTimeout(function() {
		document.adminForm.target = '';
		document.adminForm.action = document.adminForm.action.replace('&tmpl=component', '');
		vboSetFilters({exportreport: '0'}, false);
	}, 1000);
}
var vbo_overlay_on = false;
function vboShowOverlay() {
	jQuery(".vbo-info-overlay-block").fadeIn(400, function() {
		if (jQuery(".vbo-info-overlay-block").is(":visible")) {
			vbo_overlay_on = true;
		} else {
			vbo_overlay_on = false;
		}
	});
}
function vboHideOverlay() {
	jQuery(".vbo-info-overlay-block").fadeOut();
	vbo_overlay_on = false;
}
jQuery(document).ready(function() {
	jQuery("#choose-report").select2({placeholder: '<?php echo addslashes(JText::_('VBOREPORTSELECT')); ?>', width: "200px"});
	jQuery(document).mouseup(function(e) {
		if (!vbo_overlay_on) {
			return false;
		}
		var vbo_overlay_cont = jQuery(".vbo-info-overlay-content");
		if (!vbo_overlay_cont.is(e.target) && vbo_overlay_cont.has(e.target).length === 0) {
			vboHideOverlay();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vbo_overlay_on) {
			vboHideOverlay();
		}
	});
});
<?php echo $report_obj !== null && strlen($report_obj->getScript()) ? $report_obj->getScript() : ''; ?>
</script>