<?php
$printed_status = array('/admin/in_print_order', '/admin/orders');
$role_id  =  $this->session->userdata('role_id');
$printer_list = $this->student_model->get_by_role_id_in([12, 15]);
$event_list = $this->event_model->get_all()['rows'] ?? [];
$site_list = $this->site_model->get_all(['site_codes' => PARENT_SITE_CODES])['rows'] ?? [];
$printer_assignment_list = $this->printer_assignment_model->get_all();
$states = $this->state_model->get_all(['country_id' => 1, 'sort' => 'state.name', 'order' => 'ASC']);
$countries = $this->country_model->get_all(['sort' => 'country.name', 'order' => 'ASC'])['rows'] ?? [];
?>
<style>
div.dataTables_wrapper div.dataTables_processing {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-align: center;
    padding: 1em 0;
    background-color: rgb(255 255 255 / 20%);
    display: flex;
    justify-content: center;
    align-items: center;
	width: unset;
	margin-left: unset;
    margin-top: unset;
}
.btn-group-sm>.btn, .btn-sm {
    padding: 0.15rem 0.4rem;
    font-weight: 700;
    font-size: .73rem;
    line-height: 1.3;
}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h5 class="page-title float-left">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h5>

				
				<button type="button" class="btn btn-primary alignToTitle" data-toggle="modal" data-target="#printerAssignModal">
					<?=_l('Assign_printer')?>
				</button>

				<div class="row">
					<div class="col-sm-8"></div>
					<div class="col-sm-4 alignToTitle text-right">
						<div class="input-group">
							<select name="bulk-send" id="bulk-send" class="form-control bulk-send">
								<option value=""><?=_l('select_bulk_action')?></option>
								<option value="2"><?=_l('send_to_print')?></option>
								<?php if (0) { ?><option value="8"><?=_l('mark_as_printed')?></option><?php } ?>
								<option value="21"><?=_l('move_to_afs')?></option>
								<option value="9"><?=_l('ready_to_ship')?></option>
								<option value="3"><?=_l('send_ship_now')?></option>
								<option value="4"><?=_l('complete_order')?></option>
								<option value="10"><?=_l('mark_as_reprint')?></option>
								<option value="15"><?=_l('mark_as_return')?></option>
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="bulk-action">
									<?=_l('apply')?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="printerAssignModal" tabindex="-1" role="dialog" aria-labelledby="printerAssignModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="printerAssignModalLabel"><?= _l('printer') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<select name="printer_id" id="printer_id" class="form-control select2 printer" data-toggle="select2" required>
					<option value=""><?=_l('select_printer')?></option>

					<?php foreach ($printer_list ?? [] as $key => $value) { ?>
						<option value="<?=$value['id']?>"><?=$value['first_name'] . ' ' . $value['last_name']?></option>
					<?php } ?>
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="button" class="btn btn-primary assign"><?=_l('save_changes')?></button>
			</div>
		</div>
	</div>
</div>

<!-- assign awb modal -->
<div class="modal fade" id="awbModal" role="dialog" aria-labelledby="awbModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="awbModalLabel"><?= _l('assign_awb') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_order_awb'); ?>" method="post" id="form-awb-order">
					<input type="hidden" name="order_id" value="" id="awb_order_id" />
					<div class="form-group">
						<label for="awb"><?php _el('awb'); ?></label>
						<input name="awb" class="form-control" placeholder="<?=_l('enter_awb')?>"/>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-awb-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<!-- comment Modal -->
<div class="modal fade" id="holdModal" role="dialog" aria-labelledby="holdModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="holdModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_order_comment'); ?>" method="post" id="form-hold-order">
					<input type="hidden" name="order_id" value="" id="order_id" />
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-hold-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<!-- cancel Modal -->
<div class="modal fade" id="cancelModal" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="cancelModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/ajax_cancel_order'); ?>" method="post" id="form-cancel-order">
					<input type="hidden" name="order_id" value="" id="cancel_order_id" />
					<div class="form-group">
						<label for="cancel_comment"><?php _el('comment_for_cancel_order'); ?></label>
						<textarea name="comment" id="cancel_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-cancel-order" class="btn btn-danger"><?=_l('cancel_order')?></button>
			</div>
		</div>
	</div>
</div>

<!-- escalate Modal -->
<div class="modal fade" id="escalateModal" role="dialog" aria-labelledby="escalateModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="escalateModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/escalate_order'); ?>" method="post" id="form-escalate-order">
					<input type="hidden" name="order_id" value="" id="escalate_order_id" />
					<div class="form-group">
						<label for="escalate_comment"><?php _el('comment_for_escalate_order'); ?></label>
						<textarea name="comment" id="escalate_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-escalate-order" class="btn btn-danger"><?=_l('escalate_order')?></button>
			</div>
		</div>
	</div>
