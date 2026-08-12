<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> 
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> 
                    <?php echo $page_title; ?>
                </h4>
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

                    <form class="required-form" id="dynamic-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
                        <div class="row key-value-pair">
                            <div class="form-group col-md-6">
                                <label for="key"><?php echo _l('key'); ?><span class="required">*</span></label>
                                <input type="text" class="form-control" name="key[]" value="<?php echo $details['key'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="value"><?php echo _l('value'); ?><span class="required">*</span></label>
                                <input type="text" class="form-control" name="value[]" value="<?php echo $details['value'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div id="dynamic-fields"></div>
                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()">
                            <?php echo _l('submit'); ?>
                        </button>
                        <?php if (empty($details)) { ?>
                            <button type="button" class="btn btn-secondary float-right" id="add-more">
                                <?php echo _l('add_more'); ?>
                            </button>
                        <?php } ?>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('add-more').addEventListener('click', function() {
        var newField = document.createElement('div');
        newField.classList.add('row', 'key-value-pair');

        newField.innerHTML = `
            <div class="form-group col-md-6">
                <label for="key"><?php echo _l('key'); ?><span class="required">*</span></label>
                <input type="text" class="form-control" name="key[]" required>
            </div>
            <div class="form-group col-md-6">
                <label for="value"><?php echo _l('value'); ?><span class="required">*</span></label>
                <input type="text" class="form-control" name="value[]" required>
            </div>
        `;

        document.getElementById('dynamic-fields').appendChild(newField);
    });
</script>
