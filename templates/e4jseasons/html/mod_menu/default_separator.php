<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = $item->anchor_title ? ' title="' . $item->anchor_title . '"' : '';
$anchor_css = $item->anchor_css ? $item->anchor_css : '';

$linktype = $item->title;

if ($item->menu_image)
{
	$linktype = JHtml::_('image', $item->menu_image, $item->title);

	if ($item->params->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}

$parts = explode("||", $linktype);
// the "|" is the divider
if(isset($parts[1])){
    $linktype = '<span class="e4j-menutitle">'.$parts[0].'</span><span class="e4j-menusubtitle">'.$parts[1].'</span>';
}else{
    $linktype = $parts[0];
};

?>

<span class="e4jmenu separator <?php echo $anchor_css; ?>"<?php echo $title; ?>><?php echo $linktype; ?></span>
