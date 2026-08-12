<ul class="nav nav-tabs" role="tablist">
	<!-- <li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='books')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/books/books') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block"><?=_l('all')?></span>
		</a>
	</li> -->

	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='approved_books')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/approved_books') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('approved')?></span>
		</a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='in_review_books')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/in_review_books') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('in_review')?></span>
		</a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='archived_books') ? 'active' : '';?>" data-bs-toggle="tab" href="<?= site_url('admin/archived_books') ?>" role="tab" target="_blank">
			<span class="d-block d-sm-none"><i class="far fa-file-archive-o"></i></span>
			<span class="d-none d-sm-block"><?=_l('archived_books')?></span>
		</a>
	</li>

	<!-- <li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='assign_books')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/assign_books') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?= _l('assign_books') ?></span>
		</a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='ordered_books_in_review')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/ordered_books_in_review') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?= _l('orders_in_review') ?></span>
		</a>
	</li> -->
</ul>
