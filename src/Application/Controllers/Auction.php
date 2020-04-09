<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Auction extends Library\BaseController
{
    private $_db;
    private $_user;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db   = new Model\AuctionModel();
        $this->_user = new Model\UserModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function AddAuction()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

        var_dump($input);
    }

    public function EditAuction()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);
    }

    public function DeleteAuction()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $id = $this->_params[0];

        $result = $this->_db->delete($id);

        return $this->_output->output(200, $result, false);
    }

    public function ListAuctions()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $result = $this->_db->list();

        return $this->_output->output(200, $result, false);
    }

    public function GetAuction()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        if(!isset($this->_params[0]))
        {
            return $this->_output->output(400, ['message' => 'No Auction ID set'], false);
        }

        $result = $this->_db->getSingle($this->_params[0]);

        return ($result == false) ? $this->_output->output(404, "Auction ID not valid", false) : $this->_output->output(200, $result, false);
    }

    public function Bid()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('POST')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $input = json_decode(file_get_contents('php://input'), true);

        if(!isset($input['uid']))
        {
            return $this->_output->output(400, ['message' => 'No User ID set'], false);
        } else if(!isset($input['aid'])) {
            return $this->_output->output(400, ['message' => 'No Auction ID set'], false);
        } else if(!isset($input['bid_amount'])) {
            return $this->_output->output(400, ['message' => 'No bid amount set'], false);
        }

        //get the user's on hand cash && current top bid
        $coins = $this->_user->getCoins($input['uid'], $input['flag']);

        if($coins < $input['bid_amount'])
        {
            return $this->_output->output(400, ['message' => "You don't have enough LitCoins in your pouch!"]);
        }

        $top = $this->_db->getSingle($input['aid']);

        if($input['bid_amount'] <= (int)$top['top_bid'])
        {
            return $this->_output->output(406, ['message' => "Your bid must be higher then the current bid!"]);
        } else if(!$top['active']) {
            return $this->_output->output(410, ['message' => "The auction is not active!"]);
        }

        //All checks have passed lets please the bid!
        $result = $this->_db->place_bid($input['aid'], $input['bid_amount'], $input['uid'], $input['flag']);

        return $this->_output->output(200, $result, false);
    }

    public function CheckStatus()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
        $result = $this->_db->getSingle($this->_params[0]);

        return $this->_output->output(200, $result, false);
    }

    public function CronCheck()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $result = $this->_db->list();

        if($result == -1)
        {
            return $this->_output->output(500, "Database Error", false);
        } 
        
        for($i = 0; $i < sizeof($result); $i++)
        {
            if(($result[$i]['end_time'] <= date("Y-m-d H:i:s")) && $result[$i]['active'] == true)
            {
                $top = $this->_EndAuction($result[$i]['aid']);
            } else if($result[$i]['active'] == false) {
                //return $this->_output->output(410, "Auction Ended", false);
            }
        }

        return $this->_output->output(200, $result, false);
    }

    private function _EndAuction($id)
    {
        //Toggle
        $this->_db->toggleStatus($id);

        //Top bidder
        $result = $this->_db->topBidder($id);

        //Send to the bot
        $response = $this->_guzzle->request(
            'POST',
            'https://marctowler-discord-rpg-bot.glitch.me/auction', 
            [
                'headers' => [
                    'content-type' => "application/json; charset=utf-8"
                ],
                'body' => json_encode($result)
            ]
        );

        //Now to release the item to the winner and the coins (minus a fee) to the seller
    }
}