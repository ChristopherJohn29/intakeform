<?php

use Mobiledrs\entities\Db_list;

class Route_sheet_model extends \Mobiledrs\core\MY_Models {
	
	protected $table_name = 'provider_route_sheet';
	protected $entity = '\Mobiledrs\entities\provider_route_sheet_management\Routesheet_entity';
	protected $pt_trans_entity = null;

	public function __construct()
	{
		parent::__construct();

		$this->pt_trans_entity = new \Mobiledrs\entities\patient_management\Transaction_entity();
	}

	public function insert(array $params, string $dbConn) : int
	{
		$entity = new \Mobiledrs\entities\Entity();

		$this->routesheet_db->trans_start();

		$this->routesheet_db->insert('provider_route_sheet', [
			'prs_providerID' => $this->input->post('prs_providerID'),
			'prs_dateOfService' => $entity->set_date_format($this->input->post('prs_dateOfService'))
		]);

		$prs_id = $this->routesheet_db->insert_id();
		$patientTransIDs = $this->insert_patientTransData($this->input->post());

		$this->routesheet_db->insert_batch(
			'provider_route_sheet_list', 
			$this->prepare_patient_details_data(
				$this->input->post(), 
				$prs_id, 
				$patientTransIDs
			)
		);

		$this->routesheet_db->trans_complete();

		return $this->routesheet_db->trans_status() ? $prs_id : 0;
	}

	public function update(array $params, string $dbConn) : bool
	{
		$entity = new \Mobiledrs\entities\Entity();

		$this->routesheet_db->trans_start();

		$this->routesheet_db->where($params['key'], $params['value']);

		$this->routesheet_db->update('provider_route_sheet', [
			'prs_providerID' => $this->input->post('prs_providerID'),
			'prs_dateOfService' => $entity->set_date_format($this->input->post('prs_dateOfService'))
		]);

		$this->routesheet_db->where_in('provider_route_sheet_list.prsl_id', $this->input->post('prsl_ids'));

		$this->routesheet_db->delete('provider_route_sheet_list');

		$patientTransIDs = $this->insert_patientTransData($this->input->post());

		$this->routesheet_db->insert_batch(
			'provider_route_sheet_list', 
			$this->prepare_patient_details_data($this->input->post(), $params['value'], $patientTransIDs)
		);

		$this->routesheet_db->trans_complete();

		return $this->routesheet_db->trans_status();
	}

	public function insert_patientTransData(array $inputPost) : array
	{
		$this->table_name = 'patient_transactions';

		$data = [];

		for ($i = 0; $i < count($inputPost['prsl_fromTime']); $i++) 
		{
			$dbName = (new Db_list)->get_db($inputPost['prsl_patientID'][$i]);

			$dataToDB = [
				'pt_tovID' => $inputPost['prsl_tovID'][$i],
				'pt_patientID' => $inputPost['prsl_patientID'][$i],
				'pt_providerID' => $inputPost['prs_providerID'],
				'pt_dateOfService' => $this->pt_trans_entity->set_date_format($inputPost['prs_dateOfService']),
				'pt_dateRef' => $this->pt_trans_entity->set_date_format($inputPost['prsl_dateRef'][$i])
			];

			if (isset($inputPost['patientTransDateIDs'][$i]))
			{
				$patientTransID = $inputPost['patientTransDateIDs'][$i];

				parent::update([
					'data' => $dataToDB,
					'key' => 'patient_transactions.pt_id',
					'value' => $patientTransID
				], $dbName);

				$data[] = $patientTransID;
			}
			else
			{
				$data[] = parent::insert(['data' => $dataToDB], $dbName);
			}
		}

		for ($i = 0; $i < count($inputPost['patientTransDateIDs']); $i++) 
		{
			if (isset($inputPost['patientTransDateIDs'][$i]) &&
				(! isset($inputPost['prsl_dateRef'][$i]))
			) {
				$patientTransID = $inputPost['patientTransDateIDs'][$i];
				$dbName = (new Db_list)->get_db($patientTransID);

				parent::delete_data([
					'table_key' => 'patient_transactions.pt_id',
					'record_id' => $patientTransID
				], $dbName);
			}
		}
		
		$this->table_name = 'provider_route_sheet';

		return $data;
	}

