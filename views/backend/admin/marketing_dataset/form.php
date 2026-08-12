<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>


<div class="row">
	<div class="col-sm-12">
		<div class="row justify-content-center">
			<div class="col-lg-8">
		<div class="card">
			<div class="card-body">
						<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

						<form class="required-form" id="dynamic-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
							<div class="row key-value-pair">
								<div class="form-group col-md-12">
									<label for="name"><?php echo _l('Name'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" name="name" value="<?php echo $details['name'] ?? ''; ?>" <?php echo isset($details['name']) ? 'disabled' : '' ; ?> required>
								</div>
							</div>
							<div class="row key-value-pair">
								<div class="form-group col-md-12">
									<label for="sql_query"><?php echo _l('SQL Query'); ?><span class="required">*</span></label><br>
									<textarea id="sql_query" name="sql_query"><?php echo $details['sql_query'] ?? ''; ?></textarea>
								</div>
							</div>
							<div class="row key-value-pair">
								<div class="form-group col-md-12">
									<label for="attachment_query"><?php echo _l('Attachment Query'); ?><span class="required">*</span></label><br>
									<textarea id="attachment_query" name="attachment_query"><?php echo $details['attachment_query'] ?? ''; ?></textarea>
								</div>
							</div>
							<div id="dynamic-fields"></div>

							<div id="query-error" class="mt-4"></div>
							<button type="button" class="btn btn-primary testQuery mr-5">
								<?php echo _l('Test Query'); ?>
							</button>

							<div id="query-error" class="mt-4"></div>
							<button type="button" class="btn btn-primary testAttachmentQuery mr-5">
								<?php echo _l('Test Attachment Query'); ?>
							</button>

							<button type="button" class="btn btn-primary d-none submit-btn" onclick="checkRequiredFields()">
								<?php echo _l('Submit'); ?>
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<div class="table-responsive mt-4">
						<div id="query-result" class="mt-4"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var editor = CodeMirror.fromTextArea(document.getElementById('sql_query'), {
		mode: 'text/x-sql',
		theme: 'dracula',
		lineNumbers: true,
		matchBrackets: true,
		autoCloseBrackets: true
	});

	var attachment_editor = CodeMirror.fromTextArea(document.getElementById('attachment_query'), {
		mode: 'text/x-sql',
		theme: 'dracula',
		lineNumbers: true,
		matchBrackets: true,
		autoCloseBrackets: true
	});

	$(document).ready(function() {
		let query_type = 'normal';
		$('.testQuery').click(function() {
			testQuery('normal');
		});

		$('.testAttachmentQuery').click(function() {
			query_type = 'attachment';
			testQuery('attachment');
		});


		if (query_type == 'attachment') {
			attachment_editor.on('focus', function() {
				$('#query-error').addClass('d-none');
				$('.alert').addClass('d-none');
				$('.submit-btn').addClass('d-none');
			});
		} else {
			editor.on('focus', function() {
				$('#query-error').addClass('d-none');
				$('.alert').addClass('d-none');
				$('.submit-btn').addClass('d-none');
			});
		}
	});

	function testQuery(query_type) {
		if (query_type == 'attachment') {
			var sql_query = attachment_editor.getValue();
		} else {
			var sql_query = editor.getValue();
		}
		
		$.ajax({
			url: "<?php echo site_url('admin/test_query'); ?>",
			type: 'POST',
			data: { sql_query: sql_query },
			dataType: 'json',
			beforeSend: function() {
				$('#query-error').html('');
				$('#query-result').html('');
			},
			success: function(response) {
				$('#query-error').removeClass('d-none');

				if (response.error) {
					$('#query-error').html('<div class="alert alert-danger">' + response.error + '</div>');
					$('.submit-btn').addClass('d-none');
				} else if (response.data.length > 0) {
					generateTable(response.data);
					$('.submit-btn').removeClass('d-none');
				} else {
					$('#query-error').html('<div class="alert alert-warning"><?=_li('No data found.') ?></div>');
				}
			},
			error: function() {
				$('#query-error').html('<div class="alert alert-danger"><?=_li('An error occurred while processing the query.') ?></div>');
			}
		});
	}

	function generateTable(data) {
		if (data.length === 0) {
			$('#query-result').html('<div class="alert alert-warning">No results found.</div>');
			return;
		}

		var table = '<div class="table-responsive" style="overflow-x: auto; max-width: 100%;">';
		table += '<table class="table table-striped table-centered mb-0 dataTable no-footer" id="ajax-datatable"><thead><tr>';

		var keys = Object.keys(data[0]);

		keys.forEach(function(key) {
			table += '<th>' + key + '</th>';
		});

		table += '</tr></thead><tbody>';

		data.forEach(function(row) {
			table += '<tr>';
			keys.forEach(function(key) {
				table += '<td>' + row[key] + '</td>';
			});
			table += '</tr>';
		});

		table += '</tbody></table></div>';
		$('#query-result').html(table);

		$('#ajax-datatable').DataTable()
	}
</script>
