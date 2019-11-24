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
									"as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id LEFT JOIN npc " . 
									"as n ON n.nid = s.nid WHERE s.sid = :id AND si.ranged = 1 AND i.type != 'Weapon' AND i.type !='Armour'");
		$stmt->execute([':id' => $id]);

		$this->_output['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$stmt2 = $this->_db->prepare("SELECT i.id, i.name, i.price, i.description, i.level_req, si.qty, w.str_mod, w.def_mod, w.dex_mod, w.spd_mod " . 
									 "FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id LEFT JOIN npc as n ON " . 
									 "n.nid = s.nid LEFT JOIN weapons as w on w.iid = i.id WHERE s.sid = :id AND si.ranged = 1 AND i.type = 'Weapon'");
		$stmt2->execute([':id' => $id]);

		$this->_output['weapons'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

		$stmt3 = $this->_db->prepare("SELECT i.id, i.name, i.price, i.description, i.level_req, si.qty, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod " . 
									 "FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id LEFT JOIN npc as n ON " . 
									 "n.nid = s.nid LEFT JOIN armour as a on a.iid = i.id WHERE s.sid = :id AND si.ranged = 1 AND i.type = 'Armour'");
		$stmt3->execute([':id' => $id]);

		$this->_output['armour'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function getShopInfo($id)
	{
		$stmt = $this->_db->prepare("SELECT s.sid, s.min_level, n.name as shopkeep, s.name, s.balance, n.lore, n.image FROM shop as s LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :id");
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function buyItem($sid, $user, $item)
	{
		//need to make sure that the shop has that item and has stock of it, then update the item by removing 1 and adding to the shopkeep's balance
		$stmt = $this->_db->prepare("SELECT si.qty, i.price, s.balance FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id " . 
									"LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :shop AND i.id = :id");
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
			$user = $this->_db->prepare("SELECT pouch FROM `character` WHERE uid = :id");
			$user->execute([':id' => $user]);
			$tmp = $user->fetch(\PDO::FETCH_ASSOC);

			if($shop['price'] > $tmp['pouch'])
			{
				return false;
			}
			
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

	public function sellItem($sid, $user, $item)
	{
		//need to make sure that the shop has that item and has stock of it, then update the item by removing 1 and adding to the shopkeep's balance
		$stmt = $this->_db->prepare("SELECT i.price, s.balance FROM shop as s LEFT JOIN shop_items as si ON s.sid = si.sid LEFT JOIN items as i on si.iid = i.id " . 
									"LEFT JOIN npc as n ON n.nid = s.nid WHERE s.sid = :shop and i.id = :id ");
		$stmt->execute(
			[
				':shop' => $sid,
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
					':shop'  => $sid,
					':id'    => $item,
					':price' => (int)floor($shop['price'] * 0.66)
				]
			);


			$stmt2 = $this->_db->prepare("DELETE FROM item_owned WHERE iid = :id AND oid = :user AND equipped = 0 LIMIT 1");
			$stmt2->execute(
				[
					':id'   => $item,
					':user' => $user
				]
			);

			$stmt3 = $this->_db->prepare("UPDATE `character` SET pouch = pouch + :amount WHERE uid = :user");
			$stmt3->execute(
				[
					':user'   => $user,
					':amount' => (int)floor($shop['price'] * 0.66)
				]
			);

			return true;
		}
	}

	public function getOpenShops()
	{
		$stmt = $this->_db->prepare("SELECT s.sid, n.name as npc_name, s.name as shop_name, s.min_level, s.balance FROM shop s INNER JOIN npc n ON s.nid = n.nid WHERE s.open = 1 ORDER BY s.sid ASC");
		$stmt->execute();

		$output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		return $output;
	}
}