	public function prepare_patient_details_data(array $inputPost, string $prsl_prsID, array $patientTransIDs) : array
	{
		$data = [];

		for ($i = 0; $i < count($inputPost['prsl_fromTime']); $i++) 
		{
			// get patient home health id from record
			$pt_hhc_params = [
				'key' => 'patient.patient_id',
				'value' => $inputPost['prsl_patientID'][$i]
			];

			$dbName = (new Db_list)->get_db($inputPost['prsl_patientID'][$i]);
			$patient_record = $this->pt_model->record($pt_hhc_params, $dbName);

			$data[] = [
				'prsl_prsID' => $prsl_prsID,
				'prsl_fromTime' => $this->time_converter->convert_to_twentyfour_hrs_time(
					$inputPost['prsl_fromTime'][$i]),
				'prsl_toTime' => $this->time_converter->convert_to_twentyfour_hrs_time(
					$inputPost['prsl_toTime'][$i]),
				'prsl_patientID' => $inputPost['prsl_patientID'][$i],
				'prsl_hhcID' => $patient_record->patient_hhcID,
				'prsl_tovID' => $inputPost['prsl_tovID'][$i],
				'prsl_notes' => $inputPost['prsl_notes'][$i],
				'prsl_patientTransID' => $patientTransIDs[$i],
				'prsl_dateRef' => $this->pt_trans_entity->set_date_format($inputPost['prsl_dateRef'][$i])
			];
		}

		return $data;
	}

	public function get_routesheet_details_data(string $prs_id) : array
	{
		$record_params = [
			'key' => 'provider_route_sheet.prs_id',
			'value' => $prs_id
		];

		$routesheet_record = $this->record($record_params, 'routesheet_db');

		$provider_params = [
			'key' => 'provider.provider_id',
			'value' => $routesheet_record->prs_providerID
		];

		$this->table_name = 'provider';
		$provider_record = $this->record($provider_params, 'gimg_db');
		$data['record'] = [
			'providerID' => $provider_record->provider_id,
			'providerName' => $provider_record->get_provider_fullname(),
			'providerEmail' => $provider_record->provider_email,
			'dateOfService' => $routesheet_record->get_date_format($routesheet_record->prs_dateOfService),
			'prs_id' => $routesheet_record->prs_id
		];

		$record_list_params = [
			'where' => [
				[
					'key' => 'provider_route_sheet_list.prsl_prsID',
					'condition' =>  '=',
					'value' => $prs_id
				]
			]
		];

		$this->table_name = 'provider_route_sheet_list';
		$this->entity = '\Mobiledrs\entities\provider_route_sheet_management\Routesheet_list_entity';
		$routesheet_list_record = $this->records($record_list_params, 'routesheet_db');
		$routesheet_list_datas = [];

		foreach ($routesheet_list_record as $routesheet_list) {
			$patient_params = [
				'key' => 'patient.patient_id',
				'value' => $routesheet_list->prsl_patientID
			];

			$dbName = (new Db_list)->get_db($routesheet_list->prsl_patientID);
			$this->table_name = 'patient';
			$patient_record = $this->record($patient_params, $dbName);

			$homeHealth_params = [
				'key' => 'home_health_care.hhc_id',
				'value' => $routesheet_list->prsl_hhcID
			];

			$this->table_name = 'home_health_care';
			$homeHealth_record = $this->record($homeHealth_params, $dbName);

			$tov_params = [
				'key' => 'type_of_visits.tov_id',
				'value' => $routesheet_list->prsl_tovID
			];

			$this->table_name = 'type_of_visits';
			$tov_record = $this->record($tov_params, $dbName);

			$routesheet_list_datas[] = [
				'routesheetID' => $routesheet_list->prsl_id,
				'patientTransID' => $routesheet_list->prsl_patientTransID,
				'dateRef' => $routesheet_list->get_date_format($routesheet_list->prsl_dateRef),
				'tovID' => $routesheet_list->prsl_tovID,
				'patientID' => $routesheet_list->prsl_patientID,
				'fromTime' => $routesheet_list->get_fromTime(),
				'toTime' => $routesheet_list->get_toTime(),
				'time' => $routesheet_list->get_combined_time(),
				'patientName' => $patient_record->patient_name,
				'patientAddress' => $patient_record->patient_address,
				'patientPhoneNum' => $patient_record->patient_phoneNum,
				'homeHealthName' => $homeHealth_record->hhc_name,
				'homeHealthContactName' => $homeHealth_record->hhc_contact_name,
				'homeHealthPhoneNum' => $homeHealth_record->hhc_phoneNumber,
				'tovName' => $tov_record->tov_name ?? '',
				'notes' => $routesheet_list->prsl_notes,
				'company' => $dbName == 'gimg_db' ? 'GIMG' : 'GMMA'
			];
		}

		$data['lists'] = $routesheet_list_datas;
		$data['dbName'] = $dbName;

		$this->table_name = 'provider_route_sheet';
		$this->entity = '\Mobiledrs\entities\provider_route_sheet_management\Routesheet_entity';

		return $data;
	}

