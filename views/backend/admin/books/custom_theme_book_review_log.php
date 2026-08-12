<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                </h4>
            </div>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-6 col-xl-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">Custom Theme Book Review Logs</h4>
            </div>
            <div class="card-body">
                <ul>
                    <?php foreach ($histories as $key =>  $value) { ?>
                    <li>
                        <?=_li($value['comment'])?> --<b><?=formatDate($value['date_added'])?></b>
                    </li>
                    <?php } ?>
				</ul>
            </div> <!-- end card body-->
        </div>
    </div>
</div>
