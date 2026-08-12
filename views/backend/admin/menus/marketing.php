<?php if(in_array($logged_in_user_role_id, array('1','5','16'))) { ?>
<li class="side-nav-item <?php if (strpos($page_name, 'marketing/') !== false) echo 'active';?>">
	<a href="javascript: void(0);" class="side-nav-link">
		<i class="dripicons-feed"></i>
		<span> <?php echo _l('marketing'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if (strpos($page_name, 'marketing/marketing') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/marketing'); ?>"><?php echo _l('marketing'); ?></a>
		</li>
		<li class="<?php if (strpos($page_name, 'share_template') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/share_template'); ?>"><?php echo _l('share_template'); ?></a>
		</li>
		<!-- <li class="<?php if (strpos($page_name, 'marketing/announcement') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/announcement'); ?>"><?php echo _l('announcement'); ?></a>
		</li> -->
	</ul>
</li>
<?php } ?>
