<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Bank extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\BankModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function openAccount()
    {

    }

    public function deposit()
    {

    }

    public function withdraw()
    {

    }

    public function getBalance()
    {

    }

    public function heist()
    {
        
    }
}
