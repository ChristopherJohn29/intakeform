<?php

namespace Mobiledrs\core;

class MY_Models extends \CI_Model {
	protected $table_name = '';
	protected $limit = 0;
	protected $offset = 5;
	protected $gimg_db = null;
	protected $gmma_db = null;
	protected $mobile_physician_db = null;
	protected $routesheet_db = null;

	public function __construct()
	{
		parent::__construct();

		$this->gimg_db = $this->load->database('gimg', TRUE);
		$this->gmma_db = $this->load->database('gmma', TRUE);
		$this->mobile_physician_db = $this->load->database('mobile_physician', TRUE);
		$this->routesheet_db = $this->load->database('routesheet', TRUE);
	}

	public function insert(array $params, string $dbConn) : int
	{
		$this->$dbConn->insert($this->table_name, $params['data']);

		return $this->$dbConn->insert_id();
	}

	public function update(array $params, string $dbConn) : bool
	{
		return $this->$dbConn->update($this->table_name, $params['data'], [$params['key'] => $params['value']]);
	}

	public function record(array $params, string $dbConn)
	{
		$this->$dbConn->where($params['key'], $params['value']);

		if (isset($params['order_by'])) 
		{
			$this->$dbConn->order_by($params['order_key'], $params['order_by']);
		}

		if (isset($params['joins'])) 
		{
			foreach ($params['joins'] as $key => $value) 
			{
				$this->$dbConn->join(
					$value['join_table_name'],
					$value['join_table_key'] . ' ' .
					$value['join_table_condition'] . ' ' .
					$value['join_table_value'],
					$value['join_table_type']
				);
			}
		}

		if (isset($params['orders'])) 
		{
			foreach ($params['orders'] as $key => $value) 
			{
				$this->$dbConn->order_by($value['column'], $value['direction']);
			}
		}

		$query = $this->$dbConn->get($this->table_name);

		return $query->custom_row_object(0, $this->entity);
	}

	public function records(array $params = [], string $dbConn)
	{
		if (isset($params['order_by'])) 
		{
			$this->$dbConn->order_by($params['key'], $params['order_by']);
		}

		if (isset($params['where'])) 
		{
			foreach ($params['where'] as $key => $value) {
				$this->$dbConn->where(
					$value['key'] . ' ' .
					$value['condition'],
					$value['value']
				);
			}
		}

		if (isset($params['orders'])) 
		{
			foreach ($params['orders'] as $key => $value) 
			{
				$this->$dbConn->order_by($value['column'], $value['direction']);
			}
		}

		if (isset($params['where_in']))
		{
			$this->$dbConn->where_in($params['where_in']['column'], $params['where_in']['values']);
		}

		if (isset($params['limit']))
		{
			$this->$dbConn->limit($params['limit']);
		}

		$query = $this->$dbConn->get($this->table_name, $this->limit, $this->offset);

		return $query->custom_result_object($this->entity);
	}

	public function find(array $params, string $dbConn)
	{
		foreach ($params['where_data'] as $i => $search) {
			$like_func = ($i > 0) ? 'or_like' : 'like';

			$this->$dbConn->$like_func($search['key'], $search['value']);
		}

		$query = $this->$dbConn->get($this->table_name);

		return $query->custom_result_object($this->entity);
	}

	public function get_records_by_join(array $params, string $dbConn)
	{	
		if (isset($params['joins'])) 
		{
			foreach ($params['joins'] as $key => $value) 
			{
				$this->$dbConn->join(
					$value['join_table_name'],
					$value['join_table_key'] . ' ' .
					$value['join_table_condition'] . ' ' .
					$value['join_table_value'],
					$value['join_table_type']
				);
			}
		}

		if (isset($params['where']))
		{
			foreach ($params['where'] as $value) {
				$this->$dbConn->where(
					$value['key'] . ' ' .
					$value['condition'],
					$value['value']
				);
			}
		}

		if (isset($params['where_in_list'])) 
		{
			$this->$dbConn->where_in(
				$params['where_in_list']['key'], 
				$params['where_in_list']['values']
			);
		}

		if (isset($params['order'])) 
		{
			$this->$dbConn->order_by($params['order']['key'], $params['order']['by']);
		}

		if (isset($params['limit']))
		{
			$this->$dbConn->limit($params['limit']);
		}

		$query = $this->$dbConn->get($this->table_name);

		return (isset($params['return_type']) && $params['return_type'] == 'row') ?
			$query->custom_row_object(0, $this->entity) :
			$query->custom_result_object($this->entity);
	}

	public function delete_data(array $params, string $dbConn)
	{
		return $this->$dbConn->delete($this->table_name, [$params['table_key'] => $params['record_id']]);
	}

	public function prepare_entity_data()
	{
		foreach($this->input->post() as $key => $value)
		{
			if (isset($this->excludes_list) && in_array($key, $this->excludes_list))
			{
				continue;
			}

			$this->record_entity->$key = $value;
		}
	}

	public function make_paid(array $params, string $dbConn)
	{
		$dataToInsert = [];

		for ($i = 0; $i < count($params['data']); $i++)
		{ 
			$dataToInsert[] = [
				$params['columnID'] => $params['data'][$i],
				$params['columnPaid'] => date('Y-m-d')
			];
		}

		return $this->$dbConn->update_batch($this->table_name, $dataToInsert, $params['columnID']);
	}
}