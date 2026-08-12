<div class="first-step">
	<div class="row">
		<div class="col-auto">
			<h1 class="heading"><?php _el('Create Your Account'); ?></h1>
			<h6 class="subheading"><?php echo _li('Or signup using your email address'); ?></h6>
		</div>
		<div class="col">
			<div class="g-signin2"
				data-width="180"
				data-height="40"
				data-longtitle="false"
				data-theme="dark"
				data-size="small"
				data-onsuccess="onSignIn"
			></div>
			<?php if (0) { ?>
			<div
				id="appleid-signin"
				data-color="black"
				data-border="true"
				data-type="sign in"
			></div>
			<?php } ?>

		</div>
	</div>

	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<input
					type="text"
					class="form-control"
					value=""
					id="student_name"
					name="student_name"
					placeholder="<?php _eli('Student Name'); ?>"
				/>
				<span class="error student-name-error"></span>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="form-group">
				<select class="form-control" id="student_grade" name="student_grade">
					<option value=""><?php _eli('Student Grade'); ?></option>
					<?php for ($i = 1; $i <= 12; $i++) { ?>
					<option value="<?=$i?>"><?=$i?></option>
					<?php } ?>
				</select>
				<span class="error student-grade-error"></span>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<input
					type="text"
					class="form-control"
					value=""
					id="parent_name"
					name="parent_name"
					placeholder="<?php _eli('Parent Name'); ?>"
				/>
				<span class="error parent-name-error"></span>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="form-group">
				<input
					type="email"
					class="form-control"
					value=""
					id="email"
					name="email"
					placeholder="<?php _eli('Parent E-Mail Id'); ?>"
				/>
				<span class="error email-error"></span>
			</div>
		</div>


		<div class="col-sm-8">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-prepend">
						<?php require __DIR__ . '/tel_select.php'; ?>
					</div>
					<input
						type="tel"
						class="form-control"
						name="parent_mobile"
						id="parent_mobile"
						placeholder="<?php echo _l('Contact Number'); ?>"
						value=""
						required
					/>
				</div>

				<span class="error parent-mobile-error text-right"></span>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="form-group">
				<button
					type="button"
					onclick="nextStep()"
					name="button"
					class="btn btn-kb"
					id="button-next"
				>
					<?php _eli('Next'); ?>
					<i class="fa fa-angle-right"></i>
				</button>
			</div>
		</div>
	</div>
</div>
