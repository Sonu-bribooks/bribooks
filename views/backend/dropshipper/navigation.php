<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu left-side-menu-detached">
	<div class="leftbar-user">
		<a href="javascript: void(0);">
			<img src="<?= $this->user_model->get_user_image_url($this->session->userdata('user_id')) ?>" alt="user-image" height="42" class="rounded-circle shadow-sm">
			<?php
				$user_info = $this->user_model->get($this->session->userdata('user_id')) ?? [];
			?>
			<span class="leftbar-user-name"><?= $user_info['first_name'] . ' ' . $user_info['last_name'] ?></span>
		</a>
	</div>

	<ul class="metismenu side-nav side-nav-light">
		<li class="side-nav-title side-nav-item"><?= _l('navigation') ?></li>

		<li class="side-nav-item">
			<a href="<?= base_url('dropShipper') ?>" class="side-nav-link <?php if ($page_name == 'dashboard') echo 'active' ?>">
				<i class="dripicons-device-desktop"></i>
				<span><?= _l('dashboard') ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?= base_url('dropShipper/all_orders') ?>" class="side-nav-link <?php if ($page_name == 'orders') echo 'active' ?>">
				<i class="dripicons-calendar"></i>
				<span><?= _l('Orders') ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?= base_url('dropShipper/bw_all_order') ?>" class="side-nav-link <?php if ($page_name == 'bw_orders') echo 'active' ?>">
				<i class="dripicons-calendar"></i>
				<span><?= _l('B & W Orders') ?></span>
			</a>
		</li>
	</ul>
</div>
