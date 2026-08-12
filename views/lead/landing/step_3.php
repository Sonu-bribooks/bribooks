<div class="pay-tab" style="min-height: 300px;">
	<h1 class="heading"><?php echo _li('Your email is validated!'); ?></h1>
	<h6 class="subheading">
		<?php echo _li('Your premium account comes with the following features'); ?>
	</h6>
	<ul class="list text-muted">
		<li>Unlimited access to practice modules</li>
		<li>Access to preparation webinars</li>
		<li>Access to Mock-Hackathons</li>
		<li>Ranked Certificate for each level you qualify.</li>
		<li>Total prizes worth USD 150,000 for performance  at each level</li>
	</ul>

	<h6>Please pay <span id="amount"></span> to complete the registration and enter the ICode Global Hackathon 2022, the World’s largest K12 Coding Competition.</h6>

	<?php if (!$has_discount_code) { ?>
	<div class="discount-code">
		<h6>Do you have a School Discount Code?</h6>

		<div class="form-group">
			<div class="input-group mb-3">
				<input
					type="text"
					class="form-control"
					value=""
					id="discount_code"
					name="discount_code"
					placeholder="<?php _el('discount_code'); ?>"
				/>
				<div class="input-group-append">
					<button
						class="btn btn-kb"
						type="button"
						id="apply-discount"
					>
						<?php _el('apply'); ?>
					</button>
				</div>
			</div>
			<span class="error discount-error ext-danger"></span>
			<span class="success discount-success text-success"></span>
		</div>
	</div>
	<?php } ?>

	<div class="text-center" style="margin-top: 30px;">
		<button
			type="button"
			class="btn-pay btn btn-kb"
		><?php _eli('PAY NOW'); ?></button>
	</div>
</div>
