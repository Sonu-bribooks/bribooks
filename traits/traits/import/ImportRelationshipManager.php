<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ImportRelationshipManager {
    private function _importRelationshipManager($rows = [], $map = [], $job_id = 0) {
        $this->load->model('school/RelationshipManager_model', 'relationship_manager_model'); 
        log_kb([
            'IMPORT_RELATIONSHIP_MANAGER_START' => [
                'rows' => count($rows),
                'map' => $map,
                'job_id' => $job_id
            ]
        ]);
        $skipped = $uploaded     = 0;
        $challenge_slug         = '';
        
        foreach ($rows as $index => $row) {
            $data = array_combine(array_keys($map), array_map(function($i) use($row) {
                return @$row[$i];
            }, array_values($map)));

            self::_updateCounter($job_id);

            if (empty($data['school_id'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($data['email'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($data['name'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($data['mobile'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }   

            $relationship_manager_info = $this->relationship_manager_model->get_all([
                'school_id'                     => $data['school_id'],
                'name'                          => $data['name'],
                'email'                         => $data['email'],
                'mobile'                        => $data['mobile'],
            ])['rows'][0] ?? [];

            if (empty($relationship_manager_info)) {

                $this->relationship_manager_model->add([
                    'school_id'                     => $data['school_id'],
                    'site_id'                       => 0,
                    'name'                          => $data['name'] ?? '',
                    'manager_id'                    => $data['manager_id'] ?? 0,
                    'mobile'                        => $data['mobile'],
                    'email'                         => $data['email'] ?? 0,
                ]);

                $uploaded++;

            } else {
                $new_manager_id = $data['manager_id'] ?? 0;
                if ($relationship_manager_info['manager_id'] !== $new_manager_id) {

                    $this->relationship_manager_model->edit(
                        $relationship_manager_info['id'],
                        [
                            'manager_id'     => $new_manager_id,
                        ]
                    );

                    $uploaded++;

                } else {

                    self::_updateCounter($job_id, true);

                    $skipped++;
                }
            }
        }

        self::_updateCompleted($job_id);

        return [
            'skipped'     => $skipped,
            'uploaded'     => $uploaded,
        ];
    }
}