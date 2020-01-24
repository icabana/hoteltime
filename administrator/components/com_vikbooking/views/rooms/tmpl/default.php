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
$orderby = $this->orderby;
$ordersort = $this->ordersort;

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VBNOROOMSFOUND'); ?></p>
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
			<th class="title left" width="150">
				<a href="index.php?option=com_vikbooking&amp;task=rooms&amp;vborderby=name&amp;vbordersort=<?php echo ($orderby == "name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "name" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "name" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWROOMONE').($orderby == "name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" align="center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=rooms&amp;vborderby=toadult&amp;vbordersort=<?php echo ($orderby == "toadult" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "toadult" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "toadult" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWROOMADULTS').($orderby == "toadult" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "toadult" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" align="center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=rooms&amp;vborderby=tochild&amp;vbordersort=<?php echo ($orderby == "tochild" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "tochild" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "tochild" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWROOMCHILDREN').($orderby == "tochild" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "tochild" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" align="center" width="75">
				<a href="index.php?option=com_vikbooking&amp;task=rooms&amp;vborderby=totpeople&amp;vbordersort=<?php echo ($orderby == "totpeople" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "totpeople" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "totpeople" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWROOMTOTPEOPLE').($orderby == "totpeople" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "totpeople" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75"><?php echo JText::_( 'VBPVIEWROOMTWO' ); ?></th>
			<th class="title center" align="center" width="75"><?php echo JText::_( 'VBPVIEWROOMTHREE' ); ?></th>
			<th class="title center" align="center" width="150"><?php echo JText::_( 'VBPVIEWROOMFOUR' ); ?></th>
			<th class="title center" align="center" width="100">
				<a href="index.php?option=com_vikbooking&amp;task=rooms&amp;vborderby=units&amp;vbordersort=<?php echo ($orderby == "units" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "units" && $ordersort == "ASC" ? "vbo-list-activesort" : ($orderby == "units" ? "vbo-list-activesort" : "")); ?>">
					<?php echo JText::_('VBPVIEWROOMSEVEN').($orderby == "units" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "units" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" align="center" width="100"><?php echo JText::_( 'VBPVIEWROOMSIX' ); ?></th>
		</tr>
		</thead>
	<?php
	$dbo = JFactory::getDBO();
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$categories = "";
		if (strlen(trim(str_replace(";", "", $row['idcat']))) > 0) {
			$cat = explode(";", $row['idcat']);
			$catsfound = false;
			$q = "SELECT `name` FROM `#__vikbooking_categories` WHERE ";
			foreach ($cat as $k => $cc) {
				if (!empty($cc)) {
					$q .= "`id`=".$dbo->quote($cc)." ";
					if ($cc != end($cat) && !empty($cat[($k + 1)])) {
						$q .= "OR ";
					}
					$catsfound = true;
				}
			}
			$q .= ";";
			if ($catsfound) {
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$lines = $dbo->loadAssocList();
					$categories = array();
					foreach($lines as $ll) {
						$categories[] = $ll['name'];
					}
					$categories = implode(", ", $categories);
				}
			}
		}
		
		if (!empty($row['idcarat'])) {
			$tmpcarat = explode(";", $row['idcarat']);
			$caratteristiche = VikBooking::totElements($tmpcarat);
		} else {
			$caratteristiche = "";
		}
		
		if (!empty($row['idopt'])) {
			$tmpopt = explode(";", $row['idopt']);
			$optionals = VikBooking::totElements($tmpopt);
		} else {
			$optionals = "";
		}
		if ($row['fromadult'] == $row['toadult']) {
			$stradult = $row['fromadult'];
		} else {
			$stradult = $row['fromadult'].' - '.$row['toadult'];
		}
		if ($row['fromchild'] == $row['tochild']) {
			$strchild = $row['fromchild'];
		} else {
			$strchild = $row['fromchild'].' - '.$row['tochild'];
		}
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikbooking&amp;task=editroom&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td class="center"><?php echo $stradult; ?></td>
			<td class="center"><?php echo $strchild; ?></td>
			<td class="center"><?php echo $row['mintotpeople'].' - '.$row['totpeople']; ?></td>
			<td class="center"><?php echo $categories; ?></td>
			<td class="center"><?php echo $caratteristiche; ?></td>
			<td class="center"><?php echo $optionals; ?></td>
			<td class="center"><?php echo $row['units']; ?></td>
			<td class="center"><a href="index.php?option=com_vikbooking&amp;task=modavail&amp;cid[]=<?php echo $row['id']; ?>"><?php echo (intval($row['avail'])=="1" ? "<i class=\"fa fa-check vbo-icn-img\" style=\"color: #099909;\" title=\"".JText::_('VBMAKENOTAVAIL')."\"></i>" : "<i class=\"fa fa-times-circle vbo-icn-img\" style=\"color: #ff0000;\" title=\"".JText::_('VBMAKENOTAVAIL')."\"></i>"); ?></a></td>
		 </tr>
		  <?php
		$kk = 1 - $kk;
		unset($categories);
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikbooking" />
	<input type="hidden" name="task" value="rooms" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}