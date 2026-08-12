<?php
$page_img_url = $this->config->item('s3_base_url') . $this->config->item('s3_themes');
$cover_img_url = $this->config->item('s3_base_url') . 'public/';
   ?>
<link href="https://fonts.googleapis.com/css2?family=Signika:wght@300;400;500;600;700" rel="stylesheet" />
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>

<style>
	.top-left {
		position: absolute;
		top: 50px;
		left: 100px;
	}

	.tox:not(.tox-tinymce-inline) .tox-editor-header {
		border: none !important;
		background-color: transparent;
	}

	.tox-tinymce {
		border: none;
	}

	.tox .tox-edit-area__iframe {
		background-color: transparent;
		border: none;
	}

	.sliding .tab {
		width: 100px;
		height: 100px;
		background-repeat: no-repeat;
		background-size: 100%;
	}

	.sliding .tab {
		opacity: 0.4;
	}

	.sliding.active .tab {
		opacity: unset !important;
		border: 1px solid #7c7f83;
	}

	/* a {
		text-decoration: none;
		display: inline-block;
		padding: 8px 16px;
	}

	a:hover {
		background-color: #ddd;
		color: black;
	} */
	.edit-col {
		background: transparent;
		border: none;
	}

	.previous {
		background-color: #f1f1f1;
		color: black;
		float: left;
		margin-top: -40%;
		z-index: 9;
		position: relative;
		text-decoration: none;
		display: inline-block;
		padding: 8px 16px;
	}

	.next {
		background-color: #f1f1f1;
		color: black;
		float: right;
		margin-top: -40%;
		z-index: 9;
		position: relative;
		text-decoration: none;
		display: inline-block;
		padding: 8px 16px;
	}

   .next:hover,
	.previous:hover {
		background-color: #ddd;
		color: black;
	}

	.frontcover {
		padding: 20px;
		background-position: center;
		background-size: cover;
		box-shadow: unset;
		box-shadow: 10px 10px 10px 0 rgb(0 0 0 / 20%);
	}

	.frontcover.page:after {
		content: "";
		position: absolute;
		top: 0;
		bottom: 0;
		right: 50%;
		width: 10%;
		background: linear-gradient(90deg, transparent, rgba(0, 0, 0, .1))
	}

	.frontcover .book-name {
		/* background-color: #fff; */
		text-transform: uppercase;
		padding: 0;
		margin: 0;
		overflow: hidden;
		/* border: 3px solid var(--warning-color); */
	}

	.book-info {
		background-color: rgba(255, 255, 255, 0.4);
		padding: 5px 10px;
		font-family: Signika;
		font-weight: 300;
		margin-left: -20px;
		margin-right: -20px;
		margin-bottom: -20px;
	}

	.backcover .author-name {
		font-size: 14px;
		color: rgb(16, 40, 75);
		text-align: right;
		margin: 0;
	}
</style>
<div class="row ">
   <div class="col-xl-12">
      <div class="card">
         <div class="card-body">
            <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
               <button class="btn btn-info btn-sm btn-rounded alignToTitle ml-2" data-toggle="modal" data-target="#communicate"><i class="mdi mdi-message"> </i><?php _el('write_comment'); ?></button>
               <?php if ($book['status'] == '2') { ?>
               <button class="btn btn-info btn-sm btn-rounded alignToTitle ml-2 rejected" data-id="<?= $book['id'] ?>">Reject</button>
               <button class="btn btn-info btn-sm btn-rounded alignToTitle ml-2" data-toggle="modal" data-target="#telecaller-modal"><i class="mdi mdi-message"> </i><?php _el('approved'); ?></button>
               <?php } ?>
               <?php if ($book['status'] == '1') { ?>
               <span class="btn btn-info btn-sm btn-rounded alignToTitle"> <?= _li('reviewer_rating') ?> : <?= $book['reviewer_rating'] ?> </span>
               <?php } ?>
               <?php if ($this->session->userdata('role_id') == '1') { ?>
               <?php if (0) { ?>
               <button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-delete-button"><i class="mdi mdi-trash"></i> Delete</button>
               <?php } ?>
               <a href="<?php echo site_url('admin/books'); ?>" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i> <?php //echo _l('back'); ?></a>
               <?php } ?>
            </h4>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
      <div class="btn-group btn-group-toggle">
         <label class="btn btn-outline-primary">
         <i class="mdi mdi-account"></i> <?= (!empty($book['author_name'])) ? $book['author_name'] : ''; ?>
         </label>
         <label class="btn btn-outline-primary">
         <i class="mdi mdi-book-open"></i> <?= (!empty($book['name'])) ? $book['name'] : ''; ?>
         </label>
         <label class="btn btn-outline-primary">
         <i class="mdi mdi-clock"></i> <?= (!empty($book['date_published'])) ? $book['date_published'] : ''; ?>
         </label>
         <a href="<?= USER_URL . 'bookstore/' . $book['slug'] ?>" class="btn btn-info" target="_blank"><i class="mdi mdi-eye"></i> <?= _li('live_preview') ?></a>
         <button class="btn btn-primary" style="margin-left: 1.6em;" onclick="contentupdate()"> Page Update </button>
         <button class="btn btn-primary" style="margin-left: .6em;" onclick="titleUpdate()"> Title Update </button>
      </div>
   </div>
