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
        //if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->getEventList();

        return $this->_output->output(200, $output, false);
    }

    public function AddEvent()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

        if(sizeof($input < 8))
        {
            return $this->_output->output(400, 'Not Enough Information Provided', false);
        }

        $output = $this->_db->addEvent($input);

        return $this->_output->output(200, $output, false);
    }

    public function EditEvent()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('PUT')) { return $this->_output->output(405, "Method Not Allowed", false); }
    }

    public function DeleteEvent()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
    }

    public function GetEvent()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
    }

    public function toggleLive()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
    }
}