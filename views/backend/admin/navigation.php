<?php
$user_id 	= $this->session->userdata('user_id');
$admin_info = $this->user_model->get($user_id);
$menus 		= get_navigation_menus();
?>

<div class="left-side-menu left-side-menu-detached">
	<div class="leftbar-user">
		<a href="javascript: void(0);">
			<img
				src="<?= $this->user_model->get_user_image_url($user_id); ?>"
				alt="..."
				height="42"
				class="rounded-circle shadow-sm"
			>
			<span class="leftbar-user-name">
				<?= $admin_info['first_name'] . ' ' . $admin_info['last_name']; ?>
			</span>
		</a>
	</div>

	<ul class="metismenu side-nav side-nav-light">
		<li class="side-nav-title side-nav-item"><?= _l('navigation'); ?></li>

		<?php foreach ($menus as $key => $item) { ?>
			<?php if (!check_route_access($item)) continue; ?>
			<li class="side-nav-item <?php if ($page_name == $item['key']) echo 'active'; ?>">
				<?php if (empty($item['childs'])) { ?>
					<a
						href="<?= strpos($item['url'], 'http') !== false ? $item['url'] : base_url($item['url']); ?>"
						class="side-nav-link"
					>
						<i class="<?= !empty($item['icon']) ? $item['icon'] : 'fas fa-folder' ?>"></i>
						<span><?= $item['name'] ?></span>
					</a>
				<?php  } else { ?>
					<a href="javascript: void(0);" class="side-nav-link">
						<i class="<?= !empty($item['icon']) ? $item['icon'] : 'fas fa-folder' ?>"></i>
						<span><?= $item['name'] ?></span>
						<span class="menu-arrow"></span>
					</a>
					<ul class="side-nav-second-level" aria-expanded="false">
						<?php foreach ($item['childs'] as $child) { ?>
						<?php if (!check_route_access($child)) continue; ?>
						<li class="">
							<a
								href="<?= strpos($child['url'], 'http') !== false ? $child['url'] : base_url($child['url']); ?>"
							><?= $child['name'] ?></a>
						</li>
						<?php } ?>
					</ul>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
</div>

<script>
$(window).on('load', function() {
	$('.side-nav-item ul').each(function() {
		if ($(this).children().length === 0) {
			$(this).parent().empty();
		}
	});
});
</script>