</div>

<!-- escalate restore Modal -->
<div class="modal fade" id="escalateRestoreModal" role="dialog" aria-labelledby="escalateRestoreModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="escalateRestoreModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/escalate_restore_order'); ?>" method="post" id="form-escalate-restore-order">
					<input type="hidden" name="order_id" value="" id="escalate_restore_order_id" />
					<div class="form-group">
						<label for="escalate_restore_comment"><?php _el('comment_for_escalate_restore_order'); ?></label>
						<textarea name="comment" id="escalate_restore_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-escalate-restore-order" class="btn btn-danger"><?=_l('escalate_restore_order')?></button>
			</div>
		</div>
	</div>
</div>

<!-- reprint modal-->
<div class="modal fade" id="reprintModal" role="dialog" aria-labelledby="reprintModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="reprintModalLabel"><?= _l('reprint_order') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/reprint_order/2'); ?>" method="post" id="form-reprint-order">
					<input type="hidden" name="order_id" value="" />
					<div class="form-group form-check">
						<input type="checkbox" class="form-check-input" name="use_different_printer" id="use_different_printer" />
						<label class="form-check-label" id="use_different_printer"><?=_l('use_different_printer')?></label>
					</div>
					<div class="form-group" id="use_different_printer_div" style="display: none;">
						<label><?php _el('select_printer'); ?></label>
						<select name="printer_id" class="form-control select2 printer" data-toggle="select2" id="reprint_printer_id" required>
							<option value=""><?=_l('select_printer')?></option>

							<?php foreach ($printer_list ?? [] as $key => $value) { ?>
								<option value="<?=$value['id']?>"><?=$value['first_name'] . ' ' . $value['last_name']?></option>
							<?php } ?>
						</select>
					</div>
					<div id="reprint-form-content"></div>
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control" required></textarea>
					</div>
					<div class="form-group">
						<label for="order_history"><?php _el('add_to_order_history'); ?></label>
						<select name="order_history" id="order_history" class="form-control select2" data-toggle="select2">
							<option value="0"><?=_l('no')?></option>
							<option value="1"><?=_l('yes')?></option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-reprint-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<!-- change version modal-->
<div class="modal fade" id="changeVersionModal" role="dialog" aria-labelledby="changeVersionModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="changeVersionModalLabel"><?= _l('change_version') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/change_order_version'); ?>" method="post" id="form-change-version">
					<input type="hidden" name="order_id" value="" />
					<div id="change-version-form-content"></div>
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control" required></textarea>
					</div>
					<div class="form-group">
						<label for="order_history"><?php _el('add_to_order_history'); ?></label>
						<select name="order_history" id="order_history" class="form-control select2" data-toggle="select2">
							<option value="1"><?=_l('yes')?></option>
							<option value="0"><?=_l('no')?></option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-change-version" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<!-- auto escalate order comment Modal -->
