<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportPincodeZone {
	private function _importPincodeZone($rows = [], $map = [], $job_id = 0) {
		$this->load->model('localisation/PincodeZone_model', 'pincode_zone_model');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['pincode']) || empty($data['zone'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

            if (self::_checkDuplicatePincode($data['pincode'], $data['id'], $data['_deleted'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (!empty($data['id'] && !empty($pincode_info = $this->db->get_where('pincode_zone', ['id'	=> $data['id']])->row_array()))) {
                $this->pincode_zone_model->edit($pincode_info['id'], [
                    'pincode'   => $data['pincode'],
                    'zone'      => $data['zone'],
                    'city'      => !empty($data['city']) ? $data['city'] : $pincode_info['city'],
                    'state'     => !empty($data['state']) ? $data['state'] :  $pincode_info['state'],
                    '_deleted'   => $data['_deleted'] ?? 0,
                ]);
            } else {
                $this->pincode_zone_model->add([
                    'pincode'   => $data['pincode'],
                    'zone'      => $data['zone'],
                    'city'      => $data['city'] ?? '',
                    'state'     => $data['state'] ?? '',
                    '_deleted'   => $data['_deleted'] ?? 0,
                ]);
            }
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

    private function _checkDuplicatePincode($pincode = '', $id = 0, $deleted = 0) {
        $result = false;

        $search_data = [
            'pincode' => $pincode
        ];

        if (!empty($id)) {
            $search_data['id_ne'] = $id;
        }

        if (empty($deleted) && !empty($code_info = $this->pincode_zone_model->get_all($search_data)['rows'] ?? [])) {
           $result = true;
        }

        return $result;
    }
}
