<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Stats extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\StatsModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function pveWin()
    {
        $outcome = $this->_db->pve_win_stats();
		
		return $this->_output->output(200, $outcome, false);
    }

    public function pveLoss()
    {
        $outcome = $this->_db->pve_loss_stats();
		
		return $this->_output->output(200, $outcome, false);
    }

    public function pvpWin()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function pvpLoss()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function currency()
    {
        $outcome = $this->_db->gold_stats();

        return $this->_output->output(200, $outcome, false);
    }

    public function level()
    {
        $outcome = $this->_db->level_stats();
		
		return $this->_output->output(200, $outcome, false);
    }
}
