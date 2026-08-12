<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_all_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('dropShipper/bw_all_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('All Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_new_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('dropShipper/bw_new_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('New Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_in_print_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('dropShipper/bw_in_print_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_li('In-Print Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_verify_print')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('dropShipper/bw_verify_print') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block">QA/QC</span>
		</a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_afs')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_afs')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('available_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_ready_to_ship')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_ready_to_ship')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_shipped_orders')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_shipped_orders')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_delivered_order')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_delivered_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='return_order')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_return_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('return')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_escalated_order')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('dropShipper/bw_escalated_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('escalated')?></span>
		</a>
	</li>
</ul>
