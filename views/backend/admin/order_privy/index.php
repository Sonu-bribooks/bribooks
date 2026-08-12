
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
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link <?= (empty( $this->uri->segment(3)) && ($this->uri->segment(2)) == 'order_privy')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/order_privy')?>" role="tab">
								<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
								<span class="d-none d-sm-block"><?=_l('pending')?></span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?= (($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'order_privy/confirm')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/order_privy/confirm')?>" role="tab">
								<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
								<span class="d-none d-sm-block"><?=_l('confirmed')?></span>
							</a>
						</li>
					</ul>
					<br />




                    <?php if (empty( $this->uri->segment(3)) && (($this->uri->segment(2)) == 'order_privy')) { ?>
						<table id="ajax-datatable-all" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th><?php echo _l('order_code'); ?></th>
                            <th><?php echo _l('product'); ?></th>
                            <th><?php echo _l('customer'); ?></th>
                            <th><?php echo _l('weight_amount'); ?></th>
                            <th><?php echo _l('status'); ?></th>
                            <th><?php echo _l('date_added'); ?></th>
                            <th><?php echo _l('comment'); ?></th>
                            <th><?php echo _l('actions'); ?></th>
                            </tr>
                        </thead>
                    </table>
					<?php } else if ((($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'order_privy/confirm')) { ?>
						<table id="ajax-datatable-confirm" class="table table-striped table-centered mb-0">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th><?php echo _l('order_code'); ?></th>
                            <th><?php echo _l('product'); ?></th>
                            <th><?php echo _l('customer'); ?></th>
                            <th><?php echo _l('weight_amount'); ?></th>
                            <th><?php echo _l('confirm_date'); ?></th>
                            <th><?php echo _l('status'); ?></th>
                            <th><?php echo _l('comment'); ?></th>
                            </tr>
                        </thead>
                    </table>
					<?php } ?>

				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
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
				<form action="<?php echo base_url('admin/add_order_privy_comment'); ?>" method="post" id="form-hold-order">
					<input type="hidden" name="order_privy_id" value="" id="order_privy_id" />
					<input type="hidden" name="order_privy_status" value="" id="order_privy_status" />
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

<script>
$(function() {
    var status = '';
    <?php if (empty( $this->uri->segment(3)) && (($this->uri->segment(2)) == 'order_privy')) { ?>
		var table_id = '#ajax-datatable-all';
        var myColumnDefs = [
			{ title: "Sn", data: "sn", width: "15%"},
			{ title: "Order Code", data: "order_code", width: "15%"},
			{ title: "Product", data: "product", width: "15%"},
			{ title: "Customer", data: "customer", width: "15%"},
			{ title: "Weight Amount", data: "weight_amount", width: "15%"},
			{ title: "Status", data: "status", width: "15%"},
			{ title: "Date Added", data: "date_added", width: "20%"},
			{ title: "Comment", data: "comment", width: "20%"},
			{ title: "Action", data: "actions", width: "20%", searchable: false, sortable: false}
        ]
	<?php } else if ((($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'order_privy/confirm')) { ?>

        var status = 1;
        var table_id = '#ajax-datatable-confirm';

		var myColumnDefs = [
			{ title: "Sn", data: "sn", width: "15%"},
			{ title: "Order Code", data: "order_code", width: "15%"},
			{ title: "Product", data: "product", width: "15%"},
			{ title: "Customer", data: "customer", width: "15%"},
			{ title: "Weight Amount", data: "weight_amount", width: "15%"},
			{ title: "Confirm Date", data: "confirm_date", width: "20%"},
			{ title: "Status", data: "status", width: "15%"},
			{ title: "Comment", data: "comment", width: "15%"},
		]


	<?php } ?>

    var table = $(table_id).DataTable( {
            "ajax" : {
                'url': '<?php echo $action_ajax; ?>',
                'type': 'GET',
                'data': { status: status },
            },
			"processing": true,
			"serverSide": true,
			"order": [[ 0, "desc" ]],
			"columns": myColumnDefs
		})

});

$(document).on('click', '.confirmOrder', function(event) {
    event.preventDefault();

    var order_privy_id = $(this).attr('order_privy_id');
    var order_privy_status = $(this).attr('order_privy_status');

    if (order_privy_id != '') {
        $('#holdModal').modal('show');
        $('#order_privy_id').val(order_privy_id);
        $('#order_privy_status').val(order_privy_status);
    }


});
</script>

<script>
    $('#form-hold-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#holdModal').modal('hide');
                $('.table').DataTable().ajax.reload()
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});
</script>
