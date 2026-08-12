<style>
    .code {
        margin-left: 7.5rem !important;
    }
</style>
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">

                <h4 class="header-title mb-3"><?php echo _l('Add Event Detail '); ?></h4>

                <form action="<?php echo $action; ?>" method="post" id="myform">
                    <div class="tab-pane" id="basic_info">
                        <div class="row">
                        </div>
                        <div class="col-12">
                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="event"><?php echo _l('event'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<select class="form-control select2" data-toggle="select2" name="event_id" id="event_id">
									<option value="<?php echo !empty($details['event_id']) ? ($details['event_id']) : ''; ?>"><?php echo !empty($details['event_id']) ? ($details['event_name']) : get_phrase('please_select'); ?></option>
									</select>
									<label id="event_id-error" class="error" for="event_id"></label>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="powered_by"><?php echo _l('powered_by'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<input type="text" class="form-control" id="powered_by" name="powered_by" value="<?php echo $details['powered_by'] ?? '' ?>" aria-label="Small" aria-describedby="inputGroup-sizing-sm">
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="short_description"><?php echo _l('short_description'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<textarea class="form-control" id="short_description" name="short_description" rows="5"><?php echo $details['short_description'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="event_heading"><?php echo _l('event_heading'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<textarea class="form-control" id="event_heading" name="event_heading" rows="5"><?php echo $details['event_heading'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="long_description"><?php echo _l('long_description'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<textarea class="form-control" id="long_description" name="long_description" rows="5"><?php echo $details['long_description'] ?? ''; ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="url"><?php echo _l('url'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<input type="text" class="form-control" id="url" name="url" value="<?php echo $details['url'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="url_heading"><?php echo _l('url_heading'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<textarea class="form-control" id="url_heading" name="url_heading" rows="5"><?php echo $details['url_heading'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="url_description"><?php echo _l('url_description'); ?> <span class="required">*</span> </label>
                                <div class="col-md-9">
									<textarea class="form-control" id="url_description" name="url_description" rows="5"><?php echo $details['url_description'] ?? ''; ?></textarea>
                                </div>
                            </div>
							<div class="form-group row mb-3" id="award_row">
                                <label class="col-md-3 col-form-label" for="awards"><?php echo _l('awards'); ?> <span class="required">*</span> </label>

								<?php if (!empty($details['award'])) { foreach(json_decode($details['award']) as $key => $value)?>
								<?php } else { ?>
									<div class="d-flex col-md-9 w-100 justify-content-between" style="gap: 1rem;">
										<div class="w-100">
											<input type="text" class="form-control"  name="award[0][name]" value="" placeholder="Award Name" required>
										</div>
										<div class="w-100">
											<input type="text" class="form-control" name="award[0][sold]" value="" placeholder="Sold" required>
										</div>
										<div class="w-100">

										<div class="input-group">
											<a
												href="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
												id="logo-thumb-image-0"
												data-toggle="image"
												class="img-thumbnail"
												data-target="#logo-image-0"
											>
												<img
													src="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
													alt="" title=""
													data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
													style="width:100px; height:100px;"
												/>
											</a>
											<input
												type="hidden"
												name="award[0][icon]"
												value=""
												id="logo-image-0"
												required
											/>
										</div>
										</div>
										<div style="min-width: 5rem;">
											<button type="button" id="add_award_row" data-count="1" class="btn btn-sm btn-outline-primary">ADD</button>
										</div>
									</div>
								<?php }  ?>

								<!-- <div class="d-flex col-md-9 offset-3 mt-3 w-100 justify-content-between" style="gap: 1rem;">
									<div class="w-100">
										<input type="text" class="form-control" id="url" name="url" value="" placeholder="name" required>
									</div>
									<div class="w-100">
										<input type="text" class="form-control" id="url" name="url" value="" placeholder="sold" required>
									</div>
									<div class="w-100">
										<input type="file" class="form-control" id="url" name="url" value="" placeholder="icon" required>
									</div>
									<div style="min-width: 5rem;">
										<button type="button"  class="btn btn-sm btn-outline-danger remove_award_row">Remove</button>
									</div>
								</div> -->

                            </div>
                            <div class="form-group">
                                <button type="submit" id="add" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-delete-button">Submit</button>
                            </div>
                        </div>

                    </div>
            </div>
        </div>
    </div>
</div>
</form>
</div>

</div>
</div>
</div>

<!-- <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script> -->


<script type="text/javascript">
$(function() {
	window['FILEMANAGER'] = '<?php echo base_url('servermanager'); ?>';
});
</script>
<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.2') ?>"></script>

<script>
// just for the demos, avoids form submit
// jQuery.validator.setDefaults({
//   debug: true,
//   success: "valid"
// });

jQuery('[id^=sold]').each(function(e) {
    jQuery(this).rules('add', {
        minlength: 2,
        required: true
    });
});

$( "#myform" ).validate({
ignore: [],
  rules: {
    event_id: {
      required: true
    },
	powered_by: {
      required: true
    },
	short_description: {
      required: true
    },
	event_heading: {
      required: true
    },
	long_description: {
      required: true
    },
	url: {
      required: true
    },
	url_heading: {
      required: true
    },
	url_description: {
      required: true
    }
  }
});
</script>
	<script>
$(function() {
	$('#event_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/get_events'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		},
		minimumInputLength: 3
	});
});
</script>

<script>
$(function() {
	$(document).on('click', '#add_award_row', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var count = $(this).data('count');
		// alert(count);
		$(this).data('count',count+1);

		var html = '<div class="d-flex col-md-9 offset-3 mt-3 w-100 justify-content-between" style="gap: 1rem;"><div class="w-100">';
			html += '<input type="text" class="form-control" name="award['+count+'][name]" value="" placeholder="Award Name" required></div><div class="w-100">';
			html += '<input type="text" class="form-control" name="award['+count+'][sold]" value="" placeholder="Sold" required></div><div class="w-100">';
			// html += '<input type="file" class="form-control" name="award['+count+'][icon]" value="" placeholder="Icon" required></div><div style="min-width: 5rem;">';

			html += '<div class="input-group">';
			html += '<a href="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>" id="logo-thumb-image-'+count+'" data-toggle="image" class="img-thumbnail" data-target="#logo-image-'+count+'">';
			html += '<img src="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>" alt="" title="" data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>" style="width:100px; height:100px;"/></a>';
			html += '<input type="hidden" name="award['+count+'][icon]" value=""  required id="logo-image-'+count+'" />';

			html += '<button type="button" class="btn btn-sm btn-outline-danger remove_award_row" data-count="'+count+'">Remove</button></div></div>';

		$("#award_row").append(html);
	})

	$(document).on('click', '.remove_award_row', function(e) {
		e.preventDefault();
		e.stopPropagation();

		var count = $(this).data('count');
		$('#add_award_row').data('count',count-1);
		$(this).data('count',count-1);

		$(this).parent().parent().remove();

	})
});
</script>
