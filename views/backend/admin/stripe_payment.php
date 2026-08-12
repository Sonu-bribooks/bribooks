<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> 
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=$page_title ?>
                </h4>
            </div> 
        </div> 
    </div>
</div>

</div>
<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?=$page_title ?></h4>

                    <form class="required-form" action="<?php echo site_url('admin/stripe_payment/update'); ?>" method="post" enctype="multipart/form-data">
                        
                        <!-- Country Selection Dropdown -->
                        <div class="form-group">
                            <label for="country"><?php echo get_phrase('Select Country'); ?></label>
                            <select class="form-control" id="country" name="country" required>
                                <option value="UAE" <?php echo ($payment_provider == 'stripe') ? 'selected' : '' ?>>UAE</option>
                                <option value="SG" <?php echo ($payment_provider == 'stripe_sg') ? 'selected' : '' ?>>Singapore</option>
                            </select>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo get_phrase('save'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
