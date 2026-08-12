<script>
// project-status-chart, task-area-chart
function renderRevenueChart() {
	if (document.getElementById('task-area-chart')) {
		$.get('<?=base_url('admin/ajax_dashboard_chart')?>', json => {
			if (!json?.chart) {
				$('#task-area-chart').remove();
				return;
			}

			const ctx = document.getElementById('task-area-chart');
			new Chart(ctx, {
				type: 'line',
				data: {
					labels: (json?.chart?.labels ?? []),
					datasets: [
						{
							label: '<?=_l('revenue')?>',
							data: (json?.chart?.data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(23 130 64)',
							backgroundColor: 'rgb(23 130 64 / 30%)',
							fill: true
						},
						{
							label: '<?=_l('revenue_last_year')?>',
							data: (json?.chart?.previous_data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(224 224 224)',
							backgroundColor: 'rgb(224 224 224 / 30%)',
							fill: true
						},
					],
				},
				options: {
					responsive: true,
					pointBackgroundColor: '#fff',
					radius: 10,
					scales: {
						y: {
							beginAtZero: true
						}
					}
				}
			});
		});
	}
}
</script>

<script>
function renderUserChart() {
	if (document.getElementById('task-bar-chart')) {
		// project-status-chart, task-area-chart
		$.get('<?=base_url('admin/ajax_dashboard_user_chart')?>', json => {
			if (!json?.chart) {
				$('#task-bar-chart').remove();
				return;
			}

			const ctx = document.getElementById('task-bar-chart');
			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: (json?.chart?.labels ?? []),
					datasets: [
						{
							label: '<?=_l('users')?>',
							data: (json?.chart?.data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(236 42 42)',
							backgroundColor: 'rgb(236 42 42 / 30%)',
							fill: true
						},
						{
							label: '<?=_l('users_last_year')?>',
							data: (json?.chart?.previous_data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(42 236 55)',
							backgroundColor: 'rgb(42 236 55 / 30%)',
							fill: true
						},
					],
				},
				options: {
					responsive: true,
					pointBackgroundColor: '#fff',
					radius: 10,
					scales: {
						y: {
							beginAtZero: true,
							stacked: true,
						},
						x: {
							stacked: true
						}
					}
				}
			});
		});
	}
}
</script>

<script>
function renderBookChart() {
	if (document.getElementById('task-line-chart')) {
		// project-status-chart, task-area-chart
		$.get('<?=base_url('admin/ajax_dashboard_book_chart')?>', json => {
			if (!json?.chart) {
				$('#task-line-chart').remove();
				return;
			}

			const ctx = document.getElementById('task-line-chart');
			new Chart(ctx, {
				type: 'line',
				data: {
					labels: (json?.chart?.labels ?? []),
					datasets: [
						{
							label: '<?=_l('book_published')?>',
							data: (json?.chart?.data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(178 42 236)',
							backgroundColor: 'rgb(178 42 236 / 30%)',
							fill: false
						},
						{
							label: '<?=_l('book_published_last_year')?>',
							data: (json?.chart?.previous_data ?? []),
							borderWidth: 2,
							borderColor: 'rgb(61 42 236)',
							backgroundColor: 'rgb(61 42 236 / 30%)',
							fill: false
						},
					],
				},
				options: {
					responsive: true,
					pointBackgroundColor: '#fff',
					radius: 10,
					scales: {
						y: {
							beginAtZero: true,
						}
					}
				}
			});
		});
	}
}
</script>
<script>
renderBookChartStatus = renderISBNBooksStatus = renderUserChartStatus = false;

function isScrolledIntoView($elem) {
	const docViewTop = $(window).scrollTop();
	const docViewBottom = docViewTop + $(window).height();

	const elemTop = $elem.offset().top;
	const elemBottom = elemTop + $elem.height();

	return (elemBottom > docViewTop && elemTop < docViewBottom);
}
$(window).on('scroll', function () {
	// const $tbc = $('#task-bar-chart');
	//
	// if ($tbc.length > 0 && isScrolledIntoView($tbc)) {
	// 	!renderUserChartStatus && setTimeout(() => renderUserChart(), 10000);
	// 	renderUserChartStatus = true;
	// }
	//
	// const $tlc = $('#task-line-chart');
	//
	// if ($tlc.length > 0 && isScrolledIntoView($tlc)) {
	// 	!renderBookChartStatus && setTimeout(() => renderBookChart(), 10000);
	// 	renderBookChartStatus = true;
	// }

	const $aid = $('#ajax-isbn-datatable');

	if ($aid.length > 0 && isScrolledIntoView($aid)) {
		!renderISBNBooksStatus && setTimeout(() => renderISBNBooks(), 10000);
		renderISBNBooksStatus = true;
	}
});
</script>
