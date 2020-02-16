<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Fight extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\FightModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function pveWin()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
        $input = json_decode(file_get_contents('php://input'), true);
		$char = $this->_db->getPlayer($input['discord_id'], 1);

		$this->_log->set_message("pveWin called for " . $char['username'] . " for " . $input['pouch'] . " litcoins", "INFO");

        //TO BE REPLACED IN BOT AS SEPERATE CALLS
        $output['coins'] = $this->_db->updateCoin($char['username'], $input['pouch'], $input['win']); 
        $output['xp']    = $this->_db->updateXP($char['username'], $input['xp'], $input['win']);
        //TO BE REPLACED IN BOT AS SEPERATE CALLS

		//update HP
		$this->_db->updatePlayer(['cur_hp' => $input['newHP']], $char['cid']);

		//Need to update player and monster fight stats, need to update XP, coin and HP for player, also updateXP() might be needed to be a seperate private function here to check for level up
        $this->_db->updatePveStats($char['cid'], $input['monster'], true);
        
		$this->_guzzle->get('https://gapi.itslit.uk/Monster/updateStats/' . $input['monster'] . '/' . $input['win']);

		return $this->_output->output(200, true, false);
    }

    public function pveLoss()
    {
        $input = json_decode(file_get_contents('php://input'), true);
		$char = $this->_db->getPlayer($input['discord_id'], 1);

		$input['pouch'] = ($char['pouch'] < $input['pouch']) ? $char['pouch'] : $input['pouch'];
		$input['xp'] = ($char['xp'] < $input['xp']) ? $char['xp'] : $input['xp'];


		$this->_log->set_message("pveLoss called for " . $char['username'] . " for " . $input['pouch'] . " litcoins", "INFO");
		$output['coins'] = $this->_db->updateCoin($char['username'], $input['pouch'], $input['win']);
		$output['xp']    = $this->_db->updateXP($char['username'], $input['xp'], $input['win']);

        //Check if drops have been issued, if so give them
        if(!empty($input['drop']) && $input['win'] == true) 
		{ 
			$this->_db->giveDrop($char['uid'], $input['drop']); 
        }
        
		//update HP
		$this->_db->updatePlayer(['cur_hp' => $input['newHP']], $char['cid']);

		//Need to update player and monster fight stats, need to update XP, coin and HP for player, also updateXP() might be needed to be a seperate private function here to check for level up
		$this->_db->updatePveStats($char['cid'], $input['monster'], $input['win']);
		$this->_guzzle->get('https://gapi.itslit.uk/Monster/updateStats/' . $input['monster'] . '/' . $input['win']);

		return $this->_output->output(200, true, false);
    }

    public function pvpWin()
    {

    }

    public function pvpLoss()
    {
        
    }
}