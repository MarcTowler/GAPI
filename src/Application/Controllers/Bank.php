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

    public function deposit()
    {
        if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
        {
            return $this->_output->output(401, "Authorization Failed", false);
        }

        return $this->_output->output(200, json_encode($this->_db->deposit($this->_params[0], $this->_params[1])), true);
    }

    public function withdraw()
    {
        if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
        {
            return $this->_output->output(401, "Authorization Failed", false);
        }

        return $this->_output->output(200, json_encode($this->_db->withdraw($this->_params[0], $this->_params[1])), true);
    }

    public function balance()
    {
        return $this->_output->output(200, json_encode($this->_db->check_balance($this->_params[0])), true);
    }

    public function open()
    {
        if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
        {
            return $this->_output->output(401, "Authorization Failed", false);
        }

        return $this->_output->output(200, json_encode($this->_db->open_account($this->_params[0])), true);
    }
}