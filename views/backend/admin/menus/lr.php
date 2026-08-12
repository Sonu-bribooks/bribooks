<li class="side-nav-item <?php if (strpos($page_name, 'lr/') !== false) echo 'active';?>">
	<a href="javascript: void(0);" class="side-nav-link">
		<i class="dripicons-gear"></i>
		<span> <?php echo _l('icode_assessment'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if (strpos($page_name, 'lr/category') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/lr_category'); ?>"><?php echo _l('category'); ?></a>
		</li>
		<li class="<?php if (strpos($page_name, 'lr/questionbank') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/lr_questionbank'); ?>"><?php echo _l('questionbank'); ?></a>
		</li>
		<li class="<?php if (strpos($page_name, 'lr/assessment') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/lr_assessment'); ?>"><?php echo _l('assessment_report'); ?></a>
		</li>
		<li class="<?php if (strpos($page_name, 'lr/assessment_code') !== false) echo 'active';?>">
			<a href="<?php echo site_url('admin/lr_assessment_code'); ?>"><?php echo _l('assessment_code'); ?></a>
		</li>
	</ul>
</li>
