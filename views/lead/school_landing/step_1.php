<div class="first-step">
	<div class="row">
		<div class="col-auto">
			<h1 class="heading"><?php _el('Partner_signup_form'); ?></h1>
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
		<div class="col-auto">
			<span class="error form-error"></span>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<input
					type="text"
					class="form-control"
					value=""
					id="school_name"
					name="school_name"
					placeholder="<?php _eli('Name of Organization'); ?>"
				/>
				<span class="error name-error"></span>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="form-group">
				<select class="form-control" id="organization_type" name="organization_type">
					<option value=""><?php _el('organization_type'); ?></option>
					<option value="K12 School">K12 School</option>
					<option value="Reseller">Reseller</option>
					<option value="K12 Publisher">K12 Publisher</option>
				</select>
				<span class="error type-error"></span>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-6">
			<div class="form-group">
				<select
					class="form-control"
					id="country"
					name="country"
				/>
					<option value=""><?php _el('select_country'); ?></option>
					<?php foreach ($this->country_model->get_all([
						'sort' => 'name',
						'order'=> 'ASC'
					])['rows'] ?? [] as $country) { ?>
					<option value="<?php echo $country['name']; ?>"><?php echo $country['name']; ?></option>
					<?php } ?>
				</select>
				<span class="error country-error"></span>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="form-group">
				<input
					type="text"
					class="form-control"
					value=""
					id="city"
					name="city"
					placeholder="<?php _eli('City'); ?>"
				/>
				<span class="error city-error"></span>
			</div>
		</div>

		<div class="col-sm-12">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-prepend">
						<?php require __DIR__ . '/../landing/tel_select.php'; ?>
					</div>
					<input
						type="tel"
						class="form-control"
						name="mobile"
						id="mobile"
						placeholder="<?php echo _l('Contact Number'); ?>"
						value=""
						required
					/>
				</div>

				<span class="error mobile-error text-right"></span>
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
					placeholder="<?php _eli('E-Mail Id'); ?>"
				/>
				<span class="error email-error"></span>
			</div>
		</div>

		<div class="col-sm-6">
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
