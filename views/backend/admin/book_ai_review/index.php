<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=$page_title?>
				<a href = "<?=$action_add?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?=_l('add')?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


<!-- ///////////////////////////////////////// -->
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
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_event')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									id="event_id"
									name="event_id"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($events as $event) { ?>
										<option value="<?= $event['id'] ?>"><?= $event['name'] ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_league')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									id="league"
									name="league"
								>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('score_less_than')?></label>
								<input
									id="score_le"
									type="number"
									name="score_le"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-2">
							<div class="form-group mb-2">
								<label><?=_l('score_greater_than')?></label>
								<input
									id="score_ge"
									type="number"
									name="score_ge"
									class="form-control input-filter"
									value="0"
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
<!-- ///////////////////////////////////////// -->

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<?php foreach ($fields as $field) { ?>
								<th><?= _l($field) ?></th>
							<?php } ?>
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
		ajax: '<?=$action_ajax?>',
		processing: true,
		serverSide: true,
		order: [[ 0, 'desc' ]],
		columns: columns
	});

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

	$('#event_id').on('change', function() {
			let ele = document.getElementById('league');
			$.post({
				url: "<?= base_url("/api/getEventLeagues") ?>",
				data: JSON.stringify({
					event_id: $(this).val()
				}),
				dataType: 'json',
				success: function(response) {
					let leagues = response.leagues

					$("#league").empty();
					ele.innerHTML = ele.innerHTML + '<option value="">Select League</option>';

					for (let i = 0; i < leagues.length; i++) {
						ele.innerHTML = ele.innerHTML + '<option value="' + leagues[i]['type'] + '_' + leagues[i]['id'] + '">' + leagues[i]['name'] + '</option>';
					}
				}
			})
		})
});
</script>
