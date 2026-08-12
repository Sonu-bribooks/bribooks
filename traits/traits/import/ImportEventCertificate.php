<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ImportEventCertificate {
	private function _importEventCertificate($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($data['book_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get($data['book_id']))) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			self::_eventCertificateRetrospective($data['event_id'], $data['book_id']);

			if (!empty($data['message_alert'])) {
				self::_eventCertificateMessageRetrospective($data['event_id'], $data['book_id']);
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped'	 	=> $skipped,
			'uploaded'		=> $uploaded,
		];
	}

	private function _eventCertificateRetrospective($event_id = 0, $book_id = 0) {
		if (empty($event_id) || empty($book_id)) return;

		$event_id   = (int)$event_id;
		$book_id	= (int)$book_id;

		$results = $this->db->query("
			SELECT
				event_id,
				max(order_id) as order_id,
				book_id,
				sum(quantity) as sold
			FROM event_order
			JOIN `order` on order.id = order_id
			WHERE event_id = '" . $event_id . "' AND event_order._deleted = '0'
			AND event_order.book_id = '" . $book_id . "'
			AND order._deleted = 0
			AND order.parent_order_id = 0
			AND order.status NOT IN (0,91,92)
			AND book_id NOT IN (
				SELECT book_id
				FROM certificates
				WHERE event_id = '" . $event_id . "'  AND _deleted = '0'
			)
			GROUP BY book_id"
		)->result_array();

		$this->load->library('GenericCertificate_lib');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		foreach ($results as $result) {
			$this->genericcertificate_lib->createCertificate($result['order_id'], false);

			if (!empty($certficates = $this->certificate_model->get_all([
				'event_id'	 => 0,
				'book_id'	 => $result['book_id']
			])['rows'] ?? [])) {
				$this->db->where_in('id', array_column($certficates, 'id'));
				$this->db->update('certificates',  [
					'_deleted'		=> 1,
					'date_deleted'	=> date('Y-m-d H:i:s'),
				]);
			}
		}
	}

	private function _eventCertificateMessageRetrospective($event_id = 0, $book_id = 0) {
		if (empty($event_id) || empty($book_id)) return;

		$event_id   = (int)$event_id;
		$book_id	= (int)$book_id;

		$results = $this->db->query("
			SELECT
			(
				SELECT id
				FROM cron
				WHERE code = concat('genericCertificateCreatedCron_', certificates.id)
			) as cert,
			certificates.id as certificate_id
			FROM certificates
			WHERE event_id = '" . $event_id . "'
			AND book_id = '" . $book_id . "'
			HAVING cert IS NULL
			ORDER BY id ASC"
		)->result_array();

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		foreach ($results as $result) {
			if (empty($certificate_info = $this->certificate_model->get($result['certificate_id']))) return;
			if (!empty($this->cron_model->getByCode(sprintf('genericCertificateCreatedCron_%s', $result['certificate_id'])))) return;

			$template_info = $this->certificate_template_model->get($certificate_info['certificate_template_id']);

			$this->cron_model->add([
				'code'		  	=> sprintf('genericCertificateCreatedCron_%s', $result['certificate_id']),
				'action'		=> 'alert_model->genericCertificateCreatedCron',
				'data'		  	=> [$result['certificate_id'], ($template_info['book_sold'] ?? 0), null],
				'site_id'	   	=> 1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
			]);
		}
	}
}
