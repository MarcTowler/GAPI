<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Item extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\ItemModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function listItems()
    {

    }

    public function addItem()
    {

    }

    public function editItem()
    {

    }

    public function deleteItem()
    {

    }

    public function toggleActive()
    {

    }
}
