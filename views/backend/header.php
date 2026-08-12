<?php $user_id = $this->session->userdata('user_id'); ?>

<style>
	.profile-dropdown {
		width: auto;
	}
	#license-tracker {
		position: absolute;
		left: 50%;
		top: 5px;
		transform: translateX(-50%);
		width: 200px;
	}
</style>

<!-- Topbar Start -->
<div class="navbar-custom topnav-navbar topnav-navbar-dark">
	<div class="container-fluid">
		<!-- LOGO -->
		<a href="<?php echo base_url($user_role_type); ?>" class="topnav-logo" style = "min-width: unset;">
			<span class="topnav-logo-lg">
				<img src="<?php echo base_url('uploads/system/logo-light.png');?>" alt="" height="40">
			</span>
			<span class="topnav-logo-sm">
				<img src="<?php echo base_url('uploads/system/logo-light-sm.png');?>" alt="" height="40">
			</span>
		</a>
		<ul class="list-unstyled topbar-right-menu float-right mb-0">
			<li class="dropdown notification-list">
				<a class="nav-link dropdown-toggle nav-user arrow-none mr-0" data-toggle="dropdown" id="topbar-userdrop" href="#" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="account-user-avatar">
						<img src="<?php echo $this->user_model->get_user_image_url($user_id); ?>" alt="user-image" class="rounded-circle">
					</span>
					<span>
						<?php
						$logged_in_user_details = $this->user_model->get($user_id);
						?>
						<span class="account-user-name"><?php echo $logged_in_user_details['first_name'].' '.$logged_in_user_details['last_name'];?></span>
						<span class="account-position">
							<?php echo strtolower($this->session->userdata('role')); ?>
						</span>
					</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown" aria-labelledby="topbar-userdrop">
					<!-- item-->
					<div class=" dropdown-header noti-title">
						<h6 class="text-overflow m-0"><?php echo _l('welcome'); ?> !</h6>
					</div>

					<?php if (strtolower($this->session->userdata('role')) == 'admin'): ?>
					<!-- Account -->
					<a href="<?php echo base_url($user_role_type . '/manage_profile'); ?>" class="dropdown-item notify-item">
						<i class="mdi mdi-account-circle mr-1"></i>
						<span><?php echo _l('my_account'); ?></span>
					</a>
					<!-- settings-->
					<a href="<?php echo base_url('admin/system_settings'); ?>" class="dropdown-item notify-item">
						<i class="mdi mdi-settings mr-1"></i>
						<span><?php echo _l('settings'); ?></span>
					</a>
					<?php endif; ?>

					<?php if ($this->session->userdata('additional_role_id')) { ?>
					<a href="<?php echo base_url('login/switchAccount/' . (int)$this->session->userdata('additional_role_id')); ?>" class="dropdown-item notify-item">
						<i class="mdi mdi-account-convert mr-1"></i>
						<span><?php echo _l('switch_to'); ?> <?php echo _l(get_user_role_by_id($this->session->userdata('additional_role_id'))); ?></span>
					</a>
					<?php } ?>

					<!-- Logout-->
					<a href="<?php echo base_url('login/logout'); ?>" id="g-signout" class="dropdown-item notify-item">
						<i class="mdi mdi-logout mr-1"></i>
						<span><?php echo _l('logout'); ?></span>
					</a>

				</div>
			</li>
		</ul>
		<a href="#" class="list-unstyled topbar-right-menu float-right mt-3" onClick="changeThemeMode(<?=$is_dark_mode?>)">
			<?php if ($is_dark_mode) { ?>
				<span class="dark-layout">
					<svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-360q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Zm0 80q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Zm326-268Z"></path></svg>
				</span>
			<?php } else { ?>
				<span class="light-layout">
					<svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" height="24" viewBox="0 -960 960 960" width="24"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Zm0-80q88 0 158-48.5T740-375q-20 5-40 8t-40 3q-123 0-209.5-86.5T364-660q0-20 3-40t8-40q-78 32-126.5 102T200-480q0 116 82 198t198 82Zm-10-270Z"></path></svg>
				</span>
			<?php } ?>
		</a>
		<a class="button-menu-mobile disable-btn">
			<div class="lines">
				<span></span>
				<span></span>
				<span></span>
			</div>
		</a>
		<?php if (0) { ?>
		<div class="app-search">
			<!--	<h4 style="color: #fff; float: left;"> <?php echo $this->db->get_where('settings' , array('key'=>'system_name'))->row()->value; ?></h4>-->
			<a href="<?php echo base_url('home'); ?>" target="" class="btn btn-outline-light ml-3"><?php echo _l('visit_website'); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
<!-- end Topbar -->

<div id="license-tracker"></div>

<script>
function changeThemeMode(mode) {
	const d = new Date();
	d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
	let expires = 'expires=' + d.toUTCString();

	document.cookie = 'is_dark_mode=' + (mode ^ 1) + ';' + expires + ';path=/';
	window.location.reload();
}
</script>
<script>
$(function() {
	$('#g-signout').on('click', function(e) {
		e.preventDefault();
		$el = $(this);

		setTimeout(() => {
			window.location = $el.attr('href');
		}, 3000);

		signOut(function() {
			window.location = $el.attr('href');
		})
	});
});
</script>
