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

    public function register()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, "Authentication failed", false); }
        if(!$this->validRequest("POST")) { return $this->_output->output(405, "Method not allowed"); }

        $success = false;
        $output = '';

        $data = json_decode(file_get_contents('php://input'), true);

        if(empty($data))
        {
            return $this->_output->output(400, "No data was POSTed to GAPI", false);
        }

        //Lets query to see if the user already is present?
        $query = $this->_db->getUser($data['id'], $data['flag']);

        //Did the query pull back anything?
        if(is_array($query))
        {
            //Is the user already registered?
            if($query['registered'] == 1)
            {
                return $this->_output->output(409, "User is already registered for the service", false);
            }

            $success = ($this->_db->registerUser($query)) ? true : false;

            $output = ($success) ? "User has been registered" : "There was an error registering the User, admins have been notified";
        }

        return $this->_output->output(200, $output, false);
    }

    public function getProfile()
    {

    }

    public function getCoins()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, "Authentication failed", false); }
        if(!$this->validRequest("GET")) { return $this->_output->output(405, "Method not allowed"); }

        $pouch = $this->_db->getCoins($this->_params[0], $this->_params[1]);

        return $this->_output->output(200, $pouch, true);
    }

    public function updateCoins()
    {

    }

    public function updateXp()
    {

    }

    public function rerollStats()
    {

    }

    public function levelUp()
    {

    }

    public function regenPoint()
    {

    }

    public function toggleStatus()
    {

    }

    public function listPlayers()
    {

    }

    public function gather()
    {

    }

    public function travel()
    {

    }

    public function listRaces()
    {

    }

    public function getRace()
    {

    }

    public function listClasses()
    {

    }

    public function getClass()
    {

    }

    public function getInventory()
    {

    }
}
