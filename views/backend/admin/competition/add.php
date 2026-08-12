<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                    <a href="<?php echo site_url('admin/competition'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i> Back</a>
                </h4>
            </div>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-6 col-xl-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Add Competition</h4>
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/save_competition/'); ?><?= (!empty($competitionInfo)) ? $competitionInfo['id'] : ''; ?>" method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="name">Competition Name<span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required="" value="<?= (!empty($competitionInfo)) ? $competitionInfo['name'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="name">User Limit<span class="required">*</span></label>
                        <input type="text" class="form-control" id="user_limit" name="user_limit" required="" value="<?= (!empty($competitionInfo)) ? $competitionInfo['limit'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="name">Site<span class="required">*</span></label>
                        <select class="form-control" name="site">
                            <option value="">Select Site</option>
                            <option value="1" <?= (!empty($competitionInfo['site_id']) && $competitionInfo['site_id']=='1') ?  'selected': ''; ?>>India</option>
                            <option value="2" <?= (!empty($competitionInfo['site_id']) && $competitionInfo['site_id']=='2') ?  'selected': ''; ?>>Global</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Subscriptions Plan<span class="required">*</span></label>
                        <select class="form-control" name="subscriptions">
                            <option value="">Select Subscriptions</option>
                            <?php
                                if(!empty($subscriptions['rows'])){
                                    foreach($subscriptions['rows'] as $sub){
                            ?>
                            <option value="<?= $sub['id']; ?>" <?= (!empty($competitionInfo['subscription_plan_id']) && $competitionInfo['subscription_plan_id']==$sub['id']) ?  'selected': ''; ?>><?= $sub['name']; ?> - <?= ($sub['site_id']==1)?'India':'Global'; ?>(<?= $sub['price']; ?>)</option>
                            <?php } }?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">Price<span class="required">*</span></label>
                        <input type="text" class="form-control" id="Price" name="price" required="" value="<?= (!empty($competitionInfo['price'])) ? $competitionInfo['price'] : '0'; ?>">
                    </div>

                    <div class="form-group">
                        <label for="name">Start Date<span class="required">*</span></label>
                        <input type="text" class="form-control" id="start_date" name="start_date" required="" value="<?= (!empty($competitionInfo)) ? $competitionInfo['start_date'] : date('Y-m-d H:i:s'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="name">End Date<span class="required">*</span></label>
                        <input type="text" class="form-control" id="end_date" name="end_date" required="" value="<?= (!empty($competitionInfo)) ? $competitionInfo['end_date'] : date('Y-m-d H:i:s'); ?>">
                    </div>

                    <div class="form-group" id="thumbnail-picker-area">
                        <label> Status</label>
                        <select class="form-control" name="status">
                            <option value="1" <?= (!empty($coverInfo) && $coverInfo->status == '1') ? 'selected="selected"' : ''; ?>>Enable</option>
                            <option value="0" <?= (!empty($coverInfo) && $coverInfo->status == '0') ? 'selected="selected"' : ''; ?>>Disable</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div> <!-- end card body-->
        </div>
    </div>
</div>
