<?php
/**
 * Fight Model
 *
 * All fighting related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */

namespace API\Model;

use API\Library;

class StatsModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function giveDrop($cid, $iid)
	{
		try {
			$stmt = $this->_db->prepare("INSERT INTO item_owned (iid, oid, equipped) VALUES(:iid,:cid, 0)");
			$stmt->execute([':cid' => $cid, ':iid' => $iid]);
		} catch(\PDOException $e) {
			var_dump($e->getMessage());die;
		}

		$success = ($this->_db->lastInsertId() > 0) ? true : false;

		return $success;
	}
}