<?php
$page_img_url = $this->config->item('s3_base_url') . $this->config->item('s3_themes');
$cover_img_url = $this->config->item('s3_base_url') . 'public/';
?>

<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<button class="btn btn-info btn-sm btn-rounded alignToTitle ml-2" data-toggle="modal" data-target="#telecaller-modal"><i class="mdi mdi-message"> </i><?php _el('write_comment'); ?></button>
					<a href="/admin/books" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i></a>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="btn-group btn-group-toggle" data-toggle="buttons">
			<label class="btn btn-outline-primary">
				<i class="mdi mdi-account"></i> <?= (!empty($book['author_name'])) ? $book['author_name'] : ''; ?>
			</label>
			<label class="btn btn-outline-primary">
				<i class="mdi mdi-book-open"></i> <?= (!empty($book['name'])) ? $book['name'] : ''; ?>
			</label>
			<label class="btn btn-outline-primary">
				<i class="mdi mdi-clock"></i> <?= (!empty($book['date_published'])) ? $book['date_published'] : ''; ?>
			</label>
		</div>
	</div>
</div>
<div class="row mt-5">
	<div class="col-sm-10 col-xs-12">

		<div class="d-flex justify-content-center">
			<div class="backcover main-div frontcover position-relative d-flex flex-column justify-content-end" id="book-front-cover" style="width:380px;height:535px;">
				<h3 class="book-name" style="color: <?= (!empty($heading_style['color'])) ? $heading_style['color'] : '#000' ?>; position: absolute; top: <?= (!empty($heading_style['top'])) ? $heading_style['top'] : '10' ?>px; left: <?= (!empty($heading_style['left'])) ? $heading_style['left'] : '10' ?>px; right: <?= (!empty($heading_style['right'])) ? $heading_style['right'] : '10' ?>px; font-size: <?= (!empty($heading_style['fontSize'])) ? $heading_style['fontSize'] : '10' ?>px; text-align: <?= (!empty($heading_style['textAlign'])) ? $heading_style['textAlign'] : 'left' ?>; font-family: <?= (!empty($heading_style['fontFamily'])) ? $heading_style['fontFamily'] : 'Cilica' ?>;"><?= $book['name'] ?></h3>
				<div class="book-info">
					<p class="author-name">Written by <?= $book['author_name'] ?></p>
				</div>
				<p class="top-left book-text"></p>
			</div>
		</div>
		<div class="d-flex justify-content-center mt-2">
			<button class="btn btn-info" id="btn-fix" onclick="_createCoverImage()">Fix Front Cover</button>
		</div>

		<a href="javascript:void(0)" class="previous">&laquo;</a>
		<a href="javascript:void(0)" class="next">&raquo;</a>
	</div>
	<div class="col-sm-2 col-xs-12" style="max-height:600px; overflow:auto;">
		<div class="row">
			<?php if (!empty($book['cover_image'])) { ?>
				<div class="p-1 sliding" style="cursor: pointer;">
					<div data-fontfamily="" data-fontsize="" data-fontcolor="" data-weight="" data-text="" data-img="<?= (!empty($book['cover_image'])) ? $cover_img_url . $cover_info['image'] : ''; ?>" style="background-image: url('<?= (!empty($book['cover_image'])) ? $cover_img_url . $cover_info['image'] : ''; ?>');" class="tab active">
						<p class="p-3">1</p>
					</div>
				</div>
			<?php } ?>
			<?php
			if (!empty($pages['rows'])) {
				$ctr = 2;
				foreach ($pages['rows'] as $page) {
					$texts = json_decode(trim($page['texts']), true);
					$text = $texts[0] ?? '';
					$text_boxes = json_decode($page['text_boxes'], true);
					$design = $text_boxes[0];
			?>
					<div class="p-1 sliding" style="cursor: pointer;">
						<div data-style="width: <?= $design['p']['w'] ?>px; height: <?= $design['p']['h'] ?>px; left: <?= $design['p']['x'] ?>px; top: <?= $design['p']['y'] ?>px; font-family: <?= $page['font_family'] ?>; font-size: <?= $page['font_size'] ?>px; font-weight: <?= $page['font_weight'] ?>; color: <?= $page['font_color'] ?>;" data-text="<?php echo strip_tags($text); ?>" data-img="<?php echo $page_img_url . $page['image']; ?>" style="background-image: url('<?php echo $page_img_url . $page['image']; ?>');" class="tab">
							<p class="p-3"><?php echo $ctr++; ?></p>
							<p></p>
						</div>
					</div>
			<?php }
			} ?>
		</div>
	</div>
