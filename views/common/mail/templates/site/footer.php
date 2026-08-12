
</div>
</div>
<footer class="text-center footer">
<div class="footer-link-cover">
	<?php
		if (!empty($site_id)) {
		$footer_image = $this->addtemplate_model->get_email_templates_image_url('footer_image_' . $site_id);
		$footer_image = !empty($footer_image) ? $footer_image : (($parent_id != $site_id) ? $this->addtemplate_model->get_email_templates_image_url('footer_image_' . $parent_id) : '');

		if(0 && $footer_image) {
	?>
	<img
		src="<?= $footer_image; ?>"
		class="footer-img"
	/>
	<?php }} ?>
</div>

<?php if(0 && empty($site_code)) { ?>
<div class="text-center">
	<a href="https://www.facebook.com/bribooksglobal"><img src="<?=site_url('assets/icons/facebook.png')?>" class="social-icon" /></a>
	<a href="https://www.instagram.com/bribooks_global/"><img src="<?=site_url('assets/icons/instagram.png')?>" class="social-icon" /></a>
	<a href="https://twitter.com/BriBooks_global"><img src="<?=site_url('assets/icons/twitter.png')?>" class="social-icon" /></a>
</div>
<?php } ?>

<span class="copyright">&copy; <?= date('Y'); ?> BriBooks</span>
<a href="<?= !empty($unsubscribe_url) ? $unsubscribe_url : 'mailto:support@bribooks.com' ?>" class="copyright">Unsubscribe</a>
</footer>
</body>
</html>
