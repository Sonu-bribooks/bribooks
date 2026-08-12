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

	<ul class="metismenu side-nav side-nav-light">
		<li class="side-nav-title side-nav-item"><?php echo _l('navigation'); ?></li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('portal'); ?>" class="side-nav-link <?php if ($page_name == 'dashboard')echo 'active';?>">
				<i class="dripicons-device-desktop"></i>
				<span><?php echo _l('dashboard'); ?></span>
			</a>
		</li>

		<?php if (0) { ?>
		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link">
				<i class="dripicons-network-1"></i>
				<span> <?php echo _l('courses'); ?> </span>
				<span class="menu-arrow"></span>
			</a>
			<ul class="side-nav-second-level" aria-expanded="false">
				<li class="<?php if ($page_name == 'courses')echo 'active';?>">
					<a href="<?php echo site_url('portal/courses'); ?>"><?php echo _l('courses'); ?></a>
				</li>
			</ul>
		</li>

		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link">
				<i class="dripicons-network-3"></i>
				<span> <?php echo _l('classes'); ?> </span>
				<span class="menu-arrow"></span>
			</a>
			<ul class="side-nav-second-level" aria-expanded="false">
				<li class="<?php if ($page_name == 'classes')echo 'active';?>">
					<a href="<?php echo site_url('portal/classes'); ?>"><?php echo _l('classes'); ?></a>
				</li>

				<li class="<?php if ($page_name == 'schedules') echo 'active';?>">
					<a href="<?php echo site_url('portal/schedules'); ?>"><?php echo _l('schedules'); ?></a>
				</li>
			</ul>
		</li>
		<?php } ?>

		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link">
				<i class="dripicons-user-group"></i>
				<span> <?php echo _l('users'); ?> </span>
				<span class="menu-arrow"></span>
			</a>
			<ul class="side-nav-second-level" aria-expanded="false">
				<?php if (0) { ?>
				<li class="<?php if ($page_name == 'teacher/index' || $page_name == 'teacher/form')echo 'active';?>">
					<a href="<?php echo site_url('portal/teachers'); ?>"><?php echo _l('teachers'); ?></a>
				</li>
				<?php } ?>

				<li class="<?php if ($page_name == 'telecaller/index' || $page_name == 'telecaller/form')echo 'active';?>">
					<a href="<?php echo site_url('portal/telecallers'); ?>"><?php echo _l('telecallers'); ?></a>
				</li>

				<li class="<?php if ($page_name == 'students')echo 'active';?>">
					<a href="<?php echo site_url('portal/students'); ?>"><?php echo _l('authors'); ?></a>
				</li>
			</ul>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('portal/lead'); ?>" class="side-nav-link <?php if ($page_name == 'lead')echo 'active';?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('leads'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('portal/book'); ?>" class="side-nav-link <?php if ($page_name == 'book')echo 'active';?>">
				<i class="dripicons-network-3"></i>
				<span><?php echo _l('books'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('portal/sites'); ?>" class="side-nav-link <?php if ($page_name == 'site/index' || $page_name == 'site/form')echo 'active';?>">
				<i class="dripicons-network-4"></i>
				<span><?php echo _l('schools'); ?></span>
			</a>
		</li>


		<li class="side-nav-item">
			<a href="<?php echo site_url('portal/import'); ?>" class="side-nav-link <?php if ($page_name == 'import' || $page_name == 'import')echo 'active';?>">
				<i class="dripicons-cloud-upload"></i>
				<span><?php echo _l('import'); ?></span>
			</a>
		</li>

		<?php if (0) { ?>
		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link <?php if ($page_name == 'revenue'): ?> active <?php endif; ?>">
				<i class="dripicons-box"></i>
				<span> <?php echo _l('report'); ?> </span>
				<span class="menu-arrow"></span>
			</a>
			<ul class="side-nav-second-level" aria-expanded="false">
				<?php if (0) { ?>
				<li class = "<?php if($page_name == 'enrolment') echo 'active'; ?>" > <a href="<?php echo site_url('portal/enrolment'); ?>"><?php echo _l('enrolment'); ?></a> </li>
				<?php } ?>
				<li class = "<?php if($page_name == 'revenue') echo 'active'; ?>" > <a href="<?php echo site_url('portal/revenue'); ?>"><?php echo _l('revenue'); ?></a> </li>
			</ul>
		</li>
		<?php } ?>
	</ul>
</div>

<script>
function getLicenseTracker() {
	$('#license-tracker').load('<?php echo base_url('portal/ajaxLicense'); ?>');
}
getLicenseTracker()
setInterval(() => {
	getLicenseTracker()
}, 30000);
</script>
