<?php

require_once(APPPATH . 'core/MY_Models.php');

class User_model extends \Mobiledrs\core\MY_Models {
	
	protected $table_name = 'user';
	protected $entity = '\Mobiledrs\entities\authentication\User_entity';

	public function __construct()
	{
		parent::__construct();
	}

	public function verify() : bool
	{
		$user_params = [
			'key' => 'user_email',
			'value' => $this->input->post('user_email')
		];

		$routesheet_user_entity = $this->record($user_params, 'routesheet_db');
		// $gmma_user_entity = $this->record($user_params, 'gmma_db');
		$validate_password = '';
		$db_name = null;

		if ( ! empty($routesheet_user_entity)) {
			$validate_password = $routesheet_user_entity->user_id && 
				$routesheet_user_entity->validate_password($this->input->post('user_password'));

			$user_entity = $routesheet_user_entity;
			$db_name = 'routesheet_db';
		}

		// if ( ! empty($gmma_user_entity) && empty($validate_password)) {
		// 	$validate_password = $gmma_user_entity->user_id && 
		// 		$gmma_user_entity->validate_password($this->input->post('user_password'));

		// 	$user_entity = $gmma_user_entity;
		// 	$db_name = 'gmma_db';
		// }		

		if ($validate_password)
		{
			$user_record_params = [
				'key' => 'user.user_id',
				'value' => $user_entity->user_id,
				'data' => ['user_sessionID' => session_id()]
			];

			$this->update($user_record_params, $db_name);

			$data = [
				'user_id' => $user_entity->user_id,
		        'user_fullname' => $user_entity->get_fullname(),
		        'user_email' => $user_entity->user_email,
		        'user_roleID' => $user_entity->user_roleID,
		        'user_db' => $db_name
			];

			$this->session->set_userdata($data);

			return true;
		}
		else 
		{
			return false;
		}
	}
}