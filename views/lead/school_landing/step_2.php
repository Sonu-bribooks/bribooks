<div class="otp-area" style="min-height: 300px;">
	<h1 class="heading"><?php echo _li('Verify yourself! <br>We Promise Not to Spam You!'); ?></h1>
	<h6 class="subheading"><?php echo _li('Enter the Validation Code to verify.'); ?></h6>

	<div class="row m-t-20">
		<div class="col-md-6">
			<span class="error otp-error"></span>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="form-group">
				<input
					type="text"
					name="otp"
					value=""
					id="otp"
					size="10"
					class="form-control"
					placeholder="Validation Code"
				/>
				<div class="text-right">
					<a
						href="javascript:void(0);"
						class="btn-link"
						onclick="resendOtp()"
					><?php _eli('Resend Validation Code'); ?></a>
				</div>
			</div>

			<div class="text-center">
				<button
					type="button"
					onclick="verifyOtp()"
					class="btn-verify btn btn-kb"
				><?php _eli('Verify'); ?></button>
			</div>
		</div>
	</div>
</div>
