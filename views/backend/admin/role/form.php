<style>
.select2-results__option:before {
	content: "";
	display: inline-block;
	position: relative;
	height: 20px;
	width: 20px;
	border: 2px solid #e9e9e9;
	border-radius: 4px;
	background-color: #fff;
	margin-right: 20px;
	vertical-align: middle;
}
.select2-results__option[aria-selected=true]:before {
	font-family: "Font Awesome 5 Free";
	content: "\f00c";
	color: #fff;
	background-color: #f77750;
	border: 0;
	display: inline-block;
	padding-left: 3px;
	font-weight: 900;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
	max-height: 200px;
	overflow-y: auto;
}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body relative">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?= $page_title ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="type"><?php echo _l('type'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="type" id="type">
								<?php foreach ($types as $key => $type) { ?>
									<option
										value="<?= $type ?>"
										<?= ($details['type'] ?? 'admin')  == $type ? ' selected' : '' ?>
									><?= $type ?></option>
								<?php } ?>
							</select>
						</div>

						<?php foreach ($permissions as $group => $group_permissions) { ?>
							<div class="well">
								<h4><?php echo _l($group . '_permissions'); ?></h4>
								<a href="javascript:void(0)" onclick="$('.permissions option').prop('selected', true);$('.permissions').trigger('change');"><?php _el('select_all'); ?></a> /
								<a href="javascript:void(0)" onclick="$('.permissions option').prop('selected', false);$('.permissions').trigger('change');"><?php _el('unselect_all'); ?></a>
								<hr />
								<?php foreach ($group_permissions as $type => $item) { ?>
									<div class="form-group">
										<label for="permissions"><?php echo _l($type . '_permissions'); ?></label>
										<select class="form-control select2 check-select2 permissions" data-toggle="select2" name="permissions[<?=$group?>][<?=$type?>][]" id="permissions_<?= $group ?>_<?= $type ?>"  multiple="multiple">
											<?php foreach ($item as $permission) { ?>
											<?php if (in_array($permission, ($details['permissions'][$group][$type] ?? []))) { ?>
											<option value="<?= $permission ?>" selected><?= $permission ?></option>
											<?php } else { ?>
											<option value="<?= $permission ?>"><?= $permission ?></option>
											<?php } ?>
											<?php } ?>
										</select>
										<a href="javascript:void(0)" onclick="$('#permissions_<?= $group ?>_<?= $type ?> option').prop('selected', true);$('#permissions_<?= $group ?>_<?= $type ?>').trigger('change');"><?php _el('select_all'); ?></a> /
										<a href="javascript:void(0)" onclick="$('#permissions_<?= $group ?>_<?= $type ?> option').prop('selected', false);$('#permissions_<?= $group ?>_<?= $type ?>').trigger('change');"><?php _el('unselect_all'); ?></a>
									</div>
								<?php } ?>
							</div>
						<?php } ?>

						<div class="form-group">
							<label for="status"><?php echo _l('status'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="status" id="status">
								<?php if (($details['status'] ?? 1)  == 1) { ?>
								<option value="0"><?php echo _l('disable'); ?></option>
								<option value="1" selected><?php echo _l('enable'); ?></option>
								<?php } else { ?>
								<option value="0" selected><?php echo _l('disable'); ?></option>
								<option value="1"><?php echo _l('enable'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="card fixed-bottom w-100 mb-0">
							<div class="card-body p-2 text-center">
								<button
									type="button"
									class="btn btn-primary w-25"
									onclick="checkRequiredFields()"
								><?php echo _l("submit"); ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$('.check-select2').select2({
	closeOnSelect : false,
	placeholder : '<?= _l('select_module') ?>',
	allowHtml: true,
	allowClear: true,
	tags: true
});
</script>
