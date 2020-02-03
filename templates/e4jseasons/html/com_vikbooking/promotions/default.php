<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

JHtml::_('jquery.framework', true, true);

$promotions = $this->promotions;
$rooms = $this->rooms;
$showrooms = $this->showrooms == 1 ? true : false;
$vbo_tn = $this->vbo_tn;

$currencysymb = VikBooking::getCurrencySymb();
$vbodateformat = VikBooking::getDateFormat();
if ($vbodateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
}elseif ($vbodateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
}else {
	$df = 'Y/m/d';
}
$datesep = VikBooking::getDateSeparator();

$days_labels = array(
	JText::_('VBJQCALSUN'),
	JText::_('VBJQCALMON'),
	JText::_('VBJQCALTUE'),
	JText::_('VBJQCALWED'),
	JText::_('VBJQCALTHU'),
	JText::_('VBJQCALFRI'),
	JText::_('VBJQCALSAT')
);

if(count($promotions) > 0) {
	?>
	<div class="vbo-promotions-container">
	<?php
	foreach ($promotions as $k => $promo) {
		?>
		<div class="vbo-promotion-details">
			<div class="vbo-promotion-det-wrapper">
				<div class="vbo-promotion-name other-font"><span><?php echo $promo['spname']; ?></span></div>
				<div class="vbo-promotion-description">
					<?php echo $promo['promotxt']; ?>
				</div>
			</div>
			<div class="vbo-promotion-wrapper">
				<div class="vbo-promotion-inn-info">
					<?php 
					//
					if($promo['type'] == 2) {
						?>
						<div class="vbo-promotion-discount">
							<div class="vbo-promotion-discount-details">
						<?php
						if($promo['val_pcent'] == 2) {
							//Percentage
							$disc_amount = ($promo['diffcost'] - abs($promo['diffcost'])) > 0 ? $promo['diffcost'] : abs($promo['diffcost']);
							?>
								<span class="vbo-promotion-discount-percent-amount"><?php echo $disc_amount; ?>%</span>
								<span class="vbo-promotion-discount-percent-txt"><?php echo JText::_('VBOPROMOPERCENTDISCOUNT'); ?></span>
							<?php
						}else {
							//Fixed
							?>
								<span class="vbo-promotion-discount-percent-amount"><span class="vbo_currency"><?php echo $currencysymb; ?></span><span class="vbo_price"><?php echo VikBooking::numberFormat($promo['diffcost']); ?></span></span>
								<span class="vbo-promotion-discount-percent-txt"><?php echo JText::_('VBOPROMOFIXEDDISCOUNT'); ?></span>
							<?php
						}
						?>
							</div>
						</div>
						<?php
					}
					?>
					<div class="vbo-promotion-dates">
						<div class="vbo-promotion-dates-left">
							<div class="vbo-promotion-date-from">
								<i class="fa fa-calendar"></i>
								<span class="vbo-promotion-date-label"><?php echo JText::_('VBOPROMORENTFROM'); ?></span>
								<span class="vbo-promotion-date-from-sp"><?php echo date(str_replace("/", $datesep, $df), $promo['promo_from_ts']); ?></span>
							</div>
							<div class="vbo-promotion-date-to">
								<i class="fa fa-calendar"></i>
								<span class="vbo-promotion-date-label"><?php echo JText::_('VBOPROMORENTTO'); ?></span>
								<span class="vbo-promotion-date-to-sp"><?php echo date(str_replace("/", $datesep, $df), $promo['promo_to_ts']); ?></span>
							</div>
						</div>
						<div class="vbo-promotion-dates-right">
							<i class="fa fa-clock-o"></i>
						<?php
						if($promo['promo_to_ts'] != $promo['promo_valid_ts'] || ($promo['promo_to_ts'] == $promo['promo_valid_ts'] && empty($promo['promodaysadv']))) {
						?>
							<div class="vbo-promotion-date-validuntil">
								<span class="vbo-promotion-date-label"><?php echo JText::_('VBOPROMOVALIDUNTIL'); ?></span>
								<span><?php echo date(str_replace("/", $datesep, $df), $promo['promo_valid_ts']); ?></span>
							</div>
						<?php
						}
						if(!empty($promo['wdays'])) {
							$wdays = explode(';', $promo['wdays']);
						?>
							<div class="vbo-promotion-date-weekdays">
							<?php
							foreach ($wdays as $wday) {
								if(!(strlen($wday) > 0)) {
									continue;
								}
								?>
								<span class="vbo-promotion-date-weekday"><?php echo $days_labels[$wday]; ?></span>
								<?php
							}
							?>
							</div>
						<?php
						}
						?>
						</div>
					</div>
				</div>
				<div class="vbo-promotion-bottom-block">
					<div class="vbo-promotion-bottom-inner container-fluid">
						<?php
						//Rooms List
						if($showrooms === true && count($rooms) > 0 && !empty($promo['idrooms'])) {
							$promo_room_ids = explode(',', $promo['idrooms']);
							$promo_rooms = array();
							foreach ($promo_room_ids as $promo_room_id) {
								$promo_room_id = intval(str_replace("-", "", trim($promo_room_id)));
								if($promo_room_id > 0) {
									$promo_rooms[$promo_room_id] = $promo_room_id;
								}
							}
							if(count($promo_rooms) > 0) {
							?>
							<div class="vbo-promotion-rooms-list row">
							<?php
								foreach ($rooms as $idroom => $room) {
									if (!array_key_exists($idroom, $promo_rooms)) {
										continue;
									}
									?>
								<div class="vbo-promotion-room-block col-xs-6 col-sm-6 col-md-4">
									<div class="vbo-promotion-room-block-inner">
										<div class="vbo-promotion-room-img">
										<?php
										if(!empty($room['img'])) {
											?>
											<img alt="<?php echo $room['name']; ?>" src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $room['img']; ?>"/>
											<?php
										}
										?>
										</div>
										<div class="vbo-promotion-room-name">
											<?php echo $room['name']; ?>
										</div>
										<div class="vbo-promotion-room-book-block">
											<a class="vbo-promotion-room-book-link" href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$room['id'].'&checkin='.$promo['promo_from_ts'].'&promo='.$promo['id']); ?>"><?php echo JText::_('VBOPROMOROOMBOOKNOW'); ?></a>
										</div>
									</div>
								</div>
									<?php
								}
							}
							?>
							</div>
							<?php
						} 
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}else {
	?>
	<h3><?php echo JText::_('VBONOPROMOTIONSFOUND'); ?></h3>
	<?php
}

?>
