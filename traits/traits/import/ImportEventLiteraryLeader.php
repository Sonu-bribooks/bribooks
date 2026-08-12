<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ImportEventLiteraryLeader {
    private function _importEventLiteraryLeader($rows = [], $map = [], $job_id = 0) {
        $this->load->model('event/EventLiteraryLeader_model', 'event_literary_leader_model'); 
        log_kb([
            'IMPORT_EVENT_LEADER_START' => [
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

            if (empty($data['event_id'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($data['site_id'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($site_info = $this->site_model->get($data['site_id']))) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            if (empty($literary_leader_info = $this->event_literary_leader_model->get_all([
                'event_id'                      => $data['event_id'],
                'type'                          => $data['type'],
                'literary_leader_challenge_id'  => $data['literary_leader_challenge_id'],
                'site_id'                       => $data['site_id'],
            ])['rows'][0] ?? [])) {

                $this->event_literary_leader_model->add([
                    'type'                          => $data['type'] ?? '',
                    'literary_leader_challenge_id'  => $data['literary_leader_challenge_id'] ?? 0,
                    'event_id'                      => $data['event_id'],
                    'site_id'                       => !empty($data['site_id']) ? $data['site_id'] : $site_info['id'],
                    'school_name'                   => $site_info['name'] ?? '',
                    'authorized_person'             => $site_info['authorized_person'] ?? '',
                    'rank'                          => $data['rank'] ?? 0,
                    'city_id'                       => $site_info['city_id'] ?? 0,
                    'state_id'                      => $site_info['state_id'] ?? 0,
                    'country_id'                    => $site_info['country_id'] ?? 0,
                    'status'                        => 1,
                    'date_added'                    => date('Y-m-d H:i:s'),
                ]);

                $uploaded++;

            } else {
                self::_updateCounter($job_id, true);

                $skipped++;
            }
        }

        self::_updateCompleted($job_id);

        return [
            'skipped'     => $skipped,
            'uploaded'     => $uploaded,
        ];

    }
}
