<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class SchoolGenericCertificate_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('common/Cron_model');
		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('event/Event_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('event/EventSite_model');
		$this->load->model('certificate/SchoolCertificate_model');
		$this->load->model('certificate/SchoolCertificateTemplate_model');
		$this->load->model('certificate/SchoolCertificateMessageTemplate_model');

		$this->cron_model		   	    = $this->CI->Cron_model;
		$this->book_model		   	    = $this->CI->Book_model;
		$this->student_model		    = $this->CI->Student_model;
		$this->event_model	 		    = $this->CI->Event_model;
		$this->event_book_model	 	    = $this->CI->EventBook_model;
		$this->event_site_model	 	    = $this->CI->EventSite_model;
		$this->school_certificate_model	= $this->CI->SchoolCertificate_model;
		$this->school_certificate_template_model			= $this->CI->SchoolCertificateTemplate_model;
		$this->school_certificate_message_template_model 	= $this->CI->SchoolCertificateMessageTemplate_model;
	}

	public function createSchoolCertificate($book_id = 0, $event_id = 0, $alert = false) {
		log_kb([
			'createSchoolCertificate' => [$book_id, $event_id]
		]);

		if (
			empty($book_id) || empty($event_id) ||
			empty($book_info    = $this->book_model->get($book_id)) ||
            empty($event_info   = $this->event_model->get($event_id))
		) return;

        log_kb([
			'createSchoolCertificate::event_info' => [$event_info]
		]);

        if (strtotime($event_info['end_date']) < time()) return;
        if (empty($author_info = $this->student_model->get($book_info['user_id']))) return;
        log_kb([
			'createSchoolCertificate::author_info' => [$author_info]
		]);
        if (empty($this->event_site_model->get_all([
			'event_id' 	=> $event_info['id'],
			'site_id' 	=> $author_info['site_id'],
		])['rows'][0] ?? '')) {
			return;
		}

        log_kb(['createSchoolCertificate::eventSiteEnroll']);

        if (empty($published_author = count(array_unique(array_column($this->event_book_model->get_all([
                'event_id' 	=> $event_info['id'],
                'site_id' 	=> $author_info['site_id'],
            ])['rows'] ?? [], 'user_id'))))) return;

        $certificate_templates = $this->school_certificate_template_model->get_all([
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
				'event_id'		=> (int)$event_info['id'],
				'status'		=> 1,
				'sort'			=> 'school_certificate_template.published_author_count',
				'order'			=> 'ASC',
			])['rows'] ?? [];

            log_kb([
                'createSchoolCertificate::certificate_templates' => [$certificate_templates]
            ]);

			if (empty($certificate_templates)) return;

        foreach ($certificate_templates as $key => $template) {
            if ($published_author >= $template['published_author_count']) {

                $certificate_key = sprintf('%s_school_%s', $template['name'], $author_info['site_id']);

                $certificate_info = $this->school_certificate_model->get_all([
                    'event_id'				=> $event_id,
                    'site_id'				=> $author_info['site_id'],
                    'name'					=> $template['name'],
                ])['rows'][0] ?? [];

                log_kb([
                    'ExistingCertificate:: ' => $certificate_info
                ]);

                if (empty($certificate_info)) {
                    $certificate_id = $this->school_certificate_model->add([
                        'site_id'					        => $author_info['site_id'],
                        'event_id'					        => $event_id,
                        'school_certificate_template_id'	=> $template['id'],
                        'achievement'				        => $template['achievement'],
                        'unique_id'					        => $template['id'],
                        'name'						        => $template['name'],
                        'image'						        => $certificate_key,
                    ]);

                    $alert && self::_sendSchoolCertificateGenerateMessage($certificate_id, $published_author);
                }
            }
        }
	}

	private function _sendSchoolCertificateGenerateMessage($certificate_id = 0, $sold = 0) {
		if (empty($certificate_id)) return;
		if (empty($certificate_info = $this->school_certificate_model->get($certificate_id))) return;

		if (!empty($this->cron_model->getByCode(sprintf('schoolGenericCertificateCreatedCron_%s', $certificate_id)))) return;

		$this->cron_model->add([
			'code'			=> sprintf('schoolGenericCertificateCreatedCron_%s', $certificate_id),
			'action'		=> 'alert_model->schoolGenericCertificateCreatedCron',
			'data'			=> [$certificate_id, $sold],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
		]);
	}
}
