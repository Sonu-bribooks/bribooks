<style type="text/css">
	div.form-group label:first-child {
		font-weight: 700;
	}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="logo-image"><?php echo _l('logo'); ?></label>
							<div class="input-group">
								<a
									href="<?php echo $this->image_model->resize(!empty($details['logo']) ? ($this->config->item('s3_base_url') . $details['logo']) : 'no_image.png', 100, 100) ?>"
									id="logo-thumb-image"
									data-toggle="image"
									class="img-thumbnail"
									data-target="#logo-image"
								>
									<img
										src="<?php echo $this->image_model->resize(!empty($details['logo']) ? ($this->config->item('s3_base_url') . $details['logo']) : 'no_image.png', 100, 100) ?>"
										alt="" title=""
										data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
										style="width:100px; height:100px;"
									/>
								</a>
								<input
									type="hidden"
									name="logo"
									value="<?php echo !empty($details['logo']) ? $this->config->item('s3_base_url') . $details['logo'] : ''; ?>"
									id="logo-image"
								/>
							</div>
						</div>

						<div class="form-group">
							<label><?php echo _l('awards_content_(3_column_x_2_row)'); ?></label>
							<?php for ($i=0; $i<6; $i++) {
								$k = $i+1;
								$awards_content = !empty($details['awards_content']) ? json_decode($details['awards_content'], 1) : [];
							?>
							<div class="form-group">
								<?php if(empty($awards_content[$i]['status'])) { ?>
								<label><input type="checkbox" name="awards_content[<?= $i; ?>][status]" value="1"> <?php echo _l('enable_awards_content_for_'.$k); ?></label>
								<?php } else { ?>
								<label><input type="checkbox" name="awards_content[<?= $i; ?>][status]" value="1" checked="checked"> <?php echo _l('enable_awards_content_for_'.$k); ?></label>
								<?php } ?>
								<br />

								<label for="thumb-image-<?= $i; ?>"><?php echo _l('image_'.$k); ?></label>
								<div class="input-group">
									<a
										href="<?php echo $this->image_model->resize(!empty($awards_content[$i]['image']) ? ($this->config->item('s3_base_url') . $awards_content[$i]['image']) : 'no_image.png', 100, 100) ?>"
										id="awards-thumb-image"
										data-toggle="image"
										class="img-thumbnail"
										data-target="#thumb-image-<?= $i; ?>"
									>
										<img
											src="<?php echo $this->image_model->resize(!empty($awards_content[$i]['image']) ? ($this->config->item('s3_base_url') . $awards_content[$i]['image']) : 'no_image.png', 100, 100) ?>"
											alt="" title=""
											data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
											style="width:100px; height:100px;"
										/>
									</a>
									<input
										type="hidden"
										name="awards_content[<?= $i; ?>][image]"
										value="<?php echo !empty($awards_content[$i]['image']) ? $this->config->item('s3_base_url') . $awards_content[$i]['image'] : ''; ?>"
										id="thumb-image-<?= $i; ?>"
									/>
								</div>

								<label><?php echo _l('text_'.$k); ?></label>
								<textarea rows="2" class="form-control" name="awards_content[<?= $i; ?>][text]"><?php echo $awards_content[$i]['text'] ?? ''; ?></textarea>

								<label><?php echo _l('qualifier_text_'.$k); ?></label>
								<textarea rows="2" class="form-control" name="awards_content[<?= $i; ?>][qualifier_text]"><?php echo $awards_content[$i]['qualifier_text'] ?? ''; ?></textarea>
							</div>
							<?php } ?>
						</div>

						<!-- <div class="form-group">
							<label><?php echo _l('event_name'); ?><span class="required">*</span></label>
							<input class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('parent_site'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="parent_site_id" id="parent_site_id" required>
								<option value=""><?php _el('select'); ?></option>
								<?php foreach ($this->site_model->get_all(['parent_ids' => [0,1,2]])['rows'] ?? [] as $site) { ?>
									<?php if (($details['parent_site_id'] ?? '') === $site['id']) { ?>
										<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label><?php echo _l('country'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="country_code" id="country_code" required>
								<option value=""><?php _el('select'); ?></option>
								<?php foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
									<?php if (($details['country_code'] ?? '') === $country['code']) { ?>
										<option value="<?php echo $country['code']; ?>" selected><?php echo $country['name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label><?php echo _l('currency'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" id="currency_code" name="currency_code" required>
								<option value=""><?php echo _l('select'); ?></option>
								<?php
								$currencies = $this->crud_model->get_currencies();
								foreach ($currencies as $currency) : ?>
									<option value="<?php echo $currency['code']; ?>" <?php if (($details['currency_code'] ?? '') === $currency['code']) echo 'selected'; ?>> <?php echo $currency['code']; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_type'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="event_type" id="event_type" required>
								<option value=""><?php _el('select'); ?></option>
								<?php foreach (EVENT_TYPE ?? [] as $key => $event_type) {
									if (($details['event_type'] ?? '') === $key) {
								?>
								<option value="<?php echo $key; ?>" selected><?php echo $event_type; ?></option>
								<?php } else { ?>
								<option value="<?php echo $key; ?>"><?php echo $event_type; ?></option>
								<?php } } ?>
							</select>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_template'); ?><span class="required">*</span></label>
							<?php $i=0;
								foreach (EVENT_TEMPLATE ?? [] as $key => $event_template) {
								if (($details['event_template'] ?? 'nyaf') === $key) {
							?>
							<br /><label><input type="radio" name="event_template" value="<?php echo $key; ?>" checked="checked" <?php echo ($i==0) ? 'required' : ''; ?>> <a target="_blank" href="<?= base_url('assets/images/event_templates/'.$key.'.jpeg'); ?>"><img src="<?= base_url('assets/images/event_templates/'.$key.'.jpeg'); ?>" alt="Image" width="160" height="240" style="border: 2px solid black;" /> <?php echo $event_template; ?></a></label>
							<?php } else { ?>
							<br /><label><input type="radio" name="event_template" value="<?php echo $key; ?>" <?php echo ($i==0) ? 'required' : ''; ?>> <a target="_blank" href="<?= base_url('assets/images/event_templates/'.$key.'.jpeg'); ?>"><img src="<?= base_url('assets/images/event_templates/'.$key.'.jpeg'); ?>" alt="Image" width="160" height="240" style="border: 2px solid black;" /> <?php echo $event_template; ?></a></label>
							<?php } $i++; } ?>
						</div>
						<div class="form-group">
							<label><?php echo _l('sub_event'); ?></label>
							<?php $sub_events = !empty($details['sub_event']) ? explode(",", $details['sub_event']) : [];
								foreach (SUB_EVENT ?? [] as $key => $sub_event) {
								if ($sub_events && in_array($key, $sub_events)) {
							?>
							<br /><label><input type="checkbox" name="sub_event[]" value="<?php echo $key; ?>" checked="checked"> <?php echo $sub_event; ?></label>
							<?php } else { ?>
							<br /><label><input type="checkbox" name="sub_event[]" value="<?php echo $key; ?>"> <?php echo $sub_event; ?></label>
							<?php } } ?>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_start_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="start_date" name="start_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['start_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="end_date" name="end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('school_registration_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="school_reg_end_date" name="school_reg_end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['school_reg_end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('student_registration_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="student_reg_end_date" name="student_reg_end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['student_reg_end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('writing_&_publishing_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="book_writing_end_date" name="book_writing_end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['book_writing_end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('promotion_&_sales_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="selling_end_date" name="selling_end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['selling_end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('url'); ?></label>
							<input class="form-control" id="url" name="url" value="<?php echo $details['url'] ?? ''; ?>" />
						</div>
						<div class="form-group">
							<label><?php echo _l('rank_url'); ?></label>
							<input class="form-control" id="rank_url" name="rank_url" value="<?php echo $details['rank_url'] ?? ''; ?>" />
						</div> -->
						<div class="form-group">
							<label><?php echo _l('status'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="status" id="status">
								<?php if (($details['status'] ?? '')) { ?>
									<option value="1" selected><?php echo _l('enabled'); ?></option>
									<option value="0"><?php echo _l('disabled'); ?></option>
								<?php } else { ?>
									<option value="1"><?php echo _l('enabled'); ?></option>
									<option value="0" selected><?php echo _l('disabled'); ?></option>
								<?php } ?>
							</select>
						</div>
						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
	window['FILEMANAGER'] = '<?php echo base_url('servermanager'); ?>';
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
});
</script>

<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.2'); ?>"></script>
