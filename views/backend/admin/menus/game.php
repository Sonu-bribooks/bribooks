<li class="side-nav-item <?php if (strpos($page_name, 'game/') !== false) echo 'active';?>">
	<a href="javascript: void(0);" class="side-nav-link">
		<i class="dripicons-basketball"></i>
		<span> <?php echo _l('game'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if (strpos($page_name, 'game') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/game'); ?>"><?php echo _l('game'); ?></a>
		</li>
	</ul>
</li>
