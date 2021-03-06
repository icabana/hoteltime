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

$current_lang = JFactory::getLanguage()->getTag();
$rtl_map = array('ar', 'dv', 'fa', 'he', 'ku', 'ps', 'sd', 'ug', 'ur', 'yi');
$rtl_attr = '';
if(in_array(substr($current_lang, 0, 2), $rtl_map)) {
	$rtl_attr = ' dir="rtl"';
}


$doc = JFactory::getDocument();
include('./templates/'.$this->template.'/config/colswitch.php');


$doc->addStyleSheet('templates/' . $this->template . '/css/bootstrap/bootstrap.css');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>"<?php echo $rtl_attr; ?> class="client-nojs">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 
  $css_string;
  $menutitle = $this->params->get('mobiletext'); 
  $get_less = $this->params->get('enabless'); 
?>
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />

<script>localStorage.clear(); </script>
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/templateskit.css" type="text/css" />
<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/templateskit.js"></script>

<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="HandheldFriendly" content="true">

<jdoc:include type="head" />
<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/main.css" type="text/css" />

<?php include('./templates/'.$this->template.'/blocks/config.php'); ?>

  <?php if($get_less == 1) { ?>
    <link rel="stylesheet/less" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/themes/<?php echo $cssname; ?>.less" type="text/css" />
    <script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/less-1.5.0.min.js"></script>
  <?php } else { ?>
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/themes/<?php echo $cssname; ?>.css" type="text/css" />
  <?php } ?> 
  <?php 
	
  
  if($resp == '0') { ?>
  <link rel="stylesheet" type="text/css"  media="only screen and (min-device-width : 280px) and (max-device-width : 1400px)" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/devices.css" />
  <link rel="stylesheet" type="text/css"  media="only screen and (min-width : 280px) and (max-width : 1400px)" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/devices.css" />
  <?php } ?> 
 <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/jquery-ui.min.css" type="text/css" />
 <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/custom.css" type="text/css" />
<?php
  echo $fontfamily;
  echo $bfontfamily;
?>

 
</head>
<body class="e4j-body-page">
<?php 
  include('./templates/'.$this->template.'/blocks/default.php');
?>
</body>
</html>