</div>
<div class="row mt-5">
   <div class="col-sm-10 col-xs-12">
      <div class="d-flex justify-content-center">
         <div class="backcover main-div frontcover position-relative d-flex flex-column justify-content-end" id="book-front-cover" style="width:380px;height:535px;">
				<form method="post" id="update-title" action="<?php echo site_url('admin/title_update'); ?>">
               <h3 name="title" class="book-name" style="color: <?= (!empty($heading_style['color'])) ? $heading_style['color'] : '#000' ?>; position: absolute; top: <?= (!empty($heading_style['top'])) ? $heading_style['top'] : '10' ?>px; left: <?= (!empty($heading_style['left'])) ? $heading_style['left'] : '10' ?>px; right: <?= (!empty($heading_style['right'])) ? $heading_style['right'] : '10' ?>px; font-size: <?= (!empty($heading_style['fontSize'])) ? $heading_style['fontSize'] : '10' ?>px; text-align: <?= (!empty($heading_style['textAlign'])) ? $heading_style['textAlign'] : 'left' ?>; font-family: <?= (!empty($heading_style['fontFamily'])) ? $heading_style['fontFamily'] : 'Cilica' ?>;"><?= $book['name'] ?></h3>
               <input name="bookid" type="hidden" value="<?= $book['id'] ?>" />
					<!-- <div name="content" spellcheck="true" class="tried" style="color:' . $page['font_color'] . ';"> ' . $text . ' </div> -->
            </form>
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
            		if (in_array($this->session->userdata('user_id'), book_edit) || 1) {
            			$form = '
					<span>Maximum Word Limit : ' . $design['p']['l'] . '</span>
					<form method="post" id="update-content" action="'.site_url("admin/page_update").'">
					<input name="pageid" type="hidden" value="' . $page['id'] . '" />
					<textarea name="content" spellcheck="true" class="tried" style="color:' . $page['font_color'] . ';"> ' . $text . ' </textarea>
					 <input name="page_index" type="hidden" value="' . $ctr . '" />
                     </form>
                     ';
            		} else {
            			$form = $text;
            		}
                  ?>
               <div class="p-1 sliding <?php echo ($ctr == $page_index) ? 'active' : '' ?>" style="cursor: pointer;">
			   <div data-style="width: <?= $design['p']['w'] ?>px; height: <?= $design['p']['h'] ?>px; left: <?= $design['p']['x'] ?>px; top: <?= $design['p']['y'] ?>px; font-family: <?= $page['font_family'] ?>; font-size: <?= $page['font_size'] ?>px; font-weight: <?= $page['font_weight'] ?>; color: <?= $page['font_color'] ?>;" data-text="<?php echo htmlspecialchars($form); ?>" data-pageid="<?= $page['id'] ?>" data-img="<?php echo $page_img_url . $page['image']; ?>" style="background-image: url('<?php echo $page_img_url . $page['image']; ?>');" class="tab">
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
            <h4 class="modal-title">Put Your Comment</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
         </div>

         <div class="modal-body p-3">
				<form action="<?php echo site_url('admin/book_review_comment'); ?>/<?= (!empty($book['id'])) ? $book['id'] : ''; ?>" method="post" id="form-telecaller">
               <div class="form-group">
                  <input type="hidden" name="status" value="1">
               </div>
               <div class="form-group">
                  <input type="radio" name="rating" value="0"> 0 </input>
                  <input type="radio" name="rating" value="1" class="ml-2"> 1 </input>
                  <input type="radio" name="rating" value="2" class="ml-2"> 2 </input>
                  <input type="radio" name="rating" value="3" class="ml-2"> 3 </input>
                  <input type="radio" name="rating" value="4" class="ml-2"> 4 </input>
                  <input type="radio" name="rating" value="5" class="ml-2"> 5 </input>
               </div>
            </form>

            <div class="text-right pt-2">
               <button type="button" class="btn btn-light" data-dismiss="modal"><?php _el('close'); ?>
               </button>
               <button type="submit" form="form-telecaller" class="btn btn-primary ml-1 save">Save
               </button>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="communicate">
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
               <div class="text-right pt-2">
                  <button type="button" class="btn btn-light" data-dismiss="modal"><?php _el('close'); ?>
                  </button>
                  <button type="submit" class="btn btn-primary ml-1">Send
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<script src=<?= base_url('assets/backend/js/vendor/html2canvas.min.js?v=1.2') ?>></script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>

