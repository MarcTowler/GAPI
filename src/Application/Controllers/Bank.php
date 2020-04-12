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

    /**
     * Bank::deposit()
     *
     * POST Request
     *
     * Deposit into a player's bank
     *
     * @return object JSON Object
     */
    public function deposit()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $data = json_decode(file_get_contents('php://input'), true);

        if(!isset($data) || empty($data))
        {
            return $this->_output->output(400, "No data POSTed to the API", false);
        }

        $pouch = $this->_db->getPouch($data['id'], $data['flag'])['pouch'];

        if($pouch < $data['amount'])
        {
            return $this->_output->output(400, "Insufficient balance to deposit", false);
        }
        
        $output = $this->_db->deposit($data['id'], $data['type'], $data['amount']);

        return $this->_output->output(200, $output, false);
    }

    /**
     * Bank::withdraw()
     *
     * POST Request
     *
     * Withdraw from a player's bank
     *
     * @return object JSON Object
     */
    public function withdraw()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        if(!isset($data) || empty($data))
        {
            return $this->_output->output(400, "No data POSTed to the API", false);
        }

        //lets get the balance and check we can do this
        $bal = $this->_db->checkBalance($data['id'], $data['flag']);

        if($bal < $data['amount'])
        {
            return $this->_output->output(400, "Insufficient balance to withdraw", false);
        }

        $output = $this->_db->withdraw($data['id'], $data['flag'], $data['amount']);

        return $this->_output->output(200, $output, false);
    }

    /**
     * Bank::balance()
     *
     * GET Request
     *
     * Check the balance of a player
     *
     * @return object JSON Object
     */
    public function balance()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->checkBalance($this->_params[0], $this->_params[1]);

        return (isset($output['success'])) ? $this->_output->output(404, $output, false) : $this->_output->output(200, $output, false);
    }

    /**
     * Bank::open()
     *
     * POST Request
     *
     * Open an account for a user, will fail on duplicate
     *
     * @return object JSON Object
     */
    public function open()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        if(!isset($data) || empty($data))
        {
            return $this->_output->output(400, "No data POSTed to the API", false);
        }

        $output = $this->_db->openAccount($data['id'], $data['flag']);

        return $this->_output->output(200, $output, false);
    }
}