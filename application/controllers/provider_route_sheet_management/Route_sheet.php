<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Mobiledrs\entities\patient_management\Type_visit_entity;

class Route_sheet extends \Mobiledrs\core\MY_Controller {
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'provider_route_sheet_management/route_sheet_model' => 'rs_model',
			'provider_route_sheet_management/Route_sheet_list_model' => 'rs_list_model',
			'patient_management/Type_visit_model' => 'tov_model',
			'patient_management/Profile_model' => 'pt_model',
			'patient_management/transaction_model' => 'pat_trans_model',
			'provider_management/profile_model' => 'pr_model'
		));

		$this->load->library('Time_converter');
	}

	public function index()
	{
		$this->check_permission('list_prs');

		$page_data = $this->rs_model->get_records_list();
		$page_data['routesheet_entity'] = new \Mobiledrs\entities\provider_route_sheet_management\Routesheet_entity();

		$this->twig->view('provider_route_sheet_management/route_sheet/list', $page_data);
	}

	public function generate()
	{
		// $this->check_permission('add_prs');

		$this->session->unset_userdata('prs_ehr_systems');
		$this->session->unset_userdata('prs_providerID');
		$this->session->unset_userdata('prs_dateOfService');

		$this->twig->view('provider_route_sheet_management/route_sheet/add', [
			'current_date' => date('Y-m-d')
		]);
	}

	public function edit(string $prs_id)
	{
		$this->check_permission('edit_prs');

		$page_data = $this->rs_model->get_routesheet_details_data($prs_id);
		$page_data['current_date'] = date('Y-m-d');

		Type_visit_entity::set_tov($page_data['dbName']);

		$tov_params = [
			'where' => [
				[
					'key' => 'type_of_visits.tov_id',
					'condition' => '<>',
					'value' => Type_visit_entity::get_tov_type('NO_SHOW')
				],
				[
					'key' => 'type_of_visits.tov_id',
					'condition' => '<>',
					'value' => Type_visit_entity::get_tov_type('CANCELLED')
				]
			]
		];		

		$page_data['tovs'] = $this->tov_model->records($tov_params, $page_data['dbName']);

		$this->twig->view('provider_route_sheet_management/route_sheet/edit', $page_data);
	}

	public function save(string $formtype, string $prs_id = '')
	{
		$this->check_permission('add_prs');

		// check first if the provider has already been created
		// a route sheet for the same day
		if ($formtype == 'add')
		{
			$entity = new \Mobiledrs\entities\Entity();

			$provider_route_sheet_params = [
				'where' => [
					[
						'key' => ' provider_route_sheet.prs_providerID',
						'condition' => '=',
						'value' => $this->input->post('prs_providerID')
					],
					[
						'key' => ' provider_route_sheet.prs_dateOfService',
						'condition' => '=',
						'value' => $entity->set_date_format($this->input->post('prs_dateOfService'))
					]
				]
			];

			$provider_route_sheets = $this->rs_model->records($provider_route_sheet_params, 'routesheet_db');

			if (count($provider_route_sheets))
			{
				$this->session->set_flashdata('danger', $this->lang->line('danger_routesheet_duplication_provider_sameday'));

				return (! empty($prs_id)) ? $this->edit($prs_id) : $this->add();
			}
		}

		$params = [
			'record_id' => $prs_id,
			'table_key' => 'provider_route_sheet.prs_id',
			'save_model' => 'rs_model',
			'redirect_url' => 'provider_route_sheet_management/route_sheet',
			'validation_group' => 'provider_route_sheet_management/route_sheet/save'
		];

		$this->save_data($params);
	}

	public function details(string $prs_id)
	{
		$this->check_permission('view_prs');

		$page_data = $this->rs_model->get_routesheet_details_data($prs_id);

		$this->twig->view('provider_route_sheet_management/route_sheet/details', $page_data);
	}

	public function print()
	{
		// $this->check_permission('print_prs');
		
		$prs_ehr_systems = json_decode($this->session->userdata('prs_ehr_systems'));
		$prs_providerID = $this->session->userdata('prs_providerID');
		$prs_dateOfService = $this->session->userdata('prs_dateOfService');

		$page_data = $this->rs_model->get_routesheet_details_summary($prs_ehr_systems, $prs_providerID, $prs_dateOfService);		

		$this->twig->view('provider_route_sheet_management/route_sheet/print', $page_data);
	}

	public function form()
	{
		// $this->check_permission('download_prs');

		$this->load->library('PDF');

		$prs_ehr_systems = json_decode($this->session->userdata('prs_ehr_systems'));
		$prs_providerID = $this->session->userdata('prs_providerID');
		$prs_dateOfService = $this->session->userdata('prs_dateOfService');

		$page_data = $this->rs_model->get_routesheet_details_summary($prs_ehr_systems, $prs_providerID, $prs_dateOfService);

		$submit_type = $this->input->post('submit_type');
		$dateOfService = $page_data['record']['dateOfService'];
		$filename = $page_data['record']['providerName'] . '_routesheet_';
		$filename .= str_replace('/', '_', $dateOfService);

		if ($submit_type == 'email') {
			$this->load->library('email');			

			$tmpDir = sys_get_temp_dir() . '/';
			$emailTemplate = $this->load->view('provider_route_sheet_management/route_sheet/email_template', $page_data, true);

			$html = $this->load->view('provider_route_sheet_management/route_sheet/pdf', $page_data, true);
			$this->pdf->page_orientation = 'L';
			$this->pdf->generate_as_attachement($html, $tmpDir . $filename);

			$this->email->from('payroll@global-img.com', 'Aridel Trinquite');
			// $this->email->to('jayson.arcayna@gmail.com');
			$this->email->to($page_data['record']['providerEmail']);
			$this->email->cc('a.trinquite@global-img.com');
			$this->email->subject('Your routesheet for the date of ' . $dateOfService);
			$this->email->message($emailTemplate);
			$this->email->attach($tmpDir . $filename . '.pdf', 'attachment', $filename . '.pdf');

			$send = $this->email->send();

			if ($send)
			{
				unlink($tmpDir . $filename . '.pdf');

				$this->session->set_flashdata('success', $this->lang->line('success_email'));
			}
			else
			{
				$this->session->set_flashdata('danger', $this->lang->line('danger_email'));	
			}

			$redirect_url = 'provider_route_sheet_management/route_sheet/report';

			redirect($redirect_url);

		} elseif ($submit_type == 'pdf') {
			$html = $this->load->view('provider_route_sheet_management/route_sheet/pdf', $page_data, true);
			
			$this->pdf->page_orientation = 'L';
			$this->pdf->generate($html, $filename);
		}
	}

	public function report()
	{
		$prs_ehr_systems = $this->input->post('prs_ehr_systems') ?? json_decode($this->session->userdata('prs_ehr_systems'));
		$prs_providerID = $this->input->post('prs_providerID') ?? $this->session->userdata('prs_providerID');
		$prs_dateOfService = $this->input->post('prs_dateOfService') ?? $this->session->userdata('prs_dateOfService');

		$page_data = $this->rs_model->get_routesheet_details_summary($prs_ehr_systems, $prs_providerID, $prs_dateOfService);

		$page_data['prs_ehr_systems'] = $prs_ehr_systems;
		$page_data['prs_providerID'] = $prs_providerID;
		$page_data['prs_dateOfService'] = $prs_dateOfService;

		$this->session->set_userdata('prs_ehr_systems', json_encode($prs_ehr_systems));
		$this->session->set_userdata('prs_providerID', $prs_providerID);
		$this->session->set_userdata('prs_dateOfService', $prs_dateOfService);

		$this->twig->view('provider_route_sheet_management/route_sheet/details', $page_data);
	}
}
