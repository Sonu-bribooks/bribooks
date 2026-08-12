<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>School info</title>
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
			height: 100px;
		}
	</style>
</head>
<body>
	<div class="logo">
		<img src="<?=site_url('uploads/system/logo-dark.png')?>" class="logo" alt="BriBooks" />
	</div>
	<table>
		<tr>
			<th colspan="7" style="text-align: center;">
				National Ranking Authors <br />Of<br /><?= $school_name; ?>
			</th>
		</tr>
		<tr>
			<th>S.No.</th>
			<th>National Rank</th>
			<th>Author Name</th>
			<th>Book Name</th>
			<th>Grade</th>
			<th>Section</th>
			<th>Certificate</th>
		</tr>

		<?php foreach ($students as $key => $student) { ?>
			<tr>
				<td><?= $key + 1 ?></td>
				<td><?= $student['rank'] ?></td>
				<td><?= $student['author_name'] ?></td>
				<td><?= $student['book_name'] ?></td>
				<td><?= $student['grade'] ?></td>
				<td><?= $student['section'] ?></td>
				<td><?= $student['certificate'] ?></td>
			</tr>
		<?php } ?>
	</table>
</body>
</html>
