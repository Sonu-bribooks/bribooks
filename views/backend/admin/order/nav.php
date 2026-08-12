<ul class="nav nav-tabs" role="tablist">
	<?php foreach ($nav_tabs['pre'] as $key => $tab) { ?>
		<li class="nav-item">
			<a class="nav-link btn-<?=$tab['color']?> <?= $this->uri->segment(2) == $tab['id'] ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=$tab['url']?>" role="tab">
				<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
				<span class="d-none d-sm-block"><?=$tab['name']?></span>
			</a>
		</li>
	<?php } ?>
	<?php foreach (ORDER_STATUS as $key => $value) { ?>
		<?php if (empty($value)) continue; ?>
		<li class="nav-item">
			<a class="nav-link <?= $this->uri->segment(3) == $key ? 'btn-success active' : '' ?>" data-bs-toggle="tab" href="<?=$nav_base_url . $key?>" role="tab">
				<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
				<span class="d-none d-sm-block <?= $this->uri->segment(3) == $key ? '' : 'text-' . ORDER_STATUS_COLOR[$value] ?>"><?=_l($key)?></span>
			</a>
		</li>
	<?php } ?>

	<?php foreach ($nav_tabs['post'] ?? [] as $key => $tab) { ?>
		<li class="nav-item">
			<a class="nav-link btn-<?=$tab['color']?> <?= $this->uri->segment(2) == $tab['id'] ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=$tab['url']?>" role="tab">
				<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
				<span class="d-none d-sm-block"><?=$tab['name']?></span>
			</a>
		</li>
	<?php } ?>
</ul>
