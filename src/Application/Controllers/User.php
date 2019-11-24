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
     * User::getPlayer()
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


        $output                = [];
        $tmp                   = [];
        $output                = $this->_db->getPlayer($this->_params[0], $this->_params[1]);

        if($output)
        {
            $output['attack_msg']  = isset($output['weapon'][0]['attack_msg']) ? $output['weapon'][0]['attack_msg'] : 'used their fists to hit';
            $output['defense_msg'] = isset($output['armour']['chest']['defense_msg']) ? $output['armour']['chest']['defense_msg'] : 'taking a step back';
            $output['mod_hp']      = $output['max_hp'] + $output['weapon'][0]['hp_mod'] + $output['armour']['head']['hp_mod'] + $output['armour']['chest']['hp_mod'] + $output['armour']['arms']['hp_mod'] + $output['armour']['legs']['hp_mod'] + $output['armour']['feet']['hp_mod'];
            $output['mod_ap']      = $output['max_ap'] + $output['weapon'][0]['ap_mod'] + $output['armour']['head']['ap_mod'] + $output['armour']['chest']['ap_mod'] + $output['armour']['arms']['ap_mod'] + $output['armour']['legs']['ap_mod'] + $output['armour']['feet']['ap_mod'];
            $output['mod_str']     = $output['str'] + $output['weapon'][0]['str_mod'] + $output['armour']['head']['str_mod'] + $output['armour']['chest']['str_mod'] + $output['armour']['arms']['str_mod'] + $output['armour']['legs']['str_mod'] + $output['armour']['feet']['str_mod'];
            $output['mod_def']     = $output['def'] + $output['weapon'][0]['def_mod'] + $output['armour']['head']['def_mod'] + $output['armour']['chest']['def_mod'] + $output['armour']['arms']['def_mod'] + $output['armour']['legs']['def_mod'] + $output['armour']['feet']['def_mod'];
            $output['mod_dex']     = $output['dex'] + $output['weapon'][0]['dex_mod'] + $output['armour']['head']['dex_mod'] + $output['armour']['chest']['dex_mod'] + $output['armour']['arms']['dex_mod'] + $output['armour']['legs']['dex_mod'] + $output['armour']['feet']['dex_mod'];
            $output['mod_spd']     = $output['spd'] + $output['weapon'][0]['spd_mod'] + $output['armour']['head']['spd_mod'] + $output['armour']['chest']['spd_mod'] + $output['armour']['arms']['spd_mod'] + $output['armour']['legs']['spd_mod'] + $output['armour']['feet']['spd_mod'];

            return $this->_output->output(200, $output, false);
        } else {
            return $this->_output->output(404, 'Player not found', false);
        }
    }

    public function registerPlayer()
    {

    }

    public function updateCoins()
    {

    }

    public function updateXP()
    {

    }

    public function updateStats()
    {

    }
}
