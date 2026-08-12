<?php
$paypal_settings = $this->db->get_where('settings', array('key' => 'paypal'))->row()->value;
$paypal = json_decode($paypal_settings);
$stripe_settings = $this->db->get_where('settings', array('key' => 'stripe_keys'))->row()->value;
$stripe = json_decode($stripe_settings);
?>
<!-- start page title -->
<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('setup_currency_informations'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-md-7" style="padding: 0;">
        <!-- System Currency Settings -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><p><?php echo _l('system_currency_settings'); ?></p></h4>
                    <form class="" action="<?php echo site_url('admin/payment_settings/system_currency'); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><?php echo _l('system_currency'); ?></label>
                            <select class="form-control select2" data-toggle="select2" id = "system_currency" name="system_currency" required>
                                <option value=""><?php echo _l('select_system_currency'); ?></option>
                                <?php
                                $currencies = $this->crud_model->get_currencies();
                                foreach ($currencies as $currency):?>
                                <option value="<?php echo $currency['code'];?>"
                                    <?php if (get_settings('system_currency') == $currency['code'])echo 'selected';?>> <?php echo $currency['code'];?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('currency_position'); ?></label>
                        <select class="form-control select2" data-toggle="select2" id = "currency_position" name="currency_position" required>
                            <option value="left" <?php if (get_settings('currency_position') == 'left') echo 'selected';?> ><?php echo _l('left'); ?></option>
                            <option value="right" <?php if (get_settings('currency_position') == 'right') echo 'selected';?> ><?php echo _l('right'); ?></option>
                            <option value="left-space" <?php if (get_settings('currency_position') == 'left-space') echo 'selected';?> ><?php echo _l('left_with_a_space'); ?></option>
                            <option value="right-space" <?php if (get_settings('currency_position') == 'right-space') echo 'selected';?> ><?php echo _l('right_with_a_space'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                    <label><?php echo _l('exchange_rate'); ?></label>
                    <input type="text" name="exchange_rate" id="exchange_rate" class="form-control" placeholder="enter exchange rate" value="<?= get_settings('exchange_rate') ?>">
                    </div>
                    <div class="row justify-content-md-center">
                        <div class="form-group col-md-6">
                            <button class="btn btn-block btn-primary" type="submit"><?php echo _l('update_system_currency'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<?php if (0) { ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title"><p><?php echo _l('setup_paypal_settings'); ?></p></h4>
                <form class="" action="<?php echo site_url('admin/payment_settings/paypal_settings'); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label><?php echo _l('active'); ?></label>
                        <select class="form-control select2" data-toggle="select2" id = "paypal_active" name="paypal_active">
                            <option value="0" <?php if ($paypal[0]->active == 0) echo 'selected';?>> <?php echo _l('no');?></option>
                            <option value="1" <?php if ($paypal[0]->active == 1) echo 'selected';?>> <?php echo _l('yes');?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('mode'); ?></label>
                        <select class="form-control select2" data-toggle="select2" id = "paypal_mode" name="paypal_mode">
                            <option value="sandbox" <?php if ($paypal[0]->mode == 'sandbox') echo 'selected';?>> <?php echo _l('sandbox');?></option>
                            <option value="production" <?php if ($paypal[0]->mode == 'production') echo 'selected';?>> <?php echo _l('production');?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('paypal_currency'); ?></label>
                        <select class="form-control select2" data-toggle="select2" id = "paypal_currency" name="paypal_currency" required>
                            <option value=""><?php echo _l('select_paypal_currency'); ?></option>
                            <?php
                            $currencies = $this->crud_model->get_paypal_supported_currencies();
                            foreach ($currencies as $currency):?>
                            <option value="<?php echo $currency['code'];?>"
                                <?php if (get_settings('paypal_currency') == $currency['code'])echo 'selected';?>> <?php echo $currency['code'];?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo _l('client_id').' ('._l('sandbox').')'; ?></label>
                    <input type="text" name="sandbox_client_id" class="form-control" value="<?php echo $paypal[0]->sandbox_client_id;?>" required />
                </div>

                <div class="form-group">
                    <label><?php echo _l('client_id').' ('._l('production').')'; ?></label>
                    <input type="text" name="production_client_id" class="form-control" value="<?php echo $paypal[0]->production_client_id;?>" required />
                </div>

                <div class="row justify-content-md-center">
                    <div class="form-group col-md-6">
                        <button class="btn btn-block btn-primary" type="submit"><?php echo _l('update_paypal_keys'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title"><p><?php echo _l('setup_stripe_settings'); ?></p></h4>
            <form class="" action="<?php echo site_url('admin/payment_settings/stripe_settings'); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label><?php echo _l('active'); ?></label>
                    <select class="form-control select2" data-toggle="select2" id = "stripe_active" name="stripe_active">
                        <option value="0" <?php if ($stripe[0]->active == 0) echo 'selected';?>> <?php echo _l('no');?></option>
                        <option value="1" <?php if ($stripe[0]->active == 1) echo 'selected';?>> <?php echo _l('yes');?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo _l('test_mode'); ?></label>
                    <select class="form-control select2" data-toggle="select2" id = "testmode" name="testmode">
                        <option value="on" <?php if ($stripe[0]->testmode == 'on') echo 'selected';?>> <?php echo _l('on');?></option>
                        <option value="off" <?php if ($stripe[0]->testmode == 'off') echo 'selected';?>> <?php echo _l('off');?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo _l('stripe_currency'); ?></label>
                    <select class="form-control select2" data-toggle="select2" id = "stripe_currency" name="stripe_currency" required>
                        <option value=""><?php echo _l('select_stripe_currency'); ?></option>
                        <?php
                        $currencies = $this->crud_model->get_stripe_supported_currencies();
                        foreach ($currencies as $currency):?>
                        <option value="<?php echo $currency['code'];?>"
                            <?php if (get_settings('stripe_currency') == $currency['code'])echo 'selected';?>> <?php echo $currency['code'];?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><?php echo _l('test_secret_key'); ?></label>
                <input type="text" name="secret_key" class="form-control" value="<?php echo $stripe[0]->secret_key;?>" required />
            </div>

            <div class="form-group">
                <label><?php echo _l('test_public_key'); ?></label>
                <input type="text" name="public_key" class="form-control" value="<?php echo $stripe[0]->public_key;?>" required />
            </div>

            <div class="form-group">
                <label><?php echo _l('live_secret_key'); ?></label>
                <input type="text" name="secret_live_key" class="form-control" value="<?php echo $stripe[0]->secret_live_key;?>" required />
            </div>

            <div class="form-group">
                <label><?php echo _l('live_public_key'); ?></label>
                <input type="text" name="public_live_key" class="form-control" value="<?php echo $stripe[0]->public_live_key;?>" required />
            </div>

            <div class="row justify-content-md-center">
                <div class="form-group col-md-6">
                    <button class="btn btn-block btn-primary" type="submit"><?php echo _l('update_stripe_keys'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<?php } ?>

</div>
<div class="col-md-5">
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading"><?php echo _l('heads_up'); ?>!</h4>
        <p>.</p>
    </div>
</div>
</div>
