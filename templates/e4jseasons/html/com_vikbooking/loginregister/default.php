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

$prices = $this->prices;
$rooms = $this->rooms;
$days = $this->days;
$checkin = $this->checkin;
$checkout = $this->checkout;
$selopt = $this->selopt;
$roomsnum = $this->roomsnum;
$adults = $this->adults;
$children = $this->children;
$arrpeople = $this->arrpeople;
$ppkg_id = VikRequest::getInt('pkg_id', '', 'request');

$strpriceid = "";
foreach($prices as $num => $pid) {
	$strpriceid .= "&priceid".$num."=".$pid;
}
$stroptid = "";
for($ir = 1; $ir <= $roomsnum; $ir++) {
	if (is_array($selopt[$ir])) {
		foreach($selopt[$ir] as $opt) {
			$stroptid .= "&optid".$ir.$opt['id']."=".$opt['quan'];
		}
	}
}
$strroomid = "";
foreach($rooms as $num => $r) {
	$strroomid .= "&roomid[]=".$r['id'];
}
$straduchild = "";
foreach($arrpeople as $indroom => $aduch) {
	$straduchild .= "&adults[]=".$aduch['adults'];
	$straduchild .= "&children[]=".$aduch['children'];
}

$action = 'index.php?option=com_user&amp;task=login';

$pitemid = VikRequest::getString('Itemid', '', 'request');

if (count($rooms) > 0 && !empty($checkin) && !empty($checkout)) {
	$goto = "index.php?option=com_vikbooking&task=oconfirm".$strpriceid.$stroptid.$strroomid.$straduchild."&roomsnum=".$roomsnum."&days=".$days."&checkin=".$checkin."&checkout=".$checkout.($ppkg_id > 0 ? '&pkg_id='.$ppkg_id : '').(!empty($pitemid) ? "&Itemid=".$pitemid : "");
	$goto = JRoute::_($goto, false);
} else {
	// The Joomla! home page
	$menu = JSite::getMenu();
	$default = $menu->getDefault();
	$uri = JFactory::getURI($default->link . '&Itemid=' . $default->id);
	$goto = $uri->toString(array (
		'path',
		'query',
		'fragment'
	));
}
$return_url = base64_encode($goto);

?>

<script language="JavaScript" type="text/javascript">
function checkVrcReg() {
	var vbvar = document.vbreg;
	if(!vbvar.name.value.match(/\S/)) {
		document.getElementById('vbfname').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbfname').style.color='';
	}
	if(!vbvar.lname.value.match(/\S/)) {
		document.getElementById('vbflname').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbflname').style.color='';
	}
	if(!vbvar.email.value.match(/\S/)) {
		document.getElementById('vbfemail').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbfemail').style.color='';
	}
	if(!vbvar.username.value.match(/\S/)) {
		document.getElementById('vbfusername').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbfusername').style.color='';
	}
	if(!vbvar.password.value.match(/\S/)) {
		document.getElementById('vbfpassword').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbfpassword').style.color='';
	}
	if(!vbvar.confpassword.value.match(/\S/)) {
		document.getElementById('vbfconfpassword').style.color='#ff0000';
		return false;
	}else {
		document.getElementById('vbfconfpassword').style.color='';
	}
	return true;
}
</script>

<div class="loginregistercont">
		
	<div class="registerblock">
	<form action="<?php echo JRoute::_('index.php?option=com_vikbooking'); ?>" method="post" name="vbreg" onsubmit="return checkVrcReg();">
	<h3><?php echo JText::_('VBREGSIGNUP'); ?></h3>
	<div class="register-cnt rl-cnt">
		<div><span id="vbfname" class="rl-label"><?php echo JText::_('VBREGNAME'); ?></span><span class="rl-inputfield"><input type="text" name="name" value="" size="20" class="vbinput"/></span></div>
		<div><span id="vbflname" class="rl-label"><?php echo JText::_('VBREGLNAME'); ?></span><span class="rl-inputfield"><input type="text" name="lname" value="" size="20" class="vbinput"/></span></div>
		<div><span id="vbfemail" class="rl-label"><?php echo JText::_('VBREGEMAIL'); ?></span><span class="rl-inputfield"><input type="text" name="email" value="" size="20" class="vbinput"/></span></div>
		<div><span id="vbfusername" class="rl-label"><?php echo JText::_('VBREGUNAME'); ?></span><span class="rl-inputfield"><input type="text" name="username" value="" size="20" class="vbinput"/></span></div>
		<div><span id="vbfpassword" class="rl-label"><?php echo JText::_('VBREGPWD'); ?></span><span class="rl-inputfield"><input type="password" name="password" value="" size="20" class="vbinput"/></span></div>
		<div><span id="vbfconfpassword" class="rl-label"><?php echo JText::_('VBREGCONFIRMPWD'); ?></span><span class="rl-inputfield"><input type="password" name="confpassword" value="" size="20" class="vbinput"/></span></div>
		<div><span><input type="submit" value="<?php echo JText::_('VBREGSIGNUPBTN'); ?>" class="booknow" name="submit" /></span></div>
	</div>
	<?php
	foreach($prices as $num => $pid) {
		?>
		<input type="hidden" name="priceid<?php echo $num; ?>" value="<?php echo $pid; ?>" />
		<?php
	}
	for($ir = 1; $ir <= $roomsnum; $ir++) {
		if (is_array($selopt[$ir])) {
			foreach($selopt[$ir] as $opt) {
				?>
				<input type="hidden" name="optid<?php echo $ir.$opt['id']; ?>" value="<?php echo $opt['quan']; ?>" />
				<?php
			}
		}
	}
	foreach($rooms as $num => $r) {
		?>
		<input type="hidden" name="roomid[]" value="<?php echo $r['id']; ?>" />
		<?php
	}
	foreach($arrpeople as $indroom => $aduch) {
		?>
		<input type="hidden" name="adults[]" value="<?php echo $aduch['adults']; ?>" />
		<input type="hidden" name="children[]" value="<?php echo $aduch['children']; ?>" />
		<?php
	}
	for($ir = 1; $ir <= $roomsnum; $ir++) {
		if (is_array($selopt[$ir])) {
			foreach($selopt[$ir] as $opt) {
				?>
				<input type="hidden" name="optid<?php echo $ir.$opt['id']; ?>" value="<?php echo $opt['quan']; ?>" />
				<?php
			}
		}
	}
	?>
	<input type="hidden" name="roomsnum" value="<?php echo $roomsnum; ?>" />
	<input type="hidden" name="days" value="<?php echo $days; ?>" />
	<input type="hidden" name="checkin" value="<?php echo $checkin; ?>" />
	<input type="hidden" name="checkout" value="<?php echo $checkout; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="register" />
	</form>
	</div>
<?php
//Joomla 3.x
?>
	<div class="loginblock">
	<form action="index.php?option=com_users" method="post">
	<h3><?php echo JText::_('VBREGSIGNIN'); ?></h3>
	<div class="register-cnt rl-cnt">
		<div><span class="rl-label"><?php echo JText::_('VBREGUNAME'); ?></span><span class="rl-inputfield"><input type="text" name="username" value="" size="20" class="vbinput"/></span></div>
		<div><span class="rl-label"><?php echo JText::_('VBREGPWD'); ?></span><span class="rl-inputfield"><input type="password" name="password" value="" size="20" class="vbinput"/></span></div>
		<div><span><input type="submit" value="<?php echo JText::_('VBREGSIGNINBTN'); ?>" class="booknow" name="Login" /></span></div>
	</div>
	<input type="hidden" name="remember" id="remember" value="yes" />
	<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	</form>
	</div>

		
</div>
