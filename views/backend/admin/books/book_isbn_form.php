
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
							<label for="isbn"><?php echo _l('ISBN'); ?></label>
							<input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo $details['isbn'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="amazon_url"><?php echo _l('amazon_url'); ?></label>
							<input type="text" class="form-control" id="amazon_url" name="amazon_url" value="<?php echo $details['amazon_url'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="amazon_price"><?php echo _l('amazon_price'); ?></label>
							<input type="text" class="form-control" id="amazon_price" name="amazon_price" value="<?php echo $details['amazon_price'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('featured'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="featured" id="featured">
								<?php if (($details['featured'] ?? '')) { ?>
								<option value="1" selected><?php echo _l('yes'); ?></option>
								<option value="0"><?php echo _l('no'); ?></option>
								<?php } else { ?>
								<option value="1"><?php echo _l('yes'); ?></option>
								<option value="0" selected><?php echo _l('no'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="isbn_country_code"><?php echo _l('isbn_country'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="isbn_country_code" id="isbn_country_code">
								<?php foreach ($this->country_model->get_all() ?? [] as $country) { ?>
								<?php if (($details['isbn_country_code'] ?? 'IN') === $country['code']) { ?>
								<option value="<?php echo $country['code']; ?>" selected><?php echo $country['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
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
