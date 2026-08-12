<style>
	.custom-alert{
		background: rgb(115,125,251);
		background: linear-gradient(0deg, rgba(45,210,253,1) 0%, rgba(45,210,253,1) 100%);
		color: #fff;
		font-weight:bold;
	}
	.custom-alert small{
		position: absolute;
		right: 1px;
		bottom: 1px;
		background: linear-gradient(0deg, #F3D832 0%, #EDCF17 100%);
		color: #0F0E0E;
		padding: 2px 5px;
		border-radius: 2px 0px 0px 2px;
		font-weight: 600;
	}
</style>
<form class="ship_form" method="post">
	<input type="hidden" name="order_id" id="hidden_order_id" value="<?= !empty($order_id) ? $order_id : ''; ?>">
	<input type="hidden" name="pickup_location_id" value="<?= !empty($pickup_location_id) ? $pickup_location_id : ''; ?>">
	<input type="hidden" name="vendor" id="vendor" value="">
	<input type="hidden" name="order_type" id="hidden_order_type" value="<?= $order_type ?? ''; ?>">
	<div class="modal-header">
		<h5 class="modal-title" id="mySmallModalLabel"><?= _l('ship_your_package_now') ?></h5>
	</div>
	<div id="updateDive">
		<div class="modal-body download-invoice-label text-center d-flex justify-content-center gap-3 align-items-start">
			<div>
				<input type="text" name="weight" id="order_weight" style="height: 37px; border=2px solid #000" value="<?= !empty($order_weight) ? $order_weight : ''; ?>">
				<div class="text-info">weight should be in grams</div>
			</div>
			<button type="button" class="btn btn-primary ml-1" id="update_weight" title="Update Weight">Update Weight</button>
		</div>
	</div>
	<div class="deliveryPartner" style="height: 26rem; overflow-y: scroll;">
		<?php if (!empty($couriers)) { ?>
		<?php foreach ($couriers as $vendor => $couriers_data) { ?>
		<div class="modal-body m-1">
			<?php if($couriers_data[0]['rate'] != 0){ ?>
			<h4><?= _l($vendor) ?></h4>
			<div class="row modal_scroll">
				<?php foreach ($couriers_data as $index=>$courier) {
					if(!empty($courier['rate'])){ ?>
				<div class="col-sm-6 col-md-6 couriourval" data-target="<?= $courier['courier_id'] ?>" >
					<div class="alert alert-success fade show" role="alert">
						<div class="custom-control custom-radio">
							<input
								type="radio"
								required=""
								id="customRadio<?= $courier['courier_id'] ?>"
								name="courier_id"
								data-vendor="<?= ($courier['vendor_name']) ?>"
								class="custom-control-input courier-input"
								value="<?= $courier['courier_id'] ?>"
								>
							<label class="custom-control-label" for="customRadio<?= $courier['courier_id'] ?>">
							<?= ucfirst($courier['courier_name']) ?>
							<?php if (!empty($courier['rate'])) { ?>
							(&#8377;<?= round($courier['rate'], 2); ?>)
							<?php } else { ?>
							(&#8377; 0)
							<?php } ?>
							</label>
						</div>
					</div>
				</div>
				<?php }
					} ?>
			</div>
			<?php } ?>
		</div>
		<?php
			} ?>
	</div>
	<div id="downloadDive" style="display: none;">
		<div class="modal-body download-invoice-label text-center d-flex justify-content-center gap-3">
			<button type="button" class="btn-primary generate-singlelabel mr-3" id="singlelabelBtn" data-id="0" data-type="book" title="Download Label"><i class="fa fa-download fa-1x mb-2" style="display:block"></i>Download Label</button>
			<button type="button" class="btn-secondary generate-invoice" id="invoiceBtn" data-id="0" data-type="book" title="Download Invoice"><i class="fa fa-download fa-1x mb-2" style="display:block"></i>Download Invoice</button>
			<a href="" class="btn btn-primary d-none ml-3" id="donwnloadSchoolAddress" taregt="_blank" data-id="0" data-type="book" title="Download School Address"><i class="fa fa-download fa-1x mb-2" style="display:block"></i>Download School Address</a>
		</div>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary" id="btnShip">Ship</button>
		<button type="button" class="btn btn-secondary closeBtn" onclick="closeModal()" data-dismiss="modal">Close</button>
	</div>
	<?php } else { ?>
	<div class="modal-body">
		<div class="m-b-10">
			<div class="alert alert-danger fade show" role="alert">
				<?= !empty($error) ? $error : 'Not Serviceable'; ?>
			</div>
		</div>
	</div>
	<?php } ?>
</form>
<script>
	function closeModal() {
		$('#ajax-datatable').DataTable().ajax.reload();
		return true;
		}

	function openDeliveryPartner(order_id, order_type) {
		if (order_id) {
			$.ajax({
				type: 'POST',
				data: { 'order_id' : order_id , 'order_type' : order_type },
				url: '/admin/get_delivery_info',
				beforeSend: function() {
					$('.fulfillment_info').modal('show');
					$('#fulfillment_info').html('');
					$el.prop('disabled', true);
				},
				complete: function() {
					$el.prop('disabled', false);
				},
				success: function (data) {
					$('#fulfillment_info').html(data.couriers);
				},
			});
		}
		}

	$('.courier-input').on('click', function() {
		$el = $(this);
		$('#vendor').val($el.data('vendor'));
	});

	$('#update_weight').on('click', function() {
		var order_weight 	= $('#order_weight').val()
		var order_id 		= $('#hidden_order_id').val()
		var order_type 	= $('#hidden_order_type').val()

		if (order_type == 'medallion') {
			var ajax_url = 'ajax_update_medallion_order';
		} else if (order_type == 'school') {
			var ajax_url = '/admin/ajax_update_school_order';
		} else {
			order_type = 'book';
			var ajax_url = 'ajax_update_order';
		}

		if (order_weight != '' && order_weight != 'undefined' && order_weight > 100) {
			if (confirm('<?=_l('Are you sure?')?>')) {
				$.ajax({
					type: 'POST',
					data: {order_id : order_id, weight : order_weight},
					url	: ajax_url,
					success: function (data) {
						if (data.success){
							success_notify(data.success);
							if (order_id != '' || order_id != 'undefined') {
								openDeliveryPartner(order_id, order_type);
							} else {
								alert('Order Is Empty');
							}

						} else if (data.error) {
							error_notify(data.error)
						}
					},
				});
			}
		} else {
			error_notify("Order Weight Is Invalid")
		}
	});

	$('.ship_form').submit(function(e) {
		e.preventDefault();

		$('#btnShip').attr('disabled','disabled');
		var order_id 	= $('#hidden_order_id').val();
		var order_type 	= $('#hidden_order_type').val();

		$.ajax({
			url: '/admin/ship',
			type: 'POST',
			data: $(this).serialize(),
			cache: false,
			success: function(data) {
				if (data.success) {
					if (order_id != '' || order_id != 'undefined') {
						$('#singlelabelBtn').attr('data-id', order_id);
						$('#singlelabelBtn').attr('data-type', order_type);
						$('#invoiceBtn').attr('data-id', order_id);
						$('#invoiceBtn').attr('data-type', order_type);
						if(order_type == "school"){
							$('#donwnloadSchoolAddress').attr('href', '/admin/school_address_download/' + order_id);
							$('#donwnloadSchoolAddress').removeClass('d-none');
						}

						$('#btnShip').hide();
						$('.deliveryPartner').hide();
						$('#updateDive').hide();
						$('#downloadDive').show();
					}

				} else if (data.error) {
					alert(data.error);
				}
			},
			complete: function() {
				$('#btnShip').removeAttr('disabled');
			}
		});
	});
</script>
