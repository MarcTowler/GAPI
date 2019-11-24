<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Admin extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\AdminModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function listUsers()
    {

    }

    public function listItems()
    {
        $output = $this->_db->listItems();

        return $this->_output->output(200, $output, false);
    }

    public function listNpcs()
    {
        $output = $this->_db->listNpcs();

        return $this->_output->output(200, $output, false);
    }

    public function listShops()
    {
        
    }
}