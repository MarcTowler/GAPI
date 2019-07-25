<?php
namespace API\Model;

use API\Library;

class ViewerModel extends Library\BaseModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function add_user($id, $user)
	{
		$stmt = $this->_db->prepare("INSERT INTO users (twitch_id, name) VALUES(:id, :name) ON DUPLICATE KEY UPDATE twitch_id=:id");
		$stmt->execute([
			':id' => $id,
			':name' => $user
		]);

		$success = ($this->_db->lastInsertId() > 0) ? true : false;

		return $success;
	}

	public function update_user($id, $mode)
	{
		$stmt = $this->_db->prepare("UPDATE users SET $mode=1 WHERE twitch_id = :id");
		$stmt->execute([':id' => $id]);

		$success = ($this->_db->lastInsertId() > 0) ? true : false;

		return $success;
	}
}