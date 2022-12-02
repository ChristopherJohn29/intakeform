<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends \Mobiledrs\core\MY_Controller {
	
	public function __construct()
	{
		parent::__construct();

		// $this->load->model(array(
		// 	'provider_route_sheet_management/route_sheet_model' => 'rs_model',
		// ));
	}

	public function index()
	{
		// $prs_params = [
		// 	'orders' => [
		// 		[
		// 			'column' => 'provider_route_sheet.prs_dateOfService',
		// 			'direction' => 'DESC'
		// 		]
		// 	],
		// 	'limit' => '15',
		// 	'return_type' => 'object'
		// ];
		
		// $page_data = $this->rs_model->get_records_list($prs_params);
		// $page_data['routesheet_entity'] = new \Mobiledrs\entities\provider_route_sheet_management\Routesheet_entity();

		// $this->twig->view('home', $page_data);
		redirect('provider_route_sheet_management/route_sheet/generate');
	}
}
