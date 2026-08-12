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
			<span class="leftbar-user-name"><?php echo $user_details['first_name'] . ' ' . $user_details['last_name']; ?></span>
		</a>
	</div>

	<ul class="metismenu side-nav side-nav-light">
		<li class="side-nav-title side-nav-item"><?php echo _l('navigation'); ?></li>

		<li class="side-nav-item">
			<a href="<?php echo site_url('printingPress'); ?>" class="side-nav-link <?php if ($page_name == 'dashboard') echo 'active'; ?>">
				<i class="dripicons-device-desktop"></i>
				<span><?php echo _l('dashboard'); ?></span>
			</a>
		</li>

		<?php if (in_array($logged_in_user_role_id, [12, 15])) { ?>
		<li class="side-nav-item <?php if ($page_name == 'dashboard') echo 'active'; ?>">
			<a target="_blank" href="https://cms.youbooks.ai" class="side-nav-link">
				<i class="dripicons-link"></i>
				<span><?php echo _l('youbooks_panel'); ?></span>
			</a>
		</li>
		<?php } ?>

		<li class="side-nav-item">
			<a href="<?php echo site_url(($this->session->userdata('role_id') == '13') ? 'printingPress/orders' : 'printingPress/new_order'); ?>" class="side-nav-link <?php if ($page_name == 'orders') echo 'active'; ?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('Orders'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo site_url(($this->session->userdata('role_id') == '13') ? 'printingPress/bw_orders' : 'printingPress/bw_new_order'); ?>" class="side-nav-link <?php if ($page_name == 'bw_orders') echo 'active'; ?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('B & W Orders'); ?></span>
			</a>
		</li>

		<li class="side-nav-item">
			<a href="<?php echo site_url(($this->session->userdata('role_id') == '13') ? 'printingPress/reprintOrders' : 'printingPress/reprint_new_order'); ?>" class="side-nav-link <?php if ($page_name == 'reprintOrders') echo 'active'; ?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('Re-Print'); ?></span> <span class="badge badge-info"><?=$this->reprint_order_model->countCopies([
					'assign_printer_id' => (int)$this->session->userdata('user_id'),
					'status'			=> 1,
				])?></span>
			</a>
		</li>

		<!-- <li class="side-nav-item">
			<a href="<?php echo site_url(($this->session->userdata('role_id') == '13') ? 'printingPress/bw_reprintOrders' : 'printingPress/bw_reprint_new_order'); ?>" class="side-nav-link <?php if ($page_name == 'bw_reprintOrders') echo 'active'; ?>">
				<i class="dripicons-calendar"></i>
				<span><?php echo _l('B & W Re-Print'); ?></span> <span class="badge badge-info"><?=$this->reprint_order_model->countCopies([
					'assign_printer_id' => (int)$this->session->userdata('user_id'),
					'status'			=> 1,
				])?></span>
			</a>
		</li> -->

		<?php if (in_array($logged_in_user_role_id, [12, 15])) { ?>
			<li class="side-nav-item">
				<a href="<?php echo base_url('printingPress/printer_assignment'); ?>" class="side-nav-link <?php if ($page_name == 'printer_assignment') echo 'active'; ?>">
					<i class="dripicons-network-3"></i>
					<span><?php echo _l('Assignment Lots'); ?></span>
				</a>
			</li>
		<?php } ?>
	</ul>
</div>
