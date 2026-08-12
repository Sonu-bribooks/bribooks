<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_all_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_all_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('All Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_new_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_new_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('new_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_in_print_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_in_print_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('in_print_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_qaqc_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_qaqc_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('QA/QC orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_afs')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_afs') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('avialable_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_ready_to_ship')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_ready_to_ship') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_shipped_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_shipped_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_delivered_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_delivered_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_return_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_return_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('return')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_escalate_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_escalate_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('escalate_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='dropshipper_cancelled_orders')?'active':'';?>" data-bs-toggle="tab" href="<?= base_url('admin/dropshipper_cancelled_orders') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('cancelled_orders')?></span>
		</a>
	</li>
</ul>
