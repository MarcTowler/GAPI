<?php
/**
 * Bank Endpoint
 *
 * All Bank related functions will be handled in here
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

}