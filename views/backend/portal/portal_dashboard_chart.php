<?php
	$months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
	$day_wise_leads = [];
	$current_month = date('F');
	$days = [];

	for ($d = 1; $d <= date('t'); $d++) {
		$time = mktime(12, 0, 0, date('m'), $d, date('Y'));
		array_push($days, date('d M', $time));

		$d = $d < 10 ? '0'.$d : $d;

		$this->db->where('date_added LIKE "' . date('Y-m') . '-' . $d . '%"');
		$leads = $this->db->get_where('lead', [
			'site_id'	=> $this->config->item('site_id'),
		])->result_array();

		$leads
			? array_push($day_wise_leads, count($leads))
			: array_push($day_wise_leads, 0);
	}

	$total_leads = $this->lead_model->get_all([
		'site_id' 	=> (int)$this->config->item('site_id')
	])['total'] ?? 0;
	$registered_students = $this->student_model->get_all([
		'site_id' 	=> (int)$this->config->item('site_id')
	])['total'] ?? 0;
	$book_written = $this->book_model->get_all([
		'site_id' 	=> (int)$this->config->item('site_id')
	])['total'] ?? 0;
	$book_published = $this->book_model->get_all([
		'site_id' 	=> (int)$this->config->item('site_id'),
		'status'	=> 1,
	])['total'] ?? 0;

?>

 <script type="text/javascript">
 ! function(o) {
     "use strict";
     var t = function() {
         this.$body = o("body"), this.charts = []
     };
     t.prototype.respChart = function(r, a, n, e) {
         Chart.defaults.global.defaultFontColor = "#8391a2", Chart.defaults.scale.gridLines.color = "#8391a2";
         var i = r.get(0).getContext("2d"),
             s = o(r).parent();
         return function() {
             var t;
             switch (r.attr("width", o(s).width()), a) {
                 case "Line":
                     t = new Chart(i, {
                         type: "line",
                         data: n,
                         options: e
                     });
                     break;
                 case "Doughnut":
                     t = new Chart(i, {
                         type: "doughnut",
                         data: n,
                         options: e
                     })
             }
             return t
         }()
     }, t.prototype.initCharts = function() {
         var t = [];
         if (0 < o("#lead-area-chart").length) {
             t.push(this.respChart(o("#lead-area-chart"), "Line", {
                 labels: [
                      <?php foreach ($days as $day): ?>
                    "<?php echo $day; ?>",
                    <?php endforeach; ?>
                 ],
                 datasets: [{
                     label: "<?php echo _l('this_day'); ?>",
                     backgroundColor: "rgba(114, 124, 245, 0.3)",
                     borderColor: "#727cf5",
                     data: [
                         <?php foreach ($day_wise_leads as $income): ?>
                        "<?php echo $income; ?>",
                        <?php endforeach; ?>
                     ]
                 }]
             }, {
                 maintainAspectRatio: !1,
                 legend: {
                     display: !1
                 },
                 tooltips: {
                     intersect: !1
                 },
                 hover: {
                     intersect: !0
                 },
                 plugins: {
                     filler: {
                         propagate: !1
                     }
                 },
                 scales: {
                     xAxes: [{
                         reverse: !0,
                         gridLines: {
                             color: "rgba(0,0,0,0.05)"
                         }
                     }],
                     yAxes: [{
                         ticks: {
                             stepSize: 10,
                             display: !1
                         },
                         min: 10,
                         max: 100,
                         display: !0,
                         borderDash: [5, 5],
                         gridLines: {
                             color: "rgba(0,0,0,0)",
                             fontColor: "#fff"
                         }
                     }]
                 }
             }))
         }
         if (0 < o("#lead-status-chart").length) {
             t.push(this.respChart(o("#lead-status-chart"), "Doughnut", {
                 labels: ["<?php echo _l('total_leads'); ?>", "<?php echo _l('registered_students'); ?>", "<?php echo _l('book_written'); ?>", "<?php echo _l('book_published'); ?>"],
                 datasets: [{
                     data: [<?php echo $total_leads; ?>, <?php echo $registered_students; ?>, <?php echo $book_written; ?>, <?php echo $book_published; ?>],
                     backgroundColor: ["#c7c7c7", "#FFC107", "#0acf97"],
                     borderColor: "transparent",
                     borderWidth: "2"
                 }]
             }, {
                 maintainAspectRatio: !1,
                 cutoutPercentage: 80,
                 legend: {
                     display: !1
                 }
             }))
         }
         return t
     }, t.prototype.init = function() {
         var r = this;
         Chart.defaults.global.defaultFontFamily = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif', r.charts = this.initCharts(), o(window).on("resize", function(t) {
             o.each(r.charts, function(t, r) {
                 try {
                     r.destroy()
                 } catch (t) {}
             }), r.charts = r.initCharts()
         })
     }, o.ChartJs = new t, o.ChartJs.Constructor = t
 }(window.jQuery),
 function(t) {
     "use strict";
     window.jQuery.ChartJs.init()
 }();

 </script>
