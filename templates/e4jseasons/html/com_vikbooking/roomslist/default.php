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

$rooms=$this->rooms;
$category=$this->category;
$vbo_tn=$this->vbo_tn;
$navig=$this->navig;

$currencysymb = VikBooking::getCurrencySymb();

if(is_array($category)) {
	?>
	<div class="vbo-roomlist-headdesc">
		<h3 class="vbclistheadt other-font"><span><?php echo $category['name']; ?></span></h3>
		<?php
		if(strlen($category['descr']) > 0) {
			?>
			<div class="vbcatdescr">
				<?php echo $category['descr']; ?>
			</div>
			<?php
		} ?>
	</div>
<?php }else {
	echo VikBooking::getFullFrontTitle();
}

?>
<div class="vblistcontainer">
<ul class="vblist">
<?php
foreach($rooms as $r) {
	$carats = VikBooking::getRoomCaratOriz($r['idcarat'], $vbo_tn);
	//BEGIN: Joomla Content Plugins Rendering
	if (class_exists('JEventDispatcher')) {
		JPluginHelper::importPlugin('content');
		$myItem = JTable::getInstance('content');
		$dispatcher = JEventDispatcher::getInstance();
		$myItem->text = $r['smalldesc'];
		$dispatcher->trigger('onContentPrepare', array('com_vikbooking.roomslist', &$myItem, &$params, 0));
		$r['smalldesc'] = $myItem->text;
	}
	//END: Joomla Content Plugins Rendering
	?>
	<li class="room_result">
	<div class="vblistroomblock">
	<?php
	if(!empty($r['img'])) {
	?>
		<div class="vbimglistdiv">
			<img src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $r['img']; ?>" alt="<?php echo $r['name']; ?>" class="vblistimg"/>
		</div>
	<?php
	}
	?>
		<div class="vbo-info-room">
			<div class="vbdescrlistdiv">
				<span class="vbrowcname"><?php echo $r['name']; ?></span>
				<span class="vblistroomcat"><?php echo VikBooking::sayCategory($r['idcat'], $vbo_tn); ?></span>
				<div class="vbrowcdescr"><?php echo $r['smalldesc']; ?></div>
			</div>
			<?php 
			if (!empty($carats)) {
				?>
				<div class="roomlist_carats">
				<?php echo $carats; ?>
				</div>
				<?php
			}
			?>
		</div>
	</div>

		<div class="vbcontdivtot">
		<div class="vbdivtot">
		<div class="vbdivtotinline">
			<div class="vbsrowprice">
			<div class="vbrowroomcapacity">
		<?php
		for($i = 1; $i <= $r['toadult']; $i++) {
			?>
			<i class="fa fa-male"></i>
			<?php
		}
		?>
			</div>
		<?php
		$custprice = VikBooking::getRoomParam('custprice', $r['params']);
		$custpricetxt = VikBooking::getRoomParam('custpricetxt', $r['params']);
		$custpricetxt = empty($custpricetxt) ? JText::_('VBLISTPERNIGHT') : JText::_($custpricetxt);
		$custpricesubtxt = VikBooking::getRoomParam('custpricesubtxt', $r['params']);
		if($r['cost'] > 0 || !empty($custprice)) {
		?>
		<div class="vbsrowpricediv">
			<span class="room_cost"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo (!empty($custprice) ? VikBooking::numberFormat($custprice) : VikBooking::numberFormat($r['cost'])); ?></span></span>
			<span class="vbliststartfrom"><?php echo $custpricetxt; ?></span>
			<?php
			if(!empty($custpricesubtxt)) {
				?>
			<div class="vbliststartfrom-subtxt"><?php echo $custpricesubtxt; ?></div>
				<?php
			}
			?>
		</div>
		<?php
		}
		?>
		
			</div>
			<div class="vbselectordiv">
				<div class="vbselectr"><a href="<?php echo JRoute::_('index.php?option=com_vikbooking&view=roomdetails&roomid='.$r['id']); ?>"><?php echo JText::_('VBSEARCHRESDETAILS'); ?></a></div>
			</div>
			
		</div>
		</div>
		</div>
	</li>
	<?php
}
?>
</ul>
</div>

<?php
//pagination
if(strlen($navig) > 0) {
	?>
	<div class="pagination"><?php echo $navig; ?></div>
	<?php
}
?>