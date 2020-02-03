<?php
/**
 * @package     VikBooking
 * @subpackage  mod_vikbooking_rooms
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// no direct access
defined('_JEXEC') or die;

$currencysymb = $params->get('currency');
$widthroom = $params->get('widthroom');
$showscrollbar = $params->get('scrollbar');
$pagination = $params->get('pagination');
$navigation = $params->get('navigation');
$autoplayparam = $params->get('autoplay');
$autoplaytime = $params->get('autoplaytime');
$totalpeople = $params->get('shownumbpeople');
$showdetails = $params->get('showdetailsbtn');
$roomdesc = $params->get('showroomdesc');
$getdesc = $params->get('mod_desc');

$numb_xrow = $params->get('numb_roomrow');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'modules/mod_vikbooking_rooms/mod_vikbooking_rooms.css');
$document->addStyleSheet(JURI::root().'modules/mod_vikbooking_rooms/src/owl.carousel.css');
$document->addStyleSheet(JURI::root().'modules/mod_vikbooking_rooms/src/owl.theme.css');

$randid = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

if(intval($params->get('loadjq')) == 1 ) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', JURI::root().'modules/mod_vikbooking_rooms/src/jquery.min.js', false, true, false, false);
}
JHtml::_('script', JURI::root().'modules/mod_vikbooking_rooms/src/owl.carousel.js', false, true, false, false);
JHtml::_('script', JURI::root().'modules/mod_vikbooking_rooms/src/device.js', false, true, false, false);

$rooms_css = ".frame ul.vbmodrooms > li {
	width:".$widthroom.";}";
$document->addStyleDeclaration($rooms_css);

?>
<div class="vbmodroomscontainer wrap">
	<?php if(!empty($getdesc)) { ?>
		<div class="vbmodroom-desc"><?php echo $getdesc; ?></div>
	<?php } ?>
	<div>
		<div id="vbo-modrooms-<?php echo $randid; ?>" class="owl-carousel vbmodrooms">
			<?php
				foreach($rooms as $c) {
			?>
			<div class="vbmodrooms-item">
				<div class="vbmodroomsboxdiv">	
					<?php
					if(!empty($c['img'])) {
					?>
						<img src="<?php echo JURI::root(); ?>components/com_vikbooking/resources/uploads/<?php echo $c['img']; ?>" class="vbmodroomsimg"/>
					<?php
					}
					?>
					<div class="vbinf">
						<div class="vbmodrooms-divblock">
					        <span class="vbmodroomsname"><?php echo $c['name']; ?></span>
						</div>
						<?php
						if($showcatname) {
						?>
							<span class="vbmodroomscat"><?php echo $c['catname']; ?></span>
						<?php
						}
						?>		
						<?php
						if($roomdesc) {
						?>	
							<span class="vbmodroomsdesc"><?php echo $c['smalldesc']; ?></span>		
						<?php
						}
						?>	
			        </div>
			        <div class="vbmodrooms_rdet">
						<?php
						if($totalpeople == 1) {
						?>
				        	<div class="vbmodroomsbeds"><span><?php echo $c['totpeople']; ?></span> <i class="fa fa-bed"></i></div>	
				        <?php }
						?>	
						<?php
							if($c['cost'] > 0) {
							?>
							<div class="vbmodroomsroomcost">
								<span class="vbo_currency"><?php echo $currencysymb; ?></span> 
								<span class="vbo_price"><?php echo modvikbooking_roomsHelper::numberFormat($c['cost']); ?></span>
							<?php
							if(array_key_exists('custpricetxt', $c) && !empty($c['custpricetxt'])) {
							?>
								<span class="vbmodroomslabelcost"><?php echo $c['custpricetxt']; ?></span>
							<?php
							}
						?>
						</div>
						<?php
						}
						?>
					</div>
					<?php
							if($showdetails == 1) {
							?>
					<div class="vbmodroomsview"><a class="btn" href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$c['id'].'&Itemid='.$params->get('itemid')); ?>"><?php echo JText::_('VBMODROOMSCONTINUE'); ?></a></div>
			        <?php } ?>
			        
				</div>	
			</div>
		<?php
		} ?>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){ 
	jQuery("#vbo-modrooms-<?php echo $randid; ?>").owlCarousel({
		items : <?php echo $numb_xrow; ?>,
		autoPlay : <?php echo $autoplayparam; ?>,
		navigation : <?php echo $navigation; ?>,
		pagination : <?php echo $pagination; ?>,
		lazyLoad : true
	});
});
</script>
