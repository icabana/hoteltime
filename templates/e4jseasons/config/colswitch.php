<?php
$document = JFactory::getDocument();

require_once "lessc.inc.php";
$less = new lessc;
$css_print = "";

$arraycolors = array('0'=>'', '1'=>'', '2'=>'', '3'=>'', '4'=>'');
$tplcolor = intval($this->params->get('tcolour'));
$resp = intval($this->params->get('responsive'));
$font = intval($this->params->get('hfont'));
$bfont = intval($this->params->get('bfont'));


switch ($tplcolor) {
	case 1:
		$cssname='style_purple';
		break;
	case 2:
		$cssname='style_gold';
		break;
	case 3:
		$cssname='style_green';
		break;
	case 4:
		$cssname='style_orange';
		break;
	default:
		$cssname='style_sky';
		break;
}

switch ($font) {
	case 1:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=PT+Sans:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$fontname='PT Sans';
		break;
	case 2:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Open+Sans:400\' rel=\'stylesheet\' type=\'text/css\'>';
		$fontname='Open Sans';
		break;
	case 3:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Droid+Sans:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$fontname='Droid Sans';
		break;
	case 4:
		$fontfamily='';
		$fontname='Century Gothic';
		break;
	case 5:
		$fontfamily='';
		$fontname='Arial';
		break;
	case 6:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Lora:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$fontname='Lora';
		break;
	case 7:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Droid+Serif:400,700\' rel=\'stylesheet\'>';
		$fontname='Droid Serif';
		break;
	case 8:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Playfair+Display:400,700,900\' rel=\'stylesheet\'>';
		$fontname='Playfair Display';
		break;
	case 9:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=PT+Serif:400,700\' rel=\'stylesheet\'>';
		$fontname='PT Serif';
		break;
	default:
		$fontfamily='<link href=\'https://fonts.googleapis.com/css?family=Lato:300,400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$fontname='Lato';
		break;
}
switch ($bfont) {
	case 1:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=PT+Sans:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$bfontname='PT Sans';
		break;
	case 2:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Open+Sans:400\' rel=\'stylesheet\' type=\'text/css\'>';
		$bfontname='Open Sans';
		break;
	case 3:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Droid+Sans:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$bfontname='Droid Sans';
		break;
	case 4:
		$bfontfamily='';
		$bfontname='Century Gothic';
		break;
	case 5:
		$bfontfamily='';
		$bfontname='Arial';
		break;
	case 6:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Lora:400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$bfontname='Lora';
		break;
	case 7:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Droid+Serif:400,700\' rel=\'stylesheet\'>';
		$bfontname='Droid Serif';
		break;
	case 8:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Playfair+Display:400,700,900\' rel=\'stylesheet\'>';
		$bfontname='Playfair Display';
		break;
	case 9:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=PT+Serif:400,700\' rel=\'stylesheet\'>';
		$bfontname='PT Serif';
		break;
	default:
		$bfontfamily='<link href=\'https://fonts.googleapis.com/css?family=Lato:300,400,700\' rel=\'stylesheet\' type=\'text/css\'>';
		$bfontname='Lato';
		break;
}

$less->setVariables(array(
  "font" => $fontname,
  "bfont" => $bfontname
));
$css_print .= $less->compile(".item-page h1, .item-page h2, .item-page h3, .item-page h4, .item-page h5, .other-font {font-family: @font;}");
$css_print .= $less->compile("body {font-family: @bfont;} .vikqt_box {font-family: @font;}");
$document->addStyleDeclaration($css_print);

//echo $less->compileFile(JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR'config''vikcomponent_owr.less');

?>