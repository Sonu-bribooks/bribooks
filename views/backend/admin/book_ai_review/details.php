<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=$page_title ?></h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-5 col-lg-5 col-xl-5">
		<div class="card">
			<div class="card-header">
				<h3><?=$info['name'] ?></h3>
			</div>
			<div class="card-body">
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('event_id') ?></b></div>
					<div class="col-sm-8">: <?=$info['event_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('event') ?></b></div>
					<div class="col-sm-8">: <?=$info['event_name'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('book_id') ?></b></div>
					<div class="col-sm-8">: <?=$info['book_id'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('version') ?></b></div>
					<div class="col-sm-8">: <?=$info['version'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('book_name') ?></b></div>
					<div class="col-sm-8">: <?=$info['name'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('author_name') ?></b></div>
					<div class="col-sm-8">: <?=$info['author'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('preview') ?></b></div>
					<div class="col-sm-8">: <a href="<?=USER_URL . 'preview/' . $info['slug'] ?>" target="_blank"><?=_l('preview') ?></a></div>
				</div>
				<div class="row mb-2 align-items-center">
					<div class="col-sm-4"><b><?=_l('score') ?></b></div>
					<div class="col-sm-8"><h2>: <?=$info['score'] ?></h2></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-4"><b><?=_l('date_generated') ?></b></div>
					<div class="col-sm-8">: <?=format_date($info['date_added']) ?></div>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h6><?=_l('rubric_breakdown') ?></h6>
			</div>
			<div class="card-body">
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('creativity_originality') ?></b></div>
					<div class="col-sm-4">: <?=$info['creativity_originality'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('character_development_depth') ?></b></div>
					<div class="col-sm-4">: <?=$info['character_development_depth'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('plot_storytelling') ?></b></div>
					<div class="col-sm-4">: <?=$info['plot_storytelling'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('grammatical_errors') ?></b></div>
					<div class="col-sm-4">: <?=$info['grammatical_errors'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('imaginative_use_of_language') ?></b></div>
					<div class="col-sm-4">: <?=$info['imaginative_use_of_language'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('theme_message') ?></b></div>
					<div class="col-sm-4">: <?=$info['theme_message'] ?></div>
				</div>
				<div class="row mb-2">
					<div class="col-sm-8"><b><?=_l('overall_impact') ?></b></div>
					<div class="col-sm-4">: <?=$info['overall_impact'] ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-sm-12 col-xs-12 col-md-7 col-lg-7 col-xl-7">
		<div class="card">
			<div class="card-header">
				<h6><?=_l('full_review_and_summary') ?></h6>
			</div>
			<div class="card-body">
				<h3><?= _l('review')?></h3>
				<?=nl2br($info['review']) ?>
				<h3><?= _l('summary')?></h3>
				<?=nl2br($info['summary']) ?>
			</div>
		</div>
	</div>
</div>
