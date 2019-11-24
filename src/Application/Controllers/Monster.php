<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Monster extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\MonsterModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function getMonster()
    {

    }

    public function getRandomMonster()
    {

    }

    public function listMonster()
    {
        
    }
}
