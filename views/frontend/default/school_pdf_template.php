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
	</style>
</head>

<body>
	<table>
		<tr>
			<th colspan="3" style="text-align: left;">
				School Name: <?= $site_info['name'] ?>
			</th>
			<th colspan="2" style="text-align:left;">
				Total Registered:<?= $total_registered ?>
			</th>
		</tr>
		<tr>
			<th>Grade</th>
			<th>Section</th>
			<th>Registered Students</th>
			<th>Books In Writing</th>
			<th>Books Published</th>
		</tr>
		<?php foreach ($grades as $grade) { ?>
			<?php foreach ($grade['sections'] as $key => $section) { ?>
			<tr>
				<?php if (!$key) { ?>
				<td rowspan="<?=count($grade['sections'])?>"><?= $grade['name'] ?></td>
				<?php } ?>
				<td><?= $section['name'] ?></td>
				<td><?= $section['reg_students'] ?></td>
				<td><?= $section['book_written'] ?></td>
				<td><?= $section['book_published'] ?></td>
			</tr>
		<?php } ?>
		<?php } ?>

		<!-- second row -->
		<!-- <tr>
			<td rowspan="4">2</td>
			<td>A</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>

		</tr>
		<tr>
			<td>B</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>

		</tr>
		<tr>
			<td>C</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>

		</tr>
		<tr>
			<td>D</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>
		</tr> -->
	</table>
</body>

</html>
