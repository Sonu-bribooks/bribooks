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
						href="<?php echo base_url('admin/add_category'); ?>"
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
		foreach ($categories as $category) :
			$total = $this->category_model->get_all(['parent_id' => $category['id']])['total'] ?? 0;
	?>
	<div class="col-sm-2 col-xs-6 on-hover-action" id="<?php echo $category['id']; ?>">
		<div class="card d-block">
			<img
				class="card-img-top"
				src="<?=$this->config->item('cloudfront_url')?>public/Categories/<?php echo $category['image']; ?>?width=320&height=480"
				alt="Card image cap"
			/>
			<div class="card-body p-1">
				<h4 class="card-title mb-0">
					<i class="<?php echo $category['font_awesome_class']; ?>"></i> <?php echo $category['name']; ?>
				</h4>
				<small style="font-style: italic;">
					<?php if ($total > 0) { ?>
						<a
							href="#"
							class="card-text viewChild"
							data-toggle="modal"
							data-target="#viewChildModel"
							style="cursor: pointer;"
							data-parent="<?=$category['id'] ?>"
						>
							<?php echo $total . ' ' . _l('sub_categories'); ?>
						</>
					<?php } else { ?>
						<p class="card-text viewChild">
							<?php echo $total . ' ' . _l('sub_categories'); ?>
						</p>
					<?php } ?>
				</small>
			</div>
			<div class="card-body p-1">
				<a
					href="<?php echo base_url('admin/add_category/' . $category['id']); ?>"
					class="btn btn-icon btn-outline-info btn-sm"
					id="category-edit-btn-<?php echo $category['id']; ?>"
					style="display: none;margin-right:5px;"
				>
					<i class="mdi mdi-wrench"></i> <?php echo _l('edit'); ?>
				</a>
				<a
					href="#" class="btn btn-icon btn-outline-danger btn-sm"
					id="category-delete-btn-<?php echo $category['id']; ?>"
					onclick="confirm_modal('<?php echo base_url('admin/delete_category/' . $category['id']); ?>');"
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
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewChildModelLabel"><?php echo _l('child_categories') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="modalImages"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('.on-hover-action').mouseenter(function() {
		 var id = this.id;
		 $('#category-delete-btn-' + id).show();
		 $('#category-edit-btn-' + id).show();
	});
	$('.on-hover-action').mouseleave(function() {
		 var id = this.id;
		 $('#category-delete-btn-' + id).hide();
		 $('#category-edit-btn-' + id).hide();
	});

	$('.viewChild').on('click', function() {
		const parent_id = $(this).data('parent');

		$.ajax({
			url: '/admin/ajax_subcategories/' + parent_id,
			method: 'GET',
			success: function(response) {
				$('#modalImages').empty();

				if (response.data && response.data.length > 0) {
						const content = response.data.map(function(item) {
							if (item.image) {
								return `
								<div class="col-sm-3 col-xs-6 on-hover-action">
									<div class="card d-block">
										<img
											class="card-img-top"
											src="<?=$this->config->item('cloudfront_url')?>public/Categories/${item.image}?width=320&height=480"
											alt="Card image cap"
										/>
										<div class="card-body p-1">
											<h4 class="card-title mb-0">
												<i class="${item.font_awesome_class}"></i> ${item.name}
											</h4>
										</div>
										<div class="card-body p-1 text-center">
											<a
												href="<?=base_url('admin/add_category/') ?>${item.id}"
												class="btn btn-icon btn-outline-info btn-sm"
												id="category-edit-btn-${item.id}"
												style="margin-right:5px;"
											>
												<i class="mdi mdi-wrench"></i>
											</a>
											<a
												href="#" class="btn btn-icon btn-outline-danger btn-sm"
												id="category-delete-btn-${item.id}"
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

				$('#viewChildModel').modal('show');
			},
			error: function(xhr, status, error) {
				$('#modalImages').html('<p><?php echo _l('error_loading_images') ?></p>');
			}
		 });
	});
</script>
