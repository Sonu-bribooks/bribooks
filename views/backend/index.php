<?php
$system_name 	= $this->db->get_where('settings', array('key' => 'system_name'))->row()->value;
$system_title 	= $this->db->get_where('settings', array('key' => 'system_title'))->row()->value;
$user_details 	= $this->user_model->get($this->session->userdata('user_id'));
$text_align	 	= $this->db->get_where('settings', array('key' => 'text_align'))->row()->value;

$is_dark_mode 	= $this->input->cookie('is_dark_mode');
$user_role_type = $this->session->userdata('user_role_type');

$user_role_type_view 	= strtolower($user_role_type);
$logged_in_user_role_id = $this->session->userdata('role_id');
$logged_in_user_role 	= $this->session->userdata('role');
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<title><?php echo get_phrase($page_title); ?> | <?php echo $system_title; ?></title>
	<!-- all the meta tags -->
	<?php include 'metas.php'; ?>
	<!-- all the css files -->
	<?php include 'includes_top.php'; ?>
</head>

<body data-layout="detached" class="<?=$user_role_type_view?>-layout">
	<!-- HEADER -->
	<?php include 'header.php'; ?>
	<div class="container-fluid">
		<div class="wrapper">
			<!-- BEGIN CONTENT -->
			<!-- SIDEBAR -->
			<?php include $user_role_type_view . '/' . 'navigation.php'; ?>
			<!-- PAGE CONTAINER-->
			<div class="content-page">
				<div class="content">
					<!-- BEGIN PlACE PAGE CONTENT HERE -->
					<?php include $user_role_type_view . '/' . $page_name . '.php'; ?>
					<!-- END PLACE PAGE CONTENT HERE -->
				</div>
			</div>
			<!-- END CONTENT -->
		</div>
	</div>
	<!-- all the js files -->
	<?php include 'includes_bottom.php'; ?>
	<?php include 'modal.php'; ?>
</body>

</html>
