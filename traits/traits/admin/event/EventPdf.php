<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('import');

trait EventPdf {
	use ImportCommon;

	public function event_pdf($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'template_id',
			'name',
			'content',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();
			$data['content'] 	= $this->input->post('content', FALSE);

			$this->event_pdf_model->add($data);
			redirect(base_url('admin/event_pdf'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();
			$data['content'] 	= $this->input->post('content', FALSE);

			$this->event_pdf_model->edit($param2, $data);
			redirect(base_url('admin/event_pdf'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_pdf_model->delete($param2);
			redirect(base_url('admin/event_pdf'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_pdf_content');
		$data['action_add'] 	= base_url('admin/event_pdf_form/add');
		$data['action_import'] 	= base_url('admin/event_pdf_import');
		$data['action_ajax'] 	= base_url('admin/ajax_event_pdf');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_pdf_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_pdf/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_pdf_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_pdf_content');
			$data['action'] 						= base_url('admin/event_pdf/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_event_pdf_content');
			$data['action'] 						= base_url('admin/event_pdf/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_pdf_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);

			$event_name							 	= ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'template_id',
			'label'		=> _l('template_id'),
			'required'	=> true,
			'value'		=> $info['template_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'content',
			'label'		=> _l('content'),
			'required'	=> false,
			'value'		=> $info['content'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_pdf() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_pdf_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'template_id'			=> $result['template_id'],
				'name'					=> $result['name'],
				'content'				=> $result['content'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function event_pdf_import($param1 = '') {
		if ($param1 == 'download') {
			import_download('event_pdf',
				['id','event_id','template_id','name','content'],
				[[
					'event_id' 		=> '0',
					'template_id' 	=> 'sample_message',
					'name' 			=> 'Sample Message For BOOK',
					'content' 		=> 'html content',
				]]
			);
		} elseif ($param1 == 'upload') {
			$data  = import_upload('event_pdf',
				['id','event_id','template_id','name','content'],
			);

			output_json($data);
			return;
		} elseif ($param1 == 'save') {
			$data  = import_save('event_pdf', [
				'model' 		=> 'event/event_pdf',
				'empty_skips' 	=> 'event_id, template_id, name, content'
			]);

			if (!empty($data) && !empty($data['job_id'])) {
				$this->generateImportChunk($data);

				$json['finish'] 	= true;
				$json['success'] 	= _l('text_save_success');
			} else {
				$json['error'] 	= _l('something_went_wrong!');
			}

			output_json($json);
			return;
		}

		$data['action_file'] 		= base_url('admin/event_pdf_import/upload');
		$data['action_save'] 		= base_url('admin/event_pdf_import/save');
		$data['action_download'] 	= base_url('admin/event_pdf_import/download');

		$data['page_name'] 		= 'generic/import_form';
		$data['page_title'] 	= _l('event_pdf_import');

		$this->load->view('backend/index', $data);
	}
}