<div class="modal fade" id="autoEscalateHoldModel" role="dialog" aria-labelledby="holdModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="holdModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_auto_escalate_order_comment'); ?>" method="post" id="form-hold-auto-escalate-order">
					<input type="hidden" name="auto_escalate_order_id" value="" id="auto_escalate_order_id" />
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control" id="auto_escalate_comment"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-hold-auto-escalate-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>
<div id="accordion">
	<div class="card mb-2">
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
				<form class="form" action="#" method="post" id="form-filter">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('order_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label for="book_id"><?php echo _l('select_book'); ?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="book_id" id="book_id">
									<option value=""><?php echo _l('select_a_book'); ?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_printer')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="assign_printer_id"
								>
									<option value=""><?=_l('all')?></option>
									<option value="NA"><?=_l('NA')?></option>
									<?php foreach ($printer_list ?? [] as $key => $value) { ?>
										<option value="<?=$value['id']?>"><?=$value['first_name'] . ' ' . $value['last_name']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('printing_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="printing_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('not_printed')?></option>
									<option value="1"><?=_l('printed')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('shiprocket_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="shipping_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('not_synced')?></option>
									<option value="1"><?=_l('synced')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('order_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('new')?></option>
									<option value="2"><?=_l('in_print')?></option>
									<option value="8"><?=_l('printed')?></option>
									<option value="9"><?=_l('ready_to_ship')?></option>
									<option value="3"><?=_l('shipped')?></option>
									<option value="4"><?=_l('delivered')?></option>
									<option value="10"><?=_l('reprint')?></option>
									<option value="15"><?=_l('return')?></option>
									<option value="21"><?=_l('afs')?></option>
									<option value="91"><?=_l('cancel')?></option>
									<option value="92"><?=_l('refunded')?></option>
									<option value="93"><?=_l('escalated')?></option>
									<option value="94"><?=_l('clone')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('order_status_not_equal_to')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="ne_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('new')?></option>
									<option value="2"><?=_l('in_print')?></option>
									<option value="8"><?=_l('printed')?></option>
									<option value="9"><?=_l('ready_to_ship')?></option>
									<option value="3"><?=_l('shipped')?></option>
									<option value="4"><?=_l('delivered')?></option>
									<option value="10"><?=_l('reprint')?></option>
									<option value="15"><?=_l('return')?></option>
									<option value="21"><?=_l('afs')?></option>
									<option value="91"><?=_l('cancel')?></option>
									<option value="92"><?=_l('refunded')?></option>
									<option value="93"><?=_l('escalated')?></option>
									<option value="94"><?=_l('clone')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('quantity_less_than')?></label>
								<input
									id="quantity_le"
									type="number"
									name="quantity_le"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('quantity_greater_than')?></label>
								<input
									id="quantity_ge"
									type="number"
									name="quantity_ge"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('has_stock')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="stock_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('not_in_stock')?></option>
									<option value="1"><?=_l('in_stock')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('has_isbn')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_isbn"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('assignment_code')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="assignment_code"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($printer_assignment_list['rows'] ?? [] as $value) { ?>
										<option value="<?=$value['code']?>"><?=$value['code']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('transaction_id')?></label>
								<input
									id="ext_transaction_id"
									type="text"
									name="ext_transaction_id"
									class="form-control input-filter"
									value=""
								>
							</div>
						</div>

						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('page_count_less_than')?></label>
								<input
									id="page_count_le"
									type="number"
									name="page_count_le"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('page_count_greater_than')?></label>
								<input
									id="page_count_ge"
									type="number"
									name="page_count_ge"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_event')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="event_id"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($event_list ?? [] as $key => $value) { ?>
										<option <?php if(!empty($event_id) && ($event_id == $value['id'])) { echo 'selected'; } ?> value="<?= $value['id']; ?>"><?= $value['name'] . ' (' . $value['id'] . ')'; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_site')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="site_code"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($site_list ?? [] as $key => $value) { ?>
										<option <?php if(!empty($site_code) && ($site_code == $value['site_code'])) { echo 'selected'; } ?> value="<?= $value['site_code']; ?>"><?= $value['name'] . ' ( ' . $value['site_code'] . ' )'; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_country')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="order_country"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($countries as $key => $country) { ?>
										<option value="<?=$country['name']?>"><?=$country['name']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_state')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="order_state"
									multiple
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($states['rows'] ?? [] as $key => $state) { ?>
										<option value="<?=$state['name']?>"><?=$state['name']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('customer_info')?></label>
								<input
									id="customer_info"
									type="text"
									name="customer_info"
									class="form-control input-filter"
									value=""
								>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button>
						</div>
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('search');?></button>
								<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="col-md-5 float-right">
					<?php if ($navigation == 'ge_nav' && $status == 9) { ?>
					<a href="<?=base_url('admin/download_global_manifest/true')?>" class="btn btn-info mt-2" id="download_manifest">
						<?=_l('download_manifest')?>
					</a>
					<?php } ?>
				</div>

				<div class="table-responsive">

					<?php include($navigation . '.php'); ?>

					<br />

					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?php echo _l('sn'); ?></th>
								<th><?php echo _l('order_code'); ?></th>
								<th><?php echo _l('customer'); ?></th>
								<th style="width: 160px;"><?php echo _l('product'); ?></th>
								<th><?php echo _l('weight_amount'); ?></th>
								<th><?php echo _l('history'); ?></th>
								<th><?php echo _l('order_date'); ?></th>
								<th><?php echo _l('printer'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
var table = null;

$(document).on('click', '.search', function(event) {
	event.preventDefault();
	var endDate = $('.end-date').val();
	var startDate = $('.start-date').val();

	table.ajax.url('<?= $action_ajax ?>?startdate=' + startDate + '&enddate=' + endDate).load();
});

$(function() {
	let columns_length = <?=in_array($this->session->userdata('role_id'), [1]) ? json_encode([10, 20, 50, 100, 200, 500, 1000]) : json_encode([10, 20, 50])?>;
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'#',
			'sn',
			'order_code',
			'customer',
			'product',
			'weight_amount',
			'history',
			'date_added',
			'printer',
			'actions'
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		'data': 'actions',
		render: callback
	});

	table = $('#ajax-datatable').DataTable({
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		'ajax': '<?php echo $action_ajax; ?>',
		'lengthMenu': columns_length,
		'processing': true,
		'serverSide': true,
		'order': [
			[0, 'desc']
		],
		'columns': columns,
		'language': {
			'loadingRecords': '&nbsp;',
			'processing': '<div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Loading...</span></div>'
		},
		'createdRow': function(row, data, dataIndex) {
            if(data.is_black_white) {
                $(row).css('background-color', '#c7c7c7');
            } else if(data.is_international) {
                $(row).css('background-color', '#fde0dc');
                /*$(row).css('border', '.13rem solid #000000');*/
            }
        }

	})
});

$(document).on('click', '.export-csv', function(event) {
	var endDate = $('.end-date').val();
	var startDate = $('.start-date').val();
	if (startDate == "") {
		alert('Kindly Fill start date')
	}
});

$('.select-all').click(function() {
	if (this.checked) {
		$(':checkbox').each(function() {
			$(this).prop('checked', true).trigger('change');
		});
	} else {
		$('.select-me').each(function() {
			$(this).prop('checked', false).trigger('change');
		});
	}
});

$(document).on('click', '.select-me', function(event) {
	if (this.checked) {
		$(this).prop('checked', true).trigger('change');
	} else {
		$(this).prop('checked', false).trigger('change');
	}
	$('.select-all').prop('checked', false).trigger('change');
});

$('#bulk-action').on('click', function(event) {
	event.preventDefault();

	var ids = [];
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
	});

	if (ids.length == 0) {
		error_notify('<?=_l('select_atleast_one_order')?>')
		return false;
	}

	let status = $('#bulk-send').val()

	if (confirm('<?=_l('Are you sure?')?>')) {
		$.ajax({
			url: '<?=base_url('admin/bulk_order_update')?>',
			type: 'POST',
			data: {
				ids: ids,
				status: status
			},
			cache: false,
			success: function(json) {
				table.ajax.reload(null, false);
				json.success && success_notify(json.success)
				json.error && error_notify(json.error)
			}
		});
	}
});

$(document).on('click', '.order-complete', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id') + '&status=' + $(this).data('orderstatus') + '&description=<?php echo _order_status(2); ?>',
			url: '<?=base_url('admin/order_history')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.ship-order', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id') + '&status=' + $(this).data('orderstatus') + '&description=<?php echo _order_status(1); ?>',
			url: '<?=base_url('admin/order_history')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.in-print', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id') + '&status=' + $(this).data('orderstatus') + '&description=<?php echo _order_status(0); ?>',
			url: '<?=base_url('admin/order_history')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$('.assign').click(function() {
	event.preventDefault();

	var ids = [];
	var quantity = 0;

	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
		quantity += parseInt($(this).data('qty'));
	});

	if (confirm('<?=_li('Are you sure? Total Copies ')?>' + quantity)) {
		$.ajax({
			url: '<?=base_url('admin/ajax_assign_order_to_printer/2')?>',
			type: 'POST',
			data: {
				ids: ids,
				printer_id: $('#printer_id').val()
			},
			cache: false,
			success: function(json) {
				table.ajax.reload(null, false);
				if (json.success) {
					success_notify(json.success);
					$('#printerAssignModal').modal('hide');
				}
			}
		});
	}
});

