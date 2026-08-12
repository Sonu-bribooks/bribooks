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
							<label><?php echo _l('event_type'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="event_type" id="event_type" required>
								<option value=""><?php _el('select'); ?></option>
								<?php foreach ($event_types ?? [] as $key => $event_type) {
									if (($details['event_type'] ?? '') == $event_type['id']) {
								?>
								<option value="<?php echo $event_type['id']; ?>" selected><?php echo $event_type['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $event_type['id']; ?>"><?php echo $event_type['name']; ?></option>
								<?php } } ?>
							</select>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_name'); ?><span class="required">*</span></label>
							<input class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required/>
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
							<label><?php echo _l('event_start_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="start_date" name="start_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['start_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php echo _l('event_end_date'); ?><span class="required">*</span></label>
							<input class="form-control datetimepicker" id="end_date" name="end_date" value="<?php echo date('m/d/Y h:i A', strtotime($details['end_date'] ?? date('m/d/Y 00:00:00', strtotime('+1 day')))); ?>" required/>
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
							<input class="form-control" id="url" name="url" value="<?php echo $details['url'] ?? 'event.bribooks.com/'; ?>" />
						</div>
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
