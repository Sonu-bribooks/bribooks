<?php if(in_array($logged_in_user_role_id, array('1'))) { ?>
<li class="side-nav-item <?php if (strpos($page_name, 'email_template') !== false) echo 'active';?>">
	<a href="javascript: void(0);" class="side-nav-link">
		<i class="dripicons-basketball"></i>
		<span> <?php echo _l('email_templates'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if(in_array($page_name, ['templates','email_template_form'])) echo 'active'; ?>">
			<a href="<?php echo site_url('admin/templates'); ?>"><?php echo _l('templates'); ?></a>
		</li>
		<!-- <li class="<?php if($page_name == 'email_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/email_template'); ?>"><?php echo _l('email_template'); ?></a>
		</li>
		<li class="<?php if($page_name == 'school_email_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/school_email_template'); ?>"><?php echo _l('school_template'); ?></a>
		</li>
		<li class="<?php if($page_name == 'book_email_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/book_email_template'); ?>"><?php echo _l('book_template'); ?></a>
		</li>
		<li class="<?php if($page_name == 'schedule_email_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/schedule_email_template'); ?>"><?php echo _l('schedule_template'); ?></a>
		</li>
		<li class="<?php if($page_name == 'nyaf_email_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/nyaf_email_template'); ?>"><?php echo _l('nyaf_email_template'); ?></a>
		</li>
		<li class="<?php if($page_name == 'add_template') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/add_template'); ?>"><?php echo _l('add_template'); ?></a>
		</li> -->
	</ul>
</li>
<?php } ?>