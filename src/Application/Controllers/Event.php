<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Event extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\EventModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function ListEvents()
    {
        $output = $this->_db->getEventList();

        return $this->_output->output(200, $output, false);
    }
}