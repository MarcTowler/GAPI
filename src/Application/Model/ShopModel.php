<?php
namespace API\Model;

use API\Library;

class ShopModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function getStock($id)
	{
		$stmt = $this->_db->prepare("SELECT i.id, i.name, i.type, i.description, i.level_req, i.modifier, si.qty, i.price FROM shop " . 
									"as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :id");
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function getShopInfo($id)
	{
		$stmt = $this->_db->prepare("SELECT n.name as shopkeep, s.name, s.balance, n.lore, n.image FROM shop as s LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :id");
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function buyItem($shop, $user, $item)
	{
		//need to make sure that the shop has that item and has stock of it, then update the item by removing 1 and adding to the shopkeep's balance
		$stmt = $this->_db->prepare("SELECT si.qty, i.price, s.balance FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id " . 
									"LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :shop and i.id = :id ");
		$stmt->execute(
			[
				':shop' => $shop,
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
					':shop'  => $shop,
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

			$stmt3 = $this->_db->prepare("UPDATE `character` SET pouch = pouch - :amount WHERE cid = :user");
			$stmt3->execute(
				[
					':user'   => $user,
					':amount' => $shop['price']
				]
			)

			return true;
		}
	}

	public function sellItem($shop, $user, $item)
	{
		//need to make sure that the shop has that item and has stock of it, then update the item by removing 1 and adding to the shopkeep's balance
		$stmt = $this->_db->prepare("SELECT i.price, s.balance FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id " . 
									"LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :shop and i.id = :id ");
		$stmt->execute(
			[
				':shop' => $shop,
				':id'   => $item
			]
		);

		$shop = $stmt->fetch(\PDO::FETCH_ASSOC);

		if($shop['balance'] < $shop['price'])
		{
			return false;
		} else {
			$stmt = $this->_db->prepare("UPDATE shop_items si INNER JOIN shop s ON (s.sid = si.sid) SET si.qty = si.qty + 1, s.balance = s.balance - :price WHERE s.sid = :shop AND si.iid = :id");
			$stmt->execute(
				[
					':shop'  => $shop,
					':id'    => $item,
					':price' => floor($shop['price'] * 0.66)
				]
			);

			$stmt2 = $this->_db->prepare("DELETE FROM item_owned WHERE iid = :id AND oid = :user AND equipped = 0 LIMIT 1");
			$stmt2->execute(
				[
					':id'   => $item,
					':user' => $user
				]
			);

			$stmt3 = $this->_db->prepare("UPDATE `character` SET pouch = pouch + :amount WHERE cid = :user");
			$stmt3->execute(
				[
					':user'   => $user,
					':amount' => $shop['price']
				]
			)

			return true;
		}
	}
}