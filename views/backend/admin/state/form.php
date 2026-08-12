
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?= $page_title ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="country"><?php echo _l('country'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="country_id" id="country">
								<?php foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
								<?php if (($details['country_id'] ?? 'IN') === $country['code']) { ?>
								<option value="<?php echo $country['id']; ?>" selected><?php echo $country['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $country['id']; ?>"><?php echo $country['name']; ?></option>
								<?php } ?>
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
