<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='reprint_new_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/reprint_new_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_li('New Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='reprint_in_print_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/reprint_in_print_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_li('In-Print Orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='reprint_verify_print')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/reprint_verify_print') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('verify_print')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='reprint_printed_order')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('printingPress/reprint_printed_order') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_li('Printed Orders')?></span>
		</a>
	</li>
</ul>
