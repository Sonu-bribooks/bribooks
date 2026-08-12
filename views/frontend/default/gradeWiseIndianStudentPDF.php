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
	</style>
</head>

<body>
	<table style="width: 100%; text-align: center; border: 1px solid #000; margin: auto; padding: auto;">
		<tr>
			<th colspan="3" style="padding: 8px; font-size: 24px;">
				<?= $school_name; ?>
			</th>
		</tr>
		<tr>
			<th style="padding: 8px; font-size: 20px;">
				Total Registered: <?= $total_registered; ?>
			</th>
			<th colspan="2" style="padding: 8px; font-size: 20px;">
				Total Authors: <?= $total_published; ?>
			</th>
		</tr>
		<tr>
			<th>Registered Students</th>
			<th>Grade</th>
			<th>Published Authors</th>
		</tr>
		<?php foreach ($students as $student) { ?>
			<tr>
				<td><?= $student['total_registered_author']; ?></td>
				<td><?= $student['grade']; ?></td>
				<td><?= $student['total_published_author']; ?></td>
			</tr>
		<?php } ?>
	</table>
</body>

</html>
