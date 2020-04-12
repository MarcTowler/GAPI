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

	public function updatePveStats($cid, $mid, $win)
	{
		if($win)
		{
			$stmt = $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, win) VALUES(:cid, :mid, 1) ON DUPLICATE KEY UPDATE win = win + 1");
			$stmt->execute(
				[
					':cid' => $cid,
					':mid' => $mid
				]
			);

			$stmt2 = $this->_db->prepare("INSERT INTO play_fight_stats (character_id, mon_win) VALUES(:cid, 1) ON DUPLICATE KEY UPDATE mon_win = mon_win + 1");
			$stmt2->execute([':cid' => $cid]);
		} else {
			$stmt = $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, loss) VALUES(:cid, :mid, 1) ON DUPLICATE KEY UPDATE loss = loss + 1");
			$stmt->execute(
				[
					':cid' => $cid,
					':mid' => $mid
				]
			);

			$stmt2 = $this->_db->prepare("INSERT INTO play_fight_stats (character_id, mon_lose) VALUES(:cid, 1) ON DUPLICATE KEY UPDATE mon_lose = mon_lose + 1");
			$stmt2->execute([':cid' => $cid]);
		}

		return true;
	}
}