<?php $form_id = uniqid('form_'); ?>
<form method="post" action="<?php echo $action ; ?>" id="<?php echo $form_id; ?>">
	<?php foreach ($fields as $field) { ?>
	<?php if ($field['type'] == 'email') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<input type="email" name="<?php echo $field['field_id']; ?>" value="" placeholder="<?php echo $field['name']; ?>" />
	</div>
	<?php } elseif ($field['type'] == 'mobile') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<input type="tel" name="<?php echo $field['field_id']; ?>" value="" placeholder="<?php echo $field['name']; ?>" />
	</div>
	<?php } elseif ($field['type'] == 'location') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<input type="text" name="<?php echo $field['field_id']; ?>[]" value="" placeholder="<?php echo $field['name']; ?>" class="location-auto" />
		<input type="hidden" name="<?php echo $field['field_id']; ?>[]" value="" id="input-latlng" class="form-control" />
		<?php echo $gmap; ?>
	</div>
	<?php } elseif ($field['type'] == 'text') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<input type="text" name="<?php echo $field['field_id']; ?>" value="" placeholder="<?php echo $field['name']; ?>" />
	</div>
	<?php } elseif ($field['type'] == 'textarea') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<textarea name="<?php echo $field['field_id']; ?>" placeholder="<?php echo $field['name']; ?>"></textarea>
	</div>
	<?php } elseif ($field['type'] == 'select') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<select name="<?php echo $field['field_id']; ?>">
			<?php foreach ($field['value'] ?? [] as $value) { ?>
			<option value="<?php echo $value['name']; ?>"><?php echo $value['name']; ?></option>
			<?php } ?>
		</select>
	</div>
	<?php } elseif ($field['type'] == 'radio') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<?php foreach ($field['value'] ?? [] as $value) { ?>
		<label><input type="radio" name="<?php echo $field['field_id']; ?>" value="<?php echo $value['name']; ?>" checked="checked"> <?php echo $value['name']; ?></label>
		<label><input type="radio" name="<?php echo $field['field_id']; ?>" value="<?php echo $value['name']; ?>"> <?php echo $value['name']; ?></label>
		<?php } ?>
	</div>
	<?php } elseif ($field['type'] == 'checkbox') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<?php foreach ($field['value'] ?? [] as $value) { ?>
		<label><input type="checkbox" name="<?php echo $field['field_id']; ?>" value="<?php echo $value['name']; ?>" checked="checked"> <?php echo $value['name']; ?></label>
		<label><input type="checkbox" name="<?php echo $field['field_id']; ?>" value="<?php echo $value['name']; ?>"> <?php echo $value['name']; ?></label>
		<?php } ?>
	</div>
	<?php } elseif ($field['type'] == 'file') { ?>
	<div>
		<label>
			<?php echo $field['name']; ?>
			<?php if ($field['required']) { ?>
			<span>*</span>
			<?php } ?>
		</label>
		<input type="file" name="<?php echo $field['field_id']; ?>" value="" placeholder="<?php echo $field['name']; ?>" />
	</div>
	<?php } ?>
	<?php } ?>

	<input type="submit" value="<?php echo _l('submit'); ?>"/>
</form>

<script>
const submitForm = (el) => {
	const requestOptions = {
		method: 'POST',
		body: new FormData(el)
	};

	fetch(el.getAttribute('action'), requestOptions)
	.then(response => response.json())
	.then(json => {
		document.dispatchEvent(new CustomEvent('FORM_SAVED', {detail: json}));
	});
}

document.getElementById('<?php echo $form_id; ?>').addEventListener('submit', function(e){
	e.preventDefault();
	submitForm(this);
});
</script>
