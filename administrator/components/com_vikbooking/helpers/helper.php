<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

 // No direct access to this file
defined('_JEXEC') OR die('Restricted Area');

class VikBookingHelper
{
	public static function printHeader($highlight = "")
	{
		$cookie = JFactory::getApplication()->input->cookie;
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') return;
		$channel_manager_btn = '';
		if (file_exists(VCM_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'lib.vikchannelmanager.php')) {
			$channel_manager_btn = '<li><span><a href="index.php?option=com_vikchannelmanager"><i class="vboicn-cloud"></i>'.JText::_('VBMENUCHANNELMANAGER').'</a></span></li>';
		}
		$backlogo = VikBooking::getBackendLogo();
		$vbo_auth_global = JFactory::getUser()->authorise('core.vbo.global', 'com_vikbooking');
		$vbo_auth_rateplans = JFactory::getUser()->authorise('core.vbo.rateplans', 'com_vikbooking');
		$vbo_auth_rooms = JFactory::getUser()->authorise('core.vbo.rooms', 'com_vikbooking');
		$vbo_auth_pricing = JFactory::getUser()->authorise('core.vbo.pricing', 'com_vikbooking');
		$vbo_auth_bookings = JFactory::getUser()->authorise('core.vbo.bookings', 'com_vikbooking');
		$vbo_auth_availability = JFactory::getUser()->authorise('core.vbo.availability', 'com_vikbooking');
		$vbo_auth_management = JFactory::getUser()->authorise('core.vbo.management', 'com_vikbooking');
		$vbo_auth_pms = JFactory::getUser()->authorise('core.vbo.pms', 'com_vikbooking');
		?>
		<div class="vbo-menu-container">
			<div class="vbo-menu-left"><img src="<?php echo VBO_ADMIN_URI.(!empty($backlogo) ? 'resources/'.$backlogo : 'vikbooking.png'); ?>" alt="VikBooking Logo" /></div>
			<div class="vbo-menu-right">
				<ul class="vbo-menu-ul">
					<?php
					if ($vbo_auth_global || $vbo_auth_management) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-cogs"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUFOUR'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<?php if ($vbo_auth_global) : ?><li><span class="<?php echo ($highlight=="14" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=payments"><?php echo JText::_('VBMENUTENEIGHT'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_global) : ?><li><span class="<?php echo ($highlight=="16" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=customf"><?php echo JText::_('VBMENUTENTEN'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_management) : ?><li><span class="<?php echo ($highlight=="21" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=translations"><?php echo JText::_('VBMENUTRANSLATIONS'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_global) : ?><li><span class="<?php echo ($highlight=="11" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=config"><?php echo JText::_('VBMENUTWELVE'); ?></a></span></li><?php endif; ?>
						</ul>
					</li>
					<?php
					}
					if ($vbo_auth_rateplans) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-briefcase"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENURATEPLANS'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<li><span class="<?php echo ($highlight=="2" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=iva"><?php echo JText::_('VBMENUNINE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="1" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=prices"><?php echo JText::_('VBMENUFIVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="17" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=coupons"><?php echo JText::_('VBMENUCOUPONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="packages" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=packages"><?php echo JText::_('VBMENUPACKAGES'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					if ($vbo_auth_rooms || $vbo_auth_pricing) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-office"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUTWO'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<?php if ($vbo_auth_rooms) : ?><li><span class="<?php echo ($highlight=="4" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=categories"><?php echo JText::_('VBMENUSIX'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_rooms) : ?><li><span class="<?php echo ($highlight=="5" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=carat"><?php echo JText::_('VBMENUTENFOUR'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_pricing) : ?><li><span class="<?php echo ($highlight=="6" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=optionals"><?php echo JText::_('VBMENUTENFIVE'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_rooms) : ?><li><span class="<?php echo ($highlight=="7" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=rooms"><?php echo JText::_('VBMENUTEN'); ?></a></span></li><?php endif; ?>
						</ul>
					</li>
					<?php
					}
					if ($vbo_auth_pricing) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-calculator"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUFARES'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<li><span class="<?php echo ($highlight=="fares" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=tariffs"><?php echo JText::_('VBMENUPRICESTABLE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="13" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=seasons"><?php echo JText::_('VBMENUTENSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="restrictions" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=restrictions"><?php echo JText::_('VBMENURESTRICTIONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="20" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=ratesoverv"><?php echo JText::_('VBMENURATESOVERVIEW'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-credit-card"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUTHREE'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<li><span class="<?php echo ($highlight=="18" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking"><?php echo JText::_('VBMENUDASHBOARD'); ?></a></span></li>
							<?php if ($vbo_auth_availability || $vbo_auth_bookings) : ?><li><span class="<?php echo ($highlight=="19" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=calendar"><?php echo JText::_('VBMENUQUICKRES'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_availability) : ?><li><span class="<?php echo ($highlight=="15" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=overv"><?php echo JText::_('VBMENUTENNINE'); ?></a></span></li><?php endif; ?>
							<?php if ($vbo_auth_bookings) : ?><li><span class="<?php echo ($highlight=="8" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=orders"><?php echo JText::_('VBMENUSEVEN'); ?></a></span></li><?php endif; ?>
							<?php echo $vbo_auth_availability || $vbo_auth_bookings ? $channel_manager_btn : ''; ?>
						</ul>
					</li>
					<?php
					if ($vbo_auth_management) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-stats-bars"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUMANAGEMENT'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<li><span class="<?php echo ($highlight=="22" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=customers"><?php echo JText::_('VBMENUCUSTOMERS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="invoices" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=invoices"><?php echo JText::_('VBMENUINVOICES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="stats" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=stats"><?php echo JText::_('VBMENUSTATS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="crons" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=crons"><?php echo JText::_('VBMENUCRONS'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					if ($vbo_auth_pms) {
					?>
					<li class="vbo-menu-parent-li">
						<span><i class="vboicn-database"></i><a href="javascript: void(0);"><?php echo JText::_('VBMENUPMS'); ?></a></span>
						<ul class="vbo-submenu-ul">
							<li><span class="<?php echo ($highlight=="pmsreports" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikbooking&amp;task=pmsreports"><?php echo JText::_('VBMENUPMSREPORTS'); ?></a></span></li>
						</ul>
					</li>
					<?php
					}
					?>
				</ul>
				<div class="vbo-menu-updates">
			<?php
			if ($highlight == '18' || $highlight=='11') {
				//VikUpdater
				JPluginHelper::importPlugin('e4j');
				$callable = array();
				if (class_exists('JEventDispatcher')) {
					$dispatcher = JEventDispatcher::getInstance();
					$callable 	= $dispatcher->trigger('isCallable');
				} else {
					$app = JFactory::getApplication();
					if (method_exists($app, 'triggerEvent')) {
						$callable = $app->triggerEvent('isCallable');
					}
				}
				if (count($callable) && $callable[0]) {
					//Plugin enabled
					$params = new stdClass;
					$params->version 	= E4J_SOFTWARE_VERSION;
					$params->alias 		= 'com_vikbooking';
					
					$upd_btn_text = strrev('setadpU kcehC');
					$ready_jsfun = '';
					$result = $dispatcher->trigger('getVersionContents', array(&$params));
					if (count($result) && $result[0]) {
						$upd_btn_text = $result[0]->response->shortTitle;
					} else {
						$ready_jsfun = 'jQuery("#vik-update-btn").trigger("click");';
					}
					?>
					<button type="button" id="vik-update-btn" onclick="<?php echo count($result) && $result[0] && $result[0]->response->compare == 1 ? 'document.location.href=\'index.php?option=com_vikbooking&task=updateprogram\'' : 'checkVersion(this);'; ?>">
						<i class="vboicn-cloud"></i> 
						<span><?php echo $upd_btn_text; ?></span>
					</button>
					<script type="text/javascript">
					function checkVersion(button) {
						jQuery(button).find('span').text('Checking...');
						jQuery.ajax({
							type: 'POST',
							url: 'index.php?option=com_vikbooking&task=checkversion&tmpl=component',
							data: {}
						}).done(function(resp){
							var obj = JSON.parse(resp);
							console.log(obj);
							if (obj.status == 1 && obj.response.status == 1) {
								jQuery(button).find('span').text(obj.response.shortTitle);
								if (obj.response.compare == 1) {
									jQuery(button).attr('onclick', 'document.location.href="index.php?option=com_vikbooking&task=updateprogram"');
								}
							}
						}).fail(function(resp){
							console.log(resp);
						});
					}
					jQuery(document).ready(function() {
						<?php echo $ready_jsfun; ?>
					});
					</script>
					<?php
				} else {
					//Plugin disabled
					//we display an empty button
					?>
					<button type="button" id="vik-update-btn" onclick="alert('The plugin Vik Updater is either disabled or not installed');">
						<i class="vboicn-cloud"></i> 
						<span></span>
					</button>
					<?php
				}
			}
			?>	
				</div>
			</div>
		</div>
		<div style="clear: both;"></div>
		<script type="text/javascript">
		jQuery.noConflict();
		var vbo_menu_type = <?php echo (int)$cookie->get('vboMenuType', '0', 'string') ?>;
		var vbo_menu_on = ((vbo_menu_type % 2) == 0);
		//
		function vboDetectMenuChange(e) {
			e = e || window.event;
			if ((e.which == 77 || e.keyCode == 77) && e.altKey) {
				//ALT+M
				vbo_menu_type++;
				vbo_menu_on = ((vbo_menu_type % 2) == 0);
				console.log(vbo_menu_type, vbo_menu_on);
				//Set Cookie for next page refresh
				var nd = new Date();
				nd.setTime(nd.getTime() + (365*24*60*60*1000));
				document.cookie = "vboMenuType="+vbo_menu_type+"; expires=" + nd.toUTCString() + "; path=/";
			}
		}
		document.onkeydown = vboDetectMenuChange;
		//
		jQuery(document).ready(function() {
			jQuery('.vbo-menu-parent-li').click(function() {
				if (jQuery(this).find('ul.vbo-submenu-ul').is(':visible')) {
					vbo_menu_on = false;
					return;
				}
				jQuery('ul.vbo-submenu-ul').hide();
				jQuery(this).find('ul.vbo-submenu-ul').show();
				vbo_menu_on = true;
			});
			jQuery('.vbo-menu-parent-li').hover(
				function() {
					if (vbo_menu_on === true) {
						jQuery(this).addClass('vbo-menu-parent-li-opened');
						jQuery(this).find('ul.vbo-submenu-ul').show();
					}
				},function() {
					if (vbo_menu_on === true) {
						jQuery(this).removeClass('vbo-menu-parent-li-opened');
						jQuery(this).find('ul.vbo-submenu-ul').hide();
					}
				}
			);
			var targetY = jQuery('.vbo-menu-right').offset().top + jQuery('.vbo-menu-right').outerHeight() + 150;
			jQuery(document).click(function(event) { 
				if (!jQuery(event.target).closest('.vbo-menu-right').length && parseInt(event.which) == 1 && event.pageY < targetY) {
					jQuery('ul.vbo-submenu-ul').hide();
					vbo_menu_on = true;
				}
			});
			if (jQuery('.vmenulinkactive').length) {
				jQuery('.vmenulinkactive').parent('li').parent('ul').parent('li').addClass('vbo-menu-parent-li-active');
				if ((vbo_menu_type % 2) != 0) {
					jQuery('.vmenulinkactive').parent('li').parent('ul').show();
				}
			}
		});
		</script>
		<?php	
	}
	
	public static function printFooter()
	{
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') return;
		echo '<br clear="all" />' . '<div id="hmfooter">' . JText::sprintf('VBFOOTER', E4J_SOFTWARE_VERSION) . ' <a href="http://www.extensionsforjoomla.com/">e4j - Extensionsforjoomla.com</a></div>';
	}

	//VikUpdater plugin methods - Start
	public static function pUpdateProgram($version)
	{
		?>
		<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">
	
			<div class="span12">
				<fieldset class="form-horizontal">
					<legend><?php $version->shortTitle ?></legend>
					<div class="control"><strong><?php echo $version->title; ?></strong></div>

					<div class="control" style="margin-top: 10px;">
						<button type="button" class="btn btn-primary" onclick="downloadSoftware(this);">
							<?php echo JText::_($version->compare == 1 ? 'VBDOWNLOADUPDATEBTN1' : 'VBDOWNLOADUPDATEBTN0'); ?>
						</button>
					</div>

					<div class="control vik-box-error" id="update-error" style="display: none;margin-top: 10px;"></div>

					<?php if ( isset($version->changelog) && count($version->changelog) ) { ?>

						<div class="control vik-update-changelog" style="margin-top: 10px;">

							<?php echo self::digChangelog($version->changelog); ?>

						</div>

					<?php } ?>
				</fieldset>
			</div>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="option" value="com_vikbooking"/>
		</form>

		<div id="vikupdater-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999999 !important; background-color: rgba(0,0,0,0.5);">
			<div id="vikupdater-loading-content" style="position: fixed; left: 33.3%; top: 30%; width: 33.3%; height: auto; z-index: 101; padding: 10px; border-radius: 5px; background-color: #fff; box-shadow: 5px 5px 5px 0 #000; overflow: auto; text-align: center;">
				<span id="vikupdater-loading-message" style="display: block; text-align: center;"></span>
				<span id="vikupdater-loading-dots" style="display: block; font-weight: bold; font-size: 25px; text-align: center; color: green;">.</span>
			</div>
		</div>
		
		<script type="text/javascript">
		var isRunning = false;
		var loadingInterval;

		function vikLoadingAnimation() {
			var dotslength = jQuery('#vikupdater-loading-dots').text().length + 1;
			if (dotslength > 10) {
				dotslength = 1;
			}
			var dotscont = '';
			for (var i = 1; i <= dotslength; i++) {
				dotscont += '.';
			}
			jQuery('#vikupdater-loading-dots').text(dotscont);
		}

		function openLoadingOverlay(message) {
			jQuery('#vikupdater-loading-message').html(message);
			jQuery('#vikupdater-loading').fadeIn();
			loadingInterval = setInterval(vikLoadingAnimation, 1000);
		}

		function closeLoadingOverlay() {
			jQuery('#vikupdater-loading').fadeOut();
			clearInterval(loadingInterval);
		}

		function downloadSoftware(btn) {

			if ( isRunning ) {
				return;
			}

			switchRunStatus(btn);
			setError(null);

			var jqxhr = jQuery.ajax({
				url: "index.php?option=com_vikbooking&task=updateprogramlaunch&tmpl=component",
				type: "POST",
				data: {}
			}).done(function(resp){

				try {
					var obj = JSON.parse(resp);
				} catch (e) {
					console.log(resp);
					return;
				}
				
				if ( obj === null ) {

					// connection failed. Something gone wrong while decoding JSON
					alert('<?php echo addslashes('Connection Error'); ?>');

				} else if ( obj.status ) {

					document.location.href = 'index.php?option=com_vikbooking';
					return;

				} else {

					console.log("### ERROR ###");
					console.log(obj);

					if ( obj.hasOwnProperty('error') ) {
						setError(obj.error);
					} else {
						setError('Your website does not have a valid support license.<br />Please visit <a href="https://extensionsforjoomla.com" target="_blank">extensionsforjoomla.com</a> to purchase a new license or to receive assistance.');
					}

				}

				switchRunStatus(btn);

			}).fail(function(resp){
				console.log('### FAILURE ###');
				console.log(resp);
				alert('<?php echo addslashes('Connection Error'); ?>');

				switchRunStatus(btn);
			}); 
		}

		function switchRunStatus(btn) {
			isRunning = !isRunning;

			jQuery(btn).prop('disabled', isRunning);

			if ( isRunning ) {
				// start loading
				openLoadingOverlay('The process may take a few minutes to complete.<br />Please wait without leaving the page or closing the browser.');
			} else {
				// stop loading
				closeLoadingOverlay();
			}
		}

		function setError(err) {

			if ( err !== null && err !== undefined && err.length ) {
				jQuery('#update-error').show();
			} else {
				jQuery('#update-error').hide();
			}

			jQuery('#update-error').html(err);

		}

	</script>
		<?php
	}

	/**
	 * Scan changelog structure.
	 *
	 * @param 	array 	$arr 	The list containing changelog elements.
	 * @param 	mixed 	$html 	The html built. 
	 * 							Specify false to echo the structure immediately.
	 *
	 * @return 	string|void 	The HTML structure or nothing.
	 */
	private static function digChangelog(array $arr, $html = '')
	{

		foreach( $arr as $elem ):

			if ( isset($elem->tag) ):

				// build attributes

				$attributes = "";
				if ( isset($elem->attributes) ) {

					foreach( $elem->attributes as $k => $v ) {
						$attributes .= " $k=\"$v\"";
					}

				}

				// build tag opening

				$str = "<{$elem->tag}$attributes>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

				// display contents

				if ( isset($elem->content) ) {

					if ( $html ) {
						$html .= $elem->content;
					} else {
						echo $elem->content;
					}

				}

				// recursive iteration for elem children

				if ( isset($elem->children) ) {
					self::digChangelog($elem->children, $html);
				}

				// build tag closure

				$str = "</{$elem->tag}>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

			endif;

		endforeach;

		return $html;
	}
	//VikUpdater plugin methods - End

}