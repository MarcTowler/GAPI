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

    public function listShops()
    {
        //list all shops that are "open"
        $this->_log->set_message("Listing all open shops", "INFO");

        $output = $this->_db->getOpenShops();

        return $this->_output->output(200, $output, false);
    }

    public function getShop()
    {
        $this->_log->set_message("Getting shop info of shop id " . $this->_params[0], "INFO");
		
		//displays stored info on the shop, lore, level restrictions, specialty etc
		$shop_id = $this->_params[0];

		$shop          = $this->_db->getShopInfo($shop_id);
		$shop['stock'] = $this->_db->getStock($shop_id);

		return $this->_output->output(200, $shop, false);
    }

    public function buy()
    {
        $this->_log->set_message("Item ID " . $this->_params[2] . " bought by user id " . $this->_params[0] . " from shop " . $this->_params[1], "INFO");
		
		$user = $this->_params[0];
		$shop = $this->_params[1];
		$id   = $this->_params[2];

		$output = $this->_db->buyItem($shop, $user, $id);
		
		return $this->_output->output(200, $output, false);
    }

    public function sell()
    {
        $this->_log->set_message("Item ID " . $this->_params[2] . " sold by user id " . $this->_params[0] . " to shop " . $this->_params[1], "INFO");
		//same as above, also needs to check if the item is equipped
		//if equipped, provide feedback in error message
		//floor(value of item * 0.66) << the sell to the shop value
		$user = $this->_params[0];
		$shop = $this->_params[1];
		$id   = $this->_params[2];
		
		$output = $this->_db->sellItem($shop, $user, $id);

		return $this->_output->output(200, ["Item sold"], false);
    }

    public function addShop()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function editShop()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function deleteShop()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function addStock()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function removeStock()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function toggleActive()
    {
        return $this->_output->output(501, "Function not implemented", false);
    }
}
