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

        $this->_db   = new Model\FightModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }


}