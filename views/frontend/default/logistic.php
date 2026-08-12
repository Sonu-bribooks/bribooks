<meta http-equiv="refresh" content="15" />
<div class="container" style="min-height: 70vh;
    justify-content: center;
    display: flex;
    flex-direction: column;">
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
</div>
