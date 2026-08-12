<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
					<h4 class="page-title" style="margin: 0; display: flex; align-items: center;">
					<i class="mdi mdi-apple-keyboard-command title_icon" style="margin-right: 6px;"></i>
					<?=$page_title?>
					</h4>

					<div style="display: flex; gap: 8px;">
					<?php if (!empty($action_download)) { ?>
						<a href="<?=$action_download?>" 
						class="btn btn-outline-primary btn-rounded" 
						style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 14px;">
						<i class="mdi mdi-download" style="margin-right: 4px;"></i> <?=_l('download_sample')?>
						</a>
					<?php } ?>
						<button 
						type="button"
						class="btn btn-outline-dark btn-rounded alignToTitle" 
						style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 14px;"
						onclick="window.history.back()"
						>
						<i class="mdi mdi-arrow-left" style="margin-right: 4px;"></i> <?=_l('back')?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?= $page_title ?>
				</h4>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div id="accordion">

									<div class="card mb-0">
										<div class="card-header bg-primary" id="file">
											<h5 class="mb-0">
												<button
													class="btn btn-link text-white collapsed"
													data-toggle="collapse"
													data-target="#collapse-file"
													aria-expanded="true"
													aria-controls="collapse-file"
												>
													<i class="far fa-file"></i> <?php _el('file'); ?>
												</button>
											</h5>
										</div>

										<div id="collapse-file" class="collapse show" aria-labelledby="file" data-parent="#accordion">
											<div class="card-body">
												<form action="<?php echo $action_file; ?>" method="post" enctype="multipart/form-data" id="form-import" class="form-horizontal">
													<div class="form-group">
														<div class="row">
															<label class="col-sm-2 control-label text-right" for="input-file"><?php _el('file'); ?></label>
															<div class="col-sm-10">
																<input type="file" name="file" class="form-control" />
															</div>
														</div>
													</div>

													<div class="clearfix">
														<button type="submit" class="btn btn-primary float-right"><?php _el('continue'); ?></button>
													</div>
												</form>
											</div>
										</div>
									</div>

									<div class="card mb-0">
										<div class="card-header bg-warning" id="mapping">
											<h5 class="mb-0">
												<button
													class="btn btn-link text-white collapsed"
													data-toggle="collapse"
													data-target="#collapse-mapping"
													aria-expanded="false"
													aria-controls="collapse-mapping"
												>
													<i class="fa fa-paper-plane"></i> <?php _el('mapping'); ?>
												</button>
											</h5>
										</div>

										<div id="collapse-mapping" class="collapse" aria-labelledby="mapping" data-parent="#accordion">
											<div class="card-body">
												<form action="<?php echo $action_save; ?>" method="post" enctype="multipart/form-data" id="form-import-save" class="form-horizontal">
													<input type="hidden" name="csv_file" id="csv-file" />
													<input type="hidden" name="type" id="input-type" />
													<div class="table-responsive">
														<table class="table table-bordered" id="table-mapping">
															<thead>
																<tr>
																	<th><?php _el('column'); ?></th>
																	<th><?php _el('csv_column'); ?></th>
																</tr>
															</thead>
															<tbody>

															</tbody>
															<tfoot>
																<tr>
																	<td colspan="2">
																		<div class="clearfix">
																			<button type="submit" class="btn btn-primary float-right"><?php _el('continue'); ?></button>
																		</div>
																	</td>
																</tr>
															</tfoot>
														</table>
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$('#button-download').on('click', function() {
	location = '<?php echo $action_download; ?>/' + $('select[name="type"]').val();
});
</script>

<script>
function matchSelected() {
	$('.headers').each(function() {
		$el = $(this);
		$el.val($el.data('name')).trigger('change');
	});
}

$('form').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();

	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.next) {
			$('#file button').trigger('click');
		}

		if (json.headers && json.headers.length > 0) {
			let headers = json.headers.map(item => `<option value="${item}">${item}</option>`).join();

			$('#table-mapping tbody').html(json.columns.map(item => `<tr>
				<td>${item}</td>
				<td>
					<select class="form-control select2 headers" data-toggle="select2" name="mapping[${item}]" data-name="${item}">
						${headers}
					</select>
				</td>
			</tr>`).join());

			$('.headers').select2();
			$('#mapping button').trigger('click');
			$('#csv-file').val(json.csv_file);
			$('#input-type').val(json.type);

			matchSelected();
		}

		if (json.finish) {
			$('form').trigger('reset');
			$('#type button').trigger('click');
			$('html, body').animate({ scrollTop: 0 }, 600);
		}

		json.success && success_notify(json.success);
		json.error && error_notify(json.error);
	});
})
</script>
