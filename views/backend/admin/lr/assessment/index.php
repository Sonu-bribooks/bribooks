<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
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
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php echo _l('id'); ?></th>
							<th><?php echo _l('assessment'); ?></th>
							<th><?php echo _l('user'); ?></th>
							<th><?php echo _l('marks'); ?></th>
							<th><?php echo _l('total_time_taken_(seconds)'); ?></th>
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

<div class="modal fade" id="answer-modal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php _el('student_answer'); ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>

			<div class="modal-body p-3">
				<h4 id="answer-info"></h4>
				<div id="videos" style="position: fixed;bottom: 0;right: 0;"></div>
				<div class="table-responsive mt-4">
					<table id="table-answer" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('id'); ?></th>
								<th><?php echo _l('question'); ?></th>
								<th><?php echo _l('correct_answer'); ?></th>
								<th><?php echo _l('user_answer'); ?></th>
								<th><?php echo _l('time_taken_(seconds)'); ?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
const getAnswers = (id) => {
	let fd = new FormData();
	fd.append('assessment_id', id);

	submitForm('<?php echo site_url('admin/ajax_lr_assessment_answers'); ?>', fd, json => {
		let html = '', videos = '';

		json.error && error_notify(json.error);
		json.info && $('#answer-info').html(json.info);

		json.data.map(item => {
			let bgColor = item.correct_answer == item.user_answer ? 'background-color: #c1ffc1;' : 'background-color: #f5aaaa;';

			if (item.user_answer == '') {
				bgColor = ''
			}

			html += `
			<tr style="${bgColor}">
				<td>${item.sn}</td>
				<td>${item.id}</td>
				<td>${item.question}</td>
				<td>${item.correct_answer}</td>
				<td>${item.user_answer}</td>
				<td>${item.time_taken}</td>
			</tr>`
		});

		json.files.map(file => {
			videos += `
			<video width="320" height="240" controls>
				<source src="${file}" type="video/mp4">
			</video>`
		})

		$('#table-answer tbody').html(html);
		$('#videos').html(videos);
	});
}
</script>
<script>
$(function() {
    let columns = JSON.parse(atob('<?php echo _render_column([
        'keys' 		=> ['sn', 'id', 'category', 'user', 'marks', 'time_taken', 'status', 'date_modified'],
        'actions'	=> [
            [
                'key' 		=> 'show_answers',
				'type' 		=> 'modal',
                'url' 		=> 'answer-modal',
                'callback' 	=> 'getAnswers',
            ],
            [
                'key' 		=> 'status',
				'type' 		=> 'status',
                'url' 		=> 'admin/lr_assessment/status/',
            ],
            [
                'key' 		=> 'delete',
                'type' 		=> 'confirm',
                'url' 		=> 'admin/lr_assessment/delete/',
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
