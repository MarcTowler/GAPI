<?php
/**
 * Item Model
 *
 * All item related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Model;

use API\Library;

class ItemModel extends Library\BaseModel
{
	public function __construct()
	{
		parent::__construct();
    }

	/**
     * ItemModel::UseItem()
     *
     * "consumes" a healing item
     *
     * @param int User ID
     * @param int Item ID
     *
     * @return string 0 = full hp, 1 = success, 2 = item not owned, 3 = generic failure
     */
    public function useItem($uid, $iid)
	{
		//does it exist
		$stmt = $this->_db->prepare("SELECT count(*) AS num FROM item_owned WHERE iid = :iid AND oid = :uid");
		$stmt->execute(
			[
				':iid' => $iid,
				':uid' => $uid
			]
        );
        
		$count = $stmt->fetch(\PDO::FETCH_ASSOC);

		if($count['num'] !== 0)
		{
            //lets get current and max health
            $health = $this->_db->prepare("SELECT cur_hp, max_hp FROM users WHERE uid = :uid");
            $health->execute([':uid' => $uid]);
            $hp = $health->fetch(\PDO::FETCH_ASSOC);

            if($hp['cur_hp'] === $hp['max_hp'])
            {
                return "0";
            }

			//lookup item's modifer, make the change then remove the item from item_owned
			$look = $this->_db->prepare("SELECT modifier FROM items WHERE id = :iid");
			$look->execute([':iid' => $iid]);

			$tmp = json_decode($look->fetch(\PDO::FETCH_ASSOC)['modifier'], true);
            $mod = (($tmp['cur_hp'] + $hp['cur_hp']) > $hp['max_hp']) ? $hp['max_hp'] : ($tmp['cur_hp'] + $hp['cur_hp']);

			//Need to add a way to stop this from going over max_hp
			$upd = $this->_db->prepare("UPDATE users SET cur_hp = :mod WHERE uid = :uid");
			$upd->execute(
				[
					':mod' => $mod,
					':uid' => $uid
				]
			);

			$del = $this->_db->prepare("DELETE FROM item_owned WHERE iid = :iid AND oid = :uid LIMIT 1");
			$del->execute(
				[
					':iid' => $iid,
					':uid' => $uid
				]
			);

			return "1";
		} else {
            return "2";
        }

		return "3";
	}
	
	/**
     * ItemModel::listItems()
     *
     * Generates an array of all items
     *
     * @return array list of all items in DB
     */
	public function listItems()
    {
        $stmt = $this->_db->prepare("SELECT i.id, i.name, i.type, i.material, i.description, i.level_req, i.modifier, i.price, i.qty, i.available FROM items as i WHERE i.type != 'Weapon' AND i.type !='Armour'");
		$stmt->execute();

		$this->_output['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$stmt2 = $this->_db->prepare("SELECT i.id, i.name, i.price, i.material, i.description, i.level_req, i.qty, i.available, w.str_mod, w.def_mod, w.dex_mod, w.spd_mod " . 
									 "FROM items as i INNER JOIN weapons w ON w.wid = i.id WHERE i.type = 'Weapon'");
		$stmt2->execute();

		$this->_output['weapons'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

		$stmt3 = $this->_db->prepare("SELECT i.id, i.name, i.price, i.material, i.description, i.level_req, i.qty, i.available, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod FROM items as i INNER JOIN armour a ON a.aid = i.id WHERE i.type = 'Armour'");
		$stmt3->execute();

		$this->_output['armour'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	/**
     * ItemModel::addItem()
     *
     * Adds item to DB
     *
     * @param array item details
     *
     * @return bool successful or not
     */
	public function addItem(array $input)
	{
		try {
			$stmt = $this->_db->prepare("INSERT INTO items (name, type, material, description, lore, level_req, modifier, price, qty, available) VALUES (:name, :type, :mat, :desc, :lore, :level, :modifier, :price, :qty, :avail)");
			$success = $stmt->execute(
				[
					':name'     => $input['name'],
					':type'     => $input['type'],
					':mat'      => $input['material'],
					':desc'     => $input['description'],
					':lore'     => $input['lore'],
					':level'    => $input['min_level'],
					':modifier' => $input['modifier'],
					':price'    => $input['cost'],
					':qty'      => $input['qty'],
					':avail'    => $input['active']
				]
			);

			if($success)
			{
				//check if it is a weapon or armour, else exit
				if($input['type'] == 'Weapon')
				{
					$weap = $this->_db->prepare("INSERT INTO weapons (iid, hp_mod, ap_mod, str_mod, def_mod, dex_mod, spd_mod, attack_msg) VALUES (:id, :hp, :ap, :str, :def, :dex, :spd, :msg)");
					$success = $weap->execute(
						[
							':id'  => $stmt->lastInsertId(),
							':hp'  => $input['hp'],
							':ap'  => $input['ap'],
							':str' => $input['str'],
							':def' => $input['def'],
							':dex' => $input['dex'],
							':spd' => $input['spd'],
							':msg' => $input['attack_msg']
						]
					);

					return $success;
				} else if($input['type'] == 'Armour') {
					$armour = $this->_db->prepare("INSERT INTO armour (iid, hp_mod, ap_mod, str_mod, def_mod, dex_mod, spd_mod, fit_position, defense_msg) VALUES (:id, :hp, :ap, :str, :def, :dex, :spd, :pos, :msg)");
					$success = $armour->execute(
						[
							':id'  => $stmt->lastInsertId(),
							':hp'  => $input['hp'],
							':ap'  => $input['ap'],
							':str' => $input['str'],
							':def' => $input['def'],
							':dex' => $input['dex'],
							':spd' => $input['spd'],
							':pos' => $input['fit_position'],
							':msg' => $input['defense_msg']
						]
					);

					return $success;
				} else {
					return $success;
				}
			} else {
				return false;
			}
		} catch(\Exception $e) {
			return $e->getMessage();
		}
	}

	/**
     * ItemModel::editItem()
     *
     * Adds item to DB
     *
     * @param array item details
     *
     * @return bool successful or not
     */
	public function editItem(array $input)
	{
		//need to update the item table, then check to see if armour or weapon and if so update those tables too
	}

	/**
     * ItemModel::removeItem()
     *
     * Removes item from DB
     *
     * @param array item details
     *
     * @return bool successful or not
     */
	public function removeItem(array $input)
	{
		$item = $this->_db->prepare("DELETE FROM items WHERE id = :id");
		$item->execute([':id' => $input['id']]);

		if($item->rowCount() > 0)
		{
			if($input['type'] == 'Weapon')
			{
				$weap = $this->_db->prepare("DELETE FROM weapons WHERE iid = :id");
				$weap->execute([':id' => $input['id']]);

				return ($weap->rowCount() > 0) ? true : false;
			} else if($input['type'] == 'Armour') {
				$arm = $this->_db->prepare("DELETE FROM armour WHERE iid = :id");
				$arm->execute([':id' => $input['id']]);

				return ($arm->rowCount() > 0) ? true : false;
			}
		}

		return ($item->rowCount() > 0) ? true : false;
	}

	/**
     * ItemModel::toggleItem()
     *
     * updates the available flag for an item
     *
     * @param int item id
     *
     * @return bool successful or not
     */
	public function toggleItem($id)
	{
		$stmt = $this->_db->prepare("UPDATE items SET available = !available WHERE id = :id");
		$stmt->execute([':id' => $id]);

		return ($stmt->rowCount() > 0) ? true : false;
	}

	/**
     * ItemModel::toggleItem()
     *
     * updates the available flag for an item
     *
	 * @param int user id
	 * @param int item id
	 * @param bool true = equip, false = un-equip
     *
     * @return bool successful or not
     */
	public function equipItem($user, $iid, $equip = true)
	{
		$stmt = $this->_db->prepare("UPDATE item_owned SET equipped = !equipped WHERE iid = :iid AND oid = :user");
		$stmt->execute(
			[
				':iid'  => $iid,
				':user' => $user
			]
		);

		return ($stmt->rowCount() > 0) ? true : false;
	}

	public function getItem($id)
	{
		$stmt = $this->_db->prepare("SELECT * FROM items WHERE id = :id");
		$stmt->execute([':id' => $id]);

		$output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $output;
	}
}