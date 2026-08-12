<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportGameStudent {
	public function runGameData() {
		$this->edb = $this->load->database('eventdb', TRUE);
		$game_ids = array_column(EVENT_2021, 'game_code');

		// Steps
		// 0. update csv students to db
		// 1. get list of students
		// 2. register student
		// 3. login to game
		// 4. update exported with student id
		// 5. enter game
		// 6. update score to test score eg.6
		// 7. test score in view
		// 8. update score to 0
		// 9. repeat one more testing
		// 10. close

		// self::_updateGameScore(27); // Blockly beginner
		// self::_updateGameScore(26); // Blockly advance
		// self::_updateGameScore(7); // Python beginner
		// self::_updateGameScore(29); // Python advance

		// self::_testRun();
		// self::_resetScore();

		// pr(self::getEventRank(29, true));
	}

	private function _testRun() {
		$game_ids = array_column(EVENT_2021, 'game_code');

		// $query 		= $this->edb->query('SHOW TABLES');
		// $tables 	= array_column($query->result_array(), 'Tables_in_leap_adventure');
		// pr($tables);

		// $this->edb->update('adt_user_score', [
		// 	'score'		=> 3
		// ], [
		// 	'user_id'	=> 4,
		// 	'game_id'	=> 4
		// ]);

		// pr(
		// 	array_filter(
		// 		$this->edb
		// 		->order_by('score', 'DESC')
		// 		->get('adt_user_score')
		// 		->result_array(),
		// 		function($item) use($game_ids) {
		// 			return in_array($item['game_id'], $game_ids);
		// 		}
		// 	)
		// );

		pr(
			$this->edb
			->where_in('game_id', $game_ids)
			->order_by('score', 'DESC')
			->get('adt_user_score')
			->result_array()
		);

		// pr(
		// 	$this->edb
		// 	->where_in('game_id', $game_ids)
		// 	->get('adt_event_user')
		// 	->result_array()
		// );

		// pr(
		// 	$this->edb
		// 	->select('DISTINCT(user_id)')
		// 	->where_in('game_id', $game_ids)
		// 	->get('adt_game_info')
		// 	->result_array()
		// );

		// pr(
		// 	$this->edb
		// 	->where_in('game_id', $game_ids)
		// 	->get('adt_game_info')
		// 	->result_array()
		// );
	}

	private function _updateUser() {
		$this->edb->update('adt_user', [
			'password'	=> md5('4f11b38ae7d5ecfa089141ccf104c66d')
		], [
			'email'		=> 'yashg9828@gmail.com'
		]);

		// $this->edb->delete('adt_event_user', [
		// 	'user_id'	=> 230
		// ]);
	}

	private function _importGameStudent($file = '') {
		if (is_file('assets/csv/' . $file)) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('assets/csv/' . $file);

			$rows = $this->parsecsv->data;

			self::_importStudents($rows);
		}
	}

	private function _importStudents($rows = []) {
		foreach ($rows as $index => $data) {
			if (empty($data['email'])) continue;

			$data['email'] = mb_strtolower($data['email']);

			// 1. Add student

			if ($student = $this->db->get_where('game_students', [
				'email'			=> $data['email'],
			])->row_array()) {
				$student_id = $student['id'];

				$this->db->update('game_students', [
					'course_id'			=> (int)$data['course_id'],
					'grade'				=> (int)$data['grade'],
					'first_name'		=> trim($data['first_name']),
					'last_name'			=> trim($data['last_name']),
					'mobile'			=> trim($data['mobile']),
					'date_modified'		=> date('Y-m-d H:i:s'),
				], [
					'id'				=> (int)$student_id
				]);
			} else {
				$user_info = $this->db->get_where('users', [
					'email' 	=> trim($data['email'])
				])->row_array();

				$this->db->insert('game_students', [
					'course_id'			=> (int)$data['course_id'],
					'grade'				=> (int)$data['grade'],
					'first_name'		=> trim($data['first_name']),
					'last_name'			=> trim($data['last_name']),
					'email'				=> trim($data['email']),
					'mobile'			=> trim($data['mobile']),
					'password'			=> $user_info ? $user_info['password'] : md5(trim($data['email'])),
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s'),
				]);

				$student_id = $this->db->insert_id();

				self::_processEventRegisteration($data);
			}
		}
	}

	private function _processEventRegisteration($data = []) {
		self::eventRegister([
			'first_name'	=> $data['first_name'],
			'last_name'		=> $data['last_name'],
			'email'			=> $data['email'],
			'mobile'		=> $data['mobile'],
			'grade'			=> $data['grade'],
			'password'		=> md5($data['email']),
			'course_id'		=> $data['course_id'],
		]);

		$user_info = $this->db->get_where('game_students', [
			'email'			=> $data['email']
		])->row_array();

		$token_info = self::eventLogin([
			'event_code'	=> EVENT_2021[$data['course_id']]['event_code'],
			'game_code'		=> EVENT_2021[$data['course_id']]['game_code'],
			'email'			=> $data['email'],
			'password'		=> $user_info['password'] ?? '',
		], false);

		$this->db->update('game_students', [
			'exported'		=> (int)$token_info['event_uid'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		],[
			'email'			=> $data['email']
		]);
	}

	private function _updateGameScore($course_id = 0) {
		$grads = [
			27 	=> 2, // Blockly beginner
			26 	=> 2, // Blockly advance
			7 	=> 2, // Python beginner
			29 	=> 2, // Python advance
		];

		$max_score = $this->edb
		->where('game_id', EVENT_2021[$course_id]['game_code'])
		->order_by('score', 'DESC')
		->limit(1)
		->get('adt_user_score')
		->row_array();

		echo '<pre>' . print_r($max_score, 1);

		foreach ($this->db->get_where('game_students', [
			'course_id'	=> $course_id
		])->result_array() as $key => $row) {
			// $score = round((($key % 6) + date('H')) * $grads[$row['course_id']] + (date('i') * 1.5));

			if ($key > 2) {
				$score = abs($max_score['score'] - rand(1, 5));
			} else {
				$score = $max_score['score'] + rand(1, 10);
			}

			$score = $score > 0 ? $score : 1;

			self::_updateScore($row['exported'], $row['course_id'], $score);
		}
	}

	private function _updateScore($student_id, $course_id = 0, $score = 0) {
		echo vsprintf('Student: %s course %s game %s score %s <br>', [
			$student_id,
			$course_id,
			EVENT_2021[$course_id]['game_code'],
			$score
		]);

		if ($row = $this->edb->get_where('adt_user_score', [
			'user_id'	=> (int)$student_id,
			'game_id'	=> EVENT_2021[$course_id]['game_code']
		])->row_array()) {
			$this->edb->update('adt_user_score', [
				'score'		=> (int)$score
			], [
				'user_id'	=> (int)$student_id,
				'game_id'	=> EVENT_2021[$course_id]['game_code']
			]);
		} else {
			$user_info = $this->db->get_where('game_students', [
				'exported'		=> (int)$student_id
			])->row_array();

			$token_info = self::eventLogin([
				'event_code'	=> EVENT_2021[$course_id]['event_code'],
				'game_code'		=> EVENT_2021[$course_id]['game_code'],
				'email'			=> trim($user_info['email']),
				'password'		=> $user_info['password'] ?? '',
			], false);

			$result = $this->icode_lib->setEndpoint($this->config->item('event_api_icode'))->setHeader([
				'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
					$token_info['event_uid'],
					$token_info['event_token'],
				]),
				'Content-Type'	=> 'application/x-www-form-urlencoded',
			])->insert('user/enterGame', [
				'gameId'		=> EVENT_2021[$course_id]['game_code'],
				'mode'			=> EVENT_2021[$course_id]['game_mode'],
				'channel'		=> 'in',
			])->rows();

			log_kb([
				'enterGame' => $results
			]);

			$result = $this->icode_lib->setEndpoint($this->config->item('event_api_icode'))->setHeader([
				'Cookie'		=>  vsprintf('USERID=%s;SESSION=%s', [
					$token_info['event_uid'],
					$token_info['event_token'],
				]),
				'Content-Type'	=> 'application/x-www-form-urlencoded',
			])->insert('checkPointer/getCheckPointer', [
				'gameId'			=> EVENT_2021[$course_id]['game_code'],
				'mode'				=> EVENT_2021[$course_id]['game_mode'],
				'stageIds[0]'		=> 1,
				'currentPointer'	=> 1,
			])->rows();

			log_kb([
				'getCheckPointer' => $results
			]);
		}
	}

	private function _clearGameData() {
		$game_ids = array_column(EVENT_2021, 'game_code');

		return;

		// $this->edb
		// ->where_in('game_id', $game_ids)
		// ->delete('adt_event_user');
		//
		// $this->edb
		// ->where_in('game_id', $game_ids)
		// ->delete('adt_game_info');
		//
		// $this->edb
		// ->where_in('game_id', $game_ids)
		// ->delete('adt_user_score');
	}

	private function _resetScore() {
		return;

		foreach ($this->db->get_where('game_students')->result_array() as $key => $row) {
			self::_updateScore($row['exported'], $row['course_id'], 0);
		}
	}
}
