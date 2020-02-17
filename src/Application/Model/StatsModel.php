<?php
/**
 * Stats Model
 *
 * All stats related database called are stored here
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

    /**
     * StatsModel::pve_win_stats()
     *
     * Gets top 10 PvE 
     *
     * @return array the top 10
     */
    public function gold_stats()
    {
        $stmt = $this->_db->prepare("SELECT username, pouch FROM `character` ORDER BY pouch DESC LIMIT 10");
        $stmt->execute();
 
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * StatsModel::level_stats()
     *
     * Gets top 10 levelled players 
     *
     * @return array the top 10 levelled players
     */
    public function level_stats()
    {
        $stmt = $this->_db->prepare("SELECT username, level FROM `character` ORDER BY level DESC LIMIT 10");
        $stmt->execute();
 
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
 
    /**
     * StatsModel::pve_win_stats()
     *
     * Gets top 10 PvE wins
     *
     * @return array the top 10 wins
     */
    public function pve_win_stats()
    {
        $stmt = $this->_db->prepare("SELECT c.username, s.mon_win FROM play_fight_stats s INNER JOIN `character` c ON c.cid = s.character_id ORDER BY s.mon_win DESC LIMIT 10");
        $stmt->execute();
 
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * StatsModel::pve_loss_stats()
     *
     * Gets top 10 PvE losses 
     *
     * @return array the top 10 losses
     */
    public function pve_loss_stats()
    {
        $stmt = $this->_db->prepare("SELECT c.username, s.mon_lose FROM play_fight_stats s INNER JOIN `character` c ON c.cid = s.character_id ORDER BY s.mon_lose DESC LIMIT 10");
        $stmt->execute();
 
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}