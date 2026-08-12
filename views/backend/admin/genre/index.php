<style>
	.pagination { margin-top: 20px; align-items: center; font-size: 16px; font-weight: 600; }
	.pagination .page-item { margin: 0 5px; }
	.pagination .page-link { border: 1px solid #ddd; color: #007bff; background-color: #fff; padding: 10px 18px; font-size: 16px; transition: all 0.3s ease; border-radius: 6px; display: inline-block; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); }
	.pagination .page-link:hover { background-color: #007bff; color: #fff; border-color: #007bff; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15); }
	.pagination .page-item.active .page-link { background-color: #007bff; color: #fff; border-color: #007bff; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1); }
	.pagination .page-item.disabled .page-link { color: #6c757d; background-color: #f1f1f1; border-color: #ddd; box-shadow: none; }
	.pagination .page-link:focus { outline: none; }
	.pagination .page-link:first-child,
	.pagination .page-link:last-child { border-radius: 6px; }
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title; ?>
					<a
						href="<?php echo base_url('admin/add_genre'); ?>"
						class="btn btn-outline-primary btn-rounded alignToTitle"
					>
						<i class="mdi mdi-plus"></i><?php echo _l('add_new') ?>
					</a>
				</h4>
			</div>
			<!-- end card body-->
		</div>
		<!-- end card -->
	</div>
	<!-- end col-->
</div>
<div class="row">
	<?php
		foreach ($genres as $genre) :
			$total = count($this->genre_model->getCategories($genre['id']) ?? []);
	?>
	<div class="col-sm-2 col-xs-6 on-hover-action" id="<?php echo $genre['id']; ?>">
		<div class="card d-block">
			<img
				class="card-img-top"
				src="<?=$this->config->item('cloudfront_url')?>public/Categories/<?php echo $genre['image']; ?>?width=320&height=480"
				alt="Card image cap"
			/>
			<div class="card-body p-1">
				<h4 class="card-title mb-0">
					<i class="<?php echo $genre['font_awesome_class']; ?>"></i> <?php echo $genre['name'] . '-' . $genre['id']; ?>
				</h4>
				<small style="font-style: italic;">
					<?php if ($total > 0) { ?>
						<a
							href="#"
							class="card-text viewChild"
							data-toggle="modal"
							data-target="#viewChildModel"
							style="cursor: pointer;"
							data-parent="<?=$genre['id'] ?>"
						>
							<?php echo $total . ' ' . _l('categories'); ?>
						</>
					<?php } else { ?>
						<p class="card-text viewChild">
							<?php echo $total . ' ' . _l('categories'); ?>
						</p>
					<?php } ?>
				</small>
			</div>
			<div class="card-body p-1">
				<a
					href="<?php echo base_url('admin/add_genre/' . $genre['id']); ?>"
					class="btn btn-icon btn-outline-info btn-sm"
					id="genre-edit-btn-<?php echo $genre['id']; ?>"
					style="display: none;margin-right:5px;"
				>
					<i class="mdi mdi-wrench"></i>
				</a>
				<a
					href="#"
					class="btn btn-icon btn-outline-warning btn-sm assign-category"
					id="genre-category-btn-<?php echo $genre['id']; ?>"
					style="display: none;margin-right:5px;"
					data-parent="<?=$genre['id'] ?>"
				>
					<i class="mdi mdi-link"></i>
				</a>
				<a
					href="#" class="btn btn-icon btn-outline-danger btn-sm"
					id="genre-delete-btn-<?php echo $genre['id']; ?>"
					onclick="confirm_modal('<?php echo base_url('admin/delete_genre/' . $genre['id']); ?>');"
					style="display: none;margin-right:5px;"
				>
					<i class="mdi mdi-delete"></i>
				</a>
			</div>
			<!-- end card-body-->
		</div>
		<!-- end card-->
	</div>
	<?php endforeach; ?>
</div>

<!-- Pagination Links -->
<div class="row">
	<div class="col-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="viewChildModel" tabindex="-1" role="dialog" aria-labelledby="viewChildModelLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewChildModelLabel"><?php echo _l('categories') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="modalImages"></div>
		</div>
	</div>
</div>

<div class="modal fade" id="assignCategoryModel" tabindex="-1" role="dialog" aria-labelledby="assignCategoryModel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?php echo _l('select_categories') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="assignCategories"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('.on-hover-action').mouseenter(function() {
		var id = this.id;
		$('#genre-delete-btn-' + id).show();
		$('#genre-category-btn-' + id).show();
		$('#genre-edit-btn-' + id).show();
	});
	$('.on-hover-action').mouseleave(function() {
		var id = this.id;
		$('#genre-delete-btn-' + id).hide();
		$('#genre-category-btn-' + id).hide();
		$('#genre-edit-btn-' + id).hide();
	});

	let categories = [];

	$.ajax({
		url: '/admin/ajax_all_categories',
		method: 'GET',
		success: function(response) {
			if (response.data && response.data.length > 0) {
				categories = response.data;
			}
		},
		error: function(xhr, status, error) {
			$('#assignCategories').html('<p><?php echo _l('error_loading') ?></p>');
		}
	});

	$('.assign-category').on('click', function(e) {
		e.preventDefault();

		const parent_id = $(this).data('parent');
		const selected = '';

		$('#assignCategories').empty();
		$('#assignCategoryModel').modal('show');

		$.get('<?=base_url('admin/ajax_genre_categories/')?>' + parent_id, function(json) {
			if (json.data) {
				const selected = json.data.map(item => item.id);

				if (categories.length > 0) {
					json.data.map(selectedItem => {
						const index = categories.findIndex(item => Number(item.id) === Number(selectedItem.id));
						const removed = categories.splice(index, 1);

						if (removed.length > 0) {
							categories.unshift(removed[0]);
						}
					});

					const content = categories.map(function(item) {
						if (item.image) {
							const checked = selected.includes(item.id) ? 'checked' : '';

							return `
							<div class="col-sm-2 col-xs-6 on-hover-action">
								<div class="card d-block">
									<img
										class="card-img-top"
										src="<?=$this->config->item('cloudfront_url')?>public/Categories/${item.image}?width=320&height=480"
										alt="Card image cap"
									/>
									<div class="card-body p-1 py-2">
										<div class="form-check">
											<input type="checkbox" class="form-check-input" name="category_ids[]" value="${item.id}" ${checked}>
											<label class="form-check-label">${item.name}-${item.id}</label>
										</div>
									</div>
								</div>
							</div>`;
						}
					}).join('');

					$('#assignCategories').html(`
						<form class="required-form" action="<?php echo base_url('admin/save_genre_categories'); ?>" method="post" enctype="multipart/form-data">
							<input type="hidden" name="genre_id" value="${parent_id}" />
							<div class="row">
								${content}
							</div>
							<div class="buttons d-block text-center">
								<button type="submit" class="btn btn-info"><?=_l('submit')?></button>
							</div>
						</form>
					`);
				} else {
					$('#assignCategories').html('<p><?php echo _l('no_data') ?></p>');
				}
			}
		});
	});

	$('.viewChild').on('click', function() {
		const parent_id = $(this).data('parent');

		$('#modalImages').empty();
		$('#viewChildModel').modal('show');

		$.ajax({
			url: '/admin/ajax_genre_categories/' + parent_id,
			method: 'GET',
			success: function(response) {
				if (response.data && response.data.length > 0) {
						const content = response.data.map(function(item) {
							if (item.image) {
								return `
								<div class="col-sm-2 col-xs-6 on-hover-action">
									<div class="card d-block">
										<img
											class="card-img-top"
											src="<?=$this->config->item('cloudfront_url')?>public/Categories/${item.image}?width=320&height=480"
											alt="Card image cap"
										/>
										<div class="card-body p-1">
											<h4 class="card-title mb-0">
												<i class="${item.font_awesome_class}"></i> ${item.name}-${item.id}
											</h4>
										</div>
										<div class="card-body p-1 text-center">
											<a
												href="<?=base_url('admin/add_category/') ?>${item.id}"
												class="btn btn-icon btn-outline-info btn-sm"
												id="genre-edit-btn-${item.id}"
												style="margin-right:5px;"
											>
												<i class="mdi mdi-wrench"></i>
											</a>
											<a
												href="#" class="btn btn-icon btn-outline-danger btn-sm"
												id="genre-delete-btn-${item.id}"
												onclick="confirm_modal('<?=base_url('admin/delete_category/') ?>${item.id}')"
												style="margin-right:5px;"
											>
												<i class="mdi mdi-delete"></i>
											</a>
										</div>
										<!-- end card-body-->
									</div>
									<!-- end card-->
								</div>`;
							}
						}).join('');

						$('#modalImages').html(`
							<div class="row">
								${content}
							</div>
						`);
				} else {
					$('#modalImages').html('<p><?php echo _l('no_images_available') ?></p>');
				}
			},
			error: function(xhr, status, error) {
				$('#modalImages').html('<p><?php echo _l('error_loading_images') ?></p>');
			}
		 });
	});
</script>
