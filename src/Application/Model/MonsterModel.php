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

        $stmt = $this->_db->prepare("SELECT * FROM npc WHERE type = 'Monster' AND level >= :min AND level <= :max AND active = 1 ORDER BY RAND() LIMIT 1");
        $stmt->execute(
            [
                ':min' => $level_range['min'],
                ':max' => $level_range['max']
            ]
        );

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->_output;
    }

    public function getSpecificMonster($id)
    {
        $stmt = $this->_db->prepare("SELECT n.name, n.hp, n.str, n.def, n.dex, n.spd, n.level, n.lore, s.loss FROM `npc` n INNER JOIN npc_fight_stats s ON n.nid = s.npc_id WHERE n.name = :id");
        $stmt->execute([':id' => $id]);

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->_output;
    }

    public function update_stats($id, $outcome)
    {
        $stmt = ($outcome == false) ? 
            $this->_db->prepare("INSERT INTO npc_fight_stats (npc_id, win) VALUES(:npc, 1) ON DUPLICATE KEY UPDATE win = win + 1") : 
            $this->_db->prepare("INSERT INTO npc_fight_stats (npc_id, loss) VALUES(:npc, 1) ON DUPLICATE KEY UPDATE loss = loss + 1");
        $stmt->execute([':npc' => $id]);

        return true;
    }
}