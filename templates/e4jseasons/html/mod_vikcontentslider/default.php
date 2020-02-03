<?php  
/**------------------------------------------------------------------------
 * mod_VikContentSlider
 * ------------------------------------------------------------------------
 * author    Valentina Arras - e4j.com
 * Copyright (C) 2014 - 2018 e4j.com. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: https://e4j.com
 * Technical Support:  templates@e4j.com
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die('Restricted Area'); 

$document = JFactory::getDocument();
$tpl_params = JFactory::getApplication()->getTemplate(true)->params;

JHtml::_('stylesheet', JURI::root().'modules/mod_vikcontentslider/css/mod_vikcontentslider.css', false, true, false, false);
JHtml::_('stylesheet', JURI::root().'modules/mod_vikcontentslider/css/animate.css', false, true, false, false);
JHtml::_('stylesheet', JURI::root().'modules/mod_vikcontentslider/css/bootstrap.css', false, true, false, false);
JHtml::_('stylesheet', JURI::root().'modules/mod_vikcontentslider/css/bootstrap-touch-slider.css', false, true, false, false);

if($params->get('load_fontawesome')) {
	JHtml::_('stylesheet', JURI::root().'modules/mod_vikcontentslider/css/fonts/src/font-awesome.min.css', false, true, false, false);
}

$arrslide = array();

if(intval($params->get('loadjq')) == 1 ) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', JURI::root().'modules/mod_vikcontentslider/src/jquery.js', false, true, false, false);
}
JHtml::_('script', JURI::root().'modules/mod_vikcontentslider/src/effects.js', false, true, false, false);
JHtml::_('script', JURI::root().'modules/mod_vikcontentslider/src/bootstrap.js', false, true, false, false);
JHtml::_('script', JURI::root().'modules/mod_vikcontentslider/src/bootstrap-touch-slider.js', false, true, false, false);

$get_align = $params->get('textalign');

$timeback = $params->get('timebackground');
$dotsnav = $params->get('dotsnav');

$autoplay = $params->get('autoplay');
$interval = $params->get('interval');
$navigation = $params->get('navigation');
$readmtext = $params->get('readmoretext');

$navenable = intval($navigation) == 1 ? true : false;
$autoplaygo = intval($autoplay) == 1 ? '1' : '0';

/** New Parameters **/
$first_read = true;
$get_title_effect = $params->get('title_effect');
$get_desc_effect = $params->get('desc_effect');
$get_readmore_effect = $params->get('readmore_effect');
$first_height = 0;

$slidejstr = $params->get('viksliderimages', '[]');
$slides = json_decode($slidejstr);
if (count($slides)) {
	foreach ($slides as $sk => $slide) {
		if((int)$slide->published < 1 || empty($slide->image)) {
			continue;
		}
		$imgabpath = JPATH_SITE.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $slide->image);
		if (file_exists($imgabpath)) {
			if(!($sk > 0)) {
				$img_size = @getimagesize($imgabpath);
				$first_height = $img_size && !($first_height > 0) ? $img_size[1] : $first_height;
			}
			$slider_entry = '<div id="vikcs-container" class="item'.($first_read ? ' active' : '').' vikcs-container-cstld">';
			if($tpl_params->get('sliderwind', 'def') == '0') {
				$slider_entry .= '<div class="slide-image vikcs-img-bckground vikcs-img-bckground-cstsld" style="background-image: url('.JURI::root().$slide->image.');">'; //Start - Div vikcs-img-bckground
			} else {
				$slider_entry .= '<img class="slide-image vikcs-img-bckground" src="'.JURI::root().$slide->image.'" alt="'.$slide->title.'"/>';
			}
			if($tpl_params->get('sliderwind', 'def') == '0') {
				$slider_entry .= '</div>'; //End - Div vikcs-img-bckground
			}
				
				$slider_entry .= '<div class="bs-slider-overlay"></div>';

				$slider_entry .= '<div class="container">'; //Start - Container
					$slider_entry .= '<div class="row">'; //Start - Row
						$slider_entry .= '<div class="slide-text slide_style_'.$get_align.'">'; //Start - slide-text

						if(!empty($slide->title)) {
							$slider_entry .= '<h2 data-animation="animated '.$get_title_effect.'">'.$slide->title.'</h2>';
						}

						$slider_entry .= '<p data-animation="animated '.$get_desc_effect.'">';
							if(!empty($slide->caption)) {
							$slider_entry .= '<span class="vikcs-desc">'.$slide->caption.'</span>';
							}
						$slider_entry .= '</p>';
						if(!empty($slide->readmore)) {
							$slider_entry .= '<a href="'.$slide->readmore.'" target="_blank" class="btn btn-default" data-animation="animated '.$get_readmore_effect.'">'.$readmtext.'</a>';
						}
			
						$slider_entry .= '</div>'; //End - slide-text
					$slider_entry .= '</div>'; //End - Row
				$slider_entry .= '</div>'; //End - Container
			$slider_entry .= '</div>';
		$first_read = false;
		$arrslide[] = $slider_entry;
		}
	}
}

	echo '<div id="bootstrap-touch-slider" class="carousel bs-slider fade control-round indicators-line vikcs-slider" data-ride="carousel" data-pause="hover" data-interval="5000">';

?>

	<!-- Indicators -->
	<?php if($dotsnav == 1) { ?>
	    <ol class="carousel-indicators">
	    	<?php foreach($arrslide as $vsl) {  ?>
	        	<li data-target="#bootstrap-touch-slider" data-slide-to="<?php echo $i; ?>" class="<?php ($first_read ? ' active' : ''); ?>"></li>
	        <?php } ?>
	    </ol>
    <?php } ?>

    <!-- Wrapper For Slides -->
    <div id="vikcs-slider" class="carousel-inner vikcs-slider vikcs-slider-cstsld" role="listbox">
		<?php 
	    if (is_array($arrslide)) {
			foreach($arrslide as $vsl) {
				echo $vsl;
			}
		}
		?>
	</div>

    <!-- Left Control -->
	<?php if($navigation == 1) { ?>
	    <a class="left carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="prev">
	        <span class="fa fa-angle-left" aria-hidden="true"></span>
	        <span class="sr-only">Previous</span>
	    </a>

	    <!-- Right Control -->
	    <a class="right carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="next">
	        <span class="fa fa-angle-right" aria-hidden="true"></span>
	        <span class="sr-only">Next</span>
	    </a>
	<?php } ?>
</div>

<script>
jQuery.noConflict();
jQuery(document).ready(function(){
	<?php if ($tpl_params->get('sliderwind', 'def') == '0') { ?>
	// Normal Size
		var altezza = jQuery(window).height();
		jQuery('.vikcs-slider-cstsld').css('height', altezza);

		// for SAFARI reload action
		jQuery('.vikcs-img-bckground-cstsld').first().on('load', function(e){
			jQuery('.vikcs-slider-cstsld').css('height', altezza);
		});

		var TRIGGER_RESIZE = true;
		var TRIGGER_TIMER = null;

		jQuery(window).resize(function() {
			jQuery('.vikcs-slider-cstsld').css('height', altezza);

			if( TRIGGER_TIMER !== null ) {
				clearTimeout(TRIGGER_TIMER);
			}

			TRIGGER_TIMER = setTimeout(function(){
					if( TRIGGER_RESIZE ) {
						jQuery(window).trigger('resize');
						TRIGGER_RESIZE = false;
					} else {
						TRIGGER_RESIZE = true;
					}
				}, 128);
		});
		// Windows Size
	<?php } ?>

	jQuery('#bootstrap-touch-slider').bsTouchSlider();
});

</script>
