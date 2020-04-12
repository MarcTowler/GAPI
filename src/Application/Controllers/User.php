<?php
/**
 * User Endpoint
 *
 * All user related functions will be handled in here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Controllers;

use API\Library;
use API\Model;

class User extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\UserModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * User::registerUser()
     *
     * POST Request
     *
     * Register's player data into the system, requires authentication
     *
     * @return object JSON Object
     */
    public function registerUser()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $data = json_decode(file_get_contents('php://input'), true);

		$success = false;
		$output = '';

		//check that $_POST exists and is not empty
		if(!isset($data) || empty($data))
		{
			return $this->_output->output(400, "No data POSTed to the API", false);
		} else {
			$tmp = $this->_db->getUser($data['name'], 0);

			//check if user exists
			if(is_array($tmp) && $tmp['registered'] == 1)
			{
				$output = "User " . $data['name'] . " already exists and is registered";
               
                return $this->_output->output(409, $output, false);
			} else {
				//bot to handle validation of data before transmission.... Next, lets check the user doesn't exist then submit
				$success = ($this->_db->registerUser($data, ($this->_headers['user'] == 'discord_bot') ? 0 : 1) == true) ? true : false;
				$output = "User " . $data['name'] . " has been registered!";
			}
		}

		return $this->_output->output(200, $output, false);
    }

    /**
     * User::getPlayer()
     *
     * GET Request
     *
     * Gets player object and returns it as a JSON object, requires authentication
     * @param string|int User identifier
     * @param int flag - 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     *
     * @return object JSON Object
     */
    public function getPlayer()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        if(!isset($this->_params[0]) || sizeof($this->_params) < 1)
        {
            $this->_log->set_message("User::getPlayer() called but no identifier set", "ERROR");

            return $this->_output->output(400, 'No user id or username specified', false);
        }

        $this->_log->set_message("User::getPlayer() called for identifier: " . $this->_params[0], "INFO");

        $output                = [];
        $tmp                   = [];
        $output                = $this->_db->getPlayer($this->_params[0], $this->_params[1]);

        if($output)
        {
            $output['attack_msg']  = isset($output['weapon'][0]['attack_msg']) ? $output['weapon'][0]['attack_msg'] : 'used their fists to hit';
            $output['defense_msg'] = isset($output['armour']['chest']['defense_msg']) ? $output['armour']['chest']['defense_msg'] : 'taking a step back';
            $output['mod_hp']      = $output['max_hp'] + $output['class']['hp_mod'] + $output['race']['hp_mod'] + $output['weapon'][0]['hp_mod'] + $output['armour']['head']['hp_mod'] + $output['armour']['chest']['hp_mod'] + $output['armour']['arms']['hp_mod'] + $output['armour']['legs']['hp_mod'] + $output['armour']['feet']['hp_mod'];
            $output['mod_ap']      = $output['max_ap'] + $output['class']['ap_mod'] + $output['race']['ap_mod'] + $output['weapon'][0]['ap_mod'] + $output['armour']['head']['ap_mod'] + $output['armour']['chest']['ap_mod'] + $output['armour']['arms']['ap_mod'] + $output['armour']['legs']['ap_mod'] + $output['armour']['feet']['ap_mod'];
            $output['mod_str']     = $output['str'] + $output['class']['str_mod'] + $output['race']['str_mod'] + $output['weapon'][0]['str_mod'] + $output['armour']['head']['str_mod'] + $output['armour']['chest']['str_mod'] + $output['armour']['arms']['str_mod'] + $output['armour']['legs']['str_mod'] + $output['armour']['feet']['str_mod'];
            $output['mod_def']     = $output['def'] + $output['class']['def_mod'] + $output['race']['def_mod'] + $output['weapon'][0]['def_mod'] + $output['armour']['head']['def_mod'] + $output['armour']['chest']['def_mod'] + $output['armour']['arms']['def_mod'] + $output['armour']['legs']['def_mod'] + $output['armour']['feet']['def_mod'];
            $output['mod_dex']     = $output['dex'] + $output['class']['dex_mod'] + $output['race']['dex_mod'] + $output['weapon'][0]['dex_mod'] + $output['armour']['head']['dex_mod'] + $output['armour']['chest']['dex_mod'] + $output['armour']['arms']['dex_mod'] + $output['armour']['legs']['dex_mod'] + $output['armour']['feet']['dex_mod'];
            $output['mod_spd']     = $output['spd'] + $output['class']['spd_mod'] + $output['race']['spd_mod'] + $output['weapon'][0]['spd_mod'] + $output['armour']['head']['spd_mod'] + $output['armour']['chest']['spd_mod'] + $output['armour']['arms']['spd_mod'] + $output['armour']['legs']['spd_mod'] + $output['armour']['feet']['spd_mod'];

            return $this->_output->output(200, $output, false);
        } else {
            return $this->_output->output(404, 'Player not found', false);
        }
	}

    /**
     * User::registerPlayer()
     *
     * POST Request
     *
     * Register's player data into the system, requires authentication
     *
     * @return object JSON Object
     */
    public function registerPlayer()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

		$data = file_get_contents('php://input');

		$output = '';
		$data = json_decode($data, true);

		//check that data exists and is not empty
		if(!isset($data) || empty($data))
		{
			return $this->_output->output(400, "No data POSTed to the API", false);
		} else {
			//lets check to see if the player exists already
			$tmp = $this->_db->getPlayer($data['id'], $data['flag']);

			//check if user exists
			if(is_array($tmp) && $tmp['registered'] == 1)
			{
                $output = "User " . $data['name'] . " already exists and is registered";
                
                return $this->_output->output(409, $output, false);
			} else {
				//bot to handle validation of data before transmission.... Next, lets check the user doesn't exist then submit
				$success = ($this->_db->registerPlayer($data, ($this->_headers['user'] == 'discord_bot') ? 0 : 1) == true) ? true : false;
				$output = "User " . $data['name'] . " has been registered!";
			}
		}

		return $this->_output->output(200, $output, false);
    }

    /**
     * User:getCoins()
     *
     * GET Request
     *
     * Returns the amount of coins for the user
     *
     * @param string|int The user identifier
     * @param int 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     *
     * @return object JSON object with success/failure response 
     */
    public function getCoins()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

		$pouch = $this->_db->getCoins($this->_params[0], $this->_params[1]);

		return $this->_output->output(200, $pouch, true);
    }

    /**
     * User:updateCoins()
     *
     * POST Request
     *
     * Updates the coins for specified user
     *
     * @return object JSON object with success/failure response 
     */
    public function updateCoins()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

		$char = $this->_db->getPlayer($input['id'], $input['flag']);

		if($input['result'] === 'Lose')
		{
			$input['amount'] = ($char['pouch'] < $input['amount']) ? $char['amount'] : $input['amount'];
		}

		$output = $this->_db->updateCoin($char['uid'], $input['amount'], (($input['result'] == "Win") ? true : false));

		return $this->_output->output(200, $output, false);
    }

    /**
     * User:updateXP()
     *
     * POST Request
     *
     * Updates the xp for specified user
     *
     * @return object JSON object with success/failure response 
     */
    public function updateXP()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
		$input = json_decode(file_get_contents('php://input'), true);
		
		$char = $this->_db->getPlayer($input['id'], $input['id_type']);

        if($input['result'] === 'lose')
		{
			$input['xp'] = ($char['xp'] < $input['xp']) ? $char['xp'] : $input['xp'];
		}

		$output = $this->_db->updateXP($char['cid'], $input['xp'], (($input['result'] == "Win") ? true : false));
    }

    /**
     * User:rerollStats()
     *
     * POST Request
     *
     * Updates all stats for specified user
     *
     * @param array the updated player array
     *
     * @return object JSON object with success/failure response 
     */
    public function rerollStats()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

        $success = $this->_db->reroll($input);

        return $this->_output->output(200, ['success' => $success], false);
	}
	
	/**
	 * User::xpNeeded()
	 *
	 * Returns XP Needed for supplied level
	 *
	 * @param int level queried
	 *
	 * @return object JSON object specifying XP needed for level provided
	 */
	public function xpNeeded()
	{
		if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
		if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
		
		$output = $this->_db->xpNeeded($this->_params[0]);

		return $this->_output->output(200, $output, false);
	}

    /**
     * User:levelUp()
     *
     * POST Request
     *
     * Updates the stats for specified user
     *
     * @param array the updated player array
     *
     * @return object JSON object with success/failure response 
     */
    public function levelUp()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $data = json_decode(file_get_contents('php://input'), true);

		$levelUp = false;

		//lets get the user's current level and check against the curve
		$player = $this->_db->getPlayer($data['id'], $data['flag']);

		$rng = rand(1, 4);

		if(((int)$player['xp'] + $data['xp']) >= $this->_db->xpNeeded($player['level'] + 1))
		{
			$levelUp = true;
			$this->_db->level($player['uid'], $rng);
		}


		if($levelUp == true)
		{
			return $this->_output->output(200, ['level up' => true, 'new level' => $player['level'] + 1]);
		} else {
			return $this->_output->output(202, ['level up' => false]);
		}
    }

    /**
     * User::regen()
     *
     * When triggered, all players will end up gaining 1HP and 1AP
     *
     * @return object JSON object with success/failure response 
     */
    public function regen()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->regen();
        
        return $this->_output->output(200, $output, false);
    }

    /**
     * User::toggleStatus()
     * 
     * When triggered, it will update the status flag for the user on gathering/travelling which will lock/unlock features
     * 
     * @return object JSON object with success/failure response
     */
    public function toggleStatus()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->toggleStatus($this->_params[0], $this->_params[1]);
        
        return $this->_output->output(200, $output, false);
    }

    public function updatePlayer()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $data = json_decode(file_get_contents('php://input'), true);

        switch($data['request'])
        {
            case 'avatar':
                break;
            case 'name':
                $output = $this->_db->update_player('username', $data['new'], $data['id'], $data['flag']);

                break;
            case 'race':
                $output = $this->_db->update_player('race', $data['new'], $data['id'], $data['flag']);
                break;
            case 'class':
                $output = $this->_db->update_player('class', $data['new'], $data['id'], $data['flag']);
                break;
            case 'gender':
                $output = $this->_db->update_player('gender', $data['new'], $data['id'], $data['flag']);

                break;
        }

        return $this->_output->output(200, $output, false);
    }

    public function listPlayers()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $result = $this->_db->listAllPlayers();

        return $this->_output->output(200, $result, false);
    }

    public function gather()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $user = $this->_db->getPlayer($this->_params[0], $this->_params[1]);

        $result = $this->_db->toggleStatus($user['uid'], 'gathering');

        return $this->_output->output(200, $result, false);
    }

    public function travel()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $user = $this->_db->getPlayer($this->_params[0], $this->_params[1]);

        $result = $this->_db->toggleStatus($user['uid'], 'travelling');

        return $this->_output->output(200, $result, false);
    }

    /**
     * User::listRaces()
     * 
     * Pulls all races from the database
     * 
     * @return object JSON object with success/failure response
     */
    public function listRaces()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $race = $this->_db->listRaces();

        return $this->_output->output(200, $race, false);
    }

    /**
     * User::getRace()
     * 
     * Pulls information on a specific race
     * 
     * @return object JSON object with success/failure response
     */
    public function getRace()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $race = $this->_db->getRace(urldecode($this->_params[0]), $this->_params[1]);

        return $this->_output->output(200, $race, false);
    }
}
