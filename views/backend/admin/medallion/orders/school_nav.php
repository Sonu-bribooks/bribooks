<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 0) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/0/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('all')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 1) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/1/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('new')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 21) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/21/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('available_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 9) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/9/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 3) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/3/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 4) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/4/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 15) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/15/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-warning"><?=_l('returned')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 91) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/91/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('cancelled')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($nav == 93) ? 'active' : '' ?>" data-bs-toggle="tab" href="<?=base_url('admin/school_medallion_orders/93/school')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-info"><?=_l('escalated')?></span>
		</a>
	</li>
</ul>
