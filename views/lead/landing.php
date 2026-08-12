<?php require __DIR__ . '/landing/header.php'; ?>

<div class="container">
	<div class="col-md-12 fm">
		<div>
			<div class="alert alert-danger error-form hide" role="alert"></div>

			<form action="" id="lead_form" method="post" class="wpcf7-form m-t-20" novalidate="novalidate">
				<input type="hidden" name="api_site_id" value="<?= $site_id; ?>" id="api_site_id">
				<input type="hidden" name="lead_id" value="" id="lead_id">
				<input type="hidden" name="utm_source" value="<?= $utm_source; ?>" id="utm_source">
				<input type="hidden" name="utm_medium" value="<?= $utm_medium; ?>" id="utm_medium">
				<input type="hidden" name="utm_campaign" value="<?= $utm_campaign; ?>" id="utm_campaign">

				<div id="carousel-form" class="carousel slide" data-ride="carousel">
					<ol class="carousel-indicators">
						<li data-target="#carousel-form" class="active"></li>
						<li data-target="#carousel-form"></li>
						<li data-target="#carousel-form"></li>
					</ol>
					<div class="carousel-inner">
						<div class="carousel-item active">
							<?php require __DIR__ . '/landing/step_1.php'; ?>
						</div>
						<div class="carousel-item">
							<?php require __DIR__ . '/landing/step_2.php'; ?>
						</div>
						<div class="carousel-item">
							<?php require __DIR__ . '/landing/step_3.php'; ?>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php require __DIR__ . '/landing/footer.php'; ?>
