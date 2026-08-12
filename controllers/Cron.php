<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('Sync_model', 'sync_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('PrinterZipDownload_model', 'printer_zip_download_model');
		$this->load->model('common/MissingCover_model', 'missing_cover_model');

		// $this->load->model('lr/Assessment_model', 'lr_assessment_model');

		$this->load->library('Cron_lib', 'cron_lib');
	}

	// private function _validateRemote() {
	// 	if ($this->input->server('REMOTE_ADDR') != (ENVIRONMENT !== 'production' ? '44.192.37.41' : '23.20.74.129')) {
	// 		return false;
	// 	}

	// 	if ($this->input->server('SERVER_NAME') != (ENVIRONMENT !== 'production' ? 'crm.dev' : 'cms') . '.bribooks.com') {
	// 		return false;
	// 	}

	// 	return true;
	// }

	//added by sonu for testing on development environment
	private function _validateRemote()
	{
		if (ENVIRONMENT === 'development') {
			return true;
		}

		if ($this->input->server('REMOTE_ADDR') != '23.20.74.129') {
			return false;
		}

		if ($this->input->server('SERVER_NAME') != 'cms.bribooks.com') {
			return false;
		}

		return true;
	}

	public function index() {
		if (!self::_validateRemote()) return;

		// Morning evening alerts
		self::_morningEveningAlert();

		$json = [];

		// $sites = [];
		//
		// foreach ($sites as $site) {
		// 	self::_configSite($site['id']);
		// 	// log_kb('Timezone => ' . print_r([
		// 	// 	'site_id'	=> $this->config->item('site_id'),
		// 	// 	'zone'	=> $this->config->item('site_timezone'),
		// 	// 	'time'	=> date('Y-m-d H:i:s')
		// 	// ], 1));
		// 	self::_siteWiseCron($site['id']);
		// }

		// No Site
		self::_siteWiseCron(0);

		self::_cleanLogFiles();
		self::_cleanSessionFiles();
		self::_cleanUploadFiles();
		// self::_cleanOnline();

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	private function _siteWiseCron($site_id) {
		// Regular Crons
		$crons = $this->cron_model->get_all([
			'status' 	=> 0,
			'type' 		=> 0,
			// 'site_id'	=> (int)$site_id,
		]);

		foreach ($crons as $cron) {
			if (
				($cron_info = $this->cron_model->get($cron['id'])) &&
				empty($cron_info['status'])
			) {
				$this->cron_model->edit($cron['id'], ['status' => 1]);

				self::_execute($cron);
			}
		}

		// Weekly Crons
		$crons = $this->cron_model->get_all([
			'type' 		=> 2,
			'site_id'	=> (int)$site_id,
		]);

		foreach ($crons as $cron) {
			$days = explode(',', $cron['days']);
			sort($days);

			foreach ($days as $key => $day) {
				if ($day == date('w')) {
					self::_execute($cron);

					if (($key + 1) == count($days)) {
						$inc_days = 7 + $days[0] - $day;
					} else {
						$inc_days = $days[($key + 1)] - $day;
					}

					$this->cron_model->edit($cron['id'], ['alert_date' => date('Y-m-d H:i:s', strtotime("+$inc_days days", strtotime($cron['alert_date'])))]);
				}
			}
		}

		// Monthly Crons
		$crons = $this->cron_model->get_all([
			'type' 		=> 3,
			'site_id'	=> (int)$site_id,
		]);

		foreach ($crons as $cron) {
			$days = explode(',', $cron['days']);
			sort($days);

			foreach ($days as $key => $day) {
				if ($day == date('d')) {
					self::_execute($cron);

					if (($key + 1) == count($days)) {
						$inc_days = 30 + $days[0] - $day;
					} else {
						$inc_days = $days[($key + 1)] - $day;
					}

					$this->cron_model->edit($cron['id'], ['alert_date' => date('Y-m-d H:i:s', strtotime("+$inc_days days", strtotime($cron['alert_date'])))]);
				}
			}
		}
	}

	private function _execute($cron) {
		// self::_configSite($cron['site_id'] ?? 0);
		
		$explode = explode('->', $cron['action']);
		log_kb([
			'CRON_EXECUTE' => $cron,
			'EXPLODE'	=> $explode
		]);
		if (!empty($explode[0]) && !empty($explode[1])) {
			$data = json_decode($cron['data'], 1);

			$data = is_array($data) ? $data : [$data];

			$this->{$explode[0]}->{$explode[1]}(...$data);
		}
	}

	private function _configSite($site_id = 0) {
		$this->site_model->initConfig($site_id);
	}

	// 15 minutes cron
	public function cron15Miniute() {
		if (!self::_validateRemote()) return;

		// self::_cleanSessionFiles();

		if (ENVIRONMENT !== 'production') return;

		// Temp exception for webinar
		// $this->alert_model->classAlert(0);

		// $sites = [];

		// foreach ($sites as $site) {
		// 	self::_configSite($site['id']);
		//
		// 	// $this->alert_model->classAlert($site['id']);
		// }

		// $this->sync_model->students();
		// $this->sync_model->enrols();
		// $this->sync_model->addStudentToClass();

		// log_message('KB', vsprintf(_l('Cron15Miniute time %s'), [date('Y-m-d H:i:s')]));
	}

	// Daily cron
	public function cronDaily() {
		if (!self::_validateRemote()) return;
		if (ENVIRONMENT !== 'production') return;

		//$this->alert_model->renewalAlert();

		//$this->sync_model->courses();
		// $this->sync_model->classes();
		// $this->sync_model->schedules();
		// $this->sync_model->teachers();

		log_message('KB', vsprintf(_l('CronDaily time %s'), [date('Y-m-d H:i:s')]));
	}

	// Cron Midnight
	public function cronMidnight() {
		if (!self::_validateRemote()) return;

		if ($type = get_settings('auto_report_range')) {
			$this->load->model('common/Report_model', 'report_model');
			$this->load->model('user/Telecaller_model', 'telecaller_model');

			$telecallers = $this->telecaller_model->get_all(['status' => '1']);

			$message = '<style>table, th, td { border: 1px solid black; border-collapse: collapse; } th, td { padding: 5px; text-align: left; }</style>';
			$message .= '<table>';
			$message .= '<tr>';
			$message .= '<th></th>';
			$message .= '<th>Leads Assigned</th>';
			$message .= '<th>Demo Scheduled</th>';
			$message .= '<th>DNPs Reported</th>';
			$message .= '<th>Revenue Generated</th>';
			$message .= '</tr>';

			$telecaller_details = $this->report_model->get_details($type);

			$subject = ucwords($type) . ' Lead Report - [ ' . $telecaller_details['subject'] . ' ]';

			$message .= '<tr>';
			$message .= '<th>Total</th>';
			$message .= '<td>'.$telecaller_details['leads_assigned'].'</td>';
			$message .= '<td>'.$telecaller_details['demo_scheduled'].'</td>';
			$message .= '<td>'.$telecaller_details['dnp_reported'].'</td>';
			$message .= '<td>'.$telecaller_details['revenue_generated'].'</td>';
			$message .= '</tr>';

			$message .= '<tr><td colspan="5"></td></tr>';

			if ($telecallers->num_rows() > 0) {
				$telecallers = $telecallers->result_array();
				foreach ($telecallers as $telecaller) {
					// echo "<pre>"; print_r($telecaller);
					$telecaller_id = $telecaller['id'];
					$telecaller_name = trim($telecaller['first_name'].' '.$telecaller['last_name']);
					$telecaller_details = $this->report_model->get_details($type, $telecaller_id);
					// pr($telecaller_details);

					$message .= '<tr>';
					$message .= '<th>'.$telecaller_name.'</th>';
					$message .= '<td>'.$telecaller_details['leads_assigned'].'</td>';
					$message .= '<td>'.$telecaller_details['demo_scheduled'].'</td>';
					$message .= '<td>'.$telecaller_details['dnp_reported'].'</td>';
					$message .= '<td>'.$telecaller_details['revenue_generated'].'</td>';
					$message .= '</tr>';
				}
			}

			$message .= '</table>';

			/*$report_info = $this->report_model->get_count(['type' => $type]);

			$subject = ucwords($type) . ' Lead Report - [ ' . $report_info['subject'] . ' ]';

			$message = '<style>table, th, td { border: 1px solid black; border-collapse: collapse; } th, td { padding: 5px; text-align: left; }</style>';
			$message .= '<table>';
			$message .= '<tr>';
			$message .= '<th>Fields</th>';
			$message .= '<th>Data</th>';
			$message .= '</tr>';
			foreach ($report_info as $key => $value) {
				$key = ucwords(str_replace(array('_'), array(' '), $key));
				$message .= '<tr>';
				$message .= '<td>Total '.$key.'</td>';
				$message .= '<td>'.$value.'</td>';
				$message .= '</tr>';
			}
			$message .= '</table>';*/

			$this->alert_model->email(
				get_settings('system_email'),
				$subject,
				$message,
				['rahul@leaplearner.in'],
				[]
			);
		}

		log_message('KB', vsprintf(_l('CronMidnight time %s'), [date('Y-m-d H:i:s')]));
	}

	public function cron() {
		if (!self::_validateRemote()) return;

		$json = [];

		$uri = $this->uri->ruri_to_assoc();

		$id 	= $uri['id'] ?? 0;
		$time 	= $uri['time'] ?? '';
		$type 	= $uri['type'] ?? '';

		if ($id) {
			$this->alert_model->{$type}($id);

			$this->cron_lib->remove(base_url('cron/cron/id/' . $id . '/time/' . $time . '/type/' . $type))->save();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	private function _cleanLogFiles() {
		$extension = $this->config->item('log_file_extension') ? $this->config->item('log_file_extension') : 'php';

		$files = glob(($this->config->item('log_path') ? $this->config->item('log_path') : APPPATH . '/logs/') . '*.' . $extension);

		foreach ($files as $file) {
			if (filectime($file) <= time() - (LOG_TIME * 24 * 60 * 60)) {
				@unlink($file);
			}
		}
	}

	private function _cleanSessionFiles() {
		$files = glob(($this->config->item('sess_save_path') ? $this->config->item('sess_save_path') . '/' : APPPATH . '/bb_sessions/') . '*');
		log_kb([
			'count'	=> count($files),
			'path'	=> ($this->config->item('sess_save_path') ? $this->config->item('sess_save_path') . '/' : APPPATH . '/bb_sessions/') . '*',
		]);
		foreach ($files as $file) {
			if (@filemtime($file) <= time() - $this->config->item('sess_time_to_update')) {
				@unlink($file);
			}
		}
	}

	private function _cleanUploadFiles() {
		self::_cleanFilesByDir('uploads/pdfs/');
		self::_cleanFilesByDir('uploads/test/');
		self::_cleanFilesByDir('uploads/pdfs/invoice/');
		self::_cleanFilesByDir('uploads/custom_theme_document/');
		self::_cleanFilesByDir('uploads/communication_kit/user/');
		self::_cleanFilesByDir('uploads/marketing/csv_file/', 60);
		self::_cleanFilesByDir('uploads/eventpass/pdfs/');
		self::_cleanFilesByDir('uploads/label/', 60);
	}

	private function _cleanFilesByDir($dirname = '', $ttl = 30) {
		if (empty($dirname)) return;
		if (strpos($dirname, 'uploads') === FALSE) return;

		$files = glob(FCPATH . $dirname . '*');

		log_kb([
			'count'	=> count($files),
			'path'	=> FCPATH . $dirname . '*',
		]);

		$file_ttl = $ttl * 24 * 3600;

		foreach ($files as $file) {
			if (is_dir($file)) continue;

			if (@filemtime($file) <= time() - $file_ttl) {
				@unlink($file);
			}
		}
	}

	public function sync() {
		echo '<pre>';
		//$this->sync_model->courses();
		//$this->sync_model->courseList();

		//$this->sync_model->classes();
		//$this->sync_model->classList();

		//$this->sync_model->teachers();
		//$this->sync_model->teacherList();

		//$this->sync_model->students();
		//$this->sync_model->studentList();

		//$this->sync_model->enrols();
		//$this->sync_model->enrolList();

		//$this->sync_model->addStudentToClass();

		//$this->sync_model->schedules();
		$page = 1;
		$limit = 1000;
		$page = ($page - 1) * $limit;
		//$this->sync_model->scheduleAdjust($page, $limit);

		//$this->sync_model->materials();
		//$this->sync_model->getMaterialId('python');
		//$this->sync_model->courses();
		//$this->sync_model->schedulelList();

		//$this->sync_model->lmsToken();

		//$this->sync_model->unmarkExported();
	}

	private function _cleanOnline() {
		$this->db->delete('online', [
			'date_added < ' => date('Y-m-d H:i:s', strtotime('-' . (int)$this->config->item('online_expire') . ' minutes'))
		]);
	}

	private function _morningEveningAlert() {
		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_URL, base_url('home/createShipments'));
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// curl_exec($ch);
		// curl_close($ch);

		$this->alert_model->expireSubscriptionCron();

		if (date('H:i') === '01:00' || date('H:i') === '01:30' || date('H:i') === '02:00' || date('H:i') === '02:30' || date('H:i') === '03:00') {
			log_kb('Executing Mid Night Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('updateOrderStatusCron', ['status' => 0]);
			// $this->cron_model->editByCode('updateMedallionOrderStatusCron', ['status' => 0]);
			$this->cron_model->editByCode('updateOrderStatusMidnightCron', ['status' => 0]);
			$this->cron_model->editByCode('addAutoEscalateOrderCron', ['status' => 0]);
		}

		if (date('H:i') === '06:00') {
			$this->cron_model->editByCode('expireSubscriptionFomo', ['status' => 0]);
		}

		if (date('H:i') === '01:00') {
			$this->alert_model->updateExchangeRateCron();
		}

		if (0 && date('H:i') === '08:30') {
			// log_kb('Executing Morning Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('campaignSchoolAlertCron', ['status' => 0]);
		}

		if (0 && date('H:i') === '10:00') {
			// log_kb('Executing Morning Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseQualifierAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseBestSellingAwardAlertCron_15', ['status' => 0]);

			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_9', ['status' => 0]);
			// $this->cron_model->editByCode('franchisePublishedNoOrderAlertCron_9', ['status' => 0]);
		}

		if (0 && date('H:i') === '12:00') {
			// log_kb('Executing Evening Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('franchiseBestSellingAlertCron_15', ['status' => 0]);
		}

		if (0 && date('H:i') === '14:00') {
			// log_kb('Executing AfterNoon Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('updateBankAlertCron_0', ['status' => 0]);
			// $this->alert_model->dailySiteReportIsrael();
			// $this->alert_model->dailySchoolPocIsrael();
		}

		if (0 && date('H:i') === '14:15') {
			// log_kb('NWFIS School Report Cron:: ' . date('H:i'));

			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, base_url('home/sendDailyReportIsrael'));
			// curl_setopt($ch, CURLOPT_HEADER, 0);
			// curl_exec($ch);
			// curl_close($ch);
		}

		if (0 && date('H:i') === '14:30') {
			// log_kb('NWFIS School Report Cron:: ' . date('H:i'));

			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, base_url('home/sendDailySchoolReportIsrael'));
			// curl_setopt($ch, CURLOPT_HEADER, 0);
			// curl_exec($ch);
			// curl_close($ch);
		}

		/*if (date('H:i') === '15:00') {
			log_kb('Executing AfterNoon Cron:: ' . date('H:i'));
			$this->alert_model->dailySchoolReportIsrael();
		}*/

		if (0 && date('H:i') === '18:00') {
			// log_kb('SC Best Seller Book Sold Cron:: ' . date('H:i'));

			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, base_url('home/sendPublishedAuthorWithBookSold'));
			// curl_setopt($ch, CURLOPT_HEADER, 0);
			// curl_exec($ch);
			// curl_close($ch);
		}

		if (date('H:i') === '19:00') {
			log_kb('Executing Evening Cron:: ' . date('H:i'));
			$this->alert_model->incompleteOrdersCron();

			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseQualifierAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseBestSellingAwardAlertCron_15', ['status' => 0]);

			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_9', ['status' => 0]);
			// $this->cron_model->editByCode('franchisePublishedNoOrderAlertCron_9', ['status' => 0]);
		}

		if (0 && date('H:i') === '20:00') {
			// $this->cron_model->editByCode('expireSubscriptionFomo', ['status' => 0]);
		}

		if (0 && date('H:i') === '22:00') {
			// log_kb('Executing Evening Cron:: ' . date('H:i'));
			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseQualifierAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseBestSellingAwardAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchiseAuthorNoBookAlertCron_15', ['status' => 0]);
			// $this->cron_model->editByCode('franchisePublishedNoOrderAlertCron_15', ['status' => 0]);
			// $this->alert_model->dailySiteReportIsrael();
		}
	}
}
