<?php
	$this->db->where('user_id', $this->session->userdata('user_id'));
	$this->db->where('status', 1);
	$this->db->order_by('date_added', 'DESC');
	$purchase_history = $this->db->get('order', $per_page, $this->uri->segment(3));
?>

<?php
$enrols = $this->enrol_model->getAll([
	'user_id'	=> $this->session->user_id,
	'no_payment'=> true,
]);

$renew = false;

foreach ($enrols as &$enrol) {
	$center_info = $this->enrol_model->getCenter($enrol['id']);

	$enrol['center'] = $center_info['center'] ?? '-';

	/*$enrol['renewal'] 		= true;
	$enrol['amount'] 		= $this->enrol_model->getRenewalAmount($enrol['id']);*/

	if (strtotime($enrol['renewal_date'] . ' -1 week') < time()) {
		$renew					= true;
		$enrol['renewal'] 		= true;
		$enrol['amount'] 		= $this->enrol_model->getRenewalAmount($enrol['id']);
	}

	if (strtotime($enrol['renewal_date']) < time()) {
		$enrol['expired'] 		= true;
	}
}
?>

<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col">
				<h1 class="page-title"><?php echo _l('payment_logs'); ?></h1>
				<br />
				<?php if (0) { ?>
				<ul>
				  <li><a href="<?php echo site_url('home/my_courses'); ?>"><?php echo _l('all_courses'); ?></a></li>
				  <li><a href="<?php echo site_url('home/my_wishlist'); ?>"><?php echo _l('wishlists'); ?></a></li>
				  <li><a href="<?php echo site_url('home/my_messages'); ?>"><?php echo _l('my_messages'); ?></a></li>
				  <li class="active"><a href="<?php echo site_url('home/purchase_history'); ?>"><?php echo _l('purchase_history'); ?></a></li>
				  <li><a href="<?php echo site_url('home/profile/user_profile'); ?>"><?php echo _l('user_profile'); ?></a></li>
				  <!-- <li><a href="<?php echo site_url('home/re_schedule'); ?>"><?php echo _l('re_schedule'); ?></a></li> -->
				</ul>
				<?php } ?>
			</div>
		</div>
	</div>
</section>


		<section class="purchase-history-list-area" style="min-height: 500px;">
			<div class="container">
				<div class="text-right">
					<a href="<?php echo site_url('home/my_courses'); ?>" class="btn btn-inverse"><?php _el('dashboard'); ?></a>
					<br />
					<br />
				</div>

				<div class="row">
					<div class="col">
						<form action="<?php echo site_url('home/addCart'); ?>" method="post" id="form-add-cart">
							<ul class="purchase-history-list">
								<li class="purchase-history-list-header">
									<div class="row">
										<div class="col-sm-4"><h4 class="purchase-history-list-title"> <?php echo _l('renew_courses'); ?> </h4></div>
										<div class="col-sm-8 hidden-xxs hidden-xs">
											<div class="row">
												<div class="col-sm-2"><b><?php echo _l('mode'); ?></b></div>
												<div class="col-sm-3"><b><?php echo _l('center'); ?></b></div>
												<div class="col-sm-3"><b><?php echo _l('amount'); ?></b></div>
												<div class="col-sm-2"><b><?php echo _l('enrolment_date'); ?></b></div>
												<div class="col-sm-2"><b><?php echo _l('renewal_date'); ?></b></div>
											</div>
										</div>
									</div>
								</li>
								<?php if ($enrols):
									foreach($enrols as $enrol_i):
									if (!empty($enrol_i['renewal'])):
								?>
								<li class="purchase-history-items mb-2">
									<div class="row">
										<div class="col-sm-1">
											<input type="checkbox" name="enrol_ids[]" value="<?php echo $enrol_i['id']; ?>" class="form-control" />
										</div>
										<div class="col-sm-3">
											<a class="purchase-history-course-title" href="#" >
												<?php echo $enrol_i['course']; ?>
											</a>

											<?php if (!empty($enrol_i['expired'])) { ?>
											<span class="badge badge-danger"><?php _el('expired'); ?></span>
											<?php } elseif (!empty($enrol_i['renewal'])) { ?>
											<span class="badge badge-warning"><?php _el('expiring_soon'); ?></span>
											<?php } else { ?>
											<?php echo _cs($enrol_i['status']); ?>
											<?php } ?>
										</div>
										<div class="col-sm-8 purchase-history-detail">
											<div class="row">
												<div class="col-sm-2 date">
													<?php _el($enrol_i['mode']); ?>
												</div>
												<div class="col-sm-3 date">
													<?php $enrol_i['center']; ?>
												</div>
												<div class="col-sm-3 price">
													<?php echo currency($enrol_i['amount']); ?>
													<span class="badge badge-info"><?php _el($enrol_i['emi_type']); ?></span>
												</div>
												<div class="col-sm-2 date">
													<?php echo date('F j, Y', $enrol_i['date_added']); ?>
												</div>
												<div class="col-sm-2 price">
													<?php echo date('F j, Y', strtotime($enrol_i['renewal_date'])); ?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<?php endif; ?>
								<?php endforeach; ?>
								<?php endif; ?>

								<?php if (!$renew) { ?>
								<li>
									<p class="text-center">
										<?php echo _l('no_records_found'); ?>
									</p>
								</li>
								<?php } ?>
							</ul>
						</form>

						<?php if ($renew) { ?>
						<div class="text-right">
							<button type="submit" form="form-add-cart" class="btn btn-primary ml-1"><?php _el('renew_all'); ?></button>
						</div>
						<?php } ?>

					</div>
				</div>

				<div class="row mt-5">
					<div class="col">
						<ul class="purchase-history-list">
							<li class="purchase-history-list-header">
								<div class="row">
									<div class="col-sm-4"><h4 class="purchase-history-list-title"> <?php echo _l('payment_logs'); ?> </h4></div>
									<div class="col-sm-8 hidden-xxs hidden-xs">
										<div class="row">
											<div class="col-sm-4"><b><?php echo _l('date'); ?></b></div>
											<div class="col-sm-3"><b><?php echo _l('total_price'); ?></b></div>
											<div class="col-sm-3"><b><?php echo _l('payment_type'); ?></b></div>
											<div class="col-sm-2">  </div>
										</div>
									</div>
								</div>
							</li>
							<?php if ($purchase_history->num_rows() > 0):
								foreach($purchase_history->result_array() as $each_purchase):
									$enrol_ids = json_decode($each_purchase['enrol_ids']);

									!$enrol_ids && ($enrol_ids = [$each_purchase['enrol_id']]);

									$enrol_ids && ($enrol_details = implode(', ', array_map(function($enrol_id) {
										$enrol_info = $this->enrol_model->get($enrol_id);
										return $enrol_info['course'];
									}, $enrol_ids)));
							?>
									<li class="purchase-history-items mb-2">
										<div class="row">
											<div class="col-sm-4">
												<a class="purchase-history-course-title" href="#" >
													<?php
														echo $enrol_details;
													?>
												</a>
											</div>
											<div class="col-sm-8 purchase-history-detail">
												<div class="row">
													<div class="col-sm-4 date">
														<?php echo date('D, d-M-Y', strtotime($each_purchase['date_added'])); ?>
													</div>
													<div class="col-sm-3 price"><b>
														<?php echo currency($each_purchase['amount']); ?>
													</b></div>
													<div class="col-sm-3 payment-type">
														<?php echo ucfirst($each_purchase['payment_type']); ?>
													</div>
													<div class="col-sm-2">
														<a href="<?php echo site_url('home/invoice/' . $each_purchase['id']); ?>" class="btn btn-receipt" target="_blank"><?php _el('receipt'); ?></a>
													</div>
												</div>
											</div>
										</div>
									</li>
								<?php endforeach; ?>
							<?php else: ?>
								<li>
									<p class="text-center">
										<?php echo _l('no_records_found'); ?>
									</p>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</section>
		<nav>
			<?php echo $this->pagination->create_links(); ?>
		</nav>

