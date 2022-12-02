<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'core/MY_AJAX_Controller.php');

class Profile extends \Mobiledrs\core\MY_AJAX_Controller {
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('provider_management/Profile_model', 'pt_model');
	}

	public function search()
	{
		// $this->check_permission('add_tr');

		$params = [
			'where_data' => [
				['key' => 'provider_firstname', 'value' =>  $this->input->get('term')],
				['key' => 'provider_lastname', 'value' =>  $this->input->get('term')]
			]
		];

		$systemRes = [];
		$selectedSystems = $this->input->get('system');
		foreach ($selectedSystems as $selectedSystem) {
			$systemRes[$selectedSystem] = $this->pt_model->find($params, $selectedSystem);
		}

		$search_data = [];
		foreach ($systemRes as $key => $result) {
			$tmp = $this->prepare_search_data($result, $key);
			$search_data = array_merge($search_data, $tmp);
		}

		echo json_encode($search_data);
	}

	public function supervising_md_search()
	{
		$this->check_permission('add_tr');

		$params = [
			'where_data' => [
				['key' => 'provider_firstname', 'value' =>  $this->input->get('term')],
				['key' => 'provider_lastname', 'value' =>  $this->input->get('term')]
			]
		];

		$res = $this->pt_model->find($params);

		$search_data = [];

		if ($res)
		{
			for ($i = 0; $i < count($res); $i++) 
			{
				if ($res[$i]->provider_supervising_MD == '0') {
					continue;
				} 

				$search_data[] = [
					'id' => $res[$i]->provider_id,
					'value' => $res[$i]->get_fullname()
				];
			}
		}

		echo json_encode($search_data);
	}

	private function prepare_search_data(array $res, string $key) : array
	{
		$data = [];
		$systemLabel = [
			'gimg_db' => 'GIMG',
			'gmma_db' => 'GMMA',
			'mobile_physician_db' => 'Mobile Physician',
		];

		for ($i = 0; $i < count($res); $i++) 
			{
				// remove duplicate records from each system results
				$key = str_replace(' ', '', strtolower($res[$i]->get_fullname()));
				if (isset($data[$key])) {
					continue;
				}

				$data[$key] = [
					'id' => trim($res[$i]->provider_firstname).'/'.trim($res[$i]->provider_lastname),
					'value' => trim($res[$i]->provider_firstname).' '.trim($res[$i]->provider_lastname)
				];
			}

		return $data;
	}
}