	public function get_routesheet_details_summary($prs_ehr_systems, $prs_providerID, $prs_dateOfService) : array
	{
		$data = [];
		foreach ($prs_ehr_systems as $ehrSystem) {
			// get provider record
			// $this->$ehrSystem->like("concat(provider_firstname, ' ', provider_lastname)", $prs_providerID);
			$providerName = explode('/', $prs_providerID);
			$this->$ehrSystem->like("provider_firstname", $providerName[0]);
			if (isset($providerName[1])) {
				$this->$ehrSystem->like("provider_lastname", $providerName[1]);
			}
			$query = $this->$ehrSystem->get('provider');
			$provider = $query->row_array();

			if (is_null($provider)) {
				continue;
			}

			$data['record'] = [
				'providerID' => $provider['provider_id'],
				'providerName' => $provider['provider_firstname'].' '.$provider['provider_lastname'],
				'providerEmail' => $provider['provider_email'],
				'dateOfService' => $this->get_date_format($prs_dateOfService)
			];

			// get provider routesheet record
			$this->$ehrSystem->where('prs_providerID', $provider['provider_id']);
			$this->$ehrSystem->where('prs_dateOfService', date_format(date_create($prs_dateOfService), 'Y-m-d'));
			$this->$ehrSystem->where('prs_archive', null);
			$this->$ehrSystem->join('provider_route_sheet', 'prs_id = prsl_prsID', 'INNER');
			$query = $this->$ehrSystem->get('provider_route_sheet_list');
			$routesheets = $query->result_array();

			// prepare data for UI
			$routesheet_list_datas = [];
			foreach ($routesheets as $routesheet) {
				// get patient record
				$this->$ehrSystem->where('patient_id', $routesheet['prsl_patientID']);
				$query = $this->$ehrSystem->get('patient');
				$patient_record = $query->row_array();

				
				// get type of visit record
				$this->$ehrSystem->where('tov_id', $routesheet['prsl_tovID']);
				$query = $this->$ehrSystem->get('type_of_visits');
				$tov_record = $query->row_array();

				// get patient transaction record
				$this->$ehrSystem->where('pt_id', $routesheet['prsl_patientTransID']);
				$query = $this->$ehrSystem->get('patient_transactions');
				$patientTransRecord = $query->row_array();

				// get homehealth record
				$this->$ehrSystem->where('hhc_id', $routesheet['prsl_hhcID']);
				$query = $this->$ehrSystem->get('home_health_care');
				$homeHealth_record = $query->row_array();


				// get supervising md record
				$this->$ehrSystem->where('provider_id', $patientTransRecord['pt_supervising_mdID']);
				$query = $this->$ehrSystem->get('provider');
				$supervisingRecord = $query->row_array();

				$tmp = [
					'routesheetID' => $routesheet['prsl_id'],
					'patientTransID' => $routesheet['prsl_patientTransID'],
					'dateRef' => $this->get_date_format($routesheet['prsl_dateRef']),
					'tovID' => $routesheet['prsl_tovID'],
					'patientID' => $routesheet['prsl_patientID'],
					'fromTime' => $this->get_fromTime($routesheet['prsl_fromTime']),
					'toTime' => $this->get_toTime($routesheet['prsl_toTime']),
					'time' => $this->get_combined_time($routesheet['prsl_fromTime'], $routesheet['prsl_toTime']),
					'patientName' => $patient_record['patient_name'],
					'patientAddress' => $patient_record['patient_address'],
					'patientPhoneNum' => $patient_record['patient_phoneNum'],
					'homeHealthName' => $homeHealth_record['hhc_name'],
					'homeHealthContactName' => $homeHealth_record['hhc_contact_name'],
					'homeHealthPhoneNum' => $homeHealth_record['hhc_phoneNumber'],
					'tovName' => $tov_record['tov_name'] ?? '',
					'notes' => $routesheet['prsl_notes'],
					'company' => '',
					'supervisingMD_firstname' => $supervisingRecord['provider_firstname'],
					'supervisingMD_lastname' => $supervisingRecord['provider_lastname']
				];

				if ($ehrSystem === 'gimg_db') {
					$tmp['company'] = 'GIMG';
				} else if ($ehrSystem === 'gmma_db') {
					$tmp['company'] = 'GMMA';
				} else {
					$tmp['company'] = 'Mobile Physician';
				}

				$routesheet_list_datas[] = $tmp;
			}

			if (isset($data['lists'])) {
				$data['lists'] = array_merge($data['lists'], $routesheet_list_datas);
			} else {
				$data['lists'] = $routesheet_list_datas;
			}
		}

		// sort by earliest time
		usort($data['lists'], function ($a, $b) {
			$aFromTime = strtotime($a['fromTime']);
			$bFromTime = strtotime($b['fromTime']);

			if ($aFromTime == $bFromTime) {
		        return 0;
		    }

		    return ($aFromTime < $bFromTime) ? -1 : 1;
		});

		return $data;
	}

