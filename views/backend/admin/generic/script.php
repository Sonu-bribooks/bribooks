<script>
$(function() {
	window['FILEMANAGER'] = '<?= base_url('servermanager') ?>';
});
</script>
<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.7') ?>"></script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>
<script>
function initDatetime(inputTarget) {
	$(inputTarget).datetimepicker({
		format: 'MM/DD/YYYY hh:mm:ss A',
		showClose: true,
	});
}
function initSelect(inputTarget) {
	$(inputTarget).select2({});
}
function initSelectAjax(inputTarget) {
	$(inputTarget).select2({
		multiple: $(this).prop('multiple'),
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				const href = $(this).data('ajax-url');
				const sp = new URLSearchParams((new URL(href)).search);

				const includes = sp.get('includes');
				const data = {};

				if (includes) {
					includes.split(',').forEach(item => {
						const value = $(`#${item}`).val();
						const name = $(`#${item}`).prop('name');
						data[name]= value;
					});
				}

				return {
					search: params.term,
					...data,
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 3
	});
}
function initSelectAjaxWithOptions(inputTarget) {
	$(inputTarget).on('change', function() {
		$el = $(this);

		if (!$el.attr('data-ajax-options')) {
			return;
		}

		const value = $el.val();
		let href = $el.attr('data-ajax-options');
		const sp = new URLSearchParams((new URL(href)).search);
		const target = sp.get('target');
		const input = sp.get('input');
		const includes = sp.get('includes');
		const values = $(`#${target}`).data('value').toString();
		const targetValue = values ? values.split(',') : [];
		const targetName = $(`#${target}`).data('name');

		if (includes) {
			includes.split(',').forEach(item => {
				const value = $(`#${item}`).val();
				const name = $(`#${item}`).prop('name');
				href += `&${name}=${value}`;
			});
		} else {
			href += value;
		}

		$(`#${target}`).html('');

		$.get(href, json => {
			if (json.length > 0) {
				const options = json.map(item => {
					const selected = targetValue.includes(item.id)
						? ((input == 'select' || input == 'select2') ? 'selected' : 'checked')
						: '';

					if (input == 'select' || input == 'select2') {
						return (
							`<option value="${item.id}" ${selected}>${item.text}</option>`
						);
					}

					return (
						`<div class="form-check">
							<input
								class="form-check-input"
								type="checkbox"
								name="${targetName}"
								value="${item.id}"
								${selected}
							/>
							<label class="form-check-label" for="defaultCheck1">
								${item.text}
							</label>
						</div>`
					);
				});
				$(`#${target}`).html(options.join(''));
			}
		});
	});

	$(inputTarget).trigger('change');
}
function initSelectChange(inputTarget) {
	$(inputTarget).on('change', function() {
		$el = $(this);
		const target = $el.data('target');

		if (this.value == 0) {
			$(`.${target}`).hide();
			$(`.${target} select, .${target} input`).prop('required', false);
			$(`.${target} label .required`).remove();
		} else {
			$(`.${target}`).show();
			$(`.${target} select, .${target} input`).prop('required', true);
			$(`.${target} label`).append('<span class="required">*</span>');
		}
	});

	$(inputTarget).trigger('change');
}
function initTinymce(inputTarget) {
	const tinyconfig = {
		selector: inputTarget,
		branding: false,
		force_br_newlines: true,
		force_p_newlines: false,
		forced_root_block: '',
		plugins: 'lists code emoticons link',
		toolbar: 'undo redo | styleselect | bold italic | ' +
			'alignleft aligncenter alignright alignjustify | ' +
			'outdent indent | numlist bullist | emoticons',
	};

	tinymce.remove();
	tinymce.init(tinyconfig);
}

function loadGroupItem(target) {
	const rootContainer = $(target).parent().parent();
	const groupContainer = rootContainer.find('.group-container');
	const itemClone = rootContainer.find('.group-item').first().clone();
	const indexContainer = $(target).parent().find('.group-item-index');
	const currentIndex = parseInt(indexContainer.text());

	itemClone.find('.select2-container').remove();
	itemClone.find('.tox-tinymce').remove();
	itemClone.find('.remove-container').removeClass('d-none')

	itemClone.find('input, select, textarea').each(function() {
		const el = $(this);

		if (el.attr('type') === 'color') {
			el.val('#000000'); // default color
		} else {
			el.val('');
		}

		el.prop('checked', false);
		el.prop('name', el.prop('name').replace(/\[\d\]/, `[${currentIndex}]`));

		if (el.attr('id')) {
			el.prop('id', el.prop('id') + '_' + currentIndex);
		} else {
			el.prop('id', 'field_' + currentIndex + '_' + Date.now());
		}

		el.removeAttr('data-select2-id');

		if (el.is('select')) {
			el.find('option').removeAttr('data-select2-id');

			el.next('.select2').remove();
		}
	});

	itemClone.find('.image-field').each(function () {
		const base = $(this).data('image-base');

		const uid = base + '_' + currentIndex + '_' + Date.now();

		$(this).find('.image-input')
			.attr('id', uid)
			.val('');

		$(this).find('.img-thumbnail')
			.attr('id', 'logo-thumb-' + uid)
			.attr('data-target', '#' + uid);

		const img = $(this).find('img');
		img.attr('src', img.data('placeholder'));
	});

	itemClone.find('.color-field').each(function () {
		const base = $(this).data('color-base');
		const uid  = base + '_' + currentIndex + '_' + Date.now();

		$(this).find('.color-picker')
			.attr('id', uid)
			.attr('data-target', uid + '_hex')
			.val('#ffffff');

		$(this).find('.color-hex')
			.attr('id', uid + '_hex')
			.val('#ffffff');
	});

	itemClone.appendTo(groupContainer);
	indexContainer.text(currentIndex + 1);

	initSelect('[data-toggle="select2"]');
	initDatetime('.datetimepicker');

	itemClone.find('.filter_select, .filter_multi_select').each(function() {
		$el = $(this);
		initSelectAjax($el);
	});

	itemClone.find('.tinymce').each(function() {
		$el = $(this);
		$el.removeAttr('aria-hidden');
		$el.show();
		initTinymce('#' + $el.attr('id'));
	});
}

$(function() {
	initDatetime('.datetimepicker');
	initSelectAjax('.filter_select.select_ajax');
	initTinymce('.tinymce');
	initSelectAjaxWithOptions('select[data-ajax-options]');
	initSelectChange('select[data-target]');
});

document.addEventListener('input', function (e) {
	if (e.target.classList.contains('color-picker')) {
		const hexInput = document.getElementById(e.target.dataset.target);
		if (hexInput) hexInput.value = e.target.value;
	}

	if (e.target.classList.contains('color-hex')) {
		const value = e.target.value;
		if (/^#([0-9A-Fa-f]{6})$/.test(value)) {
			const picker = document.getElementById(e.target.id.replace('_hex', ''));
			if (picker) picker.value = value;
		}
	}
});

function generate() {
	document.getElementById('gen_code').value = Math.random().toString(36).slice(2).toUpperCase();
}
</script>