$(document).on('click', '.sync-order', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/ajax_sync_order')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				setTimeout(() => table.ajax.reload(null, false), 800);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.btn-readyship', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/ajax_move_to_ready_to_ship')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.btn-fetchawb', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/ajax_fetch_awb')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(function() {
	$(document).on('change', '#currency_code', function() {
		$el = $(this);
		table.ajax.url('<?= $action_ajax ?>?currency=' + $el.val()).load();
	})
});

$(function() {
	$(document).on('change', '#filter_printer', function() {
		$el = $(this);
		table.ajax.url('<?= $action_ajax ?>?filter_printer_id=' + $el.val()).load();
	})
});

$(function() {
	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
	})
});

$(function() {
	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= base_url('admin/export_orders/' . ($navigation == 'ge_nav' ? 2 : 47)); ?>/2?' + filters.join('&');
		}
	})
});

// $('#form-export').on('submit', function(e) {
// 	e.preventDefault();
// 	e.stopPropagation();
// 	$el = $(this);
// 	const fd = new FormData($el[0]);
// 	submitForm($el.attr('action'), fd, json => {
// 		console.log(json);
// 	});
// });

function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}

$(document).on('click', '.btn-hold', function() {
	$('#order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-cancel', function() {
	$('#cancel_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-escalate', function() {
	$('#escalate_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-escalate-restore', function() {
	$('#escalate_restore_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-refund', function(event) {
	if (confirm('Are you sure to refund the order?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id'),
			url: '<?=base_url('admin/refund_order')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$('#form-hold-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#holdModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$('#form-cancel-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#cancel_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#cancel_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_cancel_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to cancel the order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#cancelModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$('#form-escalate-restore-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#escalate_restore_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#escalate_restore_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_escalate_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to restore the escalated order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#escalateRestoreModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$('#form-escalate-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#escalate_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#escalate_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_escalate_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to escalate the order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#escalateModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.btn-awb-assign', function() {
	$('#awb_order_id').val($(this).data('id'));
	$('#awbModal').modal('show');
});

$('#form-awb-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#awbModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.btn-reprint', function() {
	$el = $(this);
	$('input[name=order_id]').val($el.data('id'));

	$.post('<?=base_url('admin/ajax_order_products')?>', {order_id: $el.data('id')}, json => {
		if (json.products) {
			const html = json.products.map((item, index) => '<label><?=_l('product')?> #' + (index + 1) + ': ' + item.name + '</label><input type="text" class="form-control" name="product[' + item.product_id + ']" value="' + item.quantity + '"/>');
			$('#reprint-form-content').html(html.join());
			$('#reprint_printer_id').val(json.printer_id).trigger('change');
		}
	});
});

$('#form-reprint-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#reprintModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.change-order-version', function() {
	$el = $(this);
	$('input[name=order_id]').val($el.data('id'));

	$.post('<?=base_url('admin/ajax_order_products')?>', {order_id: $el.data('id')}, json => {
		if (json.products) {
			const html = json.products.map((item, index) => {
				let html = '<label><?=_l('product')?> #' + (index + 1) + ': ' + item.name + '</label>';
				let versions = item.versions.map(version => `<option value="${version}" ${item.version == version ? 'selected' : ''}>${version}</option>`)
				versions = versions.join('')
				html += `<select class="form-control" name="product[${item.product_id}]">${versions}</select>`;

				return html;
			});
			$('#change-version-form-content').html(html.join());
			$('#changeVersionModal').modal('show');
		}
	});
});

$('#form-change-version').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#changeVersionModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(function() {
	$('#book_id').select2({
		ajax: {
			url: '<?php echo site_url('admin/ajax_filter_books'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		}
	});
});

$(document).on('click', '[data-copy]', function() {
	navigator.clipboard.writeText($(this).data('copy'));
});

$(function() {
	$('#use_different_printer').on('change', function() {
		if ($(this).prop('checked')) {
			$('#use_different_printer_div').show();
		} else {
			$('#use_different_printer_div').hide();
		}
	});
	$('#use_different_printer').trigger('change');
});

function filterByBookSlug(slug) {
	table.ajax.url('<?= $action_ajax ?>?book_slug=' + slug).load();
}

function filterByBookIsbn(isbn) {
	table.ajax.url('<?= $action_ajax ?>?book_isbn=' + isbn).load();
}

function removeNotification(event_id) {
	$.post('<?php echo base_url('admin/removeNotification'); ?>', {event_id: event_id}, function(json) {
		/*console.log(json);*/
	});
}

$(function () {
	var source = new EventSource('<?php echo base_url('admin/getNotification'); ?>');

	source.addEventListener('order', function (e) {
		var json = JSON.parse(e.data);

		if (json.event_id) {
			console.log(json)
			json.order[0]?.slug && filterByBookSlug(json.order[0]?.slug);
			json.order[0]?.isbn && filterByBookIsbn(json.order[0]?.isbn);
			removeNotification(json.event_id);
		}
	}, false);
});

$(document).on('click', '.btn-stockfulfillment', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/ajax_book_stock_fulfillment')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.closeAutoEscalateOrder', function() {
	if (confirm('Are you sure you want to close?')) {
		const fd = new FormData();
		fd.append('auto_escalate_order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/ajax_auto_escalate_order_close')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.autoEscalateOrderComment', function() {
	$('#auto_escalate_order_id').val($(this).data('id'));
	$("#auto_escalate_comment").val($(this).data('comment'))
});

$('#form-hold-auto-escalate-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#autoEscalateHoldModel').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});
</script>
