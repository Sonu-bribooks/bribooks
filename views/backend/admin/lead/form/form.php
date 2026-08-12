<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('add_new_form'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo _l('lead_form_add'); ?></h4>

				<?php if (validation_errors()) { ?>
				<div class="alert alert-danger"><?php echo validation_errors(); ?></div>
				<?php } ?>

				<form class="required-form" action="<?php echo site_url($action); ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
					</div>

					<div class="form-group">
						<label for="seo"><?php echo _l('seo'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="seo" name="seo" value="<?php echo $seo; ?>" required>
					</div>

					<div class="form-group">
						<label><?php echo _l('theme'); ?></label>
						<select name="theme" class="form-control">
							<?php foreach ($themes as $theme_i) { ?>
							<?php if ($theme_i == $theme) { ?>
							<option value="<?php echo $theme_i; ?>" selected="selected"><?php echo $theme_i; ?></option>
							<?php } else { ?>
							<option value="<?php echo $theme_i; ?>"><?php echo $theme_i; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="table-responsive mt-4">
						<table id="fields" class="table table-striped table-centered mb-0">
							<thead>
								<tr>
									<th style="width: 60%"><?php echo _l('name'); ?></th>
									<th style="width: 15%"><?php echo _l('sort_order'); ?></th>
									<th style="width: 1%"><?php echo _l('actions'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $row = 0; ?>
								<?php $columns = []; ?>
								<?php foreach ($fields ?? [] as $field) { ?>
								<tr id="field-row<?php echo $row; ?>">
									<td class="text-left" style="width: 60%">
										<label><?php echo _l('type'); ?><span class="required">*</span></label>
										<select name="field[<?php echo $row; ?>][type]" onchange="modifyVal(<?php echo $row; ?>, this);" id="<?php echo $row; ?>" class="form-control type">
											<option value="<?php echo $field['type']; ?>" selected><?php echo _l($field['type']); ?></option>

											<optgroup label="<?php echo _l('input'); ?>">
												<option value="text"><?php echo _l('text'); ?></option>
												<option value="email"><?php echo _l('email'); ?></option>
												<option value="mobile"><?php echo _l('mobile'); ?></option>
												<option value="location"><?php echo _l('location'); ?></option>
												<option value="textarea"><?php echo _l('textarea'); ?></option>
											</optgroup>
											<optgroup label="<?php echo _l('choose'); ?>">
												<option value="select"><?php echo _l('select'); ?></option>
												<option value="radio"><?php echo _l('radio'); ?></option>
												<option value="checkbox"><?php echo _l('checkbox'); ?></option>
											</optgroup>
											<optgroup label="<?php echo _l('file'); ?>">
												<option value="file"><?php echo _l('file'); ?></option>
											</optgroup>
										</select>

										<label><?php echo _l('field_id'); ?><span class="required">*</span></label>
										<input type="text" name="field[<?php echo $row; ?>][field_id]" placeholder="<?php echo _l('field_id'); ?>" value="<?php echo $field['field_id']; ?>" class="form-control" />

										<label><?php echo _l('name'); ?><span class="required">*</span></label>
										<input type="text" name="field[<?php echo $row; ?>][name]"  value="<?php echo $field['name']; ?>" placeholder="<?php echo _l('name'); ?>" class="form-control" />

										<div id="display-input<?php echo $row; ?>">
											<label><?php echo _l('value'); ?></label>
											<input type="text" name="field[<?php echo $row; ?>][input]"  value="<?php echo $field['input']; ?>" id="input-input<?php echo $row; ?>" placeholder="<?php echo _l('value'); ?>" class="form-control" />
										</div>

										<div id="display-validation<?php echo $row; ?>">
											<label><?php echo _l('regex'); ?></label>
											<input type="text" name="field[<?php echo $row; ?>][validation]"  value="<?php echo $field['validation']; ?>" placeholder="<?php echo _l('regex'); ?>" class="form-control"/>
										</div>

										<label><?php echo _l('required'); ?><span class="required">*</span></label>
										<div class="col-sm-10">
											<?php if ($field['required']) { ?>
											<label class="radio-inline"><input type="radio" name="field[<?php echo $row; ?>][required]" value="1" checked="checked"> <?php echo _l('yes'); ?></label>
											<label class="radio-inline"><input type="radio" name="field[<?php echo $row; ?>][required]" value="0"> <?php echo _l('no'); ?></label>
											<?php } else { ?>
											<label class="radio-inline"><input type="radio" name="field[<?php echo $row; ?>][required]" value="1"> <?php echo _l('yes'); ?></label>
											<label class="radio-inline"><input type="radio" name="field[<?php echo $row; ?>][required]" value="0" checked="checked"> <?php echo _l('no'); ?></label>
											<?php } ?>
										</div>

										<div class="table-responsive">
											<table id="value<?php echo $row; ?>" class="table table-striped table-bordered table-hover">
												<thead>
													<tr>
														<td class="text-left"><?php echo _l('value'); ?></td>
														<td class="text-right"><?php echo _l('sort_order'); ?></td>
														<td></td>
													</tr>
												</thead>
												<tbody>
													<?php $columns[$row] = 0; ?>
													<?php foreach($field['value'] ?? [] as $value) { ?>
													<tr id="value-row<?php echo $row; ?><?php echo $columns[$row]; ?>">
														<td class="text-left" style="width: 60%;">
															<input type="text" name="field[<?php echo $row; ?>][value][<?php echo $columns[$row]; ?>][name]" value="<?php echo $value['name']; ?>" placeholder="<?php echo _l('value'); ?>" class="form-control" />
														</td>
														<td class="text-right"><input type="text" name="field[<?php echo $row; ?>][value][<?php echo $columns[$row]; ?>][sort_order]" value="<?php echo $value['sort_order']; ?>" placeholder="<?php echo _l('sort_order'); ?>" class="form-control" /></td>
														<td class="text-left"><button onclick="$('#value-row<?php echo $row; ?><?php echo $columns[$row]; ?>').remove();" data-toggle="tooltip" title="<?php echo _l('remove'); ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
													</tr>
													<?php $columns[$row]++; ?>
													<?php } ?>
												</tbody>
												<tfoot>
													<tr>
														<td colspan="2"></td>
														<td class="text-left"><button type="button" onclick="addValue(<?php echo $row; ?>);" data-toggle="tooltip" title="<?php echo _l('add_new'); ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
													</tr>
												</tfoot>
											</table>
										</div>

										<label><?php echo _l('status'); ?><span class="required">*</span></label>
										<select name="field[<?php echo $row; ?>][status]" class="form-control">
											<?php if ($field['status']) { ?>
											<option value="1" selected="selected"><?php echo _l('enabled'); ?></option>
											<option value="0"><?php echo _l('disabled'); ?></option>
											<?php } else { ?>
											<option value="1"><?php echo _l('enabled'); ?></option>
											<option value="0" selected="selected"><?php echo _l('disabled'); ?></option>
											<?php } ?>
										</select>
									</td>

									<td class="text-right" style="width: 15%"><input type="text" name="field[<?php echo $row; ?>][sort_order]" placeholder="<?php echo _l('sort_order'); ?>"  value="<?php echo $field['sort_order']; ?>" class="form-control" /></td>
									<td class="text-left" style="width: 1%"><button type="button" onclick="$(\'#field-row<?php echo $row; ?>\').remove();" data-toggle="tooltip" title="<?php echo _l('remove'); ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
								</tr>
								<?php $row++; ?>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="8">
										<button type="button" class="btn btn-primary float-right" onClick="addField();"><?php echo _l('add_fields'); ?> </button>
									</td>
								</tr>
							<tfoot>
						</table>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


<script type="text/javascript">
var row = 0;
var value_row = <?php echo json_encode($columns); ?>;

function addValue(row) {
	row = parseInt(row);

	if (typeof value_row[row] === 'undefined') {
		value_row[row] = 0;
	}

	html = '';

	html += `<tr id="value-row${row + '' + value_row[row]}">
				<td class="text-left" style="width: 60%;">
					<input type="text" name="field[${row}][value][${value_row[row]}][name]" value="" placeholder="<?php echo _l('value'); ?>" class="form-control" />
				</td>
				<td class="text-right"><input type="text" name="field[${row}][value][${value_row[row]}][sort_order]" value="" placeholder="<?php echo _l('sort_order'); ?>" class="form-control" /></td>
				<td class="text-left"><button onclick="$('#value-row${row + '' + value_row[row]}').remove();" data-toggle="tooltip" title="<?php echo _l('remove'); ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
			</tr>`;

	$('#value' + row + '>tbody').append(html);
	value_row[row]++;
}

function modifyVal(row, el) {
	if (el.value == 'select' || el.value == 'radio' || el.value == 'checkbox') {
		$('#value' + row).show();
		$('#display-input' + row + ', #display-validation' + row).hide();
	} else {
		$('#value' + row).hide();
		$('#display-input' + row + ', #display-validation' + row).show();
	}

	if (el.value == 'textarea') {
		$('#display-input' + row + ' > div').html('<textarea name="field[' + row + '][input]" placeholder="<?php echo _l('value'); ?>" id="input-input' + row + '" class="form-control">' + $('#input-input' + row).val() + '</textarea>');
	} else {
		$('#display-input' + row + ' > div').html('<input type="text" name="field[' + row + '][input]" value="' + $('#input-input' + row).val() + '" placeholder="<?php echo _l('value'); ?>" id="input-input' + row + '" class="form-control" />');
	}
}

$(document).ready(function() {
	$('.type').each(function() {
		modifyVal($(this).attr('id'), this);
	});
});

function addField() {
	html = `<tr id="field-row${row}">
				<td class="text-left" style="width: 60%">
					<label><?php echo _l('type'); ?><span class="required">*</span></label>
					<select name="field[${row}][type]" onchange="modifyVal(${row}, this);" id="${row}" class="form-control type">
						<optgroup label="<?php echo _l('input'); ?>">
							<option value="text"><?php echo _l('text'); ?></option>
							<option value="email"><?php echo _l('email'); ?></option>
							<option value="mobile"><?php echo _l('mobile'); ?></option>
							<option value="location"><?php echo _l('location'); ?></option>
							<option value="textarea"><?php echo _l('textarea'); ?></option>
						</optgroup>
						<optgroup label="<?php echo _l('choose'); ?>">
							<option value="select"><?php echo _l('select'); ?></option>
							<option value="radio"><?php echo _l('radio'); ?></option>
							<option value="checkbox"><?php echo _l('checkbox'); ?></option>
						</optgroup>
						<optgroup label="<?php echo _l('file'); ?>">
							<option value="file"><?php echo _l('file'); ?></option>
						</optgroup>
					</select>

					<label><?php echo _l('field_id'); ?><span class="required">*</span></label>
					<input type="text" name="field[${row}][field_id]" placeholder="<?php echo _l('field_id'); ?>" class="form-control" />

					<label><?php echo _l('name'); ?><span class="required">*</span></label>
					<input type="text" name="field[${row}][name]" value="" placeholder="<?php echo _l('name'); ?>" class="form-control" />

					<div id="display-input${row}">
						<label><?php echo _l('value'); ?></label>
						<input type="text" name="field[${row}][input]" value="" id="input-input${row}" placeholder="<?php echo _l('value'); ?>" class="form-control" />
					</div>

					<div id="display-validation${row}">
						<label><?php echo _l('regex'); ?></label>
						<input type="text" name="field[${row}][validation]" value="" placeholder="<?php echo _l('regex'); ?>" class="form-control"/>
					</div>

					<label><?php echo _l('required'); ?><span class="required">*</span></label>
					<div class="col-sm-10">
						<label class="radio-inline"><input type="radio" name="field[${row}][required]" value="1"> <?php echo _l('yes'); ?></label>
						<label class="radio-inline"><input type="radio" name="field[${row}][required]" value="0" checked="checked"> <?php echo _l('no'); ?></label>
					</div>

					<div class="table-responsive">
						<table id="value${row}" class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<td class="text-left"><?php echo _l('value'); ?></td>
									<td class="text-right"><?php echo _l('sort_order'); ?></td>
									<td></td>
								</tr>
							</thead>
							<tbody></tbody>
							<tfoot>
								<tr>
									<td colspan="2"></td>
									<td class="text-left"><button type="button" onclick="add<?php echo _l('value'); ?>(${row});" data-toggle="tooltip" title="<?php echo _l('add_new'); ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
								</tr>
							</tfoot>
						</table>
					</div>

					<label><?php echo _l('status'); ?><span class="required">*</span></label>
					<select name="field[${row}][status]" class="form-control">
						<option value="1"><?php echo _l('enabled'); ?></option>
						<option value="0" selected="selected"><?php echo _l('disabled'); ?></option>
					</select>
				</td>

				<td class="text-right" style="width: 15%"><input type="text" name="field[${row}][sort_order]" placeholder="<?php echo _l('sort_order'); ?>" value="" class="form-control" /></td>
				<td class="text-left" style="width: 1%"><button type="button" onclick="$(\'#field-row${row}\').remove();" data-toggle="tooltip" title="<?php echo _l('remove'); ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
			</tr>`;

	$('#fields>tbody').append(html);

	modifyVal(row, $('#input-input' + row)[0]);

	row++;
}
</script>
