<?php
/**
 * Bank Model
 *
 * All Bank related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Model;

use API\Library;

class BankModel extends Library\BaseModel
{
    public function __construct()
    {
		    parent::__construct();
    }

    /**
     * Bank::deposit()
     *
     * Deposits gold to the specified user's account
     *
     * @param int User ID
     * @param int Flag 0 = username, 1 = discord id, 2 = twitch id, 3 = uid
     * @param int Amount to deposit
     *
     * @return bool success or failure
     */
    public function deposit($id, $flag, $amount)
    {
        //first off we need to check what coins the player has on them
        $stmt = $this->_db->prepare("UPDATE bank b INNER JOIN `users` u ON b.uid = u.uid SET b.balance = b.balance + :amt, u.pouch = u.pouch - :amt WHERE " .
            (($flag == 0) ? 'u.username = :id' : 
            (($flag == 1) ? 'u.discord_id = :id' : 
            (($flag == 2) ? 'u.twitch_id = :id' : 'u.uid = :id'))));
        $stmt->execute(
            [
                ':id'  => $id,
                ':amt' => $amount
            ]
        );
        
        $success = ($stmt->rowCount() > 0) ? true : false;

        
    }

    /**
     * Bank::withdraw()
     *
     * Withdraws gold from the specified user's account
     *
     * @param int User ID
     * @param int Flag 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     * @param int Amount to withdraw
     *
     * @return bool success or failure
     */
    public function withdraw($id, $type, $amount)
    {
        $upd = $this->_db->prepare("UPDATE bank b INNER JOIN `users` u ON b.uid = u.uid SET b.balance = b.balance - :amt, u.pouch = u.pouch + :amt WHERE " .
            (($flag == 0) ? 'u.username = :id' : 
            (($flag == 1) ? 'u.discord_id = :id' : 
            (($flag == 2) ? 'u.twitch_id = :id' : 'u.uid = :id'))));
        $upd->execute(
            [
                ':id'  => $id,
                ':amt' => $amount
            ]
        );

        $success = ($upd->rowCount() > 0) ? true : false;
      }

    /**
     * Bank::checkBalance()
     *
     * Returns user's bank balance
     *
     * @param int User ID
     * @param int Flag 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     *
     * @return int Amount of gold or 0
     */
    public function checkBalance($id, $flag)
    {
        $stmt = $this->_db->prepare("SELECT b.balance FROM bank b INNER JOIN `users` u ON b.uid = u.uid WHERE " . 
            (($flag == 0) ? 'u.username = :id' : 
            (($flag == 1) ? 'u.discord_id = :id' : 
            (($flag == 2) ? 'u.twitch_id = :id' : 'u.uid = :id'))));
        $stmt->execute([':id' => $id]);

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return ($this->_output) ? $this->_output : ['success' => false, 'reason' => "no account"];
    }

    /**
     * Bank::openAccount()
     *
     * Creates a bank account for a user
     *
     * @param int User ID
     * @param int Flag 0 = username, 1 = discord id, 2 = twitch id, 3 = uid
     *
     * @return array success or failure error
     */
    public function openAccount($id, $flag)
    {
        try {
            $ins = $this->_db->prepare("INSERT INTO bank (uid, balance, protected) VALUES (
                (SELECT u.uid FROM `users` u WHERE " . 
                (($flag == 0) ? "u.username = :id" : 
                (($flag == 1) ? "u.discord_id = :id" : 
                (($flag == 2) ? "u.twitch_id = :id" : "u.uid = :id"))) . "), 0, 0)");
            $ins->execute([':id' => $id]);

            return ['success' => true];
        } catch(\PDOEXCEPTION $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Bank::getPouch()
     *
     * Not for a bank but allows to easily pull pouch for comparison
     *
     * @param int User ID
     * @param int Flag 0 = username, 1 = discord id, 2 = twitch id, 3 = uid
     *
     * @return array success or failure error
     */
    public function getPouch($id, $flag)
    {
        $stmt = $this->_db->prepare("SELECT u.pouch FROM `users` u WHERE " . 
            (($flag == 0) ? "u.username = :id" : 
            (($flag == 1) ? "u.discord_id = :id" : 
    (($flag == 2) ? "u.twitch_id = :id" : "u.uid = :id")))/* . ", 0, 0)"*/);
        $stmt->execute([':id' => $id]);

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->_output;
    }
}