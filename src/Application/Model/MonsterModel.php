<?php
namespace API\Model;

use API\Library;

class MonsterModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
    }

    public function get_monster($level_range)
    {
        if(!is_array($level_range))
        {
            $this->_output = "Level Range needs to be an array";
        }

        $stmt = $this->_db->prepare("SELECT * FROM npc WHERE level >= :min AND level <= :max AND active = 1 ORDER BY RAND() LIMIT 1");
        $stmt->execute(
            [
                ':min' => $level_range['min'],
                ':max' => $level_range['max']
            ]
        );

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->_output;
    }

    public function update_stats($id, $outcome)
    {
        $stmt = ($outcome == "win") ? 
            $this->_db->prepare("INSERT INTO monster_stats (nid, wins) VALUES(:npc, wins + 1) ON DUPLICATE KEY UPDATE wins = wins + 1") : 
            $this->_db->prepare("INSERT INTO monster_stats (nid, losses) VALUES(:npc, losses + 1) ON DUPLICATE KEY UPDATE losses = losses + 1");
        $stmt->execute([':npc' => $id]);

        return true;
    }
}