<meta http-equiv="refresh" content="20">
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <div class="col-md-3 float-right">
                        <select class="form-control select2" id="site_code" data-toggle="select2" onchange="window.location='<?= $action_filter ?>/' + this.value">
                            <option value="" selected><?=_l('all')?></option>
                            <?php foreach ($this->site_model->get_all([
                                'status'        => 1,
                                'site_codes' => EVENT_BASE_PARENT_SITE_CODES
                            ])['rows'] ?? [] as $site) { ?>
                            <option value="<?php echo $site['site_code']; ?>" <?php echo ($site['site_code'] == $site_code) ? 'selected' : ''; ?>><?php echo $site['name'] . ' ( ' . $site['site_code'] . ' )'; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div id="accordion">
    <div class="card">
        <div class="card-header" id="heading-1">
            <h5 class="m-0">
                <a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
                    <?=_l('filters')?>
                </a>

                <a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
                    <i class="dripicons-view-apps"></i>
                </a>
            </h5>
        </div>
        <div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
            <div class="card-body">
                <form class="form" action="javascript:;" method="post" id="form-filter">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-2">
                                <label><?=_l('date')?></label>
                                <div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue" data-cancel-class="btn-light" style="width: 100%;">
                                    <i class="mdi mdi-calendar"></i>&nbsp;
                                    <span id="selectedValue" class="selectedValue">
                                        <?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y");?>
                                    </span> <i class="mdi mdi-menu-down"></i>
                                </div>
                                <input
                                    id="date_range1"
                                    type="hidden"
                                    name="date_range"
                                    class="input-filter date_range"
                                    value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y");?>"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 text-right">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('search');?></button>
                            <button type="button" class="btn btn-danger ml-2" id="reset-button"> <?php echo _l('reset');?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 col-xl-6">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="all_registrations"><?= !empty($data['all_school_register']) ? $data['all_school_register'] : 0; ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('total_registered_schools'); ?></p>
                                    <small class="text-success"><b id="all_new_registrations"><?= $data['all_new_school_register'] ?? 0 ?></b> <?php echo _l('today_registrations'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-6">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="registrations"><?= !empty($data['school_register']) ? ($data['school_register'] - $self_sites) : 0; ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('new_registered_schools'); ?></p>
                                    <small class="text-success"><b id="new_registrations"><?= $data['new_school_register'] ?? 0 ?></b> <?php echo _l('today_registrations'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>

<?php if(in_array(strtolower($site_code), ['in-nyaf23'])) { ?>
<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 col-xl-12">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="all_registrations_by_default"><?= !empty($data['all_new_student_register_in_school']) ? $data['all_new_student_register_in_school'] : 0; ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('old_registered_schools'); ?></p>
                                    <small class="text-success"><b id="new_school_register_by_default"><?= $data['new_student_register_in_school'] ?? 0 ?></b> <?php echo _l('today_registrations'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>
<?php } ?>

<?php if(in_array(strtolower($site_code), ['ge-nyafus'])) { ?>
<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 col-xl-6">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="all_registrations_by_default"><?= !empty($data['all_new_school_register_by_default']) ? $data['all_new_school_register_by_default'] : 0; ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('total_registered_schools_by_default'); ?></p>
                                    <small class="text-success"><b id="new_school_register_by_default"><?= $data['new_school_register_by_default'] ?? 0 ?></b> <?php echo _l('today_registrations'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-6">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="all_registrations_by_schoolv2"><?= !empty($data['all_new_school_register_by_schoolv2']) ? $data['all_new_school_register_by_schoolv2'] : 0; ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('total_registered_schools_by_schoolv2'); ?></p>
                                    <small class="text-success"><b id="new_school_register_by_schoolv2"><?= $data['new_school_register_by_schoolv2'] ?? 0 ?></b> <?php echo _l('today_registrations'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>
<?php } ?>

<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 col-xl-4">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-bookmark text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="books"><?= $data['all_users'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('total_registered_authors'); ?></p>
                                    <small class="text-success"><b id="all_published_books"><?= $data['all_new_users'] ?? 0 ?></b> <?php echo _l('today_registered_authors'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-bookmark text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="books"><?= $data['users'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('new_registered_authors'); ?></p>
                                    <small class="text-success"><b id="published_books"><?= $data['new_users'] ?? 0 ?></b> <?php echo _l('today_registered_authors'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-4">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-bookmark text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="books"><?= $data['old_users'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('old_registered_authors'); ?></p>
                                    <small class="text-success"><b id="published_books"><?= $data['old_new_users'] ?? 0 ?></b> <?php echo _l('today_registered_authors'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>

<div class="row">
    <div class="col-12">
        <div class="card widget-inline">
            <div class="card-body p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 col-xl-3">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0">
                                <div class="card-body text-center">
                                    <i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="users"><?= $data['books'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('books_written'); ?></p>
                                    <small class="text-success"><b id="paid_users"><?= $data['new_books'] ?? 0 ?></b> <?php echo _l('today_books'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- <div class="col-sm-6 col-xl-3">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="orders"><?= $data['publish_book'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('publish_book'); ?></p>
                                    <small class="text-success"><b id="new_orders"><?= $data['publish_book'] ?? 0 ?></b> <?php echo _l('publish_book'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div> -->
                    <div class="col-sm-6 col-xl-3">
                        <a href="<?php ?>" class="text-secondary">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="orders"><?= $data['publish_book'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('books_published'); ?></p>
                                    <small class="text-success"><b id="new_orders"><?= $data['new_publish_book'] ?? 0 ?></b> <?php echo _l('today_published_book'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="orders"><?= $data['ordered_books'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('books_ordered'); ?></p>
                                    <small class="text-success"><b id="new_orders"><?= $data['new_ordered_books'] ?? 0 ?></b> <?php echo _l('today_books_ordered'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <a href="<?php echo $order_url ?? ''; ?>" class="text-secondary" target="_blank">
                            <div class="card shadow-none m-0 border-left">
                                <div class="card-body text-center">
                                    <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                    <h3><span id="orders"><?= $data['orders'] ?? 0 ?></span></h3>
                                    <p class="text-muted font-15 mb-0"><?php echo _l('orders'); ?></p>
                                    <small class="text-success"><b id="new_orders"><?= $data['new_orders'] ?? 0 ?></b> <?php echo _l('today_orders'); ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row -->
            </div>
        </div> <!-- end card-box-->
    </div> <!-- end col-->
</div>

<script>
function update_date_range() {
    var x = $('.selectedValue').html();
    $('.date_range').val(x);
}

$(function() {
    $(document).on('click', '#reset-button', function(e) {
        var site_code = ($('#site_code').val() != '') ? $('#site_code').val() : 'all';
        window.location='<?= $action_filter; ?>' + '/' + site_code;
    })

    $(document).on('submit', '#form-filter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $el = $(this);
        let filters = [];
        $el.find('.input-filter').each(function() {
            // console.log($(this).attr('name') + '=' + $(this).val().trim());
            filters.push($(this).attr('name') + '=' + $(this).val().trim());
        });

        var site_code = ($('#site_code').val() != '') ? $('#site_code').val() : 'all';
        window.location='<?= $action_filter; ?>' + '/' + site_code + '?' + filters.join('&');
    })
});
</script>
