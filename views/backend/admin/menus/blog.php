<li class="side-nav-item <?php if (strpos($page_name, 'blog/') !== false) echo 'active';?>">
	<a href="javascript: void(0);" class="side-nav-link">
		<i class="dripicons-message"></i>
		<span> <?php echo _l('blog'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if (strpos($page_name, 'blog') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/blog'); ?>"><?php echo _l('blog'); ?></a>
		</li>
	</ul>
</li>
