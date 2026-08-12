<div class="wid100">
	<div class="sel-grade"><?php echo $grade; ?></div>
	<br />
	<div class="logo">
		<img
			src="<?php echo base_url('uploads/system/logo-light.png');?>"
			title="<?php echo get_settings('system_name'); ?>"
		/>
	</div>
	<div class="text-center wid100">
		<h2 class="highlight_heading"><?php echo $page_title; ?></h2>
	</div>
	<ul class="cat_sel">
		<?php $first_lock_key = -1; ?>
		<?php foreach ($categories as $key => $category) { ?>
		<li class="<?php echo $category['is_locked'] === false && $category_id !== 0 ? 'report' : 'no-report'; ?>">
			<?php $first_lock_key = $category['is_locked'] && $first_lock_key === -1 ? $key : $first_lock_key; ?>
			<?php if ($category['is_locked'] && $key > $first_lock_key) { ?>
			<div class="locked">
				<img
					src="<?php echo $category['image']; ?>"
					alt="<?php echo $category['name']; ?>"
				/>
				<div><?php echo $category['name']; ?></div>
			</div>
			<?php } else { ?>
			<a
				href="<?php echo site_url('assessment') . '?cat=' . $category['id']; ?>"
			>
				<img
					src="<?php echo $category['image']; ?>"
					alt="<?php echo $category['name']; ?>"
				/>
				<div><?php echo $category['name']; ?></div>

				<?php if ($category['is_locked'] === false && $category_id !== 0) { ?>
				<span class="view-report"><?php _el('view_report'); ?></span>
				<?php } ?>
			</a>
			<?php } ?>
		</li>
		<?php } ?>
	</ul>
</div>
