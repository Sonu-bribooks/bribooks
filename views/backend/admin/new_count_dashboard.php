<style type="text/css">
.table td, .table th { padding: 0.25rem; }
.table tr { height: 50px; }
.card-body { padding-bottom: 5px; }
</style>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('count_dashboardd'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">

                <a href="<?= $action_filter ?>" class="btn btn-outline-primary btn-rounded m-1 <?php echo empty($site_code) ? 'active' : ''  ?>"></i> All</a>
                <a href="<?= $action_filter ?>/today" class="btn btn-outline-primary btn-rounded m-1 <?php echo ('today' == $site_code) ? 'active' : ''; ?>" style="margin-botton:2px"></i> Today</a>

			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<?php if(empty($site_code)){ ?>
<div class="row">
	<div class="col-xl-12">
		<div class="card" id='unpaid-instructor-revenue' style="overflow: auto;">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php _el('count_dashboard'); ?>
				</h4>
				<div class="table-responsive">
					<table class="table table-centered table-hover mb-0">
						<thead>
							<th><?php echo _l('event_name'); ?></th>
							<th><?php echo _l('total_registered_schools'); ?></th>
							<th><?php echo _l('new_registered_schools'); ?></th>
							<th><?php echo _l('total_registered_authors'); ?></th>
							<th><?php echo _l('new_registered_authors'); ?></th>
							<th><?php echo _l('old_registered_authors'); ?></th>
							<th><?php echo _l('books_written'); ?></th>
							<th><?php echo _l('books_published'); ?></th>
							<th><?php echo _l('books_ordered'); ?></th>
							<th><?php echo _l('orders'); ?></th>
						</thead>
						<tbody>
                            <?php foreach ($data as $key => $event_data) {  $endCount = count($data);?>
                                <tr>
                                    <td>
                                        <b><?php echo $event_data['event_name']; ?></b>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['all_school_register']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['school_register']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['all_users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['old_users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['books']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['publish_book']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['ordered_books']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['orders']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                </tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<?php if(!empty($site_code)){ ?>
<div class="row">
	<div class="col-xl-12">
		<div class="card" id='unpaid-instructor-revenue' style="overflow: auto;">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php _el('today_count_dashboard'); ?>
				</h4>
				<div class="table-responsive">
					<table class="table table-centered table-hover mb-0">
						<thead>
							<th><?php echo _l('event_name'); ?></th>
							<th><?php echo _l('today_total_registered_schools'); ?></th>
							<th><?php echo _l('today_new_registered_schools'); ?></th>
							<th><?php echo _l('today_total_registered_authors'); ?></th>
							<th><?php echo _l('today_new_registered_authors'); ?></th>
							<th><?php echo _l('today_old_registered_authors'); ?></th>
							<th><?php echo _l('today_books_written'); ?></th>
							<th><?php echo _l('today_books_published'); ?></th>
							<th><?php echo _l('today_books_ordered'); ?></th>
							<th><?php echo _l('today_orders'); ?></th>
						</thead>
						<tbody>
                            <?php foreach ($data as $key => $event_data) {  $endCount = count($data);?>
                                <tr>
                                    <td>
                                        <b><?php echo $event_data['event_name']; ?></b>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['all_school_register']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['school_register']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['all_users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['old_users']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['books']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['publish_book']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['ordered_books'] ?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                    <td>
                                        <?php echo ($key+1 == $endCount ? "<b>" : ""). ($event_data['orders']?? 0).($key+1 == $endCount ? "</b>" : ""); ?>
                                    </td>
                                </tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>


<div class="modal fade" id="reassign-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php _el('reassign_teacher'); ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>

			<div class="modal-body p-3">
				<form action="<?php echo site_url('admin/update_reassign'); ?>" method="post" id="form-status">
					<input type="hidden" name="schedule_id" value="" />
					<input type="hidden" name="original_teacher_id" value="" />

					<div class="table-responsive-sm">
						<table class="table table-striped table-centered">
							<tbody>
							</tbody>
						</table>
					</div>
				</form>

				<div class="text-right pt-2">
					<button
						type="button"
						class="btn btn-light"
						data-dismiss="modal"
					><?php _el('close'); ?>
					</button>
					<button
						onclick="setTimeout(function(){ $('#status-modal').modal('hide'); }, 2000)"
						type="submit"
						form="form-status"
						class="btn btn-primary ml-1 save"
					><?php _el('assign'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
