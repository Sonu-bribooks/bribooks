<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Cron_lib {
	private $cron_list;
	private $cron_tmp_file;

	public function __construct() {
		$this->cron_tmp_file = APPPATH . 'logs/crontab.txt';
		// $this->cron_list = explode(PHP_EOL, shell_exec('crontab -l'));
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->cron_list = [];
        } else {
            $crontab = shell_exec('crontab -l');
            $this->cron_list = $crontab ? explode(PHP_EOL, $crontab) : [];
        }
	}

	public function __destruct() {
		if (is_file($this->cron_tmp_file)) unlink($this->cron_tmp_file);
	}

	public function get() {
		return $this->cron_list;
	}

	public function getByCode($code) {
		foreach ($this->cron_list as $key => $cron) {
			if (strpos($cron, $code) !== false) {
				return $this->cron_list[$key];
			}
		}
	}

	public function add($cron_job, $code = '') {
		$code && $this->remove($code);

		if (is_array($cron_job)) {
			foreach ($cron_job as $cron_job_i) {
				$this->cron_list[] = $cron_job_i;
			}
		} else {
			$this->cron_list[] = $cron_job;
		}

		return $this;
	}

	public function remove($code) {
		foreach ($this->cron_list as $key => $cron) {
			if (strpos($cron, $code) !== false) {
				unset($this->cron_list[$key]);
			}
		}

		return $this;
	}

	public function removeAll() {
		exec('crontab -r');

		return $this;
	}

	public function save() {
		foreach ($this->cron_list as $key => $value) {
			if (!$value) {
				unset($this->cron_list[$key]);
			}
		}

		file_put_contents($this->cron_tmp_file, implode(PHP_EOL, array_unique($this->cron_list)) . PHP_EOL);

		exec('crontab ' . $this->cron_tmp_file);

		return $this;
	}
}

/*
* * * * * /usr/bin/php /var/www/html/cron.php > /dev/null 2>&1

* * * * * command to be executed
- - - - -
| | | | |
| | | | ----- Day of week (0 - 7) (Sunday=0 or 7)
| | | ------- Month (1 - 12)
| | --------- Day of month (1 - 31)
| ----------- Hour (0 - 23)
------------- Minute (0 - 59)

* all possible values
, list of all values separated by comma
- range values
/ step value
*/



//$cron_job = new CronJob();

//$results = $cron_job->removeAll()
//	->add('MAILFROM="revanta@revanta.co.uk"')
//	->add('#SHELL="/usr/local/cpanel/bin/jailshell"')
//	->add('*/15 * * * * http://revanta.co.uk/jairo/cron')
//	->save()
//	->get();

//print_r($results);
