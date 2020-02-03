<?php  
/**------------------------------------------------------------------------
 * mod_vikbooking_currencyconverter - VikBooking
 * ------------------------------------------------------------------------
 * author    Alessio Gaggii - e4j - Extensionsforjoomla.com
 * copyright Copyright (C) 2014 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.extensionsforjoomla.com
 * Technical Support:  tech@extensionsforjoomla.com
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die('Restricted Area');

$session =& JFactory::getSession();
$last_lang = $session->get('vboLastCurrency', '');

$document = & JFactory :: getDocument();
$document->addStyleSheet(JURI::root().'modules/mod_vikbooking_currencyconverter/mod_vikbooking_currencyconverter.css');
if(intval($params->get('loadjqueryvb')) == 1) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', JURI::root().'components/com_vikbooking/resources/jquery-1.11.3.js', false, true, false, false);
}

$active_suff = empty($last_lang) ? $def_currency : $last_lang;

?>
<script type="text/javascript">
jQuery.noConflict();
var sendprices = new Array();
var vbcurconvbasepath = '<?php echo JURI::root().'modules/mod_vikbooking_currencyconverter/images/flags/'; ?>';
var vbcurconvbaseflag = '<?php echo JURI::root().'modules/mod_vikbooking_currencyconverter/images/flags/'.$active_suff.'.png'; ?>';
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
		jQuery(".vbo_price").text("").addClass("vbo_converting");
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
				jQuery("#vbcurconv-flag-img").attr("src", vbcurconvbasepath+toCurrency+".png");
				jQuery("#vbcurconv-flag-img").attr("alt", toCurrency);
				jQuery("#vbcurconv-flag-img").attr("title", toCurrency);
				jQuery("#vbcurconv-flag-symb").html(convobj[0].symbol);
			}
		}).fail(function(){
			jQuery(".vbo_price").removeClass("vbo_converting");
			vboUndoConversion();
		});
	}else {
		jQuery("#modcurconvsel").val("<?php echo $active_suff; ?>");
	}
}
function vboUndoConversion() {
	jQuery(".vbo_currency").text(fromSymbol);
	jQuery(".vbo_price").each(function(i) {
		jQuery(this).text(sendprices[i]);
	});
	jQuery("#vbcurconv-flag-symb").text(fromSymbol);
	jQuery("#vbcurconv-flag-img").attr("src", vbcurconvbaseflag);
	jQuery("#vbcurconv-flag-img").attr("alt", fromCurrency);
	jQuery("#vbcurconv-flag-img").attr("title", fromCurrency);
	jQuery("#modcurconvsel").val(fromCurrency);
}
</script>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>">
	<div class="vbcurconvcontainer">
		<div class="vbcurconv-inner">
			<div class="vbcurconv-flag">
			<?php
			//if (file_exists(JPATH_SITE . DS . 'modules' . DS . 'mod_vikbooking_currencyconverter' . DS . 'images' . DS . 'flags' . DS . $active_suff . '.png'))
			echo '<img id="vbcurconv-flag-img" alt="'.$active_suff.'" title="'.$active_suff.'" src="'.JURI::root().'modules/mod_vikbooking_currencyconverter/images/flags/'.$active_suff.'.png'.'"/>';
			$active_symb = array_key_exists($active_suff, $currencymap) ? '&#'.$currencymap[$active_suff]['symbol'].';' : '';
			?>
				<span id="vbcurconv-flag-symb"><?php echo $active_symb; ?></span>
			</div>
			<div class="vbcurconv-menu">
				<span>
					<select id="modcurconvsel" name="mod_vikbooking_currencyconverter" onchange="vboConvertCurrency(this.value);">
					<?php
				    foreach($currencies as $cur) {
				    	$three_code = substr($cur, 0, 3);
						$curparts = explode(':', $cur);
						if($currencynameformat == 1) {
							$curname = $three_code;
						}elseif($currencynameformat == 2) {
							$curname = trim($curparts[1]);
						}else {
							$curname = trim($curparts[1]).' ('.$three_code.')';
						}
						?>
						<option value="<?php echo $three_code; ?>"<?php echo ((empty($last_lang) && $three_code == $def_currency) || (!empty($last_lang) && $three_code == $last_lang) ? ' selected="selected"' : ''); ?>><?php echo $curname; ?></option>
						<?php
				    }
				    ?>
				    </select>
		   		</span>
		   		<i class="vbcurconv-arrow"></i>
		    </div>
		</div>
	</div>
</div>