<script>
   $('.rejected').click(function(e) {
   	var book_id = $(this).data("id");
   	let status = "0";
   	if (confirm("Are you sure?")) {
   		$.ajax({
				url: "<?= site_url('admin/book_review_comment/'); ?>" + book_id,
   			type: "POST",
   			data: {
   				status: status
   			},
   			cache: false,
   			success: function(response) {
   				window.location.href = '';
   			}
   		});
   	}
   
   })
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
   let edit_mode = false;
   
   function tinyfun() {
   	tinymce.init({
   		selector: 'textarea',
   		branding: false,
   		force_br_newlines: true,
   		menubar: false,
   		statusbar: false,
   		force_p_newlines: false,
   		forced_root_block: '',
   		toolbar: '',
   		content_style: `
            body { color: ${$('.tried')[0]?.style.color || 'inherit'}; }
            p { margin-bottom: -14px; },
            img { width: 280px !important; height: auto !important; }
        `,
   		browser_spellcheck: true,
   	});
   }
   
   function titleUpdate() {
   	$('#update-title').submit();
   }
   
   function contentupdate() {
   	$('#update-content').submit();
   }
   
   function setDesign(el, front = false) {
      console.log("Element data-text:", el.attr('data-text'));
      console.log("Element data-img:", el.attr('data-img'));

      $('.main-div').css('background-image', 'url("' + el.attr('data-img') + '")');
      $('.book-text').html(el.attr('data-text'));
      $('.book-text').attr('style', el.attr('data-style'));

      if ($('.sliding.active').is($('.sliding').first()) && <?= (!empty($book['cover_image']) ? 1 : 0) ?> == 1) {
         $('.book-name, .book-info, #btn-fix').removeClass('d-none');
         $('.main-div').css('width', '380px');
         $('.main-div').removeClass('page');
      } else {
         $('.book-name, .book-info, #btn-fix').addClass('d-none');
         $('.main-div').css('width', '805px');
         $('.main-div').addClass('page');
      }
   }

   $(document).ready(function() {
      $('.sliding').click(function() {
         $('.sliding.active').removeClass('active'); 
         $(this).addClass('active'); 
         setDesign($(this).children('.tab'));
         tinyfun();
      });

      $('.next').click(function() {
         if (!$('.sliding.active').is($('.sliding').last())) {
            $('.sliding.active').removeClass('active').next().addClass('active');
            $('.sliding.active').trigger('click');
   			tinyfun();
         }
      });

      $('.previous').click(function() {
         if (!$('.sliding.active').is($('.sliding').first())) {
            $('.sliding.active').removeClass('active').prev().addClass('active');
            $('.sliding.active').trigger('click');
   			tinyfun();
         }
      });
      const activeSlide = $('.sliding.active');

      if (activeSlide.length) {
         setDesign(activeSlide.children('.tab'));
         tinyfun();
      } else {         
         $('.sliding').first().addClass('active').trigger('click');
      }
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
<script type="text/javascript">
   $(document).ready(function() {
   	tinyMCE.init({
   		branding: false,
   		force_br_newlines: true,
   		menubar: false,
   		statusbar: false,
   		force_p_newlines: false,
   		forced_root_block: '',
   		selector: '.book-name',
   		toolbar: "",
   		inline: true,
   		browser_spellcheck: true,

			// content_css: ,
   	});
   
   });
</script>
