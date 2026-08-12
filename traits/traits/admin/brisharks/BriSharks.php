<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('admin/brisharks/common');
load_trait('admin/brisharks/report');
load_trait('admin/brisharks/user');
load_trait('admin/brisharks/startup');
load_trait('admin/brisharks/event');

trait BriSharks {
	use BSMessageTemplate;
	use BSPayment;
	use BSUserInviteCode;
	use BSStartup;
	use BSUser;
	use BSLocalisation;
	use BSEvent;
	use BSEventInvite;
	use BSEventChallenge;
	use BSSite;
}
