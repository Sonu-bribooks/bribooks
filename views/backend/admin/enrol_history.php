<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('enrol_history'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('enrol_histories'); ?></h4>
				<div class="row justify-content-md-center">
					<div class="col-xl-6">
						<form class="form-inline" action="<?php echo site_url('admin/enrol_history/filter_by_date_range') ?>" method="get">
							<div class="col-xl-10">
								<div class="form-group">
									<div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"	data-cancel-class="btn-light" style="width: 100%;">
										<i class="mdi mdi-calendar"></i>&nbsp;
										<span id="selectedValue"><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?></span> <i class="mdi mdi-menu-down"></i>
									</div>
									<input id="date_range" type="hidden" name="date_range" value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>">
								</div>
							</div>
							<div class="col-xl-2">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('filter');?></button>
							</div>
						</form>
					</div>
				</div>
				<div class="table-responsive mt-4">
					<?php if (count($enrol_history) > 0): ?>
						<table id="basic-datatable" class="table table-striped table-centered mb-0">
							<thead>
								<tr>
									<th>#</th>
									<th><?php echo _l('photo'); ?></th>
									<th><?php echo _l('user_name'); ?></th>
									<th><?php echo _l('center'); ?></th>
									<th><?php echo _l('enrolled_course'); ?></th>
									<th><?php echo _l('enrolment_date'); ?></th>
									<th><?php echo _l('renewal_amount'); ?></th>
									<th><?php echo _l('renewal_date'); ?></th>
									<th><?php echo _l('emi_type'); ?></th>
									<th><?php echo _l('status'); ?></th>
									<th><?php echo _l('actions'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($enrol_history as $key => $enrol): ?>
									<tr class="gradeU">
										<td><?php echo $key + 1; ?></td>
										<td>
											<img src="<?php echo $this->user_model->get_user_image_url($enrol['user_id']); ?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
											<?php if ($enrol['archived']) { ?>
											<span class="badge badge-danger"><?php _el('archived'); ?></span>
											<?php } ?>
										</td>
										<td>
											<a href="<?php echo site_url('admin/user_form/edit_user_form/' . $enrol['user_id']); ?>" target="_blank"><b><?php echo $enrol['user']; ?></b> <i class="dripicons-pencil"></i><a><br>
											<small><?php echo _l('mobile').': '.$enrol['mobile']; ?></small><br>
											<small><?php echo _l('enrol_id').': '.$enrol['id']; ?></small>
										</td>
										<td>
											<?php if ($enrol['mode'] == 'offline') { ?>
											<?php echo $this->enrol_model->getMultipleCenter($enrol['id']); ?>
											<?php } else { ?>
											<?php echo $enrol['mode']; ?>
											<?php } ?>
										</td>
										<td><strong><a href="<?php echo site_url('admin/course_form/course_edit/'.$enrol['course_id']); ?>" target="_blank"><?php echo ellipsis($enrol['course']); ?></a></strong></td>
										<td><?php echo date('D, d-M-Y', strtotime($enrol['doj'])); ?></td>
										<td><?php echo currency($this->enrol_model->getRenewalAmount($enrol['id'])); ?></td>
										<td><?php echo date('D, d-M-Y', strtotime($enrol['renewal_date'])); ?></td>
										<td><?php echo _l($enrol['emi_type']); ?></td>
										<td class="text-left">
											<?php if ($enrol['status']) { ?>
											<i class="mdi mdi-circle text-success" style="font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('active'); ?>"></i>
											<?php } else { ?>
											<i class="mdi mdi-circle text-danger" style="font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('inactive'); ?>"></i>
											<?php } ?>
										</td>
										<td>
											<div class="dropright dropright">
												<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="mdi mdi-dots-vertical"></i>
												</button>
												<ul class="dropdown-menu">
													<li class="mt-1 text-center">
														<button type="button" data-toggle="modal" data-target="#payment-modal"class="btn btn-outline-info btn-icon btn-rounded btn-sm" onclick="$('input[name=enrol_id]').val(<?php echo $enrol['id']; ?>);$('input[name=amount]').val(<?php echo ($enrol_amount = $this->enrol_model->getRenewalAmount($enrol['id'])); ?>);"> <i class="dripicons-link"></i><?php _el('send_link'); ?> </button>
													</li>
													<li class="mt-1 text-center">
														<button type="button" data-toggle="modal" data-target="#edit-modal"class="btn btn-outline-success btn-icon btn-rounded btn-sm" onclick="$('input[name=enrol_id]').val(<?php echo $enrol['id']; ?>);getEnrol(<?php echo $enrol['id']; ?>);getEmis(<?php echo $enrol['id']; ?>, '<?php echo $enrol['emi_type']; ?>', <?php echo $enrol_amount; ?>);"> <i class="dripicons-pencil"></i> <?php _el('edit'); ?></button>
													</li>
													<li class="mt-1 text-center">
														<?php if ($enrol['archived']) { ?>
														<button type="button" class="btn btn-outline-success btn-icon btn-rounded btn-sm" onclick="confirm_modal('<?php echo site_url('admin/enrol_archive/'.$enrol['id']); ?>');"> <i class="dripicons-clockwise"></i> <?php $enrol['archived'] ? _el('restore') : _el('archive'); ?></button>
														<?php } else {  ?>
														<button type="button" class="btn btn-outline-danger btn-icon btn-rounded btn-sm" onclick="confirm_modal('<?php echo site_url('admin/enrol_archive/'.$enrol['id']); ?>');"> <i class="dripicons-archive"></i> <?php $enrol['archived'] ? _el('restore') : _el('archive'); ?></button>
														<?php } ?>
													</li>
												</ul>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
					<?php if (count($enrol_history) == 0): ?>
						<div class="img-fluid w-100 text-center">
						<img style="opacity: 1; width: 100px;" src="<?php echo base_url('assets/backend/images/file-search.svg'); ?>"><br>
						<?php echo _l('no_data_found'); ?>
						</div>
					<?php endif; ?>
				</div>


				<div class="modal fade" id="payment-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('send_payment_link'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('admin/send_payment_link'); ?>" method="post" id="form-payment">
									<input type="hidden" name="enrol_id" value="" />
									<div class="form-group">
										<label for="amount"><?php _el('leave_empty_for_default_subscription'); ?></label>
										<input
											type="amount"
											name="amount"
											placeholder="<?php _el('enter_amount'); ?>"
											class="form-control"
											id="amount"
										/>
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
										type="submit"
										form="form-payment"
										class="btn btn-primary ml-1"
									><?php _el('send'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="edit-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('edit_enrolment'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('admin/update_enrol'); ?>" method="post" id="form-enrol">
									<input type="hidden" name="enrol_id" value="" />

									<div class="form-group">
										<label for="renewal_date"><?php _el('renewal_date'); ?></label>
										<input
											type="renewal_date"
											name="renewal_date"
											placeholder="<?php _el('renewal_date'); ?>"
											class="form-control datepicker-autoclose"
											id="renewal_date"
										/>
									</div>
									<div class="form-group">
										<label for="doj"><?php _el('DOJ'); ?></label>
										<input
											type="doj"
											name="doj"
											placeholder="<?php _el('doj'); ?>"
											class="form-control datepicker-autoclose"
											id="doj"
										/>
									</div>

									<div class="form-group">
										<label for="emi_type"><?php echo _l('emi_type'); ?><span class="required">*</span> </label>
										<select class="form-control select2" data-toggle="select2" name="emi_type" id="emi_type" required>
											<option value=""><?php echo _l('select_emi_type'); ?></option>
										</select>
									</div>

									<div class="form-group">
										<label for="amount"><?php echo _l('amount'); ?><span class="required">*</span> </label>
										<input type="text" class="form-control" name="amount" id="enrol_amount" value="" required>
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
										type="submit"
										form="form-enrol"
										class="btn btn-primary ml-1"
									><?php _el('update'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script type="text/javascript">
	function update_date_range()
	{
		var x = $("#selectedValue").html();
		$("#date_range").val(x);
	}
</script>
<script>
const getEmis = (enrol_id, emi_type, amount) => {
	let fd = new FormData();
	fd.append('enrol_id', enrol_id);

	$('#enrol_amount').val(amount);

	submitForm('<?php echo site_url('admin/get_emis'); ?>', fd, json => {
		if (json.emis) {
			let html = '';
			emis = json.emis;

			json.emis.map(emi => {
				emi.amount > 0 && (html += `<option value="${emi.key}" data-amount="${emi.amount}">${emi.key}</option>`);
			});

			html += `<option value="other" data-amount="${amount}"><?php echo _li('other'); ?></option>`;

			$('#emi_type').html(html);
			$('#emi_type').select2();

			$('#emi_type').val(emi_type).trigger('change');

			$('#emi_type').on('change', function() {
				$(this).find('option:selected').data('amount') && $('#enrol_amount').val($(this).find('option:selected').data('amount'));
			});
		} else {
			error_notify(json.error)
		}
	});
}
</script>
<script>
$('form:not(.form-inline)').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}

		json.redirect && setTimeout(() => (window.location = json.redirect), 200);
	});
});

const getEnrol = (enrol_id) => {

	let fd = new FormData();
	fd.append('enrol_id', enrol_id);

	submitForm('<?php echo site_url('admin/get_enrol'); ?>', fd, json => {
		if (json.success) {
			$('#renewal_date').val(json.enrol.renewal_date);
			$('#doj').val(json.enrol.doj);

			$('.datepicker-autoclose').datepicker({
				autoclose: true,
				todayHighlight: true,
				format: "mm/dd/yyyy",
			});
		} else {
			error_notify(json.error)
		}
	});
};
</script>
