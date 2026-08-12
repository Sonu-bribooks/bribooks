<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_orders')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_orders')?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('all_b_&_w')?></span>
		</a>
	</li>
	<?php if ($this->session->userdata('role_id') != 10) { ?>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_new_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_new_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('new')?></span>
		</a>
	</li>
	<?php } ?>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_in_print_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_in_print_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('in_print')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_printed_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_printed_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('printed')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_afs')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_afs')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('available_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_ready_to_ship')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_ready_to_ship')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_shipped_orders')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_shipped_orders')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_delivered_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_delivered_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_return_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_return_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('return')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_reprint_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_reprint_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-warning"><?=_l('reprint')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_cancel_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_cancel_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('cancel')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_refunded_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_refunded_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-primary"><?=_l('refunded')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_escalated_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_escalated_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('escalated')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_cloned_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_cloned_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-primary"><?=_l('cloned')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_auto_escalated_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/bw_auto_escalated_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-primary"><?=_l('auto_escalated')?></span>
		</a>
	</li>
</ul>
