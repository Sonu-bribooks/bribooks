
</div>
</div>
<footer class="text-center footer">
<div class="footer-link-cover">
	<?php
		$footer_image = $this->addtemplate_model->get_email_templates_image_url('footer_image_' . $site_id);
		$footer_image = !empty($footer_image) ? $footer_image : (($parent_id != $site_id) ? $this->addtemplate_model->get_email_templates_image_url('footer_image_' . $parent_id) : '');

		if(0 && $footer_image) {
	?>
	<img
		src="<?= $footer_image; ?>"
		class="footer-img"
	/>
	<?php } ?>
</div>

<div class="footer-info mt-3" 
     style="background-color:#10284B; color:white; padding:1px; text-align:center;">
	<p><strong>Address:</strong> 19 Grasmere Ln Nashua NH 03063</p>
	<p><strong>Contact Us:</strong> +12676530118</p>
	<p>
		<a href="<?= !empty($unsubscribe_url) ? $unsubscribe_url : 'mailto:support@bribooks.com' ?>" 
		   style="color:white; text-decoration:underline;">
			Unsubscribe
		</a>
	</p>
</div>
</footer>
</body>
</html>
