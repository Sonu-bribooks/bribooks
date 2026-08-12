<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('api/briminds/common');

trait BriMinds {
	use BMOtp;
	use BMSchoolSignup;
	use BMUserSignup;
	use BMPrepSite;
}
