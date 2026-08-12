<div class="table-responsive">
	<table class="table table-bordered table-striped table-centered mb-0">
		<thead>
		<tr>
			<?php $headers = array_keys($results[0] ?? []); ?>
			<th><?= _l('sn.') ?></th>
			<?php foreach ($headers as $header) { ?>
				<th><?= _l($header) ?></th>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($results as $index => $item) { ?>
				<tr>
					<td><?= $index + 1 ?></td>
					<?php foreach ($headers as $header) { ?>
						<td><?= $item[$header] ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
