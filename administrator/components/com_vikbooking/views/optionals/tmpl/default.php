<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;

$vbo_app = VikBooking::getVboApplication();
$vbo_app->prepareModalBox();
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOOPTIONALSFOUND'); ?></p>
	<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikbooking" />
	</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikbooking" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vbo-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSONE' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSTWO' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSTHREE' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSFOUR' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSEIGHT' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSFIVE' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWOPTIONALSPERPERS' ); ?></th>
			<th class="title center" align="center" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSSIX' ); ?></th>
			<th class="title left" width="150"><?php echo JText::_( 'VBPVIEWOPTIONALSSEVEN' ); ?></th>
			<th class="title center" align="center" width="50"><?php echo JText::_( 'VBPVIEWOPTIONALSORDERING' ); ?></th>
		</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editoptional&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo (strlen($row['descr'])>150 ? substr($row['descr'], 0, 150) : $row['descr']); ?></td>
			<td class="center"><?php echo $row['cost']; ?></td>
			<td class="center"><?php echo VikBooking::getAliq($row['idiva']); ?>%</td>
			<td class="center"><?php echo $row['maxprice']; ?></td>
			<td class="center"><?php echo (intval($row['perday'])==1 ? "Y" : "N"); ?></td>
			<td class="center"><?php echo (intval($row['perperson'])==1 ? "Y" : "N"); ?></td>
			<td class="center"><?php echo (intval($row['hmany'])==1 ? "&gt; 1" : "1"); ?></td>
			<td><?php echo (file_exists(VBO_SITE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$row['img']) ? '<a href="'.VBO_SITE_URI.'resources/uploads/'.$row['img'].'" class="vbomodal" target="_blank">'.$row['img'].'</a>' : ''); ?></td>
			<td class="center"><a href="index.php?option=com_vikbooking&amp;task=sortoption&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up"><i class="fa fa-arrow-up vbo-icn-img"></i></a> <a href="index.php?option=com_vikbooking&amp;task=sortoption&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down"><i class="fa fa-arrow-down vbo-icn-img"></i></a></td>
		</tr>
		  <?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="optionals" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}