<!-- <div class="" id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="mb-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					Filters
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>

		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body p-5">
				<div>
					<span>Enter Start Date</span>

					<input class="form-control alignToTitle start-date" name="start-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<div>
					<span>Enter End Date</span>
					<input class="form-control alignToTitle end-date" name="end-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<button class="btn btn-primary alignToTitle search">Search</button>
			</div>
		</div>
	</div>
</div> -->
<!-- <button class="btn btn-primary alignToTitle export-csv">Export </button> -->
<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='schoolLead')?'active':'';?>" data-bs-toggle="tab" href="/admin/school_lead" role="tab">
			<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
			<span class="d-none d-sm-block">All</span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='fresh_lead')?'active':'';?>" data-bs-toggle="tab" href="/admin/fresh_lead" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-user"></i></span>
			<span class="d-none d-sm-block"><?= _l('fresh_lead')?></span>
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link <?= ($this->uri->segment(2)=='assign_leads')?'active':'';?>" data-bs-toggle="tab" href="/admin/assign_leads" role="tab">
			<span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
			<span class="d-none d-sm-block"><?= _l('assign_leads') ?></span>
		</a>
	</li>
</ul>
