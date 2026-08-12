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
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<a href="<?php echo base_url('admin/add_cover'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
						<i class="mdi mdi-plus"> </i> <?= _l('add_new'); ?>
					</a>
					<a href="<?php echo base_url('admin/add_bulk_cover'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
						<i class="mdi mdi-plus"> </i> <?= _l('add_bulk'); ?>
					</a>
				</h4>
				<div style="float:right; display:flex; margin-top:14px">
					<input type="text" class="form-control search" value="<?php if ($search != '') echo $search; ?>" placeholder="search..." style="width:200px; margin-right:5px;">
					<button type="submit" class="btn btn-outline-primary" id="searchCover"> <?= _l('submit'); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="covers-content">
	<div class="row">
		<?php if (!empty($covers)) { ?>
			<?php foreach ($covers as $cover) { ?>
				<div class="col-sm-2 col-xs-6 on-hover-action" id="<?= $cover['id']; ?>">
					<div class="card d-block">
						<img
							class="card-img-top img-thumbnail"
							src="<?=$this->config->item('cloudfront_url')?>public/<?php echo $cover['image']; ?>?width=320&height=480"
							alt="<?php echo $cover['category']; ?>"
						>
						<div class="card-body p-1">
							<h4 class="card-title mb-0"><?php echo $cover['category']; ?></h4>
							<small style="font-style: italic;">
								<p class="card-text"><?php echo substr($cover['image'], strrpos($cover['image'], '/') + 1); ?></p>
							</small>
							<small>
								<p class="card-text"> <?= _l('books'); ?>: <b><?php echo $this->book_model->get_all(['cover_id' => $cover['id']])['total']; ?></b></p>
							</small>
						</div>
						<div class="card-body p-1">
							<a href="/admin/add_cover/<?php echo $cover['id']; ?>" class="btn btn-icon btn-outline-info btn-sm" id="covers-edit-btn-<?= $cover['id']; ?>" style="display: none; margin-right:5px;">
								<i class="mdi mdi-wrench"></i> <?=_l('edit') ?>
							</a>
							<a href="#" class="btn btn-icon btn-outline-danger btn-sm" id="covers-delete-btn-<?= $cover['id']; ?>" style="float: right; display: none; margin-right:5px;" onclick="confirm_modal('<?php echo base_url('admin/delete_cover/' . $cover['id']); ?>');">
								<i class="mdi mdi-delete"></i>
							</a>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="col-sm-12">
				<h4> <?= _l('no_record_found_...'); ?></h4>
			</div>
		<?php } ?>
	</div>
</div>

<!-- Pagination Links -->
<div class="row">
	<div class="col-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>

<script type="text/javascript">
	$('.on-hover-action').mouseenter(function() {
		var id = this.id;
		$('#covers-delete-btn-' + id).show();
		$('#covers-edit-btn-' + id).show();
	}).mouseleave(function() {
		var id = this.id;
		$('#covers-delete-btn-' + id).hide();
		$('#covers-edit-btn-' + id).hide();
	});
	$('#searchCover').on('click', function() {
		var value = $('.search').val();
		var encodedValue = encodeURIComponent(value);
		window.location.href = '/admin/covers?search=' + encodedValue;
	});

</script>
