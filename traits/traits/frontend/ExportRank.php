<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ExportRank {
	public function exportRank() {
		$filename = 'exported_rank_data_' . date('Y_m_d_H_i_s') . '.csv';

		$rank_7 = self::_getRankByCourseId(7, true);
		$rank_26 = self::_getRankByCourseId(26, true);
		$rank_27 = self::_getRankByCourseId(27, true);
		$rank_29 = self::_getRankByCourseId(29, true);

		// $rank_data = array_merge($rank_7['data'] ?? [], $rank_26['data'] ?? [], $rank_27['data'] ?? [], $rank_29['data'] ?? []);
		// $rank_data = array_merge($rank_7 ?? [], $rank_26 ?? [], $rank_27 ?? [], $rank_29 ?? []);

		// pr([
		// 	$rank_7,
		// 	$rank_26,
		// 	$rank_27,
		// 	$rank_29,
		// ]);

		$results = [];

		self::_mixedRank($rank_7, $results);
		self::_mixedRank($rank_26, $results);
		self::_mixedRank($rank_27, $results);
		self::_mixedRank($rank_29, $results);

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function _mixedRank($rank_data, &$results = []) {
		foreach ($rank_data as $rank => $item) {
			$results[] = [
				'Name' 			=> $item['first_name'] . ' ' . $item['last_name'],
				'Rank' 			=> $rank + 1,
				'Score' 		=> empty($item['score']) ? 0 : $item['score'],
				'School' 		=> $item['school_name'],
				'Email Id' 		=> $item['email'],
				'Course'		=> $item['game'],
			];
		}
	}

	private function writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	private function _getRankByCourseId($course_id = 0) {
		$this->edb = $this->load->database('eventdb', TRUE);

		$game_ids = array_column(EVENT_2021, 'game_code');

		return $this->edb
		->select('
			adt_user_score.*,
			adt_user.user_first_name AS first_name,
			adt_user.user_last_name AS last_name,
			adt_user.email,
			adt_user.user_school_name AS school_name,
			adt_game.game_name AS game
		')
		->where('adt_user_score.game_id', EVENT_2021[$course_id]['game_code'])
		->join('adt_user', 'adt_user_score.user_id = adt_user.user_id')
		->join('adt_game', 'adt_game.game_id = adt_user_score.game_id')
		->order_by('adt_user_score.score', 'DESC')
		->get('adt_user_score')
		->result_array();
	}
}
