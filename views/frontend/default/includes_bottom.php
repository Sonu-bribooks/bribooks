<script src="<?php echo base_url().'assets/frontend/default/js/vendor/modernizr-3.5.0.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/vendor/jquery-3.2.1.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/popper.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/slick.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/select2.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/tinymce.min.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/multi-step-modal.js'; ?>"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/jquery.webui-popover.min.js'; ?>"></script>
<script src="https://content.jwplatform.com/libraries/O7BMTay5.js"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/main.js?v=1.0.1'; ?>"></script>
<script src="<?php echo base_url().'assets/global/toastr/toastr.min.js'; ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap-tagsinput.min.js'; ?>"></script>

<script src="<?php echo site_url('assets/global/fullcalendar/moment.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/jquery-ui.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/fullcalendar.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/student.calendar.init.js?v=1.1');?>"></script>

<script src="<?php echo site_url('assets/global/datepicker/bootstrap-datepicker.min.js');?>"></script>

<script src="<?php echo base_url().'assets/frontend/default/js/custom.js'; ?>"></script>

<!-- SHOW TOASTR NOTIFIVATION -->
<?php if ($this->session->flashdata('flash_message') != ""):?>
<script type="text/javascript">
	toastr.success(`<?php echo $this->session->flashdata("flash_message");?>`);
</script>
<?php endif;?>

<?php if ($this->session->flashdata('error_message')):?>
<script type="text/javascript">
	toastr.error(`<?php echo $this->session->flashdata("error_message");?>`, 'Error', {timeOut: 15000});
</script>
<?php endif;?>

<script type="text/javascript">
function success_notify(message) {
  toastr.success(message);
}

function error_notify(message) {
  toastr.error(message);
}
</script>

<script>
const submitForm = (url, data, cb) => {
	$.ajax({
		url: url,
		type: 'post',
		dataType: 'json',
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json) {
			cb(json);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
};
</script>
