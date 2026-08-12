<style>
	.custom-alert{
		background: rgb(115,125,251);
		background: linear-gradient(0deg, rgba(45,210,253,1) 0%, rgba(45,210,253,1) 100%);
		color: #fff;
		font-weight:bold;
	}
	.custom-alert small{position: absolute;
		right: 1px;
		bottom: 1px;
		background: linear-gradient(0deg, #F3D832 0%, #EDCF17 100%);
		color: #0F0E0E;
		padding: 2px 5px;

		border-radius: 2px 0px 0px 2px;
		font-weight: 600;
	}
	.modal_scroll {
		max-height: 350px;
		overflow-y: scroll;
		/* Add the ability to scroll */
	}

	/* Hide scrollbar for Chrome, Safari and Opera */
	/* .modal_scroll::-webkit-scrollbar {
		display: none;
	} */

	/* Hide scrollbar for IE and Edge */
	/* .modal_scroll {
		-ms-overflow-style: none;
	} */
</style>
<form class="ship_form" method="post">
	<input type="hidden" name="order_id" id="hidden_order_id" value="<?= !empty($order_id) ? $order_id : ''; ?>">
   	<input type="hidden" name="pickup_location_id" value="<?= !empty($pickup_location_id) ? $pickup_location_id : ''; ?>">
   	<input type="hidden" name="vendor" id="vendor" value="">
   	<input type="hidden" name="order_type" id="hidden_order_type" value="<?= $order_type ?? ''; ?>">
	<div class="modal-header">
		<h5 class="modal-title" id="mySmallModalLabel"><?= _l('ship_your_package_now') ?></h5>
	</div>

	<div class="deliveryPartner">
		<?php if (!empty($couriers)) { ?>
		<div class="modal-body">
			<?php foreach ($couriers as $vendor => $couriers_data) { ?>
			<h4><?= _l($vendor) ?></h4>
			<div class="row modal_scroll">
				<?php foreach ($couriers_data as $courier) { ?>
				<div class="col-sm-6 couriourval" data-target="<?= $courier['courier_id'] ?>">
					<div class="alert alert-success fade show" role="alert">
						<div class="custom-control custom-radio">
							<input
								type="radio"
								required=""
								id="customRadio<?= $courier['courier_id'] ?>"
								name="courier_id"
								data-vendor="<?= $courier['vendor_name'] ?>"
								class="custom-control-input courier-input"
								value="<?= $courier['courier_id'] ?>"
							>
							<label class="custom-control-label" for="customRadio<?= $courier['courier_id'] ?>"><?= $courier['courier_name'] ?> <?php if (!empty($courier['rate'])) { ?> (&#8377;<?= round($courier['rate'], 2); ?>) <?php } ?></label>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>

	<div id="downloadDive" style="display: none;">
		<div class="modal-body download-invoice-label text-center d-flex justify-content-center gap-3">
			<a href="#" class="btn-primary mr-3" id="dropshipLabelBtn" data-id="0" data-type="book" title="Download Label" target="_blank"><i class="fa fa-download fa-1x mb-2" style="display:block"></i>Download Label</a>
			<a href="#" class="btn-secondary" id="dropshipInvoiceBtn" data-id="0" data-type="book" title="Download Invoice" target="_blank"><i class="fa fa-download fa-1x mb-2" style="display:block"></i>Download Invoice</a>
		</div>
	</div>

	<div class="modal-footer">
		<button type="submit" class="btn btn-primary" id="btnShip">Ship</button>
		<button type="button" class="btn btn-secondary" onclick="closeModal()" data-dismiss="modal">Close</button>
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

	$('.courier-input').on('click', function() {
		$el = $(this);
		$('#vendor').val($el.data('vendor'));
	});

	$('.ship_form').submit(function(e) {
		e.preventDefault();

		var base_url = '<?php echo base_url(); ?>'

		$('#btnShip').attr('disabled','disabled');
		var order_id 	= $('#hidden_order_id').val();
		var order_type 	= $('#hidden_order_type').val();

		$.ajax({
			url: 'ship',
			type: 'POST',
			data: $(this).serialize(),
			cache: false,
			success: function(data) {
				if (data.success) {
					if (order_id != '' || order_id != 'undefined') {
						$('#dropshipLabelBtn').attr('data-id', order_id);
						$('#dropshipLabelBtn').attr('data-type', order_type);
						$('#dropshipLabelBtn').attr('href', (base_url + 'dropShipper/download_label/' + order_id));
						$('#dropshipLabelBtn').show();

						$('#dropshipInvoiceBtn').attr('data-id', order_id);
						$('#dropshipInvoiceBtn').attr('data-type', order_type);
						$('#dropshipInvoiceBtn').attr('href', (base_url + 'dropShipper/download_invoice/' + order_id));
						$('#dropshipInvoiceBtn').show();

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
