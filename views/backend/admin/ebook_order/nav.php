<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= (($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'ebook_orders/0')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/ebook_orders/0')?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('all_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= (($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'ebook_orders/47')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/ebook_orders/47')?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('domestic_orders')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= (($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'ebook_orders/2')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/ebook_orders/2')?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('global_orders')?></span>
		</a>
	</li>
</ul>
