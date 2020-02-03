<?php
/**
 * Copyright (c) Extensionsforjoomla.com - E4J - Templates for Joomla
 *
 * You should have received a copy of the License
 * along with this program.  If not, see <http://www.extensionsforjoomla.com/>.
 *
 * For any bug, error please contact us
 * We will try to fix it.
 *
 * Extensionsforjoomla.com - All Rights Reserved
 *
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
$get_logo = $this->params->get('logo');
$get_logo_int = $this->params->get('logo-internal');
$get_type_slider = $this->params->get('sliderwind'); 
$app = JFactory::getApplication();
$menu = $app->getMenu();
$lang = JFactory::getLanguage();

?>
<header class="<?php echo ($this->params->get('getmenufixed') ? "headfixed" : ''); ?>">
	<div <?php if (($menu->getActive() == $menu->getDefault($lang->getTag())) || ($menu->getActive() == $menu->getDefault())) {
			if($get_type_slider == 0) { 
				if($get_type_slider == 0) { echo "id=\"headt-part\""; } 
			} 
		}
		?> 
		class="head-top-part">
		<?php include('./templates/'.$this->template.'/blocks/header/upmenu.php'); ?>
		<div class="logomenupart e4j-mainmenu <?php echo ($this->params->get('getmenufixed') ? "fixedmenu" : ''); ?>">
			<div id="lmpart">
				<div class="menumob-btn">
					<div class="menumob-btn-inner">
						<div id="menumob-btn-ico" onclick="vikShowResponsiveMenu();">
						  <span></span>
						  <span></span>
						  <span></span>
						  <span></span>
						</div>
					</div>
				</div>
				<?php if (($menu->getActive() == $menu->getDefault($lang->getTag())) || ($menu->getActive() == $menu->getDefault())) {
					if(!empty($get_logo)) { ?>
						<div id="tbar-logo">
							<p><a href="<?php echo $this->params->get('logolink');?>"><img src="<?php echo $get_logo;?>" /></a></p>
						</div>
					<?php } 
				} elseif (!empty($get_logo_int)) { ?>
						<div id="tbar-logo">
							<p><a href="<?php echo $this->params->get('logolink');?>"><img src="<?php echo $get_logo_int;?>" /></a></p>
						</div>
					<?php } else { ?>
						<div id="tbar-logo">
							<p><a href="<?php echo $this->params->get('logolink');?>"><img src="<?php echo $get_logo;?>" /></a></p>
						</div>
					<?php } ?>	

				<?php if($this->countModules('user')) { ?>				
				<div id="tbar-user">
					<div id="tbar-preuser">
						<?php include('./templates/'.$this->template.'/blocks/header/user.php'); ?>
					</div>
				</div>
				<?php } ?>
				<?php if($this->countModules('mainmenu')) { ?>		
				<div id="mainmenu">
					<?php include('./templates/'.$this->template.'/blocks/header/mainmenu.php'); ?>
				</div>
				<?php } ?>	
			</div>
			<?php if($this->countModules('submenu')) { ?>		
				<div id="submenu">
					<?php include('./templates/'.$this->template.'/blocks/header/submenu.php'); ?>
				</div>
			<?php } ?>	
		</div>
	</div>
	<div id="contentheader" <?php 

	if (($menu->getActive() == $menu->getDefault($lang->getTag())) || ($menu->getActive() == $menu->getDefault())) {
		if($get_type_slider == 0) { 
			echo "class=\"contentheader-topfix\""; 
		} 
	} else {
		echo "class=\"contentheader-nofix\""; 
	}
	?>>	
			<div <?php if($get_type_slider == 0) { echo "id=\"slideadv\""; }  ?> class="slideadv<?php if($get_type_slider == 0) { echo " header_slider"; } ?>">
			<?php if($this->countModules('slide-up')) { ?>
				<div class="upsearch h-search md-search">
					<div class="grid-block">
						<jdoc:include type="modules" name="slide-up" style="e4jstyle" />
					</div>
				</div>
			<?php } ?>			
			<?php if($this->countModules('slider')) { ?>					
				<div id="contain-slider" class="cnt-slider">
					<div class="slidmodule">
						<div id="slider">
							<div id="imgslider">
								<jdoc:include type="modules" name="slider" style="e4jstyle" />
							</div>
						</div>
					</div>
				</div>					
			<?php } ?>
			<?php if($this->countModules('slider-fullscreen')) { ?>					
				<div id="contain-slider-fullscreen" class="cnt-slider">						
					<div class="slidmodule">
						<div id="slider">
							<div id="imgslider">
								<jdoc:include type="modules" name="slider-fullscreen" style="e4jstyle" />
							</div>
						</div>
					</div>						
				</div>					
			<?php } ?>
			<?php if($this->countModules('slide-left')) { ?>
				<div class="leftsearch v-search md-search">
					<div class="grid-block">
						<jdoc:include type="modules" name="slide-left" style="e4jstyle" />
					</div>
				</div>
			<?php } ?>	
			<?php if($this->countModules('slide-right')) { ?>
				<div class="rightsearch v-search md-search">
					<div class="grid-block">
						<jdoc:include type="modules" name="slide-right" style="e4jstyle" />
					</div>
				</div>
			<?php } ?>
			<?php if($this->countModules('slide-center')) { ?>
				<div class="centersearch h-search md-search">
					<div class="grid-block">
						<div class="h-search-inner">
							<jdoc:include type="modules" name="slide-center" style="e4jstyle" />
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if($this->countModules('slide-down')) { ?>
				<div class="bottomsearch h-search md-search">
					<div class="grid-block">
						<div class="h-search-inner">
							<jdoc:include type="modules" name="slide-down" style="e4jstyle" />
						</div>
					</div>
				</div>
			<?php } ?>
		</div>			
	</div>
</header>	
	
