<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('pending_payments'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('pending_payments'); ?></h4>
				<div class="table-responsive-sm mt-4">
					<?php if (count($enrols) > 0): ?>
						<table id="basic-datatable" class="table table-striped table-centered mb-0">
							<thead>
								<tr>
									<th>#</th>
									<th><?php echo _l('student'); ?></th>
									<th><?php echo _l('center'); ?></th>
									<th><?php echo _l('enrolled_course'); ?></th>
									<th><?php echo _l('enrolment_date'); ?></th>
									<th><?php echo _l('renewal_amount'); ?></th>
									<th><?php echo _l('renewal_date'); ?></th>
									<th><?php echo _l('emi_type'); ?></th>
									<th><?php echo _l('actions'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($enrols as $key => $enrol):
									$user_data = $this->db->get_where('users', array('id' => $enrol['user_id']))->row_array();
									$course_data = $this->db->get_where('course', array('id' => $enrol['course_id']))->row_array();?>
									<tr class="gradeU">
										<td><?php echo $key + 1; ?></td>
										<td>
											<a href="<?php echo site_url('admin/user_form/edit_user_form/' . $enrol['user_id']); ?>" target="_blank"><b><?php echo $user_data['first_name'].' '.$user_data['last_name']; ?></b> <i class="dripicons-pencil"></i></a><br>
											<small><?php echo _l('mobile').': '.$user_data['mobile']; ?></small>
										</td>
										<td>
											<?php if ($enrol['mode'] == 'offline') { ?>
											<?php echo $this->enrol_model->getMultipleCenter($enrol['id']); ?>
											<?php } else { ?>
											<?php echo $enrol['mode']; ?>
											<?php } ?>
										</td>
										<td><strong><a href="<?php echo site_url('admin/course_form/course_edit/'.$course_data['id']); ?>" target="_blank"><?php echo ellipsis($course_data['title']); ?></a></strong></td>
										<td><?php echo date('D, d-M-Y', strtotime($enrol['doj'])); ?></td>
										<td><?php echo currency($this->enrol_model->getRenewalAmount($enrol['id'])); ?></td>
										<td><?php echo date('D, d-M-Y', strtotime($enrol['renewal_date'])); ?></td>
										<td><?php echo $enrol['emi_type']; ?></td>
										<td>
											<div class="dropright dropright">
												<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="mdi mdi-dots-vertical"></i>
												</button>
												<ul class="dropdown-menu">
													<li class="mt-1 text-center">
														<button type="button" data-toggle="modal" data-target="#payment-modal" class="btn btn-outline-info btn-icon btn-rounded btn-sm"
															onclick="$('input[name=enrol_id]').val(<?php echo $enrol['id']; ?>);$('input[name=amount]').val(<?php echo $this->enrol_model->getRenewalAmount($enrol['id']); ?>);getEmis(<?php echo $enrol['id']; ?>, '<?php echo $enrol['emi_type']; ?>');"
														> <i class="dripicons-link"></i> <?php _el('send_link'); ?></button></li>
													<li class="mt-1 mb-1 text-center">
														<button type="button" data-toggle="modal" data-target="#offline-modal" class="btn btn-outline-warning btn-icon btn-rounded btn-sm"
															onclick="$('input[name=enrol_id]').val(<?php echo $enrol['id']; ?>);$('input[name=amount]').val(<?php echo $this->enrol_model->getRenewalAmount($enrol['id']); ?>);"
														> <i class="dripicons-wallet"></i> <?php _el('collect_offline'); ?></button>
													</li>
												</ul>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
					<?php if (count($enrols) == 0): ?>
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
										<label for="amount"><?php _el('leave_for_default_subscription'); ?></label>

										<?php if (0) { ?>
										<div class="form-group d-none">
											<label for="emi_type"><?php _el('frequency'); ?></label>
											<select
												class="form-control select2"
												data-toggle="select2"
												name="emi_type"
												id="emi_type"
											>

											</select>
										</div>
										<?php } ?>

										<div class="form-group">
											<label for="amount"><?php _el('amount'); ?></label>
											<input
												type="amount"
												name="amount"
												placeholder="<?php _el('enter_amount'); ?>"
												class="form-control"
												id="amount"
											/>
										</div>
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

				<div class="modal fade" id="offline-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('offline_payment'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('admin/payment_collection'); ?>" method="post" id="form-payment-offline">
									<input type="hidden" name="enrol_id" value="" />
									<div class="form-group">
										<label for="offline-amount"><?php _el('leave_for_default_subscription'); ?></label>

										<div class="form-group">
											<label for="amount"><?php _el('amount'); ?></label>
											<input
												type="amount"
												name="amount"
												placeholder="<?php _el('enter_amount'); ?>"
												class="form-control"
												id="offline-amount"
											/>
										</div>
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
										form="form-payment-offline"
										class="btn btn-primary ml-1"
									><?php _el('confirm'); ?>
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
$('form').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);

			json.redirect && setTimeout(() => {
				window.location = json.redirect
			}, 200);
		} else {
			error_notify(json.error)
		}
	});
});
</script>
<script>
const getEmis = (id, emi_type) => {
	let fd = new FormData();
	fd.append('enrol_id', id);

	submitForm('<?php echo site_url('admin/get_emis'); ?>', fd, json => {
		if (json.emis) {
			let html = '';
			emis = json.emis;

			json.emis.map(emi => {
				emi.amount > 0 && (html += `<option value="${emi.key}" data-amount="${emi.amount}">${emi.key}</option>`);
			});

			html += '<option value="other"><?php echo _li('other'); ?></option>'

			$('#emi_type').html(html);
			$('#emi_type').select2();

			$('#emi_type').val(emi_type).trigger('change');

			$('#emi_type').on('change', function() {
				$('#amount').val($(this).find('option:selected').data('amount'));
			});
		} else {
			error_notify(json.error)
		}
	});
}
</script>
