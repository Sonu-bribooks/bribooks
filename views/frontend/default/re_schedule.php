<section class="page-header-area my-course-area">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title"><?php echo _l('re_schedule'); ?></h1>
				<br />
				<?php if (0) { ?>
				<ul>
                  <li><a href="<?php echo site_url('home/my_courses'); ?>"><?php echo _l('all_courses'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_wishlist'); ?>"><?php echo _l('wishlists'); ?></a></li>
                  <li><a href="<?php echo site_url('home/my_messages'); ?>"><?php echo _l('my_messages'); ?></a></li>
                  <li><a href="<?php echo site_url('home/purchase_history'); ?>"><?php echo _l('purchase_history'); ?></a></li>
                  <li><a href="<?php echo site_url('home/profile/user_profile'); ?>"><?php echo _l('user_profile'); ?></a></li>
				  <li class="active"><a href="<?php echo site_url('home/re_schedule'); ?>"><?php echo _l('schedule'); ?></a></li>
                </ul>
				<?php } ?>
            </div>
        </div>
    </div>
</section>

<section class="my-courses-area">
    <div class="container">

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-12">
								<div
									id="calendar"
									data-event-url="<?php echo site_url('home/events/'.$course_id);?>"
									data-schedule-url="<?php echo site_url('home/reschedule'); ?>"
									data-action="renderEvent"
								></div>
							</div>

						</div>
					</div>
				</div>

				<div class="modal fade" id="event-modal" tabindex="-1">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><span class="change-title"></span></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>
							<div class="modal-body p-3">
								<div class="text-center">
									<a href="#" class="btn btn-lg" id="online-link"><?php _el('access_online_class'); ?></a>
									<br />
									<hr />
									<br />
								</div>
								<form action="<?php echo site_url('home/reschedule'); ?>" method="post">
									<h5 class="text-center"><?php _el('request_reschedule'); ?></h5>
									<div class="form-group">
										<label for="schedule"><?php echo _l('schedule'); ?></label>
										<input type="text" id="schedule" name="schedule" value="" class="form-control datepicker-autoclose" placeholder="<?php _el('select_schedule'); ?>" />
									</div>

									<div class="form-group">
										<label for="reason"><?php echo _l('reason'); ?></label>
										<textarea name="reason" placeholder="<?php _el('reason'); ?>" rows="7" class="form-control"></textarea>
									</div>
								</form>

								<div class="text-right pt-2">
									<button
										type="button"
										class="btn btn-light"
										data-dismiss="modal"
									>
										<?php echo _l('close'); ?>
									</button>
									<button
										type="button"
										class="btn btn-primary ml-1 save-class"
									>
										<?php echo _l('save'); ?>
									</button>
									<!-- <button
										type="button"
										class="btn btn-danger delete-class"
									>
										<?php echo _l('delete'); ?>
									</button> -->
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
    </div>
</section>

<script>
$(function() {
	$('.datepicker-autoclose').datepicker({
		autoclose: true,
		todayHighlight: true,
		format: "mm/dd/yyyy",
		startDate: "+1d",
		// endDate: "+2d"
	});
});

const renderEvent = (data) => {
	console.log(data);
}
</script>
