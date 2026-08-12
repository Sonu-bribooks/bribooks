
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
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="code"><?php echo _l('code'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="code" name="code" value="<?php echo $details['code'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="lang_code"><?php echo _l('language_code'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="lang_code" name="lang_code" value="<?php echo $details['lang_code'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="code"><?php echo _l('telephone_code'); ?><span class="required">* <?php _el('include +'); ?></span></label>
							<input type="text" class="form-control" id="tel_code" name="tel_code" value="<?php echo $details['tel_code'] ?? ''; ?>" required>
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
