<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Extension extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\ExtensionModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function test()
    {
        return $this->_output->output(200, ["hey"], false);
    }

    public function getUserDetails()
    {

    }
}