<footer id="payment-footer">
	<?php if ($items) { ?>
	<div class="container">
		<div class="row">
			<div class="col-sm-9">
				<ul class="list-inline">
					<?php foreach ($items as $item) { ?>
					<li class="list-inline-item"><?php echo $item['course'] ?> <span class="badge badge-info"><?php echo $item['emi_type']; ?></span></li>
					<?php } ?>
				</ul>
				<button class="btn btn-receipt" id="empty-cart"><i class="fa fa-trash"></i> <?php _el('empty'); ?></button>
			</div>
			<div class="col-sm-3">
				<h3><?php echo $total['total_formatted']; ?></h3>
				<button class="btn btn-block" id="confirm-pay"><?php _el('pay'); ?></button>
			</div>
		</div>
	</div>
	<?php } ?>
</footer>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
$('#form-add-cart').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();

	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {

		json.error && error_notify(json.error);
		json.success && success_notify(json.success);

		if (json.items) {
			var items = '';

			json.items && json.items.map(item => {
				items += `<li class="list-inline-item">${item.course} <span class="badge badge-info">${item.emi_type}</span></li>`
			});

			$('#payment-footer').html(`
				<div class="container">
					<div class="row">
						<div class="col-sm-10">
							<ul class="list-inline">${items}</ul>
							<button class="btn btn-receipt" id="empty-cart"><i class="fa fa-trash"></i> <?php _el('empty'); ?></button>
						</div>
						<div class="col-sm-2">
							<h3>${json.total.total_formatted}</h3>
							<button class="btn btn-block" id="confirm-pay"><?php _el('pay'); ?></button>
						</div>
					</div>
				</div>
			`);
		}
	});
});

$(document).on('click', '#empty-cart', function() {
	if (confirm('<?php _el('are_you_sure'); ?>')) {
		submitForm('<?php echo site_url('home/emptyCart'); ?>', {}, json => {
			if (json.success) {
				success_notify(json.success);
				$('#payment-footer').empty();
			} else if (json.error) {
				error_notify(json.error);
			}
		});
	}
});

$(document).on('click', '#confirm-pay', function() {
	submitForm('<?php echo site_url('home/confirmPay'); ?>', {}, json => {
		var rzp1 = new Razorpay({    
			"key": json.order.key,     
			"amount": json.order.amount,
			"currency": json.order.currency_code,
			"name": json.order.name,    
			"description": json.order.description,    
			"image": json.order.logo,
			"order_id": json.order.order_id,
			"handler": function (response) {  
				$.post("<?= site_url('home/updateBulkTransaction') ?>", {
					payment_id: response.razorpay_payment_id,
					signature: response.razorpay_signature,
					id: json.order.id,
					order_id: json.order.order_id,
				}, json => {
					json.redirect && (location = json.redirect);
				});   
			},    
			"prefill": {
				"name": json.order.user.name,
				"email": json.order.user.email,
				"contact": json.order.user.mobile
			},    
			"notes": {        
				"address": json.order.address    
			},    
			"theme": {        
				"color": "#f08b3c"    
			}
		});

		rzp1.open();
	});
});
</script>
