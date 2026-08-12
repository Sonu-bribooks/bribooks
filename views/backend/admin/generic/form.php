<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title ?>
				<button
					type="button"
					class="btn btn-outline-dark btn-rounded alignToTitle"
					style="padding: 6px 16px;"
					onclick="window.history.back()"
				>
					<i class="mdi mdi-arrow-left" style="margin-right: 4px;"></i> <?= _l('back') ?>
				</button>
			</h4>
			</div>
			<!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			<div class="col-lg-12">
				<h4 class="mb-3 header-title"><?= $page_title ?></h4>

				<?php if (!empty($info)) { ?>
					<div class="alert alert-info">
						<?=$info ?>
					</div>
				<?php } ?>

				<form class="required-form" action="<?= $action ?>" method="post" enctype="multipart/form-data">
					<?= $this->load->view('backend/admin/generic/form_item', ['fields' => $fields], true) ?>

					<div style="display: flex; gap: 10px; margin-top: 10px;">
						<button
							type="button"
							class="btn btn-primary"
							style="padding: 6px 16px;"
							onclick="checkRequiredFields()"
						>
							<?= _l('submit') ?>
						</button>

						<button
							type="button"
							class="btn btn-dark"
							style="padding: 6px 16px; margin-left: auto;"
							onclick="window.history.back()"
						>
							<i class="mdi mdi-arrow-left" style="margin-right: 4px;"></i> <?= _l('back') ?>
						</button>
					</div>

				</form>
			</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<?= $this->load->view('backend/admin/generic/script', [], true) ?>
