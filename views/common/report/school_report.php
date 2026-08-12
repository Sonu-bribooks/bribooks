<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Grade Wise Report</title>
	<style>
		table {
			font-family: arial, sans-serif;
			border-collapse: collapse;
		}
		td {
			border: 1px solid black;
			text-align: center;
			padding: 8px;
		}
		th {
			border: 1px solid black;
			text-align: center;
			padding: 8px;
			background-color: #dddddd;
		}
		.logo {
			text-align: center;
			margin-bottom: 20px;
		}
	</style>
</head>

<body>
	<div class="logo">
		<img
			src="<?= str_replace('var/www/html/', '', base_url('/assets/images/system/register-logo-dark.png')) ?>"
			height="70"
			class="logo"
			alt="BriBooks"
		/>
	</div>
	<table style="width: 100%; text-align: center; border: 1px solid #000; margin: auto; padding: auto;">
		<tr>
			<th colspan="4" style="padding: 8px; font-size: 24px;">
				<?= $school_name; ?>
			</th>
		</tr>
		<tr>
			<th colspan="3" style="padding: 8px; font-size: 20px;">
				Total Registered Students: <?= $total_registered; ?>
			</th>
			<th style="padding: 8px; font-size: 20px;">
				Total Books: <?= $total_published; ?>
			</th>
		</tr>
		<tr>
			<th>Grade</th>
			<th>Section</th>
			<th>Registered Students</th>
			<th>Published Books</th>
		</tr>
		<?php foreach ($students as $student) { ?>
			<?php if (empty($student['total_registered_author'])) continue; ?>
			<tr>
				<td><?= $student['grade']; ?></td>
				<td><?= $student['section']; ?></td>
				<td><?= $student['total_registered_author']; ?></td>
				<td><?= $student['total_published_author']; ?></td>
			</tr>
		<?php } ?>
	</table>
</body>

</html>
