<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center">
					<h4 class="page-title mb-0">
						<i class="mdi mdi-apple-keyboard-command title_icon"></i>
						<?= $page_title ?>
					</h4>

					<button
						type="button"
						class="btn btn-outline-dark btn-rounded"
						style="padding: 6px 16px;"
						onclick="window.history.back()"
					>
						<i class="mdi mdi-arrow-left mr-1"></i> <?= _l('back') ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">

	<!-- Details -->
	<div class="col-sm-12 col-md-7 col-lg-7 col-xl-7">
		<div class="card">

			<div class="card-header">
				<h6><?= $view_title ?? _l('details') ?></h6>
			</div>

			<div class="card-body">

				<div class="table-responsive">
					<table class="table table-striped table-bordered table-sm mb-0">

						<?php foreach (($details ?? []) as $item) : ?>

							<?php
							$type = $item['type'] ?? 'text';
							?>

							<tr>
								<th width="25%">
									<?= $item['label'] ?>
								</th>

								<td>

									<?php if ($type === 'image') : ?>

										<?php if (!empty($item['value'])) : ?>
											<img
												src="<?= $item['value'] ?>"
												class="img-thumbnail"
												style="max-width:200px;max-height:200px;"
											>
										<?php endif; ?>

									<?php elseif ($type === 'video') : ?>

										<?php if (!empty($item['value'])) : ?>
											<video controls width="350">
												<source src="<?= $item['value'] ?>">
											</video>
										<?php endif; ?>

									<?php elseif ($type === 'link') : ?>

										<?php if (!empty($item['value'])) : ?>
											<a href="<?= $item['value'] ?>" target="_blank">
												<?= $item['value'] ?>
											</a>
										<?php endif; ?>

									<?php elseif ($type === 'textarea') : ?>

										<div style="white-space: pre-wrap;">
											<?= $item['value'] ?>
										</div>

									<?php elseif ($type === 'html') : ?>

										<?= $item['value'] ?>

									<?php else : ?>

										<?= $item['value'] ?>

									<?php endif; ?>

								</td>
							</tr>

						<?php endforeach; ?>

					</table>
				</div>

			</div>

		</div>
	</div>

	<!-- Comments -->
	<div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
		<div class="card">

			<div class="card-header">
				<h6><?= _l('comments') ?></h6>
			</div>

			<div class="card-body">

				<?php if (!empty($comments)) : ?>

					<ul class="list-unstyled">

						<?php foreach ($comments as $comment) : ?>

							<li class="mb-3 border-bottom pb-2">
								<div>
									<?= _li($comment['description']) ?>
								</div>

								<small class="text-muted">
									<?= formatDate($comment['date_added']) ?>
								</small>
							</li>

						<?php endforeach; ?>

					</ul>

				<?php else : ?>

					<p class="text-muted mb-0">
						<?= _l('no_comments_found') ?>
					</p>

				<?php endif; ?>

			</div>

		</div>
	</div>

</div>