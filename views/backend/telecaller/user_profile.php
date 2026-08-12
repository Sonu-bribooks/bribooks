<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('profile'); ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<section class="user-dashboard-area">
	<div class="row">
		<div class="col-xl-12">
			<div class="card">
				<div class="card-body">
					<h4 class="header-title mb-3">
						<?php echo _l('profile'); ?>
					</h4>
					<div class="row">
						<div class="col-12">
							<div class="user-dashboard-box">
								<div class="user-dashboard-sidebar">
									<div class="user-box">
										<img src="<?php echo base_url().'uploads/user_image/'.$this->session->userdata('user_id').'.jpg';?>" alt="" class="img-fluid">
										<div class="name">
											<div class="name"><?php echo $user_details['first_name'].' '.$user_details['last_name']; ?></div>
										</div>
									</div>
									<div class="user-dashboard-menu">
										<ul>
											<li class="active"><a href="<?php echo site_url('telecaller/manage_profile/user_profile'); ?>"><?php echo _l('profile'); ?></a></li>
											<li><a href="<?php echo site_url('telecaller/manage_profile/user_credentials'); ?>"><?php echo _l('account'); ?></a></li>
											<li><a href="<?php echo site_url('telecaller/manage_profile/user_photo'); ?>"><?php echo _l('photo'); ?></a></li>
										</ul>
									</div>
								</div>
								<div class="user-dashboard-content">
									<div class="content-title-box">
										<div class="title"><?php echo _l('profile'); ?></div>
										<div class="subtitle"><?php echo _l('add_information_about_yourself_to_share_on_your_profile'); ?>.</div>
									</div>
									<form action="<?php echo site_url('home/update_profile/update_basics'); ?>" method="post">
										<div class="content-box">
											<div class="basic-group">
												<div class="form-group">
													<label for="FristName"><?php echo _l('basics'); ?>:</label>
													<input type="text" class="form-control" name = "first_name" id="FristName" placeholder="<?php echo _l('first_name'); ?>" value="<?php echo $user_details['first_name']; ?>">
												</div>
												<div class="form-group">
													<input type="text" class="form-control" name = "last_name" placeholder="<?php echo _l('last_name'); ?>" value="<?php echo $user_details['last_name']; ?>">
												</div>
												<div class="form-group">
													<label for="Biography"><?php echo _l('biography'); ?>:</label>
													<textarea class="form-control author-biography-editor" name = "biography" id="Biography"><?php echo $user_details['biography']; ?></textarea>
												</div>
											</div>
											<div class="link-group">
												<div class="form-group">
													<input type="text" class="form-control" maxlength="60" name = "twitter_link" placeholder="<?php echo _l('twitter_link'); ?>" value="<?php echo $social_links['twitter']; ?>">
													<small class="form-text text-muted"><?php echo _l('add_your_twitter_link'); ?>.</small>
												</div>
												<div class="form-group">
													<input type="text" class="form-control" maxlength="60" name = "facebook_link" placeholder="<?php echo _l('facebook_link'); ?>" value="<?php echo $social_links['facebook']; ?>">
													<small class="form-text text-muted"><?php echo _l('add_your_facebook_link'); ?>.</small>
												</div>
												<div class="form-group">
													<input type="text" class="form-control" maxlength="60" name = "linkedin_link" placeholder="<?php echo _l('linkedin_link'); ?>" value="<?php echo $social_links['linkedin']; ?>">
													<small class="form-text text-muted"><?php echo _l('add_your_linkedin_link'); ?>.</small>
												</div>
											</div>
										</div>
										<div class="content-update-box">
											<button type="submit" class="btn">Save</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
