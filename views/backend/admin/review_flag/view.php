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
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header">
				<h6><?=_l('Review_Id') ?> : <?php echo $info['id']; ?></h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th><?=_l('Boook_Name') ?></th>
							<th><?=_l('Reviewer_Name') ?></th>
							<th><?=_l('Review_Comment') ?></th>
						</tr>
						<tr>
							<td><?php echo $info['book']; ?></td>
							<td><?php echo $info['author']; ?></td>
							<td><?php echo $info['text']; ?></td>
						</tr>
					</table>
				</div>
			</div> <!-- end card body-->
		</div>
	</div>
</div>
<div class="row mb-3">
	<div class="col-md-6 col-sm-12 mb-3">
        <?php if (!empty($review_flags)) { foreach ($review_flags as $review_flag) { ?>
            <div class="card">
                <div class="card-header">
                    <h6><?= ucwords($review_flag['reporter_name']) . ' : ' . date('d M, Y', strtotime($review_flag['date_added'])) ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm table-centered mb-0">
                            <tr>
                                <th><?=_l('Flag_Type') ?></th>
                                <th><?=_l('Reason') ?></th>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo $review_flag['flag_type']; ?><br />
                                </td>
                                <td>
                                    <?php echo $review_flag['reason']; ?><br />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <?php }} ?>
	</div>
</div>