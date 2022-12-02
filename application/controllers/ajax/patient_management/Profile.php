<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'core/MY_AJAX_Controller.php');

use \Mobiledrs\entities\patient_management\Type_visit_entity;
use \Mobiledrs\entities\Db_list;

class Profile extends \Mobiledrs\core\MY_AJAX_Controller {
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'patient_management/Profile_model' => 'pt_model',
			'patient_management/Transaction_model' => 'pt_trans_model'
		));
	}

	public function search()
	{
		$this->check_permission('add_tr');

		$params = [
			'where_data' => [
				['key' => 'patient_name', 'value' => $this->input->get('term')]
			]
		];

		$gimg_res = $this->pt_model->find($params, 'gimg_db');
		$gmma_res = $this->pt_model->find($params, 'gmma_db');
		$search_data = [];

		if ( ! empty($gimg_res))
		{
			$gimg_res = $this->prepare_search_data($gimg_res, 'GIMG');
		}
		else {
			$gimg_res = [];	
		}

		if ( ! empty($gmma_res))
		{
			$gmma_res = $this->prepare_search_data($gmma_res, 'GMMA');
		}
		else {
			$gmma_res = [];	
		}

		$search_data = array_merge($gimg_res, $gmma_res);

		echo json_encode($search_data);
	}

	public function get_tov($type = 'add')
	{
		$patientDBName = Db_list::get_db($this->input->get('patientID'));
        Type_visit_entity::set_tov($patientDBName);

        $initial_list = [
        	Type_visit_entity::get_tov_type('INITIAL_VISIT_HOME'),
            Type_visit_entity::get_tov_type('INITIAL_VISIT_FACILITY'),
            Type_visit_entity::get_tov_type('INITIAL_VISIT_OFFICE')
        ];

		$patient_params = [
			'where' => [
				[
					'key' => 'patient_transactions.pt_patientID',
					'condition' => '=',
					'value' => $this->input->get('patientID')
				]
			],
			'where_in' => [
				'column' => 'patient_transactions.pt_tovID',
				'values' => $initial_list
			]
		];

		$initialTrans = null;
		$patientTransDBName = $this->input->get('patientTransID') == '' ? 
			'' : Db_list::get_db($this->input->get('patientTransID'));
		if ($type == 'edit' && ! empty($this->input->get('patientTransID'))) {
			$initial_params = [
				'key' => 'patient_transactions.pt_id',
				'value' => $this->input->get('patientTransID')
			];

			$initialTrans = $this->pt_trans_model->record($initial_params, $patientTransDBName);
		}

		$patientTrans = $this->pt_trans_model->records($patient_params, $patientDBName);
		
		$tov_datas = [];
		if ($type == 'add') {
			if ($patientTrans) {
				$tov_datas = (new Type_visit_entity)->get_followup_list();
			}
			else {
				$tov_datas = Type_visit_entity::get_visits_list();
			}	
		} else {
			if ((! empty($initialTrans) && in_array($initialTrans->pt_tovID, $initial_list)) ||
				(empty($patientTrans) && empty($initialTrans))) {
				$tov_datas = Type_visit_entity::get_visits_list();
			}
			else {
				$tov_datas = (new Type_visit_entity)->get_followup_list();
			}
		}		

		$tov_list = '<option value="">Select</option>';

		foreach ($tov_datas as $tov_data)
		{
			if ($tov_data == Type_visit_entity::get_tov_type('INITIAL_VISIT_HOME')) 
			{
				$id = Type_visit_entity::get_tov_type('INITIAL_VISIT_HOME');
				$tov_list .= '<option value="' . $id . '"> Initial Visit (Home)</option>';
			}
			else if ($tov_data == Type_visit_entity::get_tov_type('INITIAL_VISIT_FACILITY')) 
			{
				$id = Type_visit_entity::get_tov_type('INITIAL_VISIT_FACILITY');
				$tov_list .= '<option value="' . $id . '"> Initial Visit (Facility)</option>';
			}
			else if ($tov_data == Type_visit_entity::get_tov_type('FOLLOW_UP_HOME')) 
			{
				$id = Type_visit_entity::get_tov_type('FOLLOW_UP_HOME');
				$tov_list .= '<option value="' . $id . '"> Follow-up Visit (Home)</option>';
			}
			else if ($tov_data == Type_visit_entity::get_tov_type('FOLLOW_UP_FACILITY')) 
			{
				$id = Type_visit_entity::get_tov_type('FOLLOW_UP_FACILITY');
				$tov_list .= '<option value="' . $id . '"> Follow-up Visit (Facility)</option>';
			}
			else if ($tov_data == Type_visit_entity::get_tov_type('INITIAL_VISIT_OFFICE')) 
			{
				$id = Type_visit_entity::get_tov_type('INITIAL_VISIT_OFFICE');
				$tov_list .= '<option value="' . $id . '"> Initial Visit (Office)</option>';
			}
			else if ($tov_data == Type_visit_entity::get_tov_type('FOLLOW_UP_OFFICE')) 
			{
				$id = Type_visit_entity::get_tov_type('FOLLOW_UP_OFFICE');
				$tov_list .= '<option value="' . $id . '"> Follow-up Visit (Office)</option>';
			}
		}

		echo $tov_list;
	}

	private function prepare_search_data(array $res, string $sectionName) : array
	{
		$data = [];

		for ($i = 0; $i < count($res); $i++) 
		{
			if ($res[$i]->provider_supervising_MD == '0') {
				continue;
			} 

			$data[] = [
				'id' => $res[$i]->patient_id,
				'value' => $sectionName . ' - ' . $res[$i]->patient_name
			];
		}

		return $data;
	}
}