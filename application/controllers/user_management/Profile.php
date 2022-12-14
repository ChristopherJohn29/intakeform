<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends \Mobiledrs\core\MY_Controller {
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			'user_management/profile_model',
			'user_management/roles_model'
		));
	}

	public function index(string $highlight = '')
	{
		// $this->check_permission('list_user');

		$params = [
			'joins' => [
				[
					'join_table_name' => 'roles',
					'join_table_key' => 'roles.roles_id',
					'join_table_condition' => '=',
					'join_table_value' => 'user.user_roleID',
					'join_table_type' => 'inner',
				]
			],
			'where' => [
				[
					'key' => 'user_roleID',
					'condition' => '<>',
					'value' => 1
				]
			],
			'return_type' => 'result'
		];
		
		$page_data['highlight'] = $highlight;
		$page_data['records'] = $this->profile_model->get_records_by_join($params, 'routesheet_db');

		$this->twig->view('user_management/profile/list', $page_data);
	}

	public function add()
	{
		// $this->check_permission('add_user');

		$params = [
			'where' => [
				[
					'key' => 'roles_id',
					'condition' => '<>',
					'value' => '1'
				]
			]
		];

		$page_data['roles'] = $this->roles_model->records($params, 'routesheet_db');

		$this->twig->view('user_management/profile/add', $page_data);
	}

	public function edit(string $user_id)
	{
		// $this->check_permission('edit_user');

		$params = [
			'key' => 'user_id',
        	'value' => $user_id
		];

		$page_data['record'] = $this->profile_model->record($params, 'routesheet_db');

		if ( ! $page_data['record'])
		{
			redirect('errors/page_not_found');
		}
		
		$role_params = [
			'where' => [
				[
					'key' => 'roles_id',
					'condition' => '<>',
					'value' => '1'
				]
			]
		];

		$page_data['roles'] = $this->roles_model->records($role_params, 'routesheet_db');

		$this->twig->view('user_management/profile/edit', $page_data);
	}

	public function save(string $formtype, string $user_id = '')
	{
		// $this->check_permission('add_user');

		// only check for duplicate emails when the email field has been changed
		$validation_group = '';
		if ($formtype == 'edit')
		{
			$params = [
				'key' => 'user_id',
	        	'value' => $user_id
			];

			$user_record = $this->profile_model->record($params, 'routesheet_db');

			if ( ! $user_record)
			{
				redirect('errors/page_not_found');
			}

			if ($user_record->has_changed_email($this->input->post('user_email')))
			{
				$validation_group = 'user_management/profile/save';
			}
			else
			{
				$validation_group = 'user_management/profile/save_update';
			}
		}
		else
		{
			// email validation
			//$this->form_validation->set_rules('email', 'Email', 'required|callback_check_email');

			$validation_group = 'user_management/profile/save';
		}

		$params = [
			'record_id' => $user_id,
			'table_key' => 'user_id',
			'save_model' => 'profile_model',
			'redirect_url' => 'user_management/profile',
			'validation_group' => $validation_group
		];

		parent::save_data($params);
	}

	public function check_email($str)
	{	
		$routesheet_db = $this->load->database('routesheet', TRUE);
		$conn = 'routesheet_db';
		$email = $routesheet_db->query('SELECT email from user where email LIKE %'.$str.'%;');

	if ($email)
        {
	        $this->form_validation->set_message('check_email', 'This email is already exists');
	        return FALSE;
        }
        else
        {
            return TRUE;
        }
	}
}
