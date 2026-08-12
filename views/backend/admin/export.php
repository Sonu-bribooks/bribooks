<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('export'); ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php echo _l('export'); ?>
				</h4>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">
										<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-import" class="form-horizontal">
											<div class="form-group">
												<div class="row">
													<label class="col-sm-2 control-label text-right" for="input-type"><?php _el('type'); ?></label>
													<div class="col-sm-10">
														<select name="type" id="input-type" class="form-control">
															<?php foreach ($types as $type_i) { ?>
															<?php if ($type_i['code'] == $type) { ?>
															<option value="<?php echo $type_i['code']; ?>" selected="selected"><?php echo $type_i['value']; ?></option>
															<?php } else { ?>
															<option value="<?php echo $type_i['code']; ?>"><?php echo $type_i['value']; ?></option>
															<?php } ?>
															<?php } ?>
														</select>
													</div>
												</div>
											</div>

											<div class="clearfix">
												<button type="submit" class="btn btn-primary float-right"><?php _el('continue'); ?></button>
											</div>
										</form>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
