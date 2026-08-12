<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=$page_title?>
				<a href = "/admin/event" class="btn btn-outline-dark btn-rounded alignToTitle ml-2"><i class="fa fa-arrow-left"></i><?php echo _l('back'); ?></a>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3"><?php echo $page_title; ?></h4>
				<div id="progressbarwizard">
					<?=$nav?>

					<div class="tab-content b-0 mb-0">
						<div id="bar" class="progress mb-3" style="height: 7px;">
							<div class="bar progress-bar progress-bar-striped progress-bar-animated bg-success"></div>
						</div>

						<div id="stage-load" class="mb-3"></div>

						<ul class="list-inline mb-0 wizard">
							<li class="previous-btn list-inline-item">
								<a href="javascript:void(0)" class="btn btn-info"><?=_l('previous')?></a>
							</li>
							<li class="next-btn list-inline-item float-right">
								<a href="javascript:void(0)" class="btn btn-info"><?=_l('next')?></a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.is-invalid~.invalid-feedback {
	display: block;
}
</style>

<script>
function changeUrl(url = '', title = '<?= _l('event') ?>') {
	if (url.length === 0) return;
	if (location.pathname.includes(url)) return;

	if (typeof (history.pushState) != 'undefined') {
		const obj = { Title: title, Url: '<?= $action ?>' + url };
		history.pushState(obj, obj.Title, obj.Url);
	}
}
function loadStage(target) {
	const stage = target.data('stage');

	document.querySelector(`[data-stage=${stage}]`).scrollIntoView({ behavior: 'smooth', block: 'end' });

	$('#stage-load').html('');
	$('#stage-load').load('<?=$action_stage?>' + stage);
	changeUrl(stage);
}
</script>
<script>
$('.btn-stage').on('click', function(e) {
	const target = $(this);
	loadStage(target);
});
$(function() {
	const target = $('.btn-stage[data-stage="<?= $current_stage ?>"]');
	loadStage(target);
	setTimeout(() => target.click(), 500);
});
</script>

<script>
function submitEventForm(target, cb = null) {
	if (confirm('<?= _li('Are you sure?') ?>')) {
		$('.invalid-feedback').remove();
		$('.form-control').removeClass('is-invalid');

		tinymce && tinymce.triggerSave();

		submitForm(target.attr('action'), new FormData(target[0]), json => {
			if (json.success) {
				success_notify(json.success);

				if (cb) {
					cb(json)
				} else {
					const target = $('.btn-stage.active').parent().next().find('.btn-stage');
					target.click();
					loadStage(target);
				}

				if (json?.redirect) {
					setTimeout(() => window.location = json.redirect, 300);
				}
			} else {
				json?.error && error_notify(json.error);

				Object.entries((json?.errors ?? {})).map(([key, error], index) => {
					key = key.replace(/[^\w\-_]/img, '');
					$('#' + key).addClass('is-invalid');
					$('#' + key).parent().append(`<span class="invalid-feedback">${error}</span>`);
				});
			}
		});
	}
	return false;
}
</script>

<script>
$(document).on('click', '.next-btn .btn, .previous-btn .btn', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	const form = $('#stage-load').find('form');

	if (form.find('button.save').length > 0 && $el.text() == 'Next') {
		submitEventForm(form);
	} else {
		let target = [];

		if ($el.text() == 'Next') {
			target = $('.btn-stage.active').parent().next().find('.btn-stage');
		} else {
			target = $('.btn-stage.active').parent().prev().find('.btn-stage');
		}

		if (target.length > 0) {
			target.trigger('click');
			loadStage(target);
		}
	}
});
</script>

<script>
$(function() {
	window['FILEMANAGER'] = '<?= base_url('servermanager') ?>';
});
</script>
<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.3') ?>"></script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>
<script type="text/javascript">
window.tinyconfig = {
	selector: '.tinymce',
	menubar: false,
	plugins: [
		'link', 'lists',
		'autolink'
	],
	toolbar: [
		'undo redo | bold italic underline | fontsize | forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent'
	],
	valid_elements: 'p[style],strong,em,span[style],a[href],ul,ol,li',
	valid_styles: {
		'*': 'font-size,font-family,color,text-decoration,text-align'
	},
	powerpaste_word_import: 'clean',
	powerpaste_html_import: 'clean',
	branding: false,
	force_br_newlines: true,
	force_p_newlines: false,
	forced_root_block: '',
	relative_urls : false,
	remove_script_host : false,
	document_base_url : "/",
	convert_urls : false,
};
$(function() {
	tinymce.init(tinyconfig);
})
</script>
