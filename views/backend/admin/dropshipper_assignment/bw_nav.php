<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_all_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_all_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('All Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_new_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_new_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('New Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_in_print_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_in_print_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('In Print Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_qaqc_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_qaqc_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block">QA/QC orders</span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_bw_afs')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_bw_afs') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('avialable_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_bw_ready_to_ship')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_bw_ready_to_ship') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_shipped_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_shipped_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_delivered_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_delivered_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_return_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_return_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('return')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_escalate_bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/dropshipper_escalate_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('escalate_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_cancelled_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_cancelled_bw_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('cancelled_orders')?></span>
		</a>
	</li>
</ul>
