<?php /*echo "<pre>"; print_r($teachers); die;*/ ?>
<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<form class="required-form" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="parent">Select Event<span class="required">*</span></label>
						<select class="form-control select2 temp_type" data-toggle="select2" name="event_id" id="event_id" >
							<option value="0"><?php echo get_phrase('all'); ?></option>
							<?php foreach ($events as $event) : ?>
								<option
									value="<?php echo $event['id']; ?>"
									<?=$event['id'] == ($details['event_id'] ?? '') ? ' selected' : ''?>
								><?php echo $event['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="parent">Select Type<span class="required">*</span></label>
						<select class="form-control select2 temp_type" data-toggle="select2" name="type" id="type" >
							<option value="0" <?= '0' == ($details['type'] ?? '') ? ' selected' : ''?>><?php echo get_phrase('normal'); ?></option>
							<option value="1" <?= '1' == ($details['type'] ?? '') ? ' selected' : ''?>><?php echo get_phrase('invite'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="message"><?php echo _l('message'); ?><span class="required">*</span></label>
						<textarea name="message" id="summernote-basic" class="form-control"><?php echo $details['message'] ?? ''; ?></textarea>
					</div>

					<button type="button" class="btn btn-primary" id="subBtn" onclick="checkRequiredFields()"><?php echo _l('submit'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script type="text/javascript">

$(function() {

		$(".temp_type").on('change', function(){ 
			if(window.location.toString().includes("add")){
				$.ajax({
					url: '<?php echo site_url('admin/check_event_share_template'); ?>',
					type: 'POST',
					dataType: 'json',
					data: {
						event_id : $('#event_id').val(), 
						type : $('#type').val()
					},
					success: function(data){
						console.log(data);
						if(data.event_id != ''){
							error_notify('Message already added for this event');
							$('#subBtn').prop('disabled', true);
						}else{
							$('#subBtn').prop('disabled', false);
		
						}
					}
				});
			}
		});
})
</script>

