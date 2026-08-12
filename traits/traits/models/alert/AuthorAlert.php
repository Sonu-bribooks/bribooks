<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AuthorAlert
{
	public function createNyafAuthorImage($id) {
		if(empty($id))
			return;

		self::createNyafAuthorImageCron($id);
	}

	public function createNyafAuthorImageCron($book_id) {
		if(empty($book_id))
			return;

		$this->load->model('book/Book_model', 'book_model');

		$book_info = $this->book_model->get($book_id);
		if(empty($book_info))
			return;

		/*$this->load->model('user/UserDetails_model', 'user_details_model');

		$user_details_info = $this->user_details_model->getByUid($book_info['user_id']);
		if(!empty($user_details_info))
			return;*/

		$this->load->model('user/User_model', 'user_model');

		$user_info = [];
		if(empty($user_info))
			return;

		$url = vsprintf(USER_URL . 'registration?uid=%s&code=%s', [
			$user_info['id'],
			$user_info['verification_code'],
		]);

		$author_name = explode(" ", trim($book_info['author_name']));

		$mobile = $user_info['mobile'];

		self::_sendWhatsappImage(
			$mobile,
			[
				'template'		=> '779647000253168',
				'parameters'	=> [
					ucfirst($author_name[0]),
					$url
				],
				'document'	=> [
					'name' => 'author_image.jpeg',
					'link' => base_url('assets/marketing/dummy_author_image.jpeg')
				]
			]
		);
	}

	public function createNyafAuthorImageTest($id) {
		if(empty($id))
			return;

		self::createNyafAuthorImageTestCron($id);
	}

	public function createNyafAuthorImageTestCron($book_id) {
		if(empty($book_id))
			return;

		$this->load->model('book/Book_model', 'book_model');

		$book_info = $this->book_model->get($book_id);
		if(empty($book_info))
			return;

		/*$this->load->model('user/UserDetails_model', 'user_details_model');

		$user_details_info = $this->user_details_model->getByUid($book_info['user_id']);
		if(!empty($user_details_info))
			return;*/

		$this->load->model('user/User_model', 'user_model');

		$user_info = [];
		if(empty($user_info))
			return;

		$url = vsprintf(USER_URL . 'registration?uid=%s&code=%s', [
			$user_info['id'],
			$user_info['verification_code'],
		]);

		$author_name = explode(" ", trim($book_info['author_name']));

		$mobiles = ['917303234240','919534086239','919818056013','919716120257'];

		$mobile = $mobiles[array_rand($mobiles, 1)];

		self::_sendWhatsappImage(
			'917042467407',
			[
				'template'		=> '1998131643852139',
				'parameters'	=> [
					ucfirst($author_name[0]),
					$url
				],
				'document'	=> [
					'name' => 'author_image.jpeg',
					'link' => base_url('assets/marketing/dummy_author_image.jpeg')
				]
			]
		);
	}

	public function userDetailsGuest($id) {
		if(empty($id))
			return;

		self::userDetailsGuestCron($id);
	}

	public function userDetailsGuestCron($user_details_guest_id) {
		if(empty($user_details_guest_id))
			return;

		$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$user_details_guest_info = $this->user_details_guest_model->get($user_details_guest_id);
		if(empty($user_details_guest_info))
			return;

		$code = $user_details_guest_info['code'];

		if (!file_exists('uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'))
			return;

		$this->load->model('user/User_model', 'user_model');

		$user_info = [];
		if(empty($user_info))
			return;

		$this->load->model('book/Book_model', 'book_model');

		$book_info = $this->book_model->get($user_details_guest_info['book_id']);
		if(empty($book_info))
			return;

		$author_name = explode(" ", trim($book_info['author_name']));

		$mobile = $user_info['mobile'];
		$email = $user_info['email'];

		self::_sendWhatsappDocument(
			$mobile,
			[
				'template'		=> '877533916661644',
				'parameters'	=> [
					ucfirst($author_name[0])
				],
				'document'	=> [
					'name' => 'entry_pass.pdf',
					'link' => base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'
				]
			]
		);

		$subject = ucfirst($author_name[0]) . ', IMPORTANT UPDATE!! Your entry passes are here!!';

		$content = '<p>Hey '.ucfirst($author_name[0]).'!</p>
<p>Congratulations! Your entry passes are here. Please find attached here the <strong>PDF files</strong> of your generated Entry passes.</p>
<p>Please ensure to carry the passes on the day of the event, for security check at the entry desk.</p>
<p>Also, please carry an <strong>identity proof</strong> along with your passes to be identified at the registration desk to generate your identity cards, which you will wear during the event.</p>
<p><strong>Registrations will start at 01:00 pm and end at 02:00 pm.</strong></p>
<p>Please remember, any entry post registration time or the unavailability of any of the documents mentioned above may result in the cancellation of your entry to the event.</p>
<p>We look forward to meeting you in person at the National Young Authors Fair Award Ceremony. If you have any queries or require further information, please do not hesitate to contact us.</p><br />
<p>Best regards,</p>
<p>Ami Dror, Founder, BriBooks.com</p>
<p>Bavin...Education World</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			[],
			[FCPATH . 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf']
		);
	}

	public function userDetailsAuthorInvite($id) {
		if(empty($id))
			return;

		self::userDetailsAuthorInviteCron($id);
	}

	public function userDetailsAuthorInviteCron($user_details_invite_id) {
		if(empty($user_details_invite_id))
			return;

		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$user_details_invite_info = $this->user_details_invite_model->get($user_details_invite_id);
		if(empty($user_details_invite_info))
			return;

		$this->load->model('user/User_model', 'user_model');

		$user_info = [];
		if(empty($user_info))
			return;

		$this->load->model('book/Book_model', 'book_model');

		$book_info = $this->book_model->get($user_details_invite_info['book_id']);
		if(empty($book_info))
			return;

		$author_name = explode(" ", trim($book_info['author_name']));

		$mobile = $user_info['mobile'];
		$email = $user_info['email'];

		if(0) {
			/*$mobiles = ['917303234240','919534086239','919716120257','918826806411'];
			$mobile = $mobiles[array_rand($mobiles, 1)];

			log_kb([
				'mobile' => $mobile,
				'email' => $email
			]);*/
		}

		$url = vsprintf(USER_URL . 'addressrequest?uid=%s&code=%s&bid=%s', [
			$user_info['id'],
			$user_info['verification_code'],
			$book_info['id'],
		]);

		self::_sendWhatsappText(
			$mobile,
			[
				'template'		=> '227859943115900',
				'parameters'	=> [
					ucfirst($author_name[0]),
					$url
				]
			]
		);

		$subject = ucfirst($author_name[0]) . ', Important Update - National Young Authors Fair';

		$content = '<p>Dear '.ucfirst($author_name[0]).'</p>
<p>Your Author certificate will be shipped to you!</p>
<p>We kindly request that you update your accurate shipping address by clicking on the following link '.$url.' before 9 pm on 31st March 2023. This will enable us to ensure a seamless delivery of your framed certificate to the right place.</p>
<p>Warmest regards,</p>
<p>Team BriBooks.</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			[],
			[]
		);
	}

	public function schoolDetailsGuest($id) {
		if(empty($id))
			return;

		self::schoolDetailsGuestCron($id);
	}

	public function schoolDetailsGuestCron($school_details_guest_id) {
		if(empty($school_details_guest_id))
			return;

		$this->load->model('school/SchoolDetailsGuest_model', 'school_details_guest_model');

		$school_details_guest_info = $this->school_details_guest_model->get($school_details_guest_id);
		if(empty($school_details_guest_info))
			return;

    	$school_info = $this->db->get_where('site', ['id' => $school_details_guest_info['site_id']])->row_array();
		if(empty($school_info))
			return;

		$code = $school_details_guest_info['code'];

    	$school = $school_info['name'];

		if (!file_exists('uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'))
			return;

		$guest_name = trim($school_details_guest_info['guest_name_1']);

		$mobile = $user_info['mobile'];
		$email 	= $user_info['email'];

		self::_sendWhatsappDocument(
			$mobile,
			[
				'template'		=> '6231898023515293',
				'parameters'	=> [
					ucfirst($guest_name)
				],
				'document'	=> [
					'name' => 'entry_pass.pdf',
					'link' => base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'
				]
			]
		);

		$subject = 'Your Exclusive Entry Passes for '.$school.' Have Been Successfully Generated!';

		$content = '<p>Dear <strong>'.ucfirst($guest_name).'</strong>,</p>
<p>It is with great pleasure that we announce the successful generation of your exclusive entry passes for the National Young Authors Fair (NYAF) Award Ceremony. We are thrilled to have <strong>'.$school.'</strong> be a part of this exclusive event, and we thank you for your participation.</p>
<p>Please find attached the PDF files of your entry passes, which will grant you and your team access to the event. We kindly request that you carry these passes with you on the day of the event to ensure a seamless security check at the entry desk.</p>
<p>We kindly request that you carry a valid form of identification along with your entry passes to be presented at the registration desk. Your identity will be verified before generating your exclusive identity cards.</p>
<p>The registration process will commence promptly at 01:00 pm and will conclude at 02:00 pm. Please ensure that you arrive on time to avoid any inconvenience.</p>
<p>Thank you for your cooperation, and we look forward to seeing you and your team at the event!</p><br />
<p>Best regards,</p>
<p>Team BriBooks!</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			[],
			[FCPATH . 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf']
		);
	}

	public function schoolDetailsInvite($id) {
		if(empty($id))
			return;

		self::schoolDetailsInviteCron($id);
	}

	public function schoolDetailsInviteCron($school_details_invite_id = '') {
		if(empty($school_details_invite_id)) return;

		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		$school_details_invite_info = $this->school_details_invite_model->get($school_details_invite_id);

		if(empty($school_details_invite_info))
			return;

		$site_info = $this->db->get_where('site', [
			'id'	=> $school_details_invite_info['site_id']
		])->row_array();

		if (empty($site_info))
			return;

		$mobile = $site_info['owner_mobile'];
		$email 	= $site_info['owner_email'];

		$url = vsprintf(USER_URL . 'schoolregistration?site_id=%s&code=%s&eid=%s', [
			$site_info['id'],
			$site_info['site_code'],
			10
		]);

		self::_sendWhatsappImage(
			$mobile,
			[
				'template'		=> '1539310753514696',
				'parameters'	=> [
					ucfirst($site_info['name']),
					ucfirst($site_info['owner_name']),
					ucfirst($site_info['name']),
					$url
				]
			]
		);

		$subject = ' Congratulations on Winning the National Literary Leadership Award for '. ucfirst($site_info['name']) .' : Next Steps';

		$content = '<p>Dear '. ucfirst($site_info['owner_name']) .',</p>
			<p>Congratulations! '. ucfirst($site_info['name']) .' has won the prestigious National Literary Leadership Award at the National Young Authors’ Fair (NYAF) 2023. <br>
			NYAF 2023 featured over 5,000 of India’s best schools, carefully selected to participate in the event. Your success is a testament to your commitment to promoting literary excellence in your school.</p>
			<p>You are cordially invited to the National Awards & Exhibition Ceremony to receive the award on March 31, 2024, at the Apparel House, Sector 44, Gurugram, Haryana. His Excellency, Mr. Naor Gillon, the Ambassador of the State of Israel to India, Sri Lanka, Nepal, and Bhutan, will be the Chief Guest, along with other dignitaries, including global best-selling authors and industry leaders.</p>
			<p>Next Steps: Kindly click on the event registration link below to complete the registration process. '.$url.' </p>
			<p>Since it is a high-security event, you are requested to complete the registration on or before March 4, 2024. A detailed itinerary shall be shared closer to the event date.</p>
			<p>In case you have any queries, please write to schools@bribooks.com.</p>
			<p>Regards, <br>
			Team BriBooks <br>
			NYAF 2023 (India)</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			['communication@bribooks.com'],
			[]
		);
	}

}
