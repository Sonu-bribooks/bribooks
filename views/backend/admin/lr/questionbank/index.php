<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<form class="row justify-content-center" action="<?php echo site_url('admin/lr_questionbank'); ?>" method="get">
					<div class="col-xl-7">
						<div class="form-group">
							<label for="course_id"><?php echo _l('category'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="category_id" id="category_id">
								<option value="<?php echo 'all'; ?>"><?php echo _l('all'); ?></option>
								<?php foreach ($categories as $category) { ?>
								<?php $category_name = $this->lr_category_model->formatName($category['id']); ?>
								<?php if ($category_id == $category['id']) { ?>
								<option value="<?php echo $category['id']; ?>" selected><?php echo $category_name; ?></option>
								<?php } else { ?>
								<option value="<?php echo $category['id']; ?>"><?php echo $category_name; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="col-xl-3">
						<div class="form-group">
							<label for="teacher_id"><?php echo _l('level'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="level" id="level">
								<option value="all"><?php echo _l('all'); ?></option>
								<?php foreach (ICODE_LEVEL as $i) { ?>
								<?php if ($level == $i) { ?>
								<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
								<?php } else { ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="col-xl-2">
						<label for=".." class="text-white"><?php echo _l('..'); ?></label>
						<button type="submit" class="btn btn-primary btn-block"><?php echo _l('filter'); ?></button>
					</div>
				</form>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php echo _l('id'); ?></th>
							<th><?php echo _l('question'); ?></th>
							<th><?php echo _l('category'); ?></th>
							<th><?php echo _l('level'); ?></th>
							<th><?php echo _l('answer'); ?></th>
							<th><?php echo _l('status'); ?></th>
							<th><?php echo _l('date_modified'); ?></th>
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
$(function() {
    let columns = JSON.parse(atob('<?php echo _render_column([
        'keys' 		=> ['sn', 'id', 'question', 'category', 'level', 'answer', 'status', 'date_modified'],
        'actions'	=> [
            [
                'key' 		=> 'edit',
                'url' 		=> 'admin/lr_questionbank_form/edit/',
            ],
            [
                'key' 		=> 'status',
				'type' 		=> 'status',
                'url' 		=> 'admin/lr_questionbank/status/',
            ],
            [
                'key' 		=> 'delete',
                'type' 		=> 'confirm',
                'url' 		=> 'admin/lr_questionbank/delete/',
            ],
        ]
    ]); ?>'));

    const action = columns.pop()
    const callback = eval(action.render)
    columns.push({
        "data": "actions",
        render: callback
    });

	$('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
});
</script>
