<?php

namespace Mobiledrs\entities;

class Db_list {

	private static $db = [
		1 => 'gmma_db',
		2 => 'gimg_db'
	];

	public static function get_db(string $id) : string
	{
		$tovSel = substr($id, 0, 1);

		return self::$db[$tovSel];
	}
}