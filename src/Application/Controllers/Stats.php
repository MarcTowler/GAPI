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
		parent::__destruct(); // TODO: Change the autogenerated stub
    }

    public function currency()
    {
        $outcome = $this->_db->gold_stats();

        return $this->_output->output(200, $outcome, false);
	}
	
	public function pve_win()
	{
		$outcome = $this->_db->pve_win_stats();
		
		return $this->_output->output(200, $outcome, false);
	}

	public function pve_loss()
	{
		$outcome = $this->_db->pve_loss_stats();
		
		return $this->_output->output(200, $outcome, false);
	}

	public function level()
	{
		$outcome = $this->_db->level_stats();
		
		return $this->_output->output(200, $outcome, false);
	}
}