<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('import'); ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php echo _l('import'); ?>
				</h4>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div id="accordion">
									<div class="card mb-0">
										<div class="card-header bg-info" id="type">
											<h5 class="mb-0">
												<button
													class="btn btn-link text-white collapsed"
													data-toggle="collapse"
													data-target="#collapse-type"
													aria-expanded="true"
													aria-controls="collapse-type"
												>
													<i class="far fa-map"></i> <?php _el('type'); ?>
												</button>
											</h5>
										</div>

										<div id="collapse-type" class="collapse show" aria-labelledby="type" data-parent="#accordion">
											<div class="card-body">
												<form action="<?php echo $action_type; ?>" method="post" enctype="multipart/form-data" id="form-type" class="form-horizontal">
													<div class="form-group">
														<div class="row">
															<label class="col-sm-2 control-label text-right" for="input-type"><?php _el('type'); ?></label>
															<div class="col-sm-10">
																<select class="form-control select2 type" data-toggle="select2" name="type">
																	<?php foreach ($types as $type) { ?>
																	<option value="<?php echo $type; ?>"><?php echo $type; ?></option>
																	<?php } ?>
																</select>

																<button type="button" class="btn btn-link float-right" id="button-download"><?php _eli('download_sample'); ?></button>
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

										<div id="collapse-file" class="collapse" aria-labelledby="file" data-parent="#accordion">
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

const getHeaders = (headers, selected) => {
	return headers.map(item => `<option value="${item}"${selected == item ? ' selected' : ''}>${item}</option>`).join();
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
			// let headers = json.headers.map(item => `<option value="${item}">${item}</option>`).join();

			$('#table-mapping tbody').html(json.columns.map(item => `<tr>
				<td>${item}</td>
				<td>
					<select class="form-control select2 headers" data-toggle="select2" name="mapping[${item}]">
						${getHeaders(json.headers, item)}
					</select>
				</td>
			</tr>`).join());

			$('.headers').select2();
			$('#mapping button').trigger('click');
			$('#csv-file').val(json.csv_file);
			$('#input-type').val(json.type);
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
