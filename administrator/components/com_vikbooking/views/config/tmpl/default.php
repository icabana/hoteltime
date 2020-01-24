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

$vbo_app = new VboApplication();
$vbo_app->prepareModalBox();

echo "<form name=\"adminForm\" id=\"adminForm\" action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">\n";

?>
<dl class="tabs" id="tab_group_id">
	<dt style="display:none;"></dt>
	<dd style="display:none;"></dd>
	<dt class="tabs <?php echo $this->curtabid == 1 ? 'open' : 'closed'; ?>" data-ptid="1" style="cursor: pointer;">
		<span>
			<h3>
				<a href="javascript:void(0);"><?php echo JText::_('VBPANELONE'); ?></a>
			</h3>
		</span>
	</dt>
	<dt class="tabs <?php echo $this->curtabid == 2 ? 'open' : 'closed'; ?>" data-ptid="2" style="cursor: pointer;">
		<span>
			<h3>
				<a href="javascript:void(0);"><?php echo JText::_('VBPANELTWO'); ?></a>
			</h3>
		</span>
	</dt>
	<dt class="tabs <?php echo $this->curtabid == 3 ? 'open' : 'closed'; ?>" data-ptid="3" style="cursor: pointer;">
		<span>
			<h3>
				<a href="javascript:void(0);"><?php echo JText::_('VBPANELTHREE'); ?></a>
			</h3>
		</span>
	</dt>
	<dt class="tabs <?php echo $this->curtabid == 4 ? 'open' : 'closed'; ?>" data-ptid="4" style="cursor: pointer;">
		<span>
			<h3>
				<a href="javascript:void(0);"><?php echo JText::_('VBPANELFOUR'); ?></a>
			</h3>
		</span>
	</dt>
	<dt class="tabs <?php echo $this->curtabid == 5 ? 'open' : 'closed'; ?>" data-ptid="5" style="cursor: pointer;">
		<span>
			<h3>
				<a href="javascript:void(0);"><?php echo JText::_('VBPANELFIVE'); ?></a>
			</h3>
		</span>
	</dt>
</dl>

<a href="javascript: void(0);" class="vbflushsession" onclick="vbFlushSession();"><?php echo JText::_('VBCONFIGFLUSHSESSION'); ?></a>

<div class="current">
	<dd class="tabs" id="pt1" style="display: <?php echo $this->curtabid == 1 ? 'block' : 'none'; ?>;">
		<?php echo $this->loadTemplate('one'); ?>
	</dd>
	<dd class="tabs" id="pt2" style="display: <?php echo $this->curtabid == 2 ? 'block' : 'none'; ?>;">
		<?php echo $this->loadTemplate('two'); ?>
	</dd>
	<dd class="tabs" id="pt3" style="display: <?php echo $this->curtabid == 3 ? 'block' : 'none'; ?>;">
		<?php echo $this->loadTemplate('three'); ?>
	</dd>
	<dd class="tabs" id="pt4" style="display: <?php echo $this->curtabid == 4 ? 'block' : 'none'; ?>;">
		<?php echo $this->loadTemplate('four'); ?>
	</dd>
	<dd class="tabs" id="pt5" style="display: <?php echo $this->curtabid == 5 ? 'block' : 'none'; ?>;">
		<?php echo $this->loadTemplate('five'); ?>
	</dd>
</div>

<script type="text/javascript">
function vbFlushSession() {
	if (confirm('<?php echo addslashes(JText::_('VBCONFIGFLUSHSESSIONCONF')); ?>')) {
		location.href='index.php?option=com_vikbooking&task=renewsession';
	} else {
		return false;
	}
}
jQuery(document).ready(function() {
	jQuery('dt.tabs').click(function() {
		var ptid = jQuery(this).attr('data-ptid');
		jQuery('dt.tabs').removeClass('open').addClass('closed');
		jQuery(this).removeClass('closed').addClass('open');
		jQuery('dd.tabs').hide();
		jQuery('dd#pt'+ptid).show();
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vbConfPt="+ptid+"; expires=" + nd.toUTCString() + "; path=/";
	});
});
</script>
<?php

echo "<input type=\"hidden\" name=\"task\" value=\"\">\n";
echo "<input type=\"hidden\" name=\"option\" value=\"com_vikbooking\"/>\n</form>";
