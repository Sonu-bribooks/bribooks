<div id="servermanager" class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title"><?php echo $heading_title; ?></h4>
			<button type="button" class="close" data-dismiss="modal">&times;</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-sm-5">
					<a href="<?php echo $parent; ?>" data-toggle="tooltip" title="<?php echo _l('parent'); ?>" id="button-parent" class="btn btn-light"><i class="fa fa-level-up-alt"></i></a>
					<a href="<?php echo $refresh; ?>" data-toggle="tooltip" title="<?php echo _l('refresh'); ?>" id="button-refresh" class="btn btn-secondary"><i class="fa fa-sync"></i></a>
						<button type="button" data-toggle="tooltip" title="<?php echo _l('upload'); ?>" id="button-upload" class="btn btn-primary"><i class="fa fa-upload"></i></button>
						<button type="button" data-toggle="tooltip" title="<?php echo _l('folder'); ?>" id="button-folder" class="btn btn-info"><i class="fa fa-folder"></i></button>
						<button type="button" data-toggle="tooltip" title="<?php echo _l('delete'); ?>" id="button-delete" class="btn btn-danger"><i class="fa fa-trash"></i></button>
				</div>
				<div class="col-sm-7">
					<div class="input-group">
						<input type="text" name="search" value="<?php echo $filter_name; ?>" placeholder="<?php echo _l('search'); ?>" class="form-control">
						<span class="input-group-btn">
						<button type="button" data-toggle="tooltip" title="<?php echo _l('search'); ?>" id="button-search" class="btn btn-primary"><i class="fa fa-search"></i></button>
						</span></div>
				</div>
			</div>
			<hr />
			<div id="upload-progress" class="progress mt-2" style="display: none;">
				<div
					id="upload-progress-bar"
					class="progress-bar progress-bar-striped progress-bar-animated"
					role="progressbar"
					style="width: 0%"
				>0%</div>
			</div>

			<?php foreach (array_chunk($images, 4) as $image) { ?>
			<div class="row">
				<?php foreach ($image as $image) { ?>
				<div class="col-sm-3 col-xs-6 text-center text-break">
					<?php if ($image['type'] == 'directory') { ?>
					<div class="text-center"><a href="<?php echo $image['href']; ?>" class="directory" style="vertical-align: middle;"><i class="fa fa-folder fa-5x"></i></a></div>
					<label>
						<input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
						<?php echo $image['name']; ?></label>
					<?php } ?>
					<?php if ($image['type'] == 'image') { ?>
					<a href="<?php echo $image['href']; ?>" class="thumbnail">
						<img
							src="<?php echo $image['thumb']; ?>"
							alt="<?php echo $image['name']; ?>"
							title="<?php echo $image['name']; ?>"
							style="width:80px; height:80px;"
						/>
					</a>
					<label>
						<input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
						<?php echo $image['name']; ?></label>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<br />
			<?php } ?>
		</div>
		<div class="modal-footer"><?php echo $pagination; ?></div>
	</div>
</div>
<script type="text/javascript">
<?php if ($target) { ?>
$('a.thumbnail').on('click', function(e) {
	e.preventDefault();

	<?php if ($thumb) { ?>
	$('#<?php echo $thumb; ?>').find('img').attr('src', $(this).find('img').attr('src'));
	$('#<?php echo $thumb; ?>').find('.caption').text($(this).next('label').text());
	<?php } ?>

	$('#<?php echo $target; ?>').val($(this).parent().find('input').val());

	$('#modal-image').modal('hide');
});
<?php } ?>

$('a.directory').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('.pagination a').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('#button-parent').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('#button-refresh').on('click', function(e) {
	e.preventDefault();

	$('#modal-image').load($(this).attr('href'));
});

$('input[name=\'search\']').on('keydown', function(e) {
	if (e.which == 13) {
		$('#button-search').trigger('click');
	}
});

$('#button-search').on('click', function(e) {
	var url = '<?php echo base_url('servermanager'); ?>?directory=<?php echo $directory; ?>';

	var filter_name = $('input[name=\'search\']').val();

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	<?php if ($thumb) { ?>
	url += '&thumb=' + '<?php echo $thumb; ?>';
	<?php } ?>

	<?php if ($target) { ?>
	url += '&target=' + '<?php echo $target; ?>';
	<?php } ?>

	<?php if ($s3_bucket) { ?>
	url += '&s3_bucket=' + '<?php echo $s3_bucket; ?>';
	<?php } ?>

	<?php if ($s3_region) { ?>
	url += '&s3_region=' + '<?php echo $s3_region; ?>';
	<?php } ?>

	$('#modal-image').load(url);
});
</script>
<script type="text/javascript">
$('#button-upload').on('click', function() {
	$('#form-upload').remove();

	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file[]" value="" multiple="multiple" /></form>');

	$('#form-upload input[name=\'file[]\']').trigger('click');

	if (typeof timer != 'undefined') {
			clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file[]\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: '<?=$action_upload ?>',
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				xhr: function() {
					var xhr = new XMLHttpRequest();
					xhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							var percent = Math.round((e.loaded / e.total) * 100);
							$('#upload-progress-bar').css('width', percent + '%').text(percent + '%');
							$('#upload-progress').show();
						}
					}, false);
					return xhr;
				},
				beforeSend: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-circle-notch fa-spin"></i>');
					$('#button-upload').prop('disabled', true);
					$('#upload-progress-bar').css('width', '0%').text('0%');
					$('#upload-progress').show();
				},
				complete: function() {
					$('#button-upload i').replaceWith('<i class="fa fa-upload"></i>');
					$('#button-upload').prop('disabled', false);
					$('#upload-progress').hide();
				},
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}

					if (json['success']) {
						alert(json['success']);

						$('#button-refresh').trigger('click');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});

$('#button-folder').popover({
	html: true,
	placement: 'bottom',
	trigger: 'click',
	sanitize: false,
	container: '#servermanager .modal-body',
	title: '<?php echo _l('folder'); ?>',
	content: function() {
		html	= '<div class="input-group">';
		html += '	<input type="text" name="folder" value="" placeholder="<?php echo _l('folder'); ?>" class="form-control" />';
		html += '	<span class="input-group-btn"><button type="button" title="<?php echo _l('folder'); ?>" id="button-create" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></span>';
		html += '</div>';

		return html;
	}
});

$('#button-folder').on('shown.bs.popover', function() {
	$('#button-create').on('click', function() {
		$.ajax({
			url: '<?=$action_folder ?>',
			type: 'post',
			dataType: 'json',
			data: 'folder=' + encodeURIComponent($('input[name=\'folder\']').val()),
			beforeSend: function() {
				$('#button-create').prop('disabled', true);
			},
			complete: function() {
				$('#button-create').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);
					$('#button-folder').popover('dispose');
					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
});

$('#modal-image #button-delete').on('click', function(e) {
	if (confirm('<?php echo _l('are_you_sure?'); ?>')) {
		$.ajax({
			url: '<?=$action_delete ?>',
			type: 'post',
			dataType: 'json',
			data: $('input[name^=\'path\']:checked'),
			beforeSend: function() {
				$('#button-delete').prop('disabled', true);
			},
			complete: function() {
				$('#button-delete').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);

					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});
</script>
