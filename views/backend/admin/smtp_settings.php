<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('smtp_settings'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('smtp_settings');?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/smtp_settings/update'); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="smtp_protocol"><?php echo get_phrase('protocol'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="protocol" id="smtp_protocol">
								<?php if (get_settings('protocol') == 'smtp') { ?>
								<option value="smtp" selected><?php echo get_phrase('smtp'); ?></option>
								<option value="mail"><?php echo get_phrase('mail'); ?></option>
								<?php } else { ?>
								<option value="smtp"><?php echo get_phrase('smtp'); ?></option>
								<option value="mail" selected><?php echo get_phrase('mail'); ?></option>
								<?php } ?>
							</select>
						</div>

                        <div class="form-group">
                            <label for="smtp_host"><?php echo get_phrase('smtp_host'); ?><span class="required">*</span></label>
                            <input type="text" name = "smtp_host" id = "smtp_host" class="form-control" value="<?php echo get_settings('smtp_host');  ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_port"><?php echo get_phrase('smtp_port'); ?><span class="required">*</span></label>
                            <input type="text" name = "smtp_port" id = "smtp_port" class="form-control" value="<?php echo get_settings('smtp_port');  ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_user"><?php echo get_phrase('smtp_username'); ?><span class="required">*</span></label>
                            <input type="text" name = "smtp_user" id = "smtp_user" class="form-control" value="<?php echo get_settings('smtp_user');  ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_pass"><?php echo get_phrase('smtp_password'); ?><span class="required">*</span></label>
                            <input type="password" name = "smtp_pass" id = "smtp_pass" class="form-control" value="<?php echo get_settings('smtp_pass');  ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_crypto"><?php echo get_phrase('smtp_crypto'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="smtp_crypto" id="smtp_crypto">
								<?php if (get_settings('smtp_crypto') == 'tls') { ?>
								<option value="tls" selected><?php echo get_phrase('tls'); ?></option>
								<option value="ssl"><?php echo get_phrase('ssl'); ?></option>
								<?php } else { ?>
								<option value="tls"><?php echo get_phrase('tls'); ?></option>
								<option value="ssl" selected><?php echo get_phrase('ssl'); ?></option>
								<?php } ?>
							</select>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo get_phrase('save'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
