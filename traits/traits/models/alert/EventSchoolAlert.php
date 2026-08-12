<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventSchoolAlert {
	public function addEventSchoolCron($id = 0, $type = '', $data='') {
		// self::cron($id, 'schoolLeadRegistrationCron');

        $this->cron_model->add([
			'code'			=> $type . '_' . $id,
			'action'		=> 'alert_model->' . $type,
			'data'			=> $data,
			'site_id'		=> $data['site_id'] ?? 1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}

    public function eventSchoolRegistrationCron ($id = 0, $event_id = 0) {

        if (
            ($site_info  = $this->site_model->get($id)) && ($event_info = $this->event_model->get($event_id))
        ) {
            $data['title']		  	= self::formatEventEmailSubject('school_registration', $event_id);
            $data['heading']		= '';
            $data['subheading']	 	= '';
            $data['subheading']		= '';
            $data['content']		= self::formatEventEmailMessage('school_registration', [
                'school_name'	  	=> $site_info['name'],
                'url'	  			=> 'https://events/bribooks.com/'.$event_info['slug'],
                'author_url'	  	=> 'https://events/bribooks.com/author/'.$site_info['id'],
                'partner_url'	  	=> 'https://events/bribooks.com/partner/'.$site_info['id'],
            ], $event_info['id']);
            $data['link']		   	= '';
            $data['link_text']	  	= '';
            $message				= $this->load->view('common/mail/templates/site/general', $data, true);

            self::email(
                $site_info['email'],
                $data['title'],
                $message,
                [],
                []
            );
        }
    }
}
