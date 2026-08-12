<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('log'); ?>
				</h4>
				<div class="d-flex flex-column flex-md-row justify-content-end align-items-center align-items-md-end">
					<div class="mb-2 mb-md-0 me-md-2 mr-1 text-truncate" style="width: 270px;">
						<select
							class="form-control select2 text-truncate input-filter"
							id="filename"
							data-toggle="select2"
							name="filename"
						>
							<option value="<?=$filename ?>" selected><?=_l('select_file')?></option>
							<?php foreach ($files as $item) { ?>
								<option value="<?= basename($item, '.' . $extension) ?>" <?php echo (basename($item, '.' . $extension) == $filename) ? 'selected' : ''; ?>>
									<?= basename($item, '.' . $extension) ?>
								</option>
							<?php } ?>
						</select>
					</div>

					<div style="width: 270px;" class="mb-2 mb-md-0 me-md-2 mr-1 text-truncate">
						<select
							class="form-control select2 text-truncate input-filter"
							id="limit"
							data-toggle="select2"
							name="limit"
						>
							<option value="100" selected><?=_l('select_limit')?></option>
							<?php foreach ($limits as $item) { ?>
								<option value="<?=$item ?>" <?php echo ($item == $limit) ? 'selected' : ''; ?>><?=$item ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="text-truncate">
						<div class="input-group">
							<input
								class="form-control input-filter"
								id="search"
								type="text"
								name="search"
								value="<?= $search ?>"
								placeholder="<?= _l('search') ?>"
							/>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="button-search">
									<?=_l('search')?>
								</button>
							</div>
						</div>
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
				<div class="row">
					<div class="col-12">
						<div class="page-header">
							<div class="container-fluid">
								<div class="float-right">
									<a href="<?php echo $download; ?>" data-toggle="tooltip" title="<?php echo _l('download'); ?>" class="btn btn-primary"><i class="fa fa-download"></i></a>
									<a onclick="confirm('<?php echo _li('Are u sure?'); ?>') ? location.href='<?php echo $clear; ?>' : false;" data-toggle="tooltip" title="<?php echo _l('clear'); ?>" class="btn btn-danger"><i class="fa fa-eraser"></i></a>
								</div>
							</div>
						</div>
						<div class="container-fluid">
							<?php if ($error_warning) { ?>
							<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
								<button type="button" class="close" data-dismiss="alert">&times;</button>
							</div>
							<?php } ?>

							<?php if ($success) { ?>
							<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
								<button type="button" class="close" data-dismiss="alert">&times;</button>
							</div>
							<?php } ?>

							<div class="panel panel-default">
								<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-list"></i> <?php echo _l('log'); ?></h3></div>
								<div class="panel-body">
									<textarea wrap="off" rows="30" readonly class="form-control"><?php echo $log; ?></textarea>
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
$(function() {
	$('.input-filter').on('change', function() {
		let filters = [];

		$('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		window.location = '<?= $action_filter ?>/?' + filters.join('&');
	});
	$('#button-search').on('click', function() {
		let filters = [];

		$('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		window.location = '<?= $action_filter ?>/?' + filters.join('&');
	});
});
</script>
