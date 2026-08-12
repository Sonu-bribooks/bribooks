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
						<label for="message"><?php echo _l('email'); ?></label>
						<input type="text" class="form-control" id="email" name="email" value="<?php echo $details['email'] ?? ''; ?>">
					</div>

                    <div class="form-group">
						<label for="message"><?php echo _l('mobile'); ?></label>
						<input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo $details['mobile'] ?? ''; ?>">
					</div>

                    <button type="button" class="btn btn-primary" id="subBtn" onclick="checkRequiredFields()"><?php echo _l('submit'); ?></button>
                    <button type="button" class="btn btn-dark" id="subBtn" onclick="history.back()"><?php echo _l('back'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
