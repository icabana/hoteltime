<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */
?>
<dl class="contact-address dl-horizontal" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
		<dl class="contact-address-company">

			<?php if ($this->contact->address && $this->params->get('show_street_address')) : ?>
				<dd>
					<i class="fa fa-map-marker"></i>
					<span class="contact-street" itemprop="streetAddress">
						<?php echo nl2br($this->contact->address) . '<br />'; ?>
					</span>
				</dd>
			<?php endif; ?>
			
		</dl>
		<dl class="contact-address-details">
				<?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
					<dd>
						<i class="fa fa-globe"></i>
						<span class="contact-suburb" itemprop="addressLocality">
							<?php echo $this->contact->suburb . '<br />'; ?>
						</span>
					</dd>
				<?php endif; ?>
				<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
					<dd>
						<span class="contact-state" itemprop="addressRegion">
							<?php echo $this->contact->state . '<br />'; ?>
						</span>
					</dd>
				<?php endif; ?>
				<?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
					<dd>
						<span class="contact-postcode" itemprop="postalCode">
							<?php echo $this->contact->postcode . '<br />'; ?>
						</span>
					</dd>
				<?php endif; ?>
				<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
			<dd>
				<span class="contact-country" itemprop="addressCountry">
					<?php echo $this->contact->country . '<br />'; ?>
				</span>
			</dd>
			<?php endif; ?>
			</dl>
	<?php endif; ?>

<?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
	<dl class="contact-email">
		<dd>
			<i class="fa fa-envelope"></i>
			<span class="contact-emailto">
				<?php echo $this->contact->email_to; ?>
			</span>
		</dd>
	</dl>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
	<dl class="contact-tel">
		<dd>
			<i class="fa fa-phone"></i>
			<span class="contact-telephone" itemprop="telephone">
				<?php echo nl2br($this->contact->telephone); ?>
			</span>
		</dd>
	</dl>
<?php endif; ?>
<?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
	<dl class="contact-fax">
		<dd>
			<i class="fa fa-fax"></i>
			<span class="contact-fax" itemprop="faxNumber">
			<?php echo nl2br($this->contact->fax); ?>
			</span>
		</dd>
	</dl>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
	<dl class="contact-mobile">
		<dd>
			<i class="fa fa-mobile"></i>
			<span class="contact-mobile" itemprop="telephone">
				<?php echo nl2br($this->contact->mobile); ?>
			</span>
		</dd>
	</dl>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
	<dl class="contact-website">
		<dd>
			<span class="contact-webpage">
				<i class="fa fa-laptop"></i>
				<a href="<?php echo $this->contact->webpage; ?>" target="_blank" itemprop="url">
				<?php echo JStringPunycode::urlToUTF8($this->contact->webpage); ?></a>
			</span>
		</dd>
	</dl>
<?php endif; ?>
</dl>
