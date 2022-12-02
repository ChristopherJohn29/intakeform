<?php

class Acl {
	
	protected $CI = null;

	private $permission_name = null;
	private $permission_id = null;
	private $role_id = null;
	private $gimg_db = null;
	private $gmma_db = null;
	private $dbConn = null;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->gimg_db = $this->CI->load->database('gimg', TRUE);
		$this->gmma_db = $this->CI->load->database('gmma', TRUE);
	}

	public function has_permission(string $role_id, string $permission_name, string $dbConn) : bool
	{
		$this->dbConn = $dbConn;
		$this->role_id = $role_id;
		$this->permission_name = $permission_name;
		$this->permission_id = $this->get_permission_id();

		if ($this->permission_id < 0)
		{
			throw new Exception("Permission is not found in the database");	
		}

		return $this->get_roles_permissions($dbConn);
	}

	private function get_permission_id() : int
	{
		$this->CI->db = $this->dbConn == 'gimg_db' ? $this->gimg_db : $this->gmma_db;

		$this->CI->db->where('permissions_name', $this->permission_name);

		$query = $this->CI->db->get('permissions');

		return $query->row_array()['permissions_id'] ?? -1;
	}

	private function get_roles_permissions() : bool 
	{
		$this->CI->db = $this->dbConn == 'gimg_db' ? $this->gimg_db : $this->gmma_db;

		$this->CI->db->where('rp_rolesID', $this->role_id);
		$this->CI->db->where('rp_permissionsID', $this->permission_id);
		
		$query = $this->CI->db->get('roles_permission');

		return $query->row_array() ? true : false;
	}
}