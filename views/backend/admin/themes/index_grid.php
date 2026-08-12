<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/add_theme'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i> Add New</a>
                </h4>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php
    if (!empty($themes['rows'])) {
        foreach ($themes['rows'] as $theme) {
    ?>
            <div class="col-md-4 col-lg-4 col-xl-4 on-hover-action" id="<?= $theme['id']; ?>">
                <div class="card d-block">
                    <img class="card-img-top" src="<?php echo $theme['image']; ?>" alt="<?php echo $theme['name']; ?>">
                    <div class="card-body">
                        <h4 class="card-title mb-0"><i class=""></i> <?php //echo $theme['name']; ?></h4>
                        <small style="font-style: italic;">
                            <p class="card-text"><?php echo $theme['category']; ?></p>
                        </small>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo site_url('admin/themes/edit_theme/'.$theme['id']); ?>" class="btn btn-icon btn-outline-info btn-sm" id="theme-edit-btn-<?= $theme['id']; ?>" style="display: none; margin-right:5px;">
                            <i class="mdi mdi-wrench"></i> <?php echo get_phrase('edit'); ?>
                        </a>
                        <a href="#" class="btn btn-icon btn-outline-danger btn-sm" id="theme-delete-btn-<?= $theme['id']; ?>" style="float: right; display: none; margin-right:5px;">
                            <i class="mdi mdi-delete"></i> <?php echo get_phrase('delete'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="col-sm-12">
            <h4>No Record Found...</h4>
        </div>
    <?php } ?>
</div>

<script type="text/javascript">
    $('.on-hover-action').mouseenter(function() {
        var id = this.id;
        $('#theme-delete-btn-' + id).show();
        $('#theme-edit-btn-' + id).show();
    });
    $('.on-hover-action').mouseleave(function() {
        var id = this.id;
        $('#theme-delete-btn-' + id).hide();
        $('#theme-edit-btn-' + id).hide();
    });
</script>