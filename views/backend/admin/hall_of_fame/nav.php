<ul class="nav nav-tabs" role="tablist">

	<li class="nav-item">
		<a class="nav-link <?= ($active_tab =='country')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/'. $fame_type .'_fame_country') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?=_l('country_list')?></span>
		</a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($active_tab =='event')?'active':'';?>" data-bs-toggle="tab" href="<?= site_url('admin/'.$fame_type.'_fame_event') ?>" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?=_l('event_list')?></span>
		</a>
	</li>

</ul>
