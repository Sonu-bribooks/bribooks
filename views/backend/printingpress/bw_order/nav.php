<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_new_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/bw_new_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('New Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_in_print_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/bw_in_print_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_li('In-Print Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_verify_print')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/bw_verify_print') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('verify_print')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_printed_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/bw_printed_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_li('Printed Orders')?></span>
		</a>
	</li>

	<?php if ($this->session->userdata('role_id') == 15) { ?>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_afs')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('printingPress/bw_afs')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('available_for_shipping')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_ready_to_ship')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('printingPress/bw_ready_to_ship')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('ready_to_ship')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_shipped_orders')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('printingPress/bw_shipped_orders')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('shipped')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_delivered_order')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('printingPress/bw_delivered_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('delivered')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_return_order')?'active':'';?>" data-bs-toggle="tab" href="<?=site_url('printingPress/bw_return_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-danger"><?=_l('return')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='bw_reprint_order')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('printingPress/bw_reprint_order')?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block text-warning"><?=_l('reprint')?></span>
		</a>
	</li>
	<?php } ?>
</ul>
