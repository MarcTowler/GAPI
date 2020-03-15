<?php
/**
 * Auction Model
 *
 * All Auction related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Model;

use API\Library;

class AuctionModel extends Library\BaseModel
{
    public function __construct()
    {
		    parent::__construct();
    }
    
    public function create(array $input)
    {
        try {
            //Try to delete the item first, if it fails then they didn't have the item
            $del = $this->_db->prepare("DELETE FROM item_owned WHERE iid = :item AND oid = :user LIMIT 1");
            $del->execute(
                [
                    ':item' => $input['iid'],
                    ':user' => $input['uid']
                ]
            );

            $auc = $this->_db->prepare("INSERT INTO auction (iid, uid, end_time) VALUES (:item, :user, :end)");
            $auc->execute(
                [
                    ':item' => $input['iid'],
                    ':user' => $input['uid'],
                    ':end'  => $input['end_time']
                ]
            );

            $result = $auc->lastInsertId();

            $bid = $this->_db->prepare("INSERT INTO auction_bids (aid, uid, amount) VALUES (:id, 0, 0)");
            $bid->execute([':id' => $result]);

            return $result;
        } catch(\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function edit()
    {

    }

    public function delete($id)
    {
        try {
            $stmt = $this->_db->prepare("DELETE FROM auction WHERE aid :id");
            $stmt->execute([':id' => $id]);
        } catch(\PDOException $e) {
            return $e->getMessage();
        }

        return true;
    }

    public function toggleStatus($id)
    {
        try {
            $stmt = $this->_db->prepare("UPDATE auction SET active = !active WHERE aid = :id");
            $stmt->execute([':id' => $id]);
        } catch(\PDOException $e) {
            return $e->getMessage();
        }

        return true;
    }

    public function list()
    {
        $stmt = $this->_db->prepare("SELECT a.aid, a.start_time, a.end_time, i.name, u.username, a.min_bid, b.amount As top_bid, a.active FROM auction a
                                     LEFT JOIN auction_bids b ON a.aid = b.aid 
                                     LEFT JOIN items i ON a.iid = i.id 
                                     LEFT JOIN users u ON u.uid = b.uid 
                                     WHERE a.active = 1 ORDER BY a.end_time DESC");
        $stmt->execute();
        $output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $output;
    }

    public function getSingle($id)
    {
        $stmt = $this->_db->prepare("SELECT a.aid, a.start_time, a.end_time, i.name, u.username, b.amount As top_bid FROM auction a LEFT JOIN auction_bids b ON a.aid = b.aid
                                     LEFT JOIN items i ON a.iid = i.id LEFT JOIN users u ON u.uid = b.uid WHERE a.aid = :id");
        $stmt->execute([':id' => $id]);

        $output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $output;
    }

    public function place_bid($id, $amount, $uid, $user_flag)
    {
        $stmt = $this->_db->prepare("INSERT INTO auction_bids (aid, uid, amount) VALUES (:id, :user, :amount)");
        $stmt->execute(
            [
                ':id'     => $id,
                ':user'   => $uid,
                ':amount' => $amount
            ]
        );

        return $stmt->lastInsterId();
    }

    public function topBidder($aid)
    {
        $stmt = $this->_db->prepare("SELECT u.username, i.name, b.amount FROM auction_bids b
                                     INNER JOIN auction a ON a.aid = b.aid
                                     INNER JOIN items i ON i.id = a.iid
                                     INNER JOIN users u ON u.uid = a.uid WHERE b.aid = :id ORDER BY b.amount DESC LIMIT 1");
        $stmt->execute([':id' => $aid]);

        $output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $output;
    }

    public function closeOut($aid)
    {

    }
}