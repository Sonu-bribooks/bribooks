<!-- bundle -->
<script src="<?php echo base_url('assets/backend/js/app.min.js'); ?>"></script>
<!-- third party js -->
<script src="<?php echo base_url('assets/backend/js/vendor/chart.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/jquery-jvectormap-1.2.2.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/jquery-jvectormap-world-mill-en.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dataTables.bootstrap4.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dataTables.responsive.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/responsive.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dataTables.buttons.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/buttons.bootstrap4.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/buttons.html5.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/buttons.flash.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/buttons.print.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dataTables.keyTable.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dataTables.select.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/summernote-bs4.min.js'); ?>"></script>
<!--script src="<?php echo base_url('assets/backend/js/vendor/fullcalendar.min.js'); ?>"></script-->
<script src="<?php echo base_url('assets/backend/js/pages/demo.summernote.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dropzone.js'); ?>"></script>
<!-- <script src="<?php echo base_url('assets/backend/js/pages/demo.dashboard.js'); ?>"></script> -->
<script src="<?php echo base_url('assets/backend/js/pages/datatable-initializer.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/font-awesome-icon-picker/fontawesome-iconpicker.min.js'); ?>" charset="utf-8"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/bootstrap-tagsinput.min.js');?>" charset="utf-8"></script>
<script src="<?php echo base_url('assets/backend/js/bootstrap-tagsinput.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/backend/js/vendor/dropzone.min.js');?>" charset="utf-8"></script>
<script src="<?php echo base_url('assets/backend/js/ui/component.fileupload.js');?>" charset="utf-8"></script>
<script src="<?php echo base_url('assets/backend/js/pages/demo.form-wizard.js'); ?>"></script>
<!-- dragula js-->
<script src="<?php echo base_url('assets/backend/js/vendor/dragula.min.js'); ?>"></script>
<!-- component js -->
<script src="<?php echo base_url('assets/backend/js/ui/component.dragula.js'); ?>"></script>

<!--script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script-->

<script src="<?php echo site_url('assets/global/fullcalendar/moment.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/jquery-ui.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/fullcalendar.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/datepicker/bootstrap-datepicker.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/datetimepicker/bootstrap-datetimepicker.min.js?v=1.3');?>"></script>
<script src="<?php echo site_url('assets/global/fullcalendar/calendar.init.js');?>"></script>

<script src="<?php echo site_url('assets/global/hopscotch/hopscotch.js');?>"></script>
<script src="<?php echo site_url('assets/global/clock.js');?>"></script>

<script src="<?php echo site_url('assets/backend/js/custom.js?v=1.0.2');?>"></script>
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/jquery.validate.min.js"></script>
<!-- <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.0/additional-methods.js"></script> -->

<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>

<!-- Dashboard chart's data is coming from this file -->
<?php include 'admin/dashboard-chart.php'; ?>

<?php if ($this->session->userdata('role_id') == 9) { ?>
	<?php include 'portal/portal_dashboard_chart.php'; ?>
<?php } elseif ($this->session->userdata('role_id') == 4 && false) { ?>
	<?php include 'telecaller/telecaller_dashboard_chart.php'; ?>
<?php } ?>

<script type="text/javascript">
	$(document).ready(function() {
		$(function() {
			 $('.icon-picker').iconpicker();
		 });
	});
</script>

<!-- Toastr and alert notifications scripts -->
<script type="text/javascript">
function notify(message) {
	$.NotificationApp.send("<?php echo _l('heads_up'); ?>!", message ,"top-right","rgba(0,0,0,0.2)","info");
}
function system_notify(heading = '', message = '', icon = 'info') {
	var options = {
		heading: heading,
		text: message,
		position: 'top-right',
		loaderBg: 'rgba(0,0,0,0.2)',
		icon: icon,
		hideAfter: 24 * 3600 * 1000,
		stack: true,
		showHideTransition: 'slide',
	};

	$.toast().reset('all');
	$.toast(options);
}

function success_notify(message) {
	$.NotificationApp.send("<?php echo _l('congratulations'); ?>!", message ,"top-right","rgba(0,0,0,0.2)","success");
}

function error_notify(message) {
	$.NotificationApp.send("<?php echo _l('oh_snap'); ?>!", message ,"top-right","rgba(0,0,0,0.2)","error");
}

function error_required_field() {
	$.NotificationApp.send("<?php echo _l('oh_snap'); ?>!", "<?php echo _l('please_fill_all_the_required_fields'); ?>" ,"top-right","rgba(0,0,0,0.2)","error");
}
</script>

<?php if ($this->session->flashdata('info_message') != ""):?>
<script type="text/javascript">
	$.NotificationApp.send("<?php echo _l('success'); ?>!", '<?php echo $this->session->flashdata("info_message");?>' ,"top-right","rgba(0,0,0,0.2)","info");
