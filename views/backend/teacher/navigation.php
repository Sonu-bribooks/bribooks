<?php
	$status_wise_courses = $this->crud_model->get_status_wise_courses();
 ?>
<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu left-side-menu-detached">
	<div class="leftbar-user">
		<a href="javascript: void(0);">
			<img src="<?php echo $this->user_model->get_user_image_url($this->session->userdata('user_id')); ?>" alt="user-image" height="42" class="rounded-circle shadow-sm">
			<?php
			$user_details = $this->user_model->get($this->session->userdata('user_id'));
			?>
			<span class="leftbar-user-name"><?php echo $user_details['first_name'].' '.$user_details['last_name']; ?></span>
		</a>
	</div>

	<!--- Sidemenu -->
	<ul class="metismenu side-nav side-nav-light">

		<li class="side-nav-title side-nav-item"><?php _el('navigation'); ?></li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('teacher'); ?>" class="side-nav-link <?php if ($page_name == 'dashboard')echo 'active';?>">
				<i class="dripicons-device-desktop"></i>
				<span><?php _el('dashboard'); ?></span>
			</a>
		</li>

		<?php if (0) { ?>
		<li class="side-nav-item">
			<a href="<?php echo site_url('teacher/schedule'); ?>" class="side-nav-link <?php if ($page_name == 'schedule')echo 'active';?>">
				<i class="dripicons-calendar"></i>
				<span><?php _el('class'); ?></span>
			</a>
		</li>
		<?php } ?>

		<li class="side-nav-item">
			<a href="<?php echo site_url('teacher/attendance'); ?>" class="side-nav-link <?php if ($page_name == 'attendance')echo 'active';?>">
				<i class="dripicons-user-group"></i>
				<span><?php _el('class'); ?></span>
			</a>
		</li>

		<?php if (0) { ?>
		<li class="side-nav-item">
			<a href="<?php echo site_url('teacher/instructor_revenue'); ?>" class="side-nav-link <?php if ($page_name == 'report' || $page_name == 'invoice')echo 'active';?>">
				<i class="dripicons-media-shuffle"></i>
				<span><?php _el('revenue'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link <?php if ($page_name == 'system_settings' || $page_name == 'frontend_settings' || $page_name == 'payment_settings' || $page_name == 'instructor_settings' || $page_name == 'smtp_settings' || $page_name == 'manage_language' ): ?> active <?php endif; ?>">
				<i class="dripicons-toggles"></i>
				<span> <?php _el('settings'); ?> </span>
				<span class="menu-arrow"></span>
			</a>


			<ul class="side-nav-second-level" aria-expanded="false">
				<li class = "<?php if($page_name == 'payment_settings') echo 'active'; ?>">
					<a href="<?php echo site_url('teacher/payment_settings'); ?>"><?php _el('payment_settings'); ?></a>
				</li>

				<li class = "<?php if($page_name == 'payment_settings') echo 'active'; ?>">
					<a href="<?php echo site_url('teacher/payment_settings'); ?>"><?php _el('payment_settings'); ?></a>
				</li>
			</ul>
		</li>
		<?php } ?>

	</ul>
</div>
