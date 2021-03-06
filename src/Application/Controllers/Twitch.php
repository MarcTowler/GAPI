<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Twitch extends Library\BaseController
{
    private $_db;
    private $_twitch;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\TwitchModel();
        $this->_twitch = new Library\Twitch();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

}
