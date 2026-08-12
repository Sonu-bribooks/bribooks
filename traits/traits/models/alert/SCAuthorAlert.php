<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SCAuthorAlert
{
	public function userDetailsAuthorInviteSC($id) {
		if(empty($id))
			return;

		self::userDetailsAuthorInviteSCCron($id);
	}

	public function userDetailsAuthorInviteSCCron($user_details_invite_id) {
		if(empty($user_details_invite_id))
			return;

		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$user_details_invite_info = $this->user_details_invite_model->get($user_details_invite_id);
		if(empty($user_details_invite_info))
			return;

		$this->load->model('user/User_model', 'user_model');

		$user_info = $this->user_model->get($user_details_invite_info['user_id']);
		if(empty($user_info))
			return;

		$this->load->model('book/Book_model', 'book_model');

		$book_info = $this->book_model->get($user_details_invite_info['book_id']);
		if(empty($book_info))
			return;

		$author_name = explode(" ", trim($book_info['author_name']));

		$mobile = $user_info['mobile'];
		$email = $user_info['email'];

		$url = vsprintf(USER_URL . 'registration/submitdetail?uid=%s&code=%s&bid=%s&eid=%s', [
			$user_info['id'],
			$user_info['verification_code'],
			$book_info['id'],
			$user_details_invite_info['event_id'],
		]);

		self::_sendWhatsappText(
			$mobile,
			[
				'template'		=> '159559610424390',
				'parameters'	=> [
					ucfirst($author_name[0]),
					$url
				]
			]
		);

		$subject = 'Congratulations, ' . ucfirst($author_name[0]) . '! Join us for the Summer Book Writing Festival Award Ceremony!';

		$content = '<p>Dear '.ucfirst($author_name[0]).'</p>
<p>Congratulations on your remarkable achievement as a talented author! We are thrilled to extend a special invitation to you for the Summer Book Writing Festival Awards and Exhibition Ceremony, taking place on September 2nd, 2023, from 02:00 pm onwards at the prestigious Apparel House in Gurugram, Haryana.</p>
<p>As our honored guest, you are invited to bring two guests along (such as your parents) to share on this momentous occasion. To ensure smooth entry into the event, we kindly request you to fill in the attached form (' . $url . ') and generate entry passes for yourself and your guests by <strong>July 27th, 2023, at 5:00 PM.</strong> Accuracy in filling out the details is essential due to the event\'s security requirements.</p>
<p>We are genuinely excited about your participation and eagerly look forward to meeting you in person at the ceremony.</p>
<p>Please Note: All the awards and goodies will be given only at the time of the Awards and Exhibition Ceremony. BriBooks will not be able to ship any item post-ceremony due to logistical reasons.</p>
<p>If you have any questions or require further information, please do not hesitate to reach out to us at 1800-309-9917 or email us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
<p>Once again, congratulations on your achievement, and we can\'t wait to celebrate your success together!</p>
<p>Best regards,</p>
<p>Team BriBooks.</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[]
		);
	}

	public function userDetailsGuestSC($id) {
		if(empty($id))
			return;

		self::userDetailsGuestSCCron($id);
	}

	public function userDetailsGuestSCCron($user_details_guest_id) {

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

		$user_info = $this->user_model->get($user_details_guest_info['user_id']);

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
				'template'		=> '1391685084786890',
				'parameters'	=> [
					ucfirst($author_name[0]),
					'02:00 pm',
					'07:00 pm'
				],
				'document'	=> [
					'name' => 'entry_pass.pdf',
					'link' => base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'
				]
			]
		);

            $data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
            $data['title']          = 'Invitation Passes for NYAF 2023, India: Awards & Exhibition';
			$data['author_name']			= $book_info['author_name'];
			$message				= $this->load->view('common/mail/nyaf/nyaf_pass', $data, true);

			!empty($email) && self::email(
			$email,
			$data['title'],
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[FCPATH . 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf']
		);
		}
	public function schoolDetailsInviteSC($id) {
		if(empty($id))
			return;

		self::schoolDetailsInviteSCCron($id);
	}

	public function schoolDetailsInviteSCCron($school_details_invite_id) {
		if(empty($school_details_invite_id))
			return;

		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		$school_details_invite_info = $this->school_details_invite_model->get($school_details_invite_id);

		if(empty($school_details_invite_info))
			return;

		$site_info = $this->db->get_where('site', [
			'id'	=> $school_details_invite_info['site_id']
		])->row_array();

		if (empty($site_info))
			return;

		$school_info = $this->db->get_where('school_lead', [
			'site_id'			=> $site_info['id']
		])->row_array();

		if (empty($school_info))
			return;

		$spoc_name = trim($school_info['school_head']);

		$mobile = $school_info['mobile'];
		$email = $school_info['email'];

		$url = vsprintf(USER_URL . 'schoolregistration?site_id=%s&code=%s&eid=4', [
			$site_info['id'],
			$site_info['site_code']
		]);

		self::_sendWhatsappImage(
			$mobile,
			[
				'template'		=> '1660352924439020',
				'parameters'	=> [
					ucfirst($spoc_name),
					$url
				]
			]
		);

		$subject = 'Congratulations, '.ucfirst($school_info['name']).'! Join us for the Summer Book Writing Festival Award Ceremony!';

		$content = '<p>Dear <strong>'.ucfirst($spoc_name).'</strong></p>
<p>We are delighted to extend a warm invitation to the esteemed Summer Book Writing Festival Awards and Exhibition Ceremony, taking place on September 2nd, 2023, from 02:00 pm onwards at the prestigious Apparel House in Gurugram.</p>
<p>Congratulations on the outstanding performance of your young authors, which has earned <strong>'.ucfirst($school_info['name']).'</strong> a well-deserved place among the top schools in India. We would be honored to have your presence at this historic event, as we come together to celebrate the remarkable creative excellence of young authors from across the country.</p>
<p>To ensure your participation, we kindly request that you complete the attached form ('.$url.') to generate exclusive entry passes for your school.</p>
<p>The submission deadline is <strong>July 27th, 2023, at 5:00 pm.</strong></p>
<p>Please ensure that all details are filled in accurately for a seamless entry process.</p>
<p>We request one representative from each school to accept the invitation on behalf of the institution for this esteemed ceremony.</p>
<p>Please Note: All the awards and goodies will be given only at the time of the Awards and Exhibition Ceremony. BriBooks will not be able to ship any item post-ceremony due to logistical reasons.</p>
<p>If you have any questions or require further information, please do not hesitate to reach out to us at 1800-309-9917 or email us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>. We are always here to assist you in any way possible.</p>
<p>We eagerly anticipate your school\'s presence and look forward to celebrating this special occasion together.</p>
<p>Sincerely,</p>
<p>Team BriBooks.</p>';

		self::email(
			$email,
			$subject,
			$content,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[]
		);
	}

	public function schoolDetailsGuestSC($id) {
		if(empty($id))
			return;

		self::schoolDetailsGuestSCCron($id);
	}

	public function schoolDetailsGuestSCCron($school_details_guest_id) {
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

		$mobile = $school_info['mobile'];
		$email = $school_info['email'];

		self::_sendWhatsappDocument(
			$mobile,
			[
				'template'		=> '928608375342155',
				'parameters'	=> [
					ucfirst($school),
					ucfirst($guest_name),
					ucfirst($school),
					'12:30 pm',
					'02:00 pm'
				],
				'document'	=> [
					'name' => 'entry_pass.pdf',
					'link' => base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'
				]
			]
		);

		$subject = 'Your Exclusive Entry Passes for '.$school.' are Ready!';

		$content = "<p>Dear ".ucfirst($guest_name).",</p>
		<p>Congratulations! Your exclusive entry passes for the National Young Authors’ Fair Awards and Exhibition Ceremony are now ready. W/'re delighted to have ".$school." join us for this special event, and we sincerely appreciate your involvement.</p>
		<p>Attached are your entry passes in PDF format. Please make sure to bring them with you on the event day for smooth entry through security.</p>
		<p>To make registration quick and easy, please remember to bring a valid ID along with your passes for identification, and generation of your unique ID cards. </p>
		<p>Registration starts promptly at 12:30 pm and ends at 02:00 pm, so please ensure that you arrive on time to avoid any inconvenience.</p>
		<p>Thank you for your cooperation, and we're looking forward to seeing you and your team at the event!</p>
		<p>Best regards,<br>
		Team BriBooks</p>";

		self::email(
			$email,
			$subject,
			$content,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[FCPATH . 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf']
		);
	}

	public function bookExhibitionInviteCron($invite_id) {
		if(empty($invite_id))
			return;

		$this->load->model('book/BookExhibition_model', 'book_exhibition_model');
		$this->load->model('common/InviteSlot_model', 'invite_slot_model');

		$invite_info = $this->book_exhibition_model->get($invite_id);

		if(empty($invite_info))
			return;

		$slot_info = $this->invite_slot_model->get($invite_info['slot_id']);

		$time_slot = date("h:i A", strtotime($slot_info['slot_start'])) . ' - ' . date("h:i A", strtotime($slot_info['slot_end']));

		$mobile = $invite_info['mobile'];
		$email  = $invite_info['email'];

		$subject = ucfirst($invite_info['name']).', IMPORTANT UPDATE!! Your entry passes are here!!';

		$message			= $this->load->view('common/mail/part/exhibition_invite', [
			'name' 			=> ucwords($invite_info['name']),
			'time_slot' 	=> $time_slot
		], true);

		!empty($email) &&  self::email(
			$email,
			$subject,
			$message,
			[],
			(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			[FCPATH . 'uploads/exhibitionpass/pdfs/entry_pass_'.$invite_info['code'].'.pdf']
		);

		!empty($mobile) &&  self::_sendWhatsappDocument(
			$mobile,
			[
				'template'		=> '1464206021117713',
				'parameters'	=> [
					ucfirst($invite_info['name']),
					$time_slot
				],
				'document'	=> [
					'name' => 'exhibition_entry_pass.pdf',
					'link' => base_url().'uploads/exhibitionpass/pdfs/entry_pass_'.$invite_info['code'].'.pdf'
				]
			]
		);
	}
}
