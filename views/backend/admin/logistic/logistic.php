<meta http-equiv="refresh" content="60" />

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
					</div>
					<div class="row">
						<div class="col-sm-12 text-right">
							<button type="submit" class="btn btn-info" onclick="update_date_range();"> <?php echo _l('search');?></button>
							<button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="row">
			<?php foreach ($users as $user) { ?>
			<div class="col-sm-3 col-xs-6 mb-4">
				<div class="card shadow-none">
					<div class="card-body text-center">
						<i class="fa fa-user text-info" style="font-size: 24px;"></i>
						<h4><span id="registrations"><?=$user['name']?></span></h4>
						<p class="text-muted font-15 mb-0"><b><?=$user['new']?></b> <?php echo _l('today_total'); ?></p>
						<small class="text-success"><b><?=$user['total']?></b> <?php echo _l('month_total'); ?></small>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div> <!-- end col-->
</div>

<script>
$(function() {
	$(document).on('click', '#btn-export', function(e) {
		var x = $('.selectedValue').html();
		$('.date_range').val(x);

		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?=base_url('admin/jax_export_logistic_dashboard_int'); ?>?' + filters.join('&');
		}
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		window.location = '<?=base_url('admin/get_logistic_dashboard_int'); ?>?' + filters.join('&');
	})
});

function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>
