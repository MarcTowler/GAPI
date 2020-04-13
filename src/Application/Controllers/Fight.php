<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Fight extends Library\BaseController
{
    private $_db;
    private $_user;
    private $_mon;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db   = new Model\FightModel();
        $this->_user = new Model\UserModel();
        $this->_mon  = new Model\MonsterModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function pveWin()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
        $input = json_decode(file_get_contents('php://input'), true);
		$char = $this->_user->getPlayer($input['id'], $input['flag']);

		$this->_log->set_message("pveWin called for " . $char['username'] . " with " . $char['cur_hp'] . "HP remaining and for " . $input['pouch'] . " litcoins", "INFO");

        $output['coins'] = $this->_user->updateCoin($char['uid'], $input['pouch'], $input['win']); 
        $output['xp']    = $this->_user->updateXP($char['uid'], $input['xp'], $input['win']);

		//update HP
		$this->_user->update_player('cur_hp', $input['newHP'], $char['uid'], 3);

		$this->_db->updatePveStats($char['uid'], $input['monster'], true);
        
		$this->_mon->update_stats($input['monster'], $input['win']);

		return $this->_output->output(200, true, false);
    }

    public function pveLoss()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

		    $char = $this->_user->getPlayer($input['id'], $input['flag']);

        $min_xp = $this->_user->xpNeeded($char['level'])['xp_needed'];

		$input['pouch'] = ($char['pouch'] < $input['pouch']) ? $char['pouch'] : $input['pouch'];
        $input['xp']    = ($char['xp'] < $input['xp']) ? $char['xp'] : $input['xp'];
        
        $input['xp'] = ($input['xp'] < $min_xp) ? $min_xp : $input['xp'];

		$this->_log->set_message("pveLoss called for " . $char['username'] . " for " . $input['pouch'] . " litcoins", "INFO");
		$output['coins'] = $this->_user->updateCoin($char['uid'], $input['pouch'], $input['win']);
		$output['xp']    = $this->_user->updateXP($char['uid'], $input['xp'], $input['win']);
        
		//update HP
		$this->_user->update_player('cur_hp', $input['newHP'], $char['uid'], 3);

		$this->_db->updatePveStats($char['uid'], $input['monster'], $input['win']);
		$this->_mon->update_stats($input['monster'], $input['win']);

		return $this->_output->output(200, true, false);
    }

    public function pvpWin()
    {

    }

    public function pvpLoss()
    {
        
    }
}