</div>

<div class="modal fade" id="telecaller-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Put Your Comment To Communicate</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>

			<div class="modal-body p-3">
				<form action="<?php echo site_url('admin/communicate_mail'); ?>/<?= (!empty($book['id'])) ? $book['id'] : ''; ?>" method="post" id="form-telecaller">
					<div class="form-group">
						<label for="class">Subject</label>
						<input type="text" class="form-control" name="mail_subject">
					</div>
					<div class="form-group">
						<label for="class">Your Comment</label>
						<textarea class="form-control" name="comment" require></textarea>
					</div>
				</form>

				<div class="text-right pt-2">
					<button type="button" class="btn btn-light" data-dismiss="modal"><?php _el('close'); ?>
					</button>
					<button type="submit" form="form-telecaller" class="btn btn-primary ml-1 save">Send
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script src=<?= base_url('assets/backend/js/vendor/html2canvas.min.js?v=1.2') ?>></script>
<script>
	async function _createCoverImage() {
		const el = document.querySelector('#book-front-cover')
		const htmlCanvas = await html2canvas(el, {
			allowTaint: true,
			useCORS: true,
		});

		$('#btn-fix').prop('disabled', true);

		htmlCanvas.toBlob((blob) => {
			const file = new File([blob], 'cover.png', {
				type: 'image/png',
			})

			const fd = new FormData();
			fd.append('image', file);

			$('#book-front-cover').css('background-image', 'url("' + URL.createObjectURL(blob) + '")');

			// upload cover image
			submitForm('<?php echo base_url('admin/book_front_cover/' . $book['id']); ?>', fd, json => {
				json.error && error_notify(json.error)
				json.success && success_notify(json.success)
				$('#btn-fix').prop('disabled', false);
			});
		}, 'image/png');
	}
</script>
<script>
	function setDesign(el, front = false) {
		$('.main-div').css('background-image', 'url("' + el.data('img') + '")');
		$('.book-text').html(el.data('text'));
		$('.book-text').attr('style', el.data('style'))

		if ($('.sliding.active').is($('.sliding').first()) && <?= (!empty($book['cover_image']) ? 1 : 0) ?> == 1) {
			$('.book-name, .book-info, #btn-fix').removeClass('d-none');
			$('.main-div').css('width', '380px');
			$('.main-div').removeClass('page');
		} else {
			$('.book-name, .book-info, #btn-fix').addClass('d-none');
			$('.main-div').css('width', '760px');
			$('.main-div').addClass('page');
		}
	}
	$(document).ready(function() {
		$('.sliding').click(function() {
			setDesign($(this).addClass('active').children('.tab'))
		});

		$('.next').click(function() {
			if (!$('.sliding.active').is($('.sliding').last())) {
				$('.sliding.active').removeClass('active').next().addClass('active');
				$('.sliding.active').trigger('click');
			}
		});

		$('.previous').click(function() {
			if (!$('.sliding.active').is($('.sliding').first())) {
				$('.sliding.active').removeClass('active').prev().addClass('active');
				$('.sliding.active').trigger('click');
			}
		});

		$('.sliding').first().addClass('active').trigger('click');
	});

	$(".bulk-delete-button").on('click', function(event) {
		event.preventDefault();
		var ids = [];
		ids.push('<?= $book['id']; ?>');
		if (confirm("Are you sure?")) {
			$.ajax({
				url: '/admin/delete_book',
				type: "POST",
				data: {
					ids: ids,
				},
				cache: false,
				success: function(response) {
					var data = JSON.parse(response);
					if (data.status)
						window.location.replace("/admin/books");
					else
						alert(data.message);
				}
			});
		}
	});
</script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>

<script type="text/javascript">
	$(document).ready(function() {
		tinymce.init({
			selector: 'textarea',
			branding: false,
			force_br_newlines: true,
			force_p_newlines: false,
			forced_root_block: '',
			plugins: 'lists code emoticons',
			toolbar: 'undo redo | styleselect | bold italic | ' +
				'alignleft aligncenter alignright alignjustify | ' +
				'outdent indent | numlist bullist | emoticons',
		});
	});
</script>