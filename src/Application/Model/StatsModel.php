<?php
namespace API\Model;

use API\Library;

class StatsModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
    }

    public function gold_stats()
    {
        $stmt = $this->_db->prepare("SELECT username, pouch FROM `character` ORDER BY pouch DESC LIMIT 10");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function level_stats()
    {
        $stmt = $this->_db->prepare("SELECT username, level FROM `character` ORDER BY level DESC LIMIT 10");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pve_win_stats()
    {
        $stmt = $this->_db->prepare("SELECT c.username, s.mon_win FROM play_fight_stats s INNER JOIN `character` c ON c.cid = s.character_id ORDER BY s.mon_win DESC LIMIT 10");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pve_loss_stats()
    {
        $stmt = $this->_db->prepare("SELECT c.username, s.mon_lose FROM play_fight_stats s INNER JOIN `character` c ON c.cid = s.character_id ORDER BY s.mon_lose DESC LIMIT 10");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}