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

    /**
     * Item::listItems()
     *
     * GET Request
     *
     * Pulls full list of items for admin page
     *
     * @return object JSON Object with success or fail message
     */
    public function listItems()
    {
        //if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->listItems();

        return $this->_output->output(200, $output, false);
    }

    /**
     * Item::addItem()
     *
     * POST Request
     *
     * Adds new item to database
     *
     * @return object JSON Object with success or fail message
     */
    public function addItem()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input  = json_decode(file_get_contents('php://input'), true);
        $output = $this->_db->addItem($input);

        return ($output) ? $this->_output->output(200, "Item Added", false) : $this->_output->output(409, "Item already exists", false);
    }

    /**
     * Item::editItem()
     *
     * POST Request
     *
     * @todo work out and implement the database side
     *
     * Updates existing item
     *
     * @return object JSON Object with success or fail message
     */
    public function editItem()
    {
        return $this->_output->output(501, "Function not implemented", false);

        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input  = json_decode(file_get_contents('php://input'), true);
        $output = $this->_db->editItem($input);

        return ($output) ? $this->_output->output(200, "Item Edited", false) : $this->_output->output(400, "Unable to edit Item", false); 
    }

    /**
     * Item::deleteItem()
     *
     * POST Request
     *
     * Removes existing item
     *
     * @return object JSON Object with success or fail message
     */
    public function deleteItem()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input  = json_decode(file_get_contents('php://input'), true);
        $output = $this->_db->removeItem($input);

        return ($output) ? $this->_output->output(200, "Item Deleted", false) : $this->_output->output(409, "Unable to delete item", false);
    }

    /**
     * Item::toggleActive()
     *
     * GET Request
     *
     * Updates item's availability to the users
     *
     * @return object JSON Object with success or fail message
     */
    public function toggleActive()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->toggleItem($this->_params[0]);

        return $this->_output->output(200, "Item availability updated", false);
    }

    /**
     * Item::useItem()
     *
     * GET Request
     *
     * Simulates taking a health item to increase player health
     *
     * @param int User identifier (uid) - note this still needs changing but item_owned needs updating first
     * @param int Item identifier
     *
     * @return object JSON Object with success or fail message
     */
    public function useItem()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
        if(count($this->_params) < 2) { return $this->_output->output(400, "Not enough Arguments provided", false); }

        $output = $this->_db->useItem($this->_params[0], $this->_params[1]);

        switch($output)
        {
            case 0:
                return $this->_output->output(304, "HP already full", false);
            case 1:
                return $this->_output->output(200, "Item successfully used", false);
            case 2:
                return $this->_output->output(404, "Item not in Inventory", false);
            case 3:
                return $this->_output->output(500, "Something went wrong with using the Item", false);
            default:
                return $this->_output->output(500, "Something went wrong", false);
        }
    }

    /**
     * Item::equipItem()
     *
     * GET Request
     *
     * Equips item into specific slot
     *
     * @param int User identifier (uid) - note this still needs changing but item_owned needs updating first
     * @param int Item identifier
     *
     * @return object JSON Object with success or fail message
     */
    public function equipItem()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
        $user = $this->_params[0];
		$iid  = $this->_params[1];

		$output = $this->_db->equipItem($user, $iid, true);

		return $this->_output->output(200, $output, false);
    }

    /**
     * Item::unequipItem()
     *
     * GET Request
     *
     * Un-equips item from specific slot
     *
     * @param int User identifier (uid) - note this still needs changing but item_owned needs updating first
     * @param int Item identifier
     *
     * @return object JSON Object with success or fail message
     */
    public function unequipItem()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $user = $this->_params[0];
		$iid  = $this->_params[1];

		$output = $this->_db->equipItem($user, $iid, false);

		return $this->_output->output(200, $output, false);
    }

    public function getItem()
    {
        //if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $output = $this->_db->getItem($this->_params[0]);

        return $this->_output->output(200, $output, false);
    }
}
