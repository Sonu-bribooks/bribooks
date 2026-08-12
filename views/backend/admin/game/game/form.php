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
						<label for="game_name"><?php echo _l('game_name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="game_name" name="game_name" value="<?php echo $details['game_name'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="start_time"><?php echo _l('start_time'); ?><span class="required">*</span></label>
						<input
							type="text"
							class="form-control datetimepicker"
							id="start_time"
							name="start_time"
							value="<?php echo date('m/d/y h:i A', strtotime($details['start_time'] ?? date('Y-m-d H:i:s'))); ?>"
							required
						>
					</div>

					<div class="form-group">
						<label for="end_time"><?php echo _l('end_time'); ?><span class="required">*</span></label>
						<input
							type="text"
							class="form-control datetimepicker"
							id="end_time"
							name="end_time"
							value="<?php echo date('m/d/y h:i A', strtotime($details['end_time'] ?? date('Y-m-d H:i:s'))); ?>" 
							required
						>
					</div>

					<div class="form-group">
						<label for="is-demo"><?php echo _l('status'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="game_status" id="game_status">
							<?php if (($details['game_status'] ?? 1)  == 1) { ?>
							<option value="0"><?php echo _l('disable'); ?></option>
							<option value="1" selected><?php echo _l('enable'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('disable'); ?></option>
							<option value="1"><?php echo _l('enable'); ?></option>
							<?php } ?>
						</select>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('submit'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
$(function() {
	$('.datetimepicker').datetimepicker({

	})
})
</script>
