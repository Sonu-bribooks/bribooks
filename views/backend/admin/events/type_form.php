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
							<label><?php echo _l('event_name'); ?><span class="required">*</span></label>
							<input class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required/>
						</div>
						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
