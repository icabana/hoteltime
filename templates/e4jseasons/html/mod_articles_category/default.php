<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// @unused variable
//$images = json_decode($item->images);
?>
<ul class="category-module">
	<div class="container-fluid">
		<div class="row">
			<?php if ($grouped) : ?>
			<?php foreach ($list as $group_name => $group) : ?>
			<li class="category-module-item col-xs-12 col-sm-6 col-md-6">
				<div class="mod-articles-category-group"><?php echo $group_name;?></div>
					<ul>
						<?php foreach ($group as $item) : ?>
							<li>
								<div class="category-module-inner d-flex">
								<?php if ($item->displayDate) : ?>
									<span class="mod-articles-category-date">
										<?php// echo $item->displayDate; ?>
										<span class="day"><?php echo substr($item->displayDate, 0,3); ?></span> 
										<span class="month"><?php echo substr($item->displayDate, 3,4); ?></span>
										<span class="day-numb"><?php echo substr($item->displayDate, 7,8); ?></span>
									</span>
								<?php endif; ?>
								<?php 
								$imgintro = json_decode($item->images)->image_intro; 
								if (!empty($imgintro)) { ?>
									<div class="modcategory-img">
										<a href="<?php echo $item->link; ?>"><img class="img-fluid" src="<?php echo $imgintro; ?>"/></a>
									</div>
								<?php }
								?>
						
								<div class="mod-articles-category-cnt">
								<?php if ($params->get('link_titles') == 1) : ?>
									<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
										<?php echo $item->title; ?>
									</a>
								<?php else : ?>
									<h4 class="mod-articles-category-title"><?php echo $item->title; ?></h4>
								<?php endif; ?>
			
								<?php if ($item->displayHits) : ?>
									<span class="mod-articles-category-hits">
										(<?php echo $item->displayHits; ?>)
									</span>
								<?php endif; ?>
			
								<?php if ($params->get('show_author')) : ?>
									<span class="mod-articles-category-writtenby">
										<?php echo $item->displayAuthorName; ?>
									</span>
								<?php endif;?>
			
								<?php if ($item->displayCategoryTitle) : ?>
									<span class="mod-articles-category-category">
										(<?php echo $item->displayCategoryTitle; ?>)
									</span>
								<?php endif; ?>
			
								<?php if ($item->displayDate) : ?>
									<span class="mod-articles-category-date"><?php echo $item->displayDate; ?></span>
								<?php endif; ?>
			
								<?php if ($params->get('show_introtext')) : ?>
									<p class="mod-articles-category-introtext">
										<?php echo $item->displayIntrotext; ?>
									</p>
								<?php endif; ?>
			
								<?php if ($params->get('show_readmore')) : ?>
									<p class="mod-articles-category-readmore">
										<a class="btn btn-grey <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
											<?php if ($item->params->get('access-view') == false) : ?>
												<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
											<?php elseif ($readmore = $item->alternative_readmore) : ?>
												<?php echo $readmore; ?>
												<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
													<?php if ($params->get('show_readmore_title', 0) != 0) : ?>
														<?php echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit')); ?>
													<?php endif; ?>
											<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
												<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
											<?php else : ?>
												<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
												<?php echo JHtml::_('string.truncate', ($item->title), $params->get('readmore_limit')); ?>
											<?php endif; ?>
										</a>
									</p>
								<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</li>
			<?php endforeach; ?>
		<?php else : ?>
			<?php foreach ($list as $item) : ?>
				<li class="category-module-item col-xs-12 col-sm-6 col-md-6"> 
					<div class="category-module-inner d-flex">
					<?php if ($item->displayDate) : ?>
						<span class="mod-articles-category-date">
							<span class="day"><?php echo substr($item->displayDate, 0,3); ?></span> 
							<span class="month"><?php echo substr($item->displayDate, 3,4); ?></span>
							<span class="day-numb"><?php echo substr($item->displayDate, 7,8); ?></span>
						</span>
					<?php endif; ?>

							<?php 
							$imgintro = json_decode($item->images)->image_intro; 
							if (!empty($imgintro)) { ?>
								<div class="modcategory-img">
									<a href="<?php echo $item->link; ?>"><img class="img-fluid" src="<?php echo $imgintro; ?>"/></a>
								</div>
							<?php }
							?>
							<div class="mod-articles-category-cnt">
								<?php if ($params->get('link_titles') == 1) : ?>
									<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
										<?php echo $item->title; ?>
									</a>
								<?php else : ?>
									<h4 class="mod-articles-category-title"><?php echo $item->title; ?></h4>
								<?php endif; ?>
					
								<?php if ($item->displayHits) : ?>
									<span class="mod-articles-category-hits">
										(<?php echo $item->displayHits; ?>)
									</span>
								<?php endif; ?>
					
								<?php if ($params->get('show_author')) : ?>
									<span class="mod-articles-category-writtenby">
										<?php echo $item->displayAuthorName; ?>
									</span>
								<?php endif;?>
					
								<?php if ($item->displayCategoryTitle) : ?>
									<span class="mod-articles-category-category">
										(<?php echo $item->displayCategoryTitle; ?>)
									</span>
								<?php endif; ?>
						
								<?php if ($params->get('show_introtext')) : ?>
									<p class="mod-articles-category-introtext">
										<?php echo $item->displayIntrotext; ?>
									</p>
								<?php endif; ?>
					
								<?php if ($params->get('show_readmore')) : ?>
									<p class="mod-articles-category-readmore">
										<a class="btn btn-grey <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
											<?php if ($item->params->get('access-view') == false) : ?>
												<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
											<?php elseif ($readmore = $item->alternative_readmore) : ?>
												<?php echo $readmore; ?>
												<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
											<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
												<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
											<?php else : ?>
												<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
												<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
											<?php endif; ?>
										</a>
									</p>
								<?php endif; ?>
							</div>
						</div>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
</ul>
