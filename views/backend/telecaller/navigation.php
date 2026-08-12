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
			<a href="<?php echo base_url('telecaller'); ?>" class="side-nav-link <?php if ($page_name == 'dashboard')echo 'active';?>">
				<i class="dripicons-device-desktop"></i>
				<span><?php echo _l('dashboard'); ?></span>
			</a>
		</li>

		<!-- <li class="side-nav-item">
			<a href="<?php echo base_url('telecaller/lead'); ?>" class="side-nav-link <?php if ($page_name == 'lead')echo 'active';?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('leads'); ?></span>
			</a>
		</li> -->

		<li class="side-nav-item">
			<a href="javascript: void(0);" class="side-nav-link <?php if ($page_name == 'telecaller/lead' || $page_name == 'telecaller/school'): ?> active <?php endif; ?>">
				<i class="dripicons-box"></i>
				<span> <?php echo _l('lead'); ?> </span>
				<span class="menu-arrow"></span>
			</a>

			<ul class="side-nav-second-level" aria-expanded="false">
				<li class = "<?php if($page_name == 'telecaller/lead') echo 'active'; ?>" > <a href="<?php echo base_url('telecaller/lead'); ?>"><?php echo _l('lead'); ?></a> </li>
				<li class = "<?php if($page_name == 'telecaller/school') echo 'active'; ?>" > <a href="<?php echo base_url('telecaller/schoolLead'); ?>"><?php echo _l('school_lead'); ?></a> </li>
			</ul>
		</li>

		<?php if (0) { ?>
		<li class="side-nav-item">
			<a href="<?php echo base_url('telecaller/schedule'); ?>" class="side-nav-link <?php if ($page_name == 'schedule')echo 'active';?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('schedule'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo base_url('telecaller/student'); ?>" class="side-nav-link <?php if ($page_name == 'student')echo 'active';?>">
				<i class="dripicons-user"></i>
				<span><?php echo _l('student'); ?></span>
			</a>
		</li>
		<?php } ?>

		<li class="side-nav-item">
			<a href="<?php echo base_url('telecaller/orders'); ?>" class="side-nav-link <?php if ($page_name == 'orders')echo 'active';?>">
				<i class="dripicons-network-3"></i>
				<span><?php echo _l('orders'); ?></span>
			</a>
		</li>
	</ul>
</div>
