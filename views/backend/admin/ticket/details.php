<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=$page_title ?></h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-5 col-lg-5 col-xl-5">
		<div class="card">
			<div class="card-header">
				<h3>#<?=$info['code'] ?></h3>
				<?=sprintf('<span class="badge badge-%s">%s</span>', $priority_info['color'], $priority_info['name']) ?>
				<?=sprintf('<span class="badge badge-%s">%s</span>', $status_info['color'], $status_info['name']) ?>
			</div>
			<div class="card-body">
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('ticket_no') ?></b></div>
					<div class="col-sm-8">: <?=$info['code'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('user_type') ?></b></div>
					<div class="col-sm-8">: <?=$info['user_type'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('student/school') ?></b></div>
					<div class="col-sm-8">: <?=$user_info['name'] ?? $user_info['first_name'] ?>-<?=$info['user_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('escalated_by') ?></b></div>
					<div class="col-sm-8">: <?=$agent_info['first_name'] ?>-<?=$info['agent_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('assigned_department') ?></b></div>
					<div class="col-sm-8">: <?=$department_info['name'] ?>-<?=$info['department_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('category') ?></b></div>
					<div class="col-sm-8">: <?=sprintf('%s > %s', $parent_info['name'], $category_info['name']) ?>-<?=$info['category_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('subject') ?></b></div>
					<div class="col-sm-8">: <?=$info['subject'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('description') ?></b></div>
					<div class="col-sm-8">: <?=$info['description'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('priority') ?></b></div>
					<div class="col-sm-8">: <?=sprintf('<span class="badge badge-%s">%s</span>', $priority_info['color'], $priority_info['name']) ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('status') ?></b></div>
					<div class="col-sm-8">: <?=sprintf('<span class="badge badge-%s">%s</span>', $status_info['color'], $status_info['name']) ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('closure_note') ?></b></div>
					<div class="col-sm-8">: <?=$info['closure_note'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('date_closed') ?></b></div>
					<div class="col-sm-8">: <?=formatDate($info['date_closed']) ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('date_added') ?></b></div>
					<div class="col-sm-8">: <?=formatDate($info['date_added']) ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('date_modified') ?></b></div>
					<div class="col-sm-8">: <?=formatDate($info['date_modified']) ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-sm-12 col-xs-12 col-md-7 col-lg-7 col-xl-7">
		<div class="card">
			<div class="card-header">
				<h4><?=_l('ticket_updates') ?></h4>
				<button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#addReply">
					<?=_l('add_reply')?>
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive mt-2">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0" style="width:100%">
						<thead>
							<tr>
								<?php foreach ($fields as $field) { ?>
									<th><?= _l($field) ?></th>
								<?php } ?>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="addReply" role="dialog" aria-labelledby="addReplyLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addReplyLabel"><?= _l('add_reply') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="javascript:;" method="post" id="form-add-reply">
					<input type="hidden" name="ticket_id" value="<?=$info['id'] ?>" />

					<div class="form-group">
						<label for="message"><?=_l('reply') ?></label>
						<textarea name="message" id="message" rows="8" class="form-control" required></textarea>
					</div>

					<div class="form-group">
						<label for="message"><?=_l('ticket_status') ?></label>
						<select
							class="form-control select2"
							data-toggle="select2"
							name="status_id"
							id="status_id"
							required
						>
							<option value=""><?= _l('select_status') ?></option>

							<?php foreach ($statuses as $status) { ?>
								<option
									value="<?= $status['id'] ?>"
								><?= $status['name'] ?></option>
							<?php } ?>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-add-reply" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<script>
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		data: 'actions',
		render: callback
	});

	table = $('#ajax-datatable').DataTable( {
		ajax: '<?php echo $action_ajax; ?>',
		processing: true,
		serverSide: true,
		order: [[ 0, 'desc' ]],
		columns: columns
	});

	$('#form-add-reply').on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();

		$el = $(this);

		if (confirm('<?=_l('Are you sure?')?>')) {
			submitForm('<?=$action_reply ?>', new FormData($el[0]), json => {
				$('#addReply').modal('hide');
				table.ajax.reload(null, false);
				json.success && success_notify(json.success)
				json.error && error_notify(json.error)
			});
		}
	});

	$(document).on('confirm_modal', function(e, data) {
		data?.status?.success && table.ajax.reload(null, false);
	});
});
</script>
