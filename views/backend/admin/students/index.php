<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<!-- <a href="<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a> -->
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<div class="" id="accordion">
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
			<div class="card-body">
				<form class="form" action="#" method="post" id="form-filter">
					<div class="row">
						<!-- <div class="col-sm-4">
							<div class="form-group mb-2">
								<label><?=_l('order_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y");?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y");?>"
								>
							</div>
						</div> -->
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?php echo _l('select_site'); ?></label>
								<!-- <select name="site_id" class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>"></select> -->

								<select name="site_id" class="input-filter form-control select2" data-toggle="select2">
		                            <option value="" selected><?=_l('all')?></option>
		                            <?php foreach ($this->site_model->get_all([
		                                'status'        => 1,
		                                'site_codes' => PARENT_SITE_CODES
		                            ])['rows'] ?? [] as $site) { ?>
		                            <option value="<?php echo $site['id']; ?>"><?php echo $site['name'] . ' ( ' . $site['site_code'] . ' )'; ?></option>
		                            <?php } ?>
		                        </select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?php echo _l('select_country'); ?></label>
								<select class="input-filter form-control select2" data-toggle="select2" id="filter_country" name="country">
									<option value="" selected><?=_l('all')?></option>
									<?php foreach ($country as $countries) { ?>
									<option value="<?php echo $countries['name']; ?>"><?php echo $countries['name']; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-12 text-right">
						<div class="btn-group">
							<button type="submit" class="btn btn-info" id="submit-button"> <?php echo _l('search');?></button>
							<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
						</div>
					</div>
					<div class="col-sm-12 text-right mb-2">
						<a href="<?= base_url('/home/nyaf_not_started_writing') ?>" class="btn btn-primary mt-2" role="button"><?= _li('nyaf_not_started_writing') ?> </a>
						<a href="<?= base_url('/home/nyaf_writing_but_not_published') ?>" class="btn btn-primary mt-2" role="button"><?= _li('nyaf_writing_but_not_published') ?> </a>
						<a href="<?= base_url('/home/nyaf_published_but_not_sold') ?>" class="btn btn-primary mt-2" role="button"><?= _li('nyaf_published_but_not_sold') ?> </a>
					</div>
				</form>
			</div>
		</div>
		<!-- <button type="button" class="btn btn-primary mt-2"></button></a> -->

	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<!-- <div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" data-site="<?=$site_id?>" id="filter_site_id" onchange="window.location='<?= $action_filter ?>?site_id=' + this.value">
						</select>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="country"><?php echo _l('select_country'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" id="filter_country" onchange="window.location='?country=' + this.value">
							<?php foreach ($country as $countries) {
								if ($country_name == $countries['name']) {
							?>
									<option value="<?php echo ""; ?>" selected><?php echo $countries['name']; ?></option>
								<?php } else { ?>
									<option value="<?php echo $countries['name']; ?>"><?php echo $countries['name']; ?></option>
							<?php }
							} ?>
						</select>
					</div>
				</div> -->

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('id'); ?></th>
								<th><?php echo _l('image'); ?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('books'); ?></th>
								<th><?php echo _l('country'); ?></th>
								<!-- <th><?php echo _l('relation'); ?></th> -->
								<th><?php echo _l('source'); ?></th>
								<!-- <th><?php echo _l('subscription_plan'); ?></th> -->
								<!-- <th><?php echo _l('hard_copy'); ?></th> -->
								<th><?php echo _l('bank_account'); ?></th>
								<th><?php echo _l('status'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
var table = null;

$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'image',
			'name',
			'books',
			'location',
			// 'relation',
			'source',
			// 'subscription_plan',
			// 'hard_copy',
			'bank_account',
			'status',
			'date_added',
		],
		'actions'	=> [
			[
				'key' 		=> 'edit',
				'url' 		=> 'admin/student_form/edit/',
			],
			[
				'key' 		=> 'update_student_profile',
				'url' 		=> 'admin/student_form/update_student_profile/',
			],
			[
				'key' 		=> 'update_profile_cred',
				'url' 		=> 'admin/student_form/update_student_cred/',
			],
			[
				'key' 		=> 'status',
				'type' 		=> 'status',
				'url' 		=> 'admin/students/status/',
			],
			[
				'key' 		=> 'delete',
				'type' 		=> 'confirm',
				'url' 		=> 'admin/students/delete/',
			],
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	table = $('#ajax-datatable').DataTable({
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [
			[0, "desc"]
		],
		"columns": columns,

	});

	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
	});
});
</script>