	public function get_records_list(array $params = []) : array
	{
		$routesheet_records = $this->records(['key' => 'prs_dateOfService', 'order_by' => 'desc'], 'routesheet_db');
		$routesheet_recordList = [];

		foreach ($routesheet_records as $routesheet) {			
			$provider_params = [
				'key' => 'provider.provider_id',
				'value' => $routesheet->prs_providerID
			];

			$this->table_name = 'provider';
			$provider_record = $this->record($provider_params, 'gimg_db');

			$routesheet_recordList[] = [
				'routesheetID' => $routesheet->prs_id,
				'dateOfService' => $routesheet->get_date_format($routesheet->prs_dateOfService),
				'providerName' => $provider_record->get_provider_fullname()
			];
		}

		$data['records'] = $routesheet_recordList;

		return $data;
	}

	public function prepare_data()
	{
	}

	public function get_date_format(string $date) : string
    {
    	return ( ! empty($date) && $date != '0000-00-00') ? date_format(date_create($date), 'm/d/Y') : '';
    }

    public function get_combined_time($fromTime, $toTime) : string
	{
		$fromTime = date_format(date_create($fromTime), 'h:i A');
		$toTime = date_format(date_create($toTime), 'h:i A');
		return $fromTime . ' - ' . $toTime;
	}

	public function get_fromTime($fromTime) : string
	{
		return date_format(date_create($fromTime), 'h:i A');
	}

	public function get_toTime($toTime) : string
	{
		return date_format(date_create($toTime), 'h:i A');
	}
}