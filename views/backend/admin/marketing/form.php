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
						<label for="type"><?php echo _l('event'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="event_id" id="event_id">
							<?php foreach ($events ?? [] as $item) { ?>
							<?php if (($details['event_id'] ?? '') == $item['id']) { ?>
							<option value="<?=$item['id']?>" selected><?=$item['name']?></option>
							<?php } else { ?>
							<option value="<?=$item['id']?>"><?=$item['name']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="name"><?php echo _l('campaign_name'); ?><span class="required">*</span></label>
						<input class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required />
					</div>

					<div class="form-group">
						<label for="type"><?php echo _l('campaign_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="type" id="type">
							<?php foreach ($types ?? [] as $item) { ?>
							<?php if (($details['type'] ?? '') == $item['key']) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group sms_gateway_input">
						<label for="type"><?php echo _l('sms_gateway'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="sms_gateway" id="sms_gateway">
							<?php foreach ($sms_gateway ?? [] as $item) { ?>
							<?php if (($details['sms_gateway'] ?? '') == $item['key']) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="filter_state"><?php echo _l('states'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="filters[state_id][]" id="event_state" multiple="multiple">
							<option value=""><?php echo _l('select_state'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="filter_users_id"><?php echo _l('users'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="filters[users_id][]" id="event_users" multiple="multiple">
							<option value=""><?php echo _l('select_users'); ?></option>
							<?php foreach ($users ?? [] as $result) { ?>
							<option value="<?= $result['id']; ?>" selected><?= vsprintf(_l('%s (%s - %s - %s)'), [
								trim($result['first_name'] . ' ' . $result['last_name']),
								$result['id'],
								$result['email'],
								$result['mobile'],
							]); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="filter_school_type"><?php echo _l('school_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="filters[site_site_type][]" id="event_school_type" multiple="multiple">
							<option value=""><?php echo _l('select_school_type'); ?></option>
							<option value="1"><?php echo _l('k12_schools'); ?></option>
							<option value="2"><?php echo _l('Nursery'); ?></option>
						</select>
					</div>

					<div class="form-group whatsapp_gateway_input">
						<label for="type"><?php echo _l('whatsapp_gateway'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="whatsapp_gateway" id="whatsapp_gateway">
							<option value="" selected><?=_l('none')?></option>
							<?php foreach ($whatsapp_gateway ?? [] as $item) { ?>
							<?php if (($details['whatsapp_gateway'] ?? '') == $item['key']) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group whatsapp_box">
						<label for="template_id"><?php echo _l('whatsapp_template_id'); ?><span class="required">*</span></label>
						<input class="form-control" id="template_id" name="template_id" value="<?php echo $details['template_id'] ?? ''; ?>" />
					</div>

					<div class="form-group">
						<label for="to"><?php echo _l('to'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="user_type" id="user_type">
							<?php foreach ($user_types ?? [] as $item) { ?>
							<?php if (strpos($details['user_type'] ?? '', $item['key']) === 0) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="to"><?php echo _l('campaign_receiver_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="receiver_type" id="receiver_type">
							<option value="" selected><?=_li('select_receiver_type')?></option>             
							<option value="user"
								<?= (isset($details['receiver_type']) && $details['receiver_type'] == 'user') ? 'selected' : ''; ?>
							><?=_li('user')?></option>               
							<option value="site"
								<?= (isset($details['receiver_type']) && $details['receiver_type'] == 'site') ? 'selected' : ''; ?>
							><?=_li('site')?></option>               
							<option value="school"
								<?= (isset($details['receiver_type']) && $details['receiver_type'] == 'school') ? 'selected' : ''; ?>
							><?=_li('school')?></option>          
						</select>
					</div>


					<div class="form-group d-none <?php if (!empty($marketing_dataset)) { echo 'd-block'; }?>" id="marketing_dataset">
						<label> <?=_l('marketing_dataset_query')?> <span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="marketing_dataset_user_type" id="marketing_dataset_query_name">
							<option value=""><?=_l('select_marketing_dataset_query_name')?></option>
							<?php if (!empty($marketing_dataset['name'])) { ?>
								<option value="<?php echo 'marketing_dataset_'. $marketing_dataset['id']; ?>" selected><?php $marketing_dataset['name']; ?></option>
							<?php } ?>
						</select>
						<?=$marketing_dataset['name']  ?? ''?>
					</div>

					<div class="form-group" id="csv_picker">
						<label> CSV File <span class="required">*</span></label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="csv_file" name="csv_file" accept=".csv" onchange="changeTitleOfImageUploader(this)" />
								<label class="custom-file-label" for="category_thumbnail">Choose File</label>
							</div>
						</div>
						<?=$details['csv_file'] ?? ''?>
					</div>

					<div class="form-group" style="display:none;" id="site_group">
						<label for="to"><?php echo _l('to'); ?></label>
						<select class="form-control select2" data-toggle="select2" data-site="<?=$details['to']?>" name="to[]" id="to" multiple>
						</select>
					</div>

					<div class="form-group sms_box">
						<label for="sms_message"><?php echo _l('sms_message'); ?><span class="required">*</span></label>
						<textarea rows="3" class="form-control" id="ms_message" name="sms"><?php echo $details['sms'] ?? ''; ?></textarea>
					</div>

					<div class="form-group whatsapp_box">
						<label for="whatsapp_message"><?php echo _l('whatsapp_message'); ?><span class="required">*</span></label>
						<textarea rows="3" class="form-control" id="whatsapp_message" name="whatsapp_message"><?php echo $details['whatsapp_message'] ?? ''; ?></textarea>
					</div>

					<div class="form-group whatsapp_box">
						<label for="whatsapp_cta_type"><?php echo _l('whatsapp_cta_type'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="whatsapp_cta_type" id="whatsapp_cta_type">
							<?php if (($details['whatsapp_cta_type'] ?? '') == 'QUICK_REPLY') { ?>
							<option value="URL"><?php echo _l('URL'); ?></option>
							<option value="QUICK_REPLY" selected><?php echo _l('QUICK_REPLY'); ?></option>
							<?php } else { ?>
							<option value="URL" selected><?php echo _l('URL'); ?></option>
							<option value="QUICK_REPLY"><?php echo _l('QUICK_REPLY'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group whatsapp_box">
						<label for="whatsapp_cta"><?php echo _l('whatsapp_cta'); ?></label>
						<input class="form-control" id="whatsapp_cta" name="whatsapp_cta" value="<?php echo $details['whatsapp_cta'] ?? ''; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="subject"><?php echo _l('subject'); ?><span class="required">*</span></label>
						<input class="form-control" id="subject" name="subject" value="<?php echo $details['subject'] ?? ''; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="email_sender"><?php echo _l('email_sender'); ?><span class="required">*</span></label>
						<input class="form-control" id="email_sender" name="email_sender" value="<?php echo $details['email_sender'] ?? 'no-reply@bribooks.info'; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="email_sender_name"><?php echo _l('email_sender_name'); ?><span class="required">*</span></label>
						<input class="form-control" id="email_sender_name" name="email_sender_name" value="<?php echo $details['email_sender_name'] ?? 'BriBooks'; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="email_reply_to"><?php echo _l('email_reply_to'); ?><span class="required">*</span></label>
						<input class="form-control" id="email_reply_to" name="email_reply_to" value="<?php echo $details['email_reply_to'] ?? 'support@bribooks.com'; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="email_bcc_to"><?php echo _l('email_bcc_to'); ?><span class="required">*</span></label>
						<input class="form-control" id="email_bcc_to" name="email_bcc_to" value="<?php echo $details['email_bcc_to'] ?? ''; ?>" />
					</div>

					<div class="form-group email_box">
						<label for="message"><?php echo _l('message'); ?><span class="required">*</span></label>
						<textarea rows="7" class="form-control tinymce" id="message" name="message"><?php echo $details['message'] ?? ''; ?></textarea>
					</div>

					<div class="form-group email_box">
						<label for="message"><?php echo _l('email_template'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="email_template_id" id="email_template_id">
							<?php foreach ($email_templates ?? [] as $item) { ?>
							<?php if (($details['email_template_id'] ?? '') == $item['key']) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="email_attachment_type"><?php echo _l('email_attachment_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="email_attachment_type" id="email_attachment_type">
							<option value="0"><?php echo _l('select_attachment_type'); ?></option>
							<?php foreach (ATTACHMENT_TYPES as $key => $email_attachment_type) { ?>
							<?php if (($details['email_attachment_type'] ?? '') == $key) { ?>
							<option value="<?php echo $key; ?>" selected><?php echo _l($email_attachment_type); ?></option>
							<?php } else { ?>
							<option value="<?php echo $key; ?>"><?php echo _l($email_attachment_type); ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group" id="email_attachment_file_div">
						<label for="thumb-image"><?php echo _l('email_attachment_file'); ?></label>
						<div class="input-group">
							<a
								href="<?php echo $this->image_model->resize($details['email_attachment_file'] ?? 'no_image.png', 100, 100) ?>"
								id="email-thumb-image"
								data-toggle="image"
								class="img-thumbnail"
								data-target="#input-email-image"
							>
								<img
									src="<?php echo $this->image_model->resize(!empty($details['attachment_file']) ? $details['attachment_file'] : 'no_image.png', 100, 100) ?>"
									alt="" title=""
									data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
								/>
							</a>
							<input
								type="hidden"
								name="email_attachment_file"
								value="<?php echo $details['email_attachment_file'] ?? ''; ?>"
								id="input-email-image"
							/>
						</div>
					</div>

					<div class="form-group">
						<label for="attachment_type"><?php echo _l('attachment_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="attachment_type" id="attachment_type">
							<option value="0"><?php echo _l('select_attachment_type'); ?></option>
							<?php foreach (ATTACHMENT_TYPES as $key => $attachment_type) { ?>
							<?php if (($details['attachment_type'] ?? '') == $key) { ?>
							<option value="<?php echo $key; ?>" selected><?php echo _l($attachment_type); ?></option>
							<?php } else { ?>
							<option value="<?php echo $key; ?>"><?php echo _l($attachment_type); ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group" id="attachment_file_div">
						<label for="thumb-image"><?php echo _l('attachment_file'); ?></label>
						<div class="input-group">
							<a
								href="<?php echo $this->image_model->resize($details['attachment_file'] ?? 'no_image.png', 100, 100) ?>"
								id="thumb-image"
								data-toggle="image"
								class="img-thumbnail"
								data-target="#input-image"
							>
								<img
									src="<?php echo $this->image_model->resize(!empty($details['attachment_file']) ? $details['attachment_file'] : 'no_image.png', 100, 100) ?>"
									alt="" title=""
									data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
								/>
							</a>
							<input
								type="hidden"
								name="attachment_file"
								value="<?php echo $details['attachment_file'] ?? ''; ?>"
								id="input-image"
							/>
						</div>
					</div>

					<div class="form-group">
						<label for="testing"><?php echo _l('parent_kit'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="parent_kit" id="parent_kit">
							<?php if (($details['parent_kit']) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="teacher_kit"><?php echo _l('teacher_kit'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="teacher_kit" id="teacher_kit">
							<?php if (($details['teacher_kit']) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="brochure"><?php echo _l('brochure'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="brochure" id="brochure">
							<?php if (($details['brochure']) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="leaflet"><?php echo _l('leaflet'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="leaflet" id="leaflet">
							<?php if (($details['leaflet']) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="school_report_pdf"><?php echo _l('school_report_pdf'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="school_report_pdf" id="school_report_pdf">
							<?php if (($details['school_report_pdf']) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="alert_date"><?php echo _l('alert_date'); ?><span class="required">*</span></label>
						<input class="form-control datetimepicker" id="alert_date" name="alert_date" required value="<?php echo date('m/d/Y h:i A', strtotime($details['alert_date'] ?? date('m/d/Y h:i A', strtotime('+10 minutes')))); ?>" />
					</div>

					<div class="form-group">
						<label for="to"><?php echo _l('frequency'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="frequency" id="frequency">
							<?php foreach ($frequencies ?? [] as $item) { ?>
							<?php if (($details['frequency'] ?? '') == $item['key']) { ?>
							<option value="<?=$item['key']?>" selected><?=$item['value']?></option>
							<?php } else { ?>
							<option value="<?=$item['key']?>"><?=$item['value']?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="url"><?php echo _l('url'); ?></label>
						<input class="form-control" id="url" name="url" value="<?php echo $details['url'] ?? ''; ?>" />
					</div>

					<div class="form-group">
						<label for="thread_rate"><?php echo _l('thread_rate'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="thread_rate" id="thread_rate">
							<option value="1" <?=isset($details['thread_rate']) && $details['thread_rate']== '1' ? 'selected' : ''?>><?php echo _l('1X'); ?></option>
							<option value="2" <?=isset($details['thread_rate']) && $details['thread_rate']== '2' ? 'selected' : ''?>><?php echo _l('2X'); ?></option>
							<option value="3" <?=isset($details['thread_rate']) && $details['thread_rate']== '3' ? 'selected' : ''?>><?php echo _l('3X'); ?></option>
							<!-- <option value="4" <?=isset($details['thread_rate']) && $details['thread_rate']== '4' ? 'selected' : ''?>><?php echo _l('4X'); ?></option>
							<option value="5" <?=isset($details['thread_rate']) && $details['thread_rate']== '5' ? 'selected' : ''?>><?php echo _l('5X'); ?></option> -->
						</select>
					</div>

					<div class="form-group">
						<label for="testing"><?php echo _l('testing'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="testing" id="testing">
							<?php if (($details['testing'] ?? 1) == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="status"><?php echo _l('status'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="status" id="status">
							<?php if (($details['status'] ?? 0) == 1) { ?>
							<option value="0"><?php echo _l('disable'); ?></option>
							<option value="1" selected><?php echo _l('enable'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('disable'); ?></option>
							<option value="1"><?php echo _l('enable'); ?></option>
							<?php } ?>
						</select>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
$(function() {
	$('#attachment_type').on('change', function() {
		if ($(this).val() > 0) {
			$('#attachment_file_div').show()
		} else {
			$('#attachment_file_div').hide()
		}
	});
	$('#attachment_type').trigger('change');
})
</script>
<script>
$(function() {
	$('#email_attachment_type').on('change', function() {
		if ($(this).val() > 0) {
			$('#email_attachment_file_div').show()
		} else {
			$('#email_attachment_file_div').hide()
		}
	});
	$('#email_attachment_type').trigger('change');
})
</script>

<script type="text/javascript">
$(function() {
	window['FILEMANAGER'] = '<?php echo base_url('filemanager'); ?>';
});
</script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>
<script type="text/javascript">
$(document).ready(function() {
	tinymce.init({
		selector: '.tinymce',
		branding: false,
		force_br_newlines: true,
		force_p_newlines: false,
		forced_root_block: '',
		relative_urls : false,
		remove_script_host : false,
		document_base_url : "/",
		convert_urls : false,
		plugins: 'lists code emoticons link image',
		toolbar: 'undo redo | sizeselect fontselect fontsizeselect link image styleselect | bold italic | ' +
			'alignleft aligncenter alignright alignjustify | ' +
			'outdent indent | numlist bullist | emoticons',
	});

	$('#type').on('change', function() {
		if (['all', 'whatsapp', 'whatsapp_annoncement', 'whatsapp_sms', 'whatsapp_email', 'whatsapp_referral', 'email_whatsapp_referral'].includes($(this).val())) {
			$('.whatsapp_box').show()
			$('.whatsapp_gateway_input').show()
		} else {
			$('.whatsapp_box').hide()
			$('.whatsapp_gateway_input').hide()
		}

		if (['all', 'email', 'email_annoncement', 'email_sms', 'sms_annoncement', 'whatsapp_annoncement', 'whatsapp_email', 'app_notifications', 'push_notifications', 'email_referral', 'email_whatsapp_referral'].includes($(this).val())) {
			$('.email_box').show()
		} else {
			$('.email_box').hide()
		}

		if (['all', 'sms', 'sms_annoncement', 'email_sms', 'whatsapp_sms', 'app_notifications', 'push_notifications', 'sms_referral'].includes($(this).val())) {
			$('.sms_box').show()
			$('.sms_gateway_input').show()
		} else {
			$('.sms_box').hide()
			$('.sms_gateway_input').hide()
		}
	});
	$('#type').trigger('change');
	$('#user_type').on('change', function() {
		if ($(this).val() == 'csv') {
			$('#csv_picker').show()
		} else if ($(this).val() == 'marketing_dataset') {
			$('#marketing_dataset').removeClass('d-none')
			$('#csv_picker').hide()
		} else {
			$('#csv_picker').hide()
		}

		if ($(this).val() == 'school_message_to_all_students' || $(this).val() == 'school_message_to_all_published_authors') {
			$('#site_group').show()
		} else {
			$('#site_group').hide()
		}
	});
	$('#user_type').trigger('change');

	$('#event_users').select2({
		ajax: {
	        url: '<?php echo site_url('admin/ajax_search_students'); ?>',
	        dataType: "json",
	        delay: 250,
	        data: function (params) {
	            return {
	                search: params.term,
	            };
	        },
	        processResults: function (data) {
	            return {
	                results: data
	            };
	        },
	        cache: true
	    },
	    placeholder: "Select Users",
	    minimumInputLength: 3
	});

	$('#marketing_dataset_query_name').select2({
    ajax: {
        url: '<?php echo site_url('admin/ajax_marketing_dataset'); ?>',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                search: params.term,
            };
        },
        processResults: function (data) {
            return {
                results: data.data.map(item => ({
                    id: 'marketing_dataset_' + item.actions.id,
                    text: item.name
                }))
            };
        },
        cache: true
    },
    placeholder: 'Select marketing dataset',
    minimumInputLength: 3
});
let selectedValue = 'marketing_dataset_<?= $details["marketing_dataset"] ?? '' ?>';

if (selectedValue.trim() !== 'marketing_dataset_') {
    $.ajax({
        url: '<?php echo site_url('admin/ajax_marketing_dataset'); ?>',
        dataType: 'json',
        data: { search: "<?= $details['marketing_dataset_name'] ?? '' ?>" },
        success: function (data) {
            let selectedText = "<?= $details['marketing_dataset_name'] ?? '' ?>";
            let option = new Option(selectedText, selectedValue, true, true);
            $('#marketing_dataset_query_name').append(option).trigger('change');
        }
    });
}

});

</script>
<script>
$(function() {
	$('#event_id').on('change', function() {
		let fd = new FormData();
		fd.append('event_id', $(this).val());

		const renderSelect = (type, placeholder, value = '') => {
			submitForm('<?php echo site_url('admin/ajax_'); ?>' + type, fd, json => {
				if (json && json.length > 0) {
					let html = '';

					html += '<option value="">' + placeholder + '</option>';

					json.map(data => {
						html += `<option value="${data.id}">${data.text}</option>`;
					});

					$('#' + type).html(html);
					$('#' + type).select2();

					if (value != '') {
						$('#' + type).val(value).trigger('change');
					}
				}
			});
		}

		renderSelect('event_state', '<?php _el('select_state'); ?>', <?php echo json_encode($details['filters']['state_id'] ?? []); ?>)
	});

	$('#event_id').trigger('change');
	$('#event_school_type').val(<?php echo json_encode($details['filters']['site_site_type'] ?? []); ?>).trigger('change');
});
</script>

<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.2'); ?>"></script>
