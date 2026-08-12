<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="m-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters')?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body">
				<form class="form-inline" action="#" method="post" id="form-filter">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?= _l('order_date'); ?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , strtotime('-1 day', $timestamp_end));?>"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label for="book_id"><?php echo _l('select_site'); ?></label>
								<select name="site_id" class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>"></select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?= _l('select_country'); ?></label>
								<select
									class="input-filter form-control select2"
									data-toggle="select2"
									name="location"
								>
									<option value="" selected><?=_l('select_country')?></option>
									<?php foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
									<?php if (strtolower($details['location']) === strtolower($country['name'])) { ?>
									<option value="<?php echo $country['name']; ?>" selected><?php echo $country['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $country['name']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?= _l('isbn_country'); ?></label>
								<select
									class="input-filter form-control select2"
									data-toggle="select2"
									name="isbn_country_code"
								>
									<option value="" selected><?=_l('select_isbn_country')?></option>
									<?php foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
									<?php if (($details['isbn_country_code'] ?? 'IN') === $country['code']) { ?>
									<option value="<?php echo $country['code']; ?>" selected><?php echo $country['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?= _l('has_isbn'); ?></label>
								<select
									class="input-filter form-control select2"
									data-toggle="select2"
									name="has_isbn"
								>
									<option value=""><?=_l('select_isbn')?></option>
									<option value="empty"><?=_l('empty_isbn')?></option>
									<option value="filled"><?=_l('filled isbn')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('has_amazon_url')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_amazon_url"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?= _l('download_title_verso'); ?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="download_title_verso"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('quantity_less_than')?></label>
								<input
									id="quantity_le"
									type="number"
									name="quantity_le"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('quantity_greater_than')?></label>
								<input
									id="quantity_ge"
									type="number"
									name="quantity_ge"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
					</div>
					<div class="col-sm-12 text-right">
						<div class="btn-group">
							<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('search');?></button>
							<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
						</div>
					</div>
				</form>
			</div>

		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<?php include('nav.php'); ?>
				<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
						<div class="table-responsive mt-4">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0 open-link">
								<thead>
									<tr>
										<th><input type="checkbox" class="select-all"></th>
										<th><?php echo _l('id'); ?></th>
										<th><?php echo _l('theme'); ?></th>
										<th><?php echo _l('user'); ?></th>
										<th><?php echo _l('country'); ?></th>
										<th><?php echo _l('name'); ?></th>
										<th><?php echo _l('isbn'); ?></th>
										<th><?php echo _l('author_name'); ?></th>
										<th><?php echo _l('reviewer'); ?></th>
										<th><?php echo _l('date_added'); ?></th>
										<th><?php echo _l('date_published'); ?></th>
										<th><?php echo _l('date_approved'); ?></th>
										<th><?php echo _l('status'); ?></th>
										<th><?php echo _l('page_count'); ?></th>
										<th><?php echo _l('actions'); ?></th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
var table = null;
</script>
<script>
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'theme',
			'user',
			'country',
			'name',
			'isbn',
			'author_name',
			'reviewer',
			'date_added',
			'date_published',
			'date_approved',
			'status',
			'page_count'
		],
		'actions'	=> [
			[
				'key' 		=> 'edit',
				'type' 		=> 'edit',
				'url' 		=> 'admin/book_form/edit/',
			],
			[
				'key' 		=> 'front & back',
				'type' 		=> 'edit',
				'url' 		=> 'admin/front_back/',
			],
			[
				'key' 		=> 'unpublish',
				'type' 		=> 'unpublish',
				'url' 		=> 'admin/books/unpublish/',
			],
			[
				'key' 		=> 'review',
				'type' 		=> 'review',
				'url' 		=> 'admin/reviewbook/',
			],
			[
				'key' 		=> 'communicate',
				'type' 		=> 'communicate',
				'url' 		=> 'admin/communicate/',
			],
			[
				'key' 		=> 'QR',
				'type' 		=> 'QR',
				'url' 		=> 'admin/qr_generate/',
			]
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	table = $('#ajax-datatable').DataTable({
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		"ajax": "<?php echo $action_ajax; ?>",
		"lengthMenu": [10, 20, 50, 100],
		"processing": true,
		"serverSide": true,
		"order": [
			[0, "desc"]
		],
		"columns": columns
	})
});
</script>

<script>
$(function() {
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
	})
});
</script>

<script>
function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>
