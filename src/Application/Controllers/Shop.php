<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Shop extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\ShopModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }


}
