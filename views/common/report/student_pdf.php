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
	<table>
		<tr>
			<th colspan="3" style="text-align: left;">
				School Name: <?= $site_info['name']; ?>
			</th>
			<th colspan="3" style="text-align:left;">
				Total Registered: <?= $total_registered; ?>
			</th>
		</tr>
		<tr>
			<th>SN</th>
			<th>Name</th>
			<th>Grade</th>
			<th>Section</th>
			<th>Books in Writing</th>
			<th>Books Published</th>
		</tr>
		<?php foreach ($students as $key => $student) { ?>
		<tr>
			<td><?= $key + 1; ?></td>
			<td><?= $student['name']; ?></td>
			<td><?= $student['grade']; ?></td>
			<td><?= $student['section']; ?></td>
			<td><?= $student['book_written']; ?></td>
			<td><?= $student['book_published']; ?></td>
		</tr>
		<?php } ?>
	</table>
</body>

</html>