</script>
<?php endif;?>

<?php if ($this->session->flashdata('error_message') != ""):?>
<script type="text/javascript">
	$.NotificationApp.send("<?php echo _l('oh_snap'); ?>!", '<?php echo $this->session->flashdata("error_message");?>' ,"top-right","rgba(0,0,0,0.2)","error");
</script>
<?php endif;?>

<?php if ($this->session->flashdata('flash_message') != ""):?>
<script type="text/javascript">
	$.NotificationApp.send("<?php echo _l('congratulations'); ?>!", '<?php echo $this->session->flashdata("flash_message");?>' ,"top-right","rgba(0,0,0,0.2)","success");
</script>
<?php endif;?>

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
<script>
function onLoad() {
	gapi.load('auth2', function() {
		gapi.auth2.init();
	});
}

function signOut(cb) {
	try {
		gapi.load('auth2', function() {
			var auth2 = gapi.auth2.getAuthInstance();

			auth2.signOut().then(function () {
				console.log('User signed out.');
				cb();
			});
			auth2.disconnect();
		});
	} catch (error) {
		console.log('Error:: ', error)
	}
}
</script>

<?php if ($this->session->userdata('user_id')) { ?>
<script>
$(function() {
	$('[data-site]').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/ajax_search_site'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		},
		minimumInputLength: 3
	});
});
</script>
<?php } ?>
<script>
function removeNotification(event_id) {
	$.post('<?=base_url('admin/removeNotification') ?>', {event_id: event_id}, function(json) {
		/*console.log(json);*/
	});
}
$(function () {
	var source = new EventSource('<?= base_url('admin/getNotification/ticket') ?>');

	source.addEventListener('ticket', function (e) {
		var json = JSON.parse(e.data);

		if (json.event_id) {
			removeNotification(json.event_id);

			if (json?.ticket && json?.ticket.length > 0) {
				const ticket = json?.ticket[0];
				system_notify(ticket?.subject, ticket?.message, ticket?.icon ?? 'info');
				let audio = new Audio('<?=base_url('assets/global/notification.wav') ?>');
				audio.play();
			}
		}
	}, false);
});
</script>

<script src="https://www.gstatic.com/firebasejs/10.5.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.5.0/firebase-messaging-compat.js"></script>

<script>
const firebaseConfig = {
	apiKey: "AIzaSyBGp_2oBETiMjZbDXuV8PuFJb-dkoQbi7I",
	authDomain: "youbooksi.firebaseapp.com",
	projectId: "youbooksi",
	storageBucket: "youbooksi.appspot.com",
	messagingSenderId: "752448992196",
	appId: "1:752448992196:web:27f065e6f6e298afe06a91",
	measurementId: "G-6T80H8ZQRY"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onMessage((payload) => {
	console.log('Message received in foreground: ', payload);

	const { title, body, image, icon, ...restPayload } = payload.data;

	if (Notification.permission === 'granted') {
		navigator.serviceWorker.getRegistration().then(reg => {
			if (reg) {
				reg.showNotification(title, {
					body,
					icon: icon || '/uploads/system/logo-light.png',
					image,
					data: restPayload
				});
			}
		});
	}
});

function registerServiceWorker() {
	if (navigator.serviceWorker) {
		navigator.serviceWorker.register('/firebase-messaging-sw.js').then(registration => {
			console.log('Service Worker registered with scope:', registration.scope);

			return navigator.serviceWorker.ready;
		}).then(registration => {
			console.log('Service Worker ready:', registration);

			return checkNotificationStatus();
		}).then(subscription => {
			console.log('Push subscribed:', subscription);
		}).catch(error => {
			console.error('Push subscription failed:', error);
		});
	}
}

const checkNotificationStatus = () => {
	if (window.Notification) {
		console.log('Notification:permission:: ', window.Notification.permission);

		if (window.Notification.permission === 'granted') {
			getNotificationToken();
		} else {
			requestNotificationPermission()
		}
	}
}

async function getNotificationToken() {
	const token = await messaging.getToken({ vapidKey: 'BFPimC8Ng7RUCeFgUOdmuk8d0cNXYeOfPvHmDItVgqONCFAim09Kb0VVVNFFVXZlNAvLwqypkzY4LlgAfzt5ogA' });
	$.post('<?=base_url('admin/ajax_webpush_save') ?>', { token }, function(json) {
		console.log(json);
	});
}

async function requestNotificationPermission() {
	if (window.Notification) {
		console.log('Notification:permission:: ', window.Notification.permission);

		if (window.Notification.permission === 'granted') {
			getNotificationToken();
		} else if (window.Notification.permission !== 'denied') {
			window.Notification.requestPermission(permission => {
				if (permission === 'granted') {
					getNotificationToken();
				}
			})
		}
	}
}

registerServiceWorker();
</script>
