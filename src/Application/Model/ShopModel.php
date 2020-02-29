<?php
/**
 * Shop Model
 *
 * All Shop related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Model;

use API\Library;

class ShopModel extends Library\BaseModel
{
    public function __construct()
    {
		    parent::__construct();
    }

    public function getOpenShops()
    {
        $stmt = $this->_db->prepare("SELECT s.sid, s.name AS shop_name, n.name AS shopkeep, s.min_level, s.balance, s.open FROM `shop` s INNER JOIN npc n ON s.nid = n.nid ");
        $stmt->execute();

        $output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $output;
    }

    public function buyItem($sid, $user, $item)
	{
		//need to make sure that the shop has that item and has stock of it, then update the item by removing 1 and adding to the shopkeep's balance
		$stmt = $this->_db->prepare("SELECT si.qty, i.price, s.balance FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id " . 
									"LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :shop and i.id = :id ");
		$stmt->execute(
			[
				':shop' => $sid,
				':id'   => $item
			]
		);
		$shop = $stmt->fetch(\PDO::FETCH_ASSOC);
		if($shop['qty'] <= 0)
		{
			return false;
		} else {
			$stmt = $this->_db->prepare("UPDATE shop_items si INNER JOIN shop s ON (s.sid = si.sid) SET si.qty = si.qty - 1, s.balance = s.balance + :price WHERE s.sid = :shop AND si.iid = :id");
			$stmt->execute(
				[
					':shop'  => $sid,
					':id'    => $item,
					':price' => $shop['price']
				]
			);
			$stmt2 = $this->_db->prepare("INSERT INTO item_owned (iid, oid, equipped) VALUES (:id, :user, 0)");
			$stmt2->execute(
				[
					':id'   => $item,
					':user' => $user
				]
			);
			$stmt3 = $this->_db->prepare("UPDATE `character` SET pouch = pouch - :amount WHERE uid = :user");
			$stmt3->execute(
				[
					':user'   => $user,
					':amount' => $shop['price']
				]
			);
			return $shop['price'];
		}
	}
}