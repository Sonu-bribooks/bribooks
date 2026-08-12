<ul class="nav nav-pills nav-justified form-wizard-header mb-3 custom-nav">
	<?php foreach ($stages as $key => $stage) { ?>
		<li class="nav-item">
			<a
				href="#<?=$stage['id']?>"
				data-toggle="tab"
				class="nav-link rounded-0 p-2 btn-stage text-nowrap<?= empty($id) && $key > 0 ? ' disabled' : '' ?>"
				data-stage="<?=$stage['id']?>"
			>
				<i class="fa <?=$stage['icon']?> mr-1"></i>
				<span class="d-none d-sm-inline"><?=$stage['name']?></span>
			</a>
		</li>
	<?php } ?>
</ul>

<style>
.custom-nav {
	overflow:hidden;
	overflow-x:auto;
	max-height:65px;
	flex-wrap:nowrap;
}
/* .custom-nav::-webkit-scrollbar {
	width: 0px;
} */
</style>
