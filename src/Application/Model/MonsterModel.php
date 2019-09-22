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

    public function update_stats($id, $player, $outcome)
    {
        $stmt = ($outcome == "win") ? 
            $this->_db->prepare("INSERT INTO npc_fight_stats (nid, win) VALUES(:npc, win = win + 1) ON DUPLICATE KEY UPDATE win = win + 1") : 
            $this->_db->prepare("INSERT INTO npc_fight_stats (nid, loss) VALUES(:npc, loss = loss + 1) ON DUPLICATE KEY UPDATE loss = loss + 1");
        $stmt->execute([':npc' => $id]);

        //this is reversed due to being from a monster perspective
        $stmt2 = ($outcome == "win") ?
            $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, loss) VALUES(:player, :npc, loss = loss + 1) ON DUPLICATE KEY UPDATE loss = loss + 1") :
            $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, win) VALUES(:player, :npc, win = win + 1) ON DUPLICATE KEY UPDATE win = win + 1");
        $stmt2->execute([
            ':npc'    => $id,
            ':player' => $player
        ]);

        return true;
    }
}