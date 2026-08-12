<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="table-responsive-sm mt-4">
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('photo'); ?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('parent'); ?></th>
								<th><?php echo _l('grade'); ?></th>
								<th><?php echo _l('enrolled_courses'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($results as $key => $result): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td>
										<img src="<?php echo $this->user_model->get_user_image_url($result['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
									</td>
									<td><?php echo $result['first_name'].' '.$result['last_name']; ?><br><small><?php echo _l('mobile').': '.$result['mobile']; ?></small></td>
									<td><?php echo $result['parent_name']; ?></td>
									<td><?php echo $result['grade']; ?></td>
									<td>
										<?php $enrolled_courses = $this->crud_model->enrol_history_by_user_id($result['id']); ?>
										<ul>
											<?php foreach ($enrolled_courses->result_array() as $enrolled_course):
												$course_details = $this->crud_model->get_course_by_id($enrolled_course['course_id'])->row_array();?>
												<li><?php echo $course_details['title']; ?></li>
											<?php endforeach; ?>
										</ul>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
