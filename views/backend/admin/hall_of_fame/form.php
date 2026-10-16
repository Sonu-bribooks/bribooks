
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title . (($active_tab === 'event') ? ' ' . _l('for_event') : ' ' . _l('for_country')); ?>
                    <button
                        type="button"
                        class="btn btn-outline-dark btn-rounded alignToTitle"
                        style="padding: 6px 16px;"
                        onclick="window.history.back()"
                    >
                        <i class="mdi mdi-arrow-left" style="margin-right: 4px;"></i> <?= _l('back') ?>
                    </button>
                </h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?= $page_title . (($active_tab === 'event') ? ' ' . _l('for_event') : ' ' . _l('for_country')); ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						
                        <?php if ($active_tab == 'event') { ?>
                            <div class="form-group">
                                <label for="event_id"><?php echo _l('event'); ?><span class="required">*</span></label>
                                <select class="form-control select2" data-toggle="select2" name="event_id" id="event_id" required>
                                    <?php foreach ($event_info as $event) { ?>
                                    <?php if (($info['event_id'] ?? '') === $event['id']) { ?>
                                    <option value="<?php echo $event['id']; ?>" selected><?php echo $event['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $event['id'] ?? ''; ?>"><?php echo $event['name'] ?? ''; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>

                        <?php } ?>
                     
                        <div class="form-group">
							<label for="country_code"><?php echo _l('country'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="country_code" id="country_code" required>
								<?php foreach ($country_info as $country) { ?>
								<?php if (($info['country_code'] ?? 'IN') === $country['code']) { ?>
								<option value="<?php echo $country['code']; ?>" selected><?php echo $country['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $country['code'] ?? ''; ?>"><?php echo $country['name'] ?? ''; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>

                        <div class="form-group">
							<label for="book_sold"><?php echo _l('book_sold'); ?><span class="required">*</span></label>
							<input type="number" class="form-control" id="book_sold" name="book_sold" value="<?php echo $info['book_sold'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="priority"><?php echo _l('priority'); ?></label>
							<input type="number" class="form-control" id="priority" name="priority" value="<?php echo $info['priority'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('status'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="status" id="status">
								<?php if (($info['status'] ?? '')) { ?>
								<option value="1" selected><?php echo _l('enable'); ?></option>
								<option value="0"><?php echo _l('desable'); ?></option>
								<?php } else { ?>
								<option value="1"><?php echo _l('enable'); ?></option>
								<option value="0" selected><?php echo _l('disable'); ?></option>
								<?php } ?>
							</select>
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
