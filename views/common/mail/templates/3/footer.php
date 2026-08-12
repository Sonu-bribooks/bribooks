</div>
</div>
<footer class="text-center footer" style="text-align: center;">
<div class="footer-link-cover">
	<!-- <img
		src="<?=is_file(FCPATH . 'assets/images/mail/' . $footer_img) ? base_url('assets/images/mail/' . $footer_img) : base_url('assets/images/mail/footer.jpg')?>"
		class="footer-img"
	/> -->
</div>

<!-- <div class="text-center">
	<a href="https://www.facebook.com/bribooksglobal"><img src="<?=site_url('assets/icons/facebook.png')?>" class="social-icon" /></a>
	<a href="https://www.instagram.com/bribooks_global/"><img src="<?=site_url('assets/icons/instagram.png')?>" class="social-icon" /></a>
	<a href="https://twitter.com/BriBooks_global"><img src="<?=site_url('assets/icons/twitter.png')?>" class="social-icon" /></a>
</div> -->

<span class="copyright">&copy; <?=date('Y')?> BriBooks</span>
<a href="<?= !empty($unsubscribe_url) ? $unsubscribe_url : 'mailto:support@bribooks.com' ?>" class="copyright">Unsubscribe</a>
</footer>
</body>
</html>
