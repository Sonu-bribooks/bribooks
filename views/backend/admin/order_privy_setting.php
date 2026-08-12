<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('order_privy_setting'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('order_privy_setting');?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/order_privy_setting/update'); ?>" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="smtp_host"><?php echo get_phrase('order_privy_limit'); ?><span class="required">*</span></label>
                            <input type="text" name = "order_privy" id = "order_privy" class="form-control" value="<?=$order_privy_value?>" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_port"><?php echo get_phrase('alert_emails'); ?><span class="required">*</span></label>
                            <textarea name = "order_privy_alert" id = "order_privy_alert" class="form-control" rows="5" cols="5" required><?=$order_privy_alert?></textarea>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo get_phrase('save'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
