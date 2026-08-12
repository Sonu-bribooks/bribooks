<?php
$event_list = $this->event_model->get_all()['rows'] ?? [];
?>
<div class="row ">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<?php if (0) { ?>
					<a href="<?= base_url('admin/export_ebooks') ?>" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-download"></i> <?=_li('Export Ebooks (Domestic)') ?></a>
					<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-hall-of-fame-button"><i class="mdi mdi-plus"></i> <?=_li('Hall of Fame') ?></button>
					<?php } ?>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div id="accordion">
	<div class="card mb-2">
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
				<form class="form" action="#" method="post" id="form-filter">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group mb-2">
								<label><?=_l('book_added_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value=""
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_event')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="event_id"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($event_list ?? [] as $key => $value) { ?>
										<option <?php if(!empty($event_id) && ($event_id == $value['id'])) { echo 'selected'; } ?> value="<?= $value['id']; ?>">
											<?= $value['name'] . ' (' . $value['id'] . ')'; ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label for="site_id"><?php echo _l('select_site'); ?></label>
								<select name="site_id" class="input-filter form-control select2" data-toggle="select2">
									<option value="" selected><?=_l('all')?></option>
									<?php foreach ($this->site_model->get_all([
										'status'		=> 1,
										'site_codes' => PARENT_SITE_CODES
									])['rows'] ?? [] as $site) { ?>
									<option value="<?php echo $site['id']; ?>"><?php echo $site['name'] . ' ( ' . $site['site_code'] . ' )'; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?= _l('select_country'); ?></label>
								<select
									class="input-filter form-control select2"
									data-toggle="select2"
									name="location"
								>
									<option value="" selected><?=_l('select_country')?></option>
									<?php foreach ($this->country_model->get_all() ?? [] as $country) { ?>
									<?php if (strtolower($details['location']) === strtolower($country['name'])) { ?>
									<option value="<?php echo $country['name']; ?>" selected><?php echo $country['name']; ?></option>
									<?php } else { ?>
									<option value="<?php echo $country['name']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label for="isbn_country_code"><?php echo _l('isbn_country'); ?></label>
								<select
									class="input-filter form-control select2"
									data-toggle="select2"
									name="isbn_country_code"
								>
									<option value="" selected><?=_l('all')?></option>
									<?php foreach ($this->country_model->get_all() ?? [] as $country) { ?>
									<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('has_isbn')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_isbn"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('has_kdp_upload')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_kdp_upload"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
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
								<label><?=_l('download_title_verso')?></label>
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
								<label><?=_l('quantity')?></label>
								<input
									id="quantity"
									type="number"
									name="quantity"
									class="form-control input-filter"
									value=""
								>
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
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('has_hall_of_fame')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_hall_of_fame"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('author_email')?></label>
								<input
									id="email"
									type="text"
									name="email"
									class="form-control input-filter"
									value=""
									placeholder="Author Email"
								>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('sku')?></label>
								<input
									id="sku"
									type="text"
									name="sku"
									class="form-control input-filter"
									value=""
									placeholder="sku"
								>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('search')?></label>
								<input
									id="search"
									type="text"
									name="search_value"
									class="form-control input-filter"
									value=""
									placeholder="search"
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
						<div class="table-responsive mt-2">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0">
								<thead>
									<tr>
										<th><input type="checkbox" class="select-all"></th>
										<th><?php echo _l('id'); ?></th>
										<th><?php echo _l('theme'); ?></th>
										<th><?php echo _l('user'); ?></th>
										<th><?php echo _l('country'); ?></th>
										<th><?php echo _l('name'); ?></th>
										<th><?php echo _l('author_name'); ?></th>
										<th><?php echo _l('date_added'); ?></th>
										<th><?php echo _l('date_published'); ?></th>
										<th><?php echo _l('date_approved'); ?></th>
										<th><?php echo _l('date_title_verso'); ?></th>
										<th><?php echo _l('status'); ?></th>
										<th><?php echo _l('sold_book'); ?></th>
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

<!-- change genre Modal -->
<div class="modal fade" id="changeGenreModal" role="dialog" aria-labelledby="changeGenreModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?= _l('change_genre') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?=base_url('admin/book_change_genre') ?>" method="post" id="form-change-genre">
					<input type="hidden" name="book_id" value="" id="book_id" />
					<div class="form-group">
						<label for="genre"><?php _el('genre'); ?></label>
						<select class="form-control select2" name="genre_id" id="genre_id" required data-ajax="<?=base_url('admin/ajax_search_genres') ?>" data-toggle="select2">
							<option value=""><?php echo _l('select_genre'); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label for="category"><?php _el('category'); ?></label>
						<select class="form-control select2" name="category_id" id="category_id" required data-ajax="<?=base_url('admin/ajax_genre_categories/') ?>" data-toggle="select2">
							<option value=""><?php echo _l('select_category'); ?></option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-change-genre" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<script>
var table = null;
</script>
<script>
$(function() {
	$('#genre_id').select2({
		ajax: {
			delay: 250,
			dataType: 'json',
			url: $('#genre_id').data('ajax'),
			data: function (params) {
				return {
					search: params.term,
					genre_id: $('#genre_id').val(),
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 3,
		dropdownParent: $('#changeGenreModal .modal-body'),
	});
	$('#category_id').select2({
		ajax: {
			delay: 250,
			dataType: 'json',
			url: function() {
				return $('#category_id').data('ajax') + $('#genre_id').val();
			},
			processResults: function (json) {
				return {
					results: (json?.data ?? []).map(item => ({ id: item?.id, text: item?.name }))
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 3,
		dropdownParent: $('#changeGenreModal .modal-body'),
	});

	$(document).on('submit', '.modal form', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		if (confirm('<?php echo _li('Are you sure?'); ?>')) {
			submitForm($el.attr('action'), new FormData($el[0]), json => {
				if (json?.success) {
					success_notify(json?.success);
					$('#changeGenreModal').modal('hide');
				} else {
					error_notify(json?.error);
				}
				table.ajax.url('<?= $action_ajax ?>');
			});
		}
	})
});
</script>
<script>
$(function() {
	let columns = JSON.parse(atob('<?=_render_column([
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
	]); ?>'));

	columns = columns.map(item => {
		if (item.render) {
			const callback = eval(item.render);
			item.render = callback;
		}

		return item;
	});

	table = $('#ajax-datatable').DataTable({
		aoColumnDefs: [{
			bSortable: false,
			aTargets: 0
		}],
		ajax: '<?php echo $action_ajax; ?>',
		lengthMenu: [10, 20, 50, 100, 200, 500, 1000],
		processing: true,
		serverSide: true,
		searching: false,
		order: [
			[0, 'desc']
		],
		columns: columns,
		createdRow: function(row, data, dataIndex) {
			if(data.custom_theme == 'YES') {
				$(row).css('background-color', '#fde0dc');
			}
		}
	})
});

$('.select-all').click(function() {
	if (this.checked) {
		$(':checkbox').each(function() {
			$(this).prop('checked', true).trigger('change');
		});
	} else {
		$('.select-me').each(function() {
			$(this).prop('checked', false).trigger('change');
		});
	}
});

$(document).on('click', '.select-me', function(event) {
	if (this.checked) {
		$(this).prop('checked', true).trigger('change');
	} else {
		$(this).prop('checked', false).trigger('change');
	}
	$('.select-all').prop('checked', false).trigger('change');
});

function change_genre(book_id) {
	if (book_id != '') {
		$('#changeGenreModal #book_id').val(book_id);
		$('#changeGenreModal').modal('show');
	}
}

function book_ai_review(book_id) {
	if (!confirm('<?=_li('Are you sure?') ?>')) return false;

	if (book_id != '') {
		$('#book_id').val(book_id);

		$.ajax({
			url: '<?= base_url('admin/ajax_book_ai_review_gen') ?>',
			type: 'POST',
			data: {
				book_id: book_id,
			},
			cache: false,
			success: function(response) {
				if (response.success) {
					success_notify(response.success)
				} else {
					error_notify(response.error)
				}
			}
		});
	}
}
</script>

<script>
$(function() {
	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
		$('#date_range1').val('');
		$('#selectedValue').text('');
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
