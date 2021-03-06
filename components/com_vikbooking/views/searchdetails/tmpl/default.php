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

//load jQuery lib
$document = JFactory::getDocument();
JHtml::_('jquery.framework', true, true);
JHtml::_('script', VBO_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
//

$session = JFactory::getSession();
$last_lang = $session->get('vboLastCurrency', '');

$vat_included = VikBooking::ivaInclusa();
$tax_summary = !$vat_included && VikBooking::showTaxOnSummaryOnly() ? true : false;

$room = $this->room;
$tar = $this->tar;
$checkin = $this->checkin;
$checkout = $this->checkout;
$adults = $this->adults;
$children = $this->children;
$daysdiff = $this->daysdiff;
$vbo_tn = $this->vbo_tn;

$currencysymb = VikBooking::getCurrencySymb();
$def_currency = VikBooking::getCurrencyName();

$carats = VikBooking::getRoomCaratOriz($room['idcarat'], $vbo_tn);

$imagegallery = false;
if(strlen($room['moreimgs']) > 0) {
	$imagegallery = true;
	$moreimages = explode(';;', $room['moreimgs']);
	$document->addStyleSheet(VBO_SITE_URI.'resources/VikFXThumbSlide.css');
	$document->addScript(VBO_SITE_URI.'resources/VikFXThumbSlide.js');
	$vikfsimgs = array();
	$imgcaptions = json_decode($room['imgcaptions'], true);
	$usecaptions = empty($imgcaptions) || is_null($imgcaptions) || !is_array($imgcaptions) || !(count($imgcaptions) > 0) ? false : true;
	foreach($moreimages as $iind => $mimg) {
		if (!empty($mimg)) {
			$vikfsimgs[] = '{image : "'.VBO_SITE_URI.'resources/uploads/big_'.$mimg.'", alt : "'.substr($mimg, 0, strpos($mimg, '.')).'", caption : "'.($usecaptions === true ? $imgcaptions[$iind] : "").'"}';
		}
	}
	$vikfx = '
jQuery.VikFXThumbSlide.set({
	images : [
		'.implode(',', $vikfsimgs).'
	],
	mainImageClass : "vikfx-thumbslide-image",
	fadeContainerClass : "vikfx-thumbslide-fade-container",
	thumbnailContainerClass: "vikfx-thumbslide-thumbnails",
	useNavigationControls: true,
	previousLinkClass : "vikfx-thumbslide-previous-image",
	nextLinkClass : "vikfx-thumbslide-next-image",
	startSlideShowClass : "vikfx-thumbslide-start-slideshow",
	stopSlideShowClass : "vikfx-thumbslide-stop-slideshow"
});
jQuery(document).ready(function () {
	jQuery.VikFXThumbSlide.init(".vikfx-thumbslide-container");
});';
	$document->addScriptDeclaration($vikfx);
}
?>

<div class="vbdetroom">
	<div class="vbroomdetcont">
		<div class="vbroomimgdesc">
			<div class="vbo-searchdet-head">
				<div class="vblistroomnamediv"><span class="vblistroomname"><?php echo $room['name']; ?></span></div>
				<div class="vikfx-thumbslide-container">
					<div class="vikfx-thumbslide-fade-container">
					<?php
					if ($imagegallery === true) {
					?>
						<div class="vikfx-thumbslide-navigation-controls">
							<div class="vikfx-thumbslide-navigation-controls-prevnext">
								<a href="javascript: void(0);" class="vikfx-thumbslide-previous-image"><i class="fa fa-chevron-circle-left"></i></a>
								<a href="javascript: void(0);" class="vikfx-thumbslide-next-image"><i class="fa fa-chevron-circle-right"></i></a>
							</div>
							<div class="vikfx-thumbslide-navigation-controls-stasto">
								<a href="javascript: void(0);" class="vikfx-thumbslide-start-slideshow"><i class="fa fa-play-circle"></i></a>
								<a href="javascript: void(0);" class="vikfx-thumbslide-stop-slideshow"><i class="fa fa-stop-circle"></i></a>
							</div>
						</div>
					<?php
					}
					?>
						<img src="<?php echo VBO_SITE_URI; ?>resources/uploads/<?php echo $room['img']; ?>" class="vikfx-thumbslide-image vblistimg"/>
						<div class="vikfx-thumbslide-caption"></div>
					</div>
			<?php
			if ($imagegallery === true) {
			?>	
					<ul class="vikfx-thumbslide-thumbnails">
				<?php
				foreach ($moreimages as $mimg) {
					if (!empty($mimg)) {
						?>
						<li>
							<a href="<?php echo VBO_SITE_URI; ?>resources/uploads/big_<?php echo $mimg; ?>" target="_blank"><img src="<?php echo VBO_SITE_URI; ?>resources/uploads/thumb_<?php echo $mimg; ?>"/></a>
						</li>
						<?php
					}
				}
				?>
					</ul>
			<?php
			}
			?>
				</div>
			</div>
			<div class="room_description_box">
		<?php
		//BEGIN: Joomla Content Plugins Rendering
		if (class_exists('JEventDispatcher')) {
			JPluginHelper::importPlugin('content');
			$myItem = JTable::getInstance('content');
			$dispatcher = JEventDispatcher::getInstance();
			$myItem->text = $room['info'];
			$dispatcher->trigger('onContentPrepare', array('com_vikbooking.roomdetails', &$myItem, &$params, 0));
			$room['info'] = $myItem->text;
		}
		//END: Joomla Content Plugins Rendering
		echo $room['info'];
		?>
			</div>
		</div>

	<?php 
	if (!empty($carats)) {
	?>
		<div class="room_carats">
			<h3 class="vbtith3"><?php echo JText::_('VBCHARACTERISTICS'); ?></h3>
			<?php echo $carats; ?>
		</div>
	<?php
	}
	?>
	</div>
	<div class="vb_detcostroom">
		<div id="vbsrchdetpriceopen" class="vb_detpriceroombt"><span><?php echo JText::_('VBPRICEDETAILS'); ?></span></div>
		<div id="vbsrchdetpricebox" class="vbsrchdetpricebox">
			<div id="vbsrchdetpriceboxinner" class="vbsrchdetpriceboxinner">
				<span class="vbroomnumnightsdet"><?php echo $daysdiff; ?> <?php echo ($daysdiff > 1 ? JText::_('VBSEARCHRESNIGHTS') : JText::_('VBSEARCHRESNIGHT')); ?></span>
				<div class="vbpricedetstable">
					<div class="vbpricedetstrhead">
						<div class="vbpricedetstable-leftcol"><?php echo JText::_('VBPRICEDETAILSDAY'); ?></div>
						<div class="vbpricedetstable-rightcol"><?php echo JText::_('VBPRICEDETAILSPRICE'); ?></div>
					</div>
					<?php
					$one = getdate($checkin);
					$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
					$rowk = 0;
					for ($i = 0; $i < $daysdiff; $i++) {
						$todayts = $fromdayts + ($i * 86400);
						$checkwday = getdate($todayts);
						if (array_key_exists('affdayslist', $tar[0])) {
							if (array_key_exists($checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon'], $tar[0]['affdayslist'])) {
								$todaycost = $tar[0]['affdayslist'][$checkwday['wday'].'-'.$checkwday['mday'].'-'.$checkwday['mon']];
							}else {
								$todaycost = $tar[0]['origdailycost'];
							}
						}else {
							$todaycost = $tar[0]['cost'] / $tar[0]['days'];
						}
						?>
						<div class="vbpricedetstr<?php echo $rowk; ?>">
							<div class="vbpricedetstable-leftcol"><?php echo VikBooking::sayWeekDay($checkwday['wday']).' '.$checkwday['mday']; ?></div>
							<div class="vbpricedetstable-rightcol"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo $tax_summary ? VikBooking::numberFormat($todaycost) : VikBooking::numberFormat(VikBooking::sayCostPlusIva($todaycost, $tar[0]['idprice'])); ?></span></div>
						</div>
						<?php
						$rowk = 1 - $rowk;
					}
					if (array_key_exists('diffusage', $tar[0])) {
						if(!empty($tar[0]['diffusagecost'])) {
							$operator = substr($tar[0]['diffusagecost'], 0, 1);
							$valpcent = substr($tar[0]['diffusagecost'], -1);
							$saydiffusage = $valpcent == "%" ? "" : '<span class="vbo_currency">'.$currencysymb."</span> ";
							$saydiffusage .= $operator." ".($valpcent != "%" ? '<span class="vbo_price">' : '').VikBooking::numberFormat(substr($tar[0]['diffusagecost'], 1, (strlen($tar[0]['diffusagecost']) - 1))).($valpcent == "%" ? " %" : "</span>");
							?>
						<div class="vbpricedetstr<?php echo $rowk; ?>">
							<div class="vbpricedetstable-leftcol">&nbsp;</div>
							<div class="vbpricedetstable-rightcol">&nbsp;</div>
						</div>
							<?php
							$rowk = 1 - $rowk;
							?>
						<div class="vbpricedetstr<?php echo $rowk; ?>">
							<div class="vbpricedetstable-leftcol"><?php echo $tar[0]['diffusage']; ?> <?php echo $tar[0]['diffusage'] > 1 ? JText::_('VBSEARCHRESADULTS') : JText::_('VBSEARCHRESADULT'); ?></div>
							<div class="vbpricedetstable-rightcol"><?php echo $saydiffusage; ?></div>
						</div>
							<?php
						}
					}
					?>
				</div>
			</div>
		</div>
		
		<div class="vbpricedet-priceblock">
			<div class="vbpricedet-priceinner">
		<?php
		if ($tar[0]['cost'] > 0) {
			?>
				<span class="room_cost"><span class="vbo_currency"><?php echo $currencysymb; ?></span> <span class="vbo_price"><?php echo $tax_summary ? VikBooking::numberFormat($tar[0]['cost']) : VikBooking::numberFormat(VikBooking::sayCostPlusIva($tar[0]['cost'], $tar[0]['idprice'])); ?></span></span>
			<?php
		}
		?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery.noConflict();
var sendprices = new Array();
var fromCurrency = '<?php echo $def_currency; ?>';
var fromSymbol;
var pricestaken = 0;
jQuery(document).ready(function() {
	if(jQuery(".vbo_price").length > 0) {
		jQuery(".vbo_price").each(function() {
			sendprices.push(jQuery(this).text());
		});
		pricetaken = 1;
	}
	if(jQuery(".vbo_currency").length > 0) {
		fromSymbol = jQuery(".vbo_currency").first().html();
	}
	<?php
	if(!empty($last_lang) && $last_lang != $def_currency) {
		?>
	if(jQuery(".vbo_price").length > 0) {
		vboConvertCurrency('<?php echo $last_lang; ?>');
	}
		<?php
	}
	?>
});
function vboConvertCurrency(toCurrency) {
	if(sendprices.length > 0) {
		jQuery(".vbo_currency").text(toCurrency);
		jQuery(".vbo_price").text("...").addClass("vbo_converting");
		var modvbocurconvax = jQuery.ajax({
			type: "POST",
			url: "<?php echo JRoute::_('index.php?option=com_vikbooking&task=currencyconverter'); ?>",
			data: {prices: sendprices, fromsymbol: fromSymbol, fromcurrency: fromCurrency, tocurrency: toCurrency, tmpl: "component"}
		}).done(function(resp) {
			jQuery(".vbo_price").removeClass("vbo_converting");
			var convobj = jQuery.parseJSON(resp);
			if(convobj.hasOwnProperty("error")) {
				alert(convobj.error);
				vboUndoConversion();
			}else {
				jQuery(".vbo_currency").html(convobj[0].symbol);
				jQuery(".vbo_price").each(function(i) {
					jQuery(this).text(convobj[i].price);
				});
			}
		}).fail(function(){
			jQuery(".vbo_price").removeClass("vbo_converting");
			vboUndoConversion();
		});
	}
}
function vboUndoConversion() {
	jQuery(".vbo_currency").text(fromSymbol);
	jQuery(".vbo_price").each(function(i) {
		jQuery(this).text(sendprices[i]);
	});
}
</script>