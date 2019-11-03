<?php
namespace API\Model;

use API\Library;

class UserModel extends Library\BaseModel
{
	private $_twitch;
	public function __construct()
	{
		parent::__construct();
		$this->_twitch = new Library\Twitch();
	}

	public function registerUser($playerArray, $source)
	{
		//lets check to see if they are already in the DB
		$user = $this->getPlayer((($source == 'discord') ? $playerArray['did'] : $playerArray['tid']), true);

		$stmt = $this->_db->prepare("INSERT INTO users (twitch_id, discord_id, username, follower, subscriber, vip, staff) VALUES
										(:tid, :did, :name, :follow, :sub, :vip, :staff)");
		$stmt->execute([
			':tid'    => $playerArray['tid'],
			':did'    => $playerArray['did'],
			':name'   => $playerArray['username'],
			':follow' => $playerArray['follow'],
			':sub'    => $playerArray['sub'],
			':vip'    => $playerArray['vip'],
			':staff'  => $playerArray['staff']
		]);

		$success = ($this->_db->lastInsertId() > 0) ? true : false;

		return $success;
	}

	public function getUser($id)
	{
		$stmt = $this->_db->prepare("SELECT * FROM users WHERE twitch_id = :id OR discord_id = :id");
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function registerPlayer($playerArray, $source)
	{
		//First lets get their user ID then insert them into the character array
		$user = $this->getUser((($source == 'discord') ? $playerArray['discord_id'] : $playerArray['twitch_id']));
		$stmt = $this->_db->prepare("INSERT INTO `character` (uid, username, level, class, cur_hp, max_hp, str, def, dex, spd, pouch, registered) VALUES
										(:uid, :name, 1, :class, :hp, :hp, :str, :def, :dex, :spd, 0, 1) ON DUPLICATE " . 
										"KEY UPDATE class = :class, cur_hp = :hp, max_hp = :hp, str = :str, def = :def, dex = :dex, spd = :spd, registered = 1");
		$stmt->execute([
			':uid'   => $user['uid'],
			':name'  => $playerArray['name'],
			':class' => $playerArray['class'],
			':hp'    => $playerArray['hp'],
			':str'   => $playerArray['str'],
			':def'   => $playerArray['def'],
			':dex'   => $playerArray['dex'],
			':spd'   => $playerArray['spd']
		]);

		$success = ($this->_db->lastInsertId() > 0) ? true : false;
		return $success;
	}

	public function updatePlayer(array $playerArray, $cid)
	{
		$sql = "UPDATE `character` SET ";
		$exec = [];
		$counter = 0;

		//loop through the array to create key => val
		foreach($playerArray as $key => $val)
		{
			if($counter == 0)
			{
				$sql .= "$key=$val";
			} else {
				$sql .= ", $key=$val";
				$counter++;
			}

		}

		$sql = rtrim($sql, ',');
		$sql .= " WHERE cid = :name";

		$stmt = $this->_db->prepare($sql);
		$stmt->execute([":name" => $cid]);

		return true;
	}

	public function getPlayer($username, $flag = false)
	{
		$stmt = '';

		//We are looking at a username
		if(!$flag) {
			$stmt = $this->_db->prepare("SELECT * FROM `character` WHERE username = :username");
			$stmt->execute([':username' => $username]);
		} else {
			//Looks like we have an ID number, lets check Twitch and Discord
			$stmt = $this->_db->prepare("SELECT * FROM `character` c LEFT JOIN users u ON u.uid = c.uid WHERE u.twitch_id = :id OR u.discord_id = :id");
			$stmt->execute([':id' => $username]);
		}
		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		if($this->_output == false)
		{
			if(!$flag)
			{
				$stmt2 = $this->_db->prepare("SELECT c.uid FROM users c WHERE c.name = :id");
				$stmt2->execute([':id' => $username]);
			} else {
				$stmt2 = $this->_db->prepare("SELECT c.uid, c.name FROM users c WHERE c.twitch_id = :id OR c.discord_id = :id");
				$stmt2->execute([':id' => $username]);
			}
			$id = $stmt2->fetch(\PDO::FETCH_ASSOC);

			$insert = $this->_db->prepare("INSERT INTO `character` (uid, username, class, level, cur_hp, max_hp, str, def, dex, spd, pouch) VALUES (:id, :username, 1, 1, 0, 0, 0, 0, 0, 0, 0)");
			if(!$flag)
			{
				$insert->execute(
					[
						':id'       => $id['uid'],
						':username' => $username
					]
				);
			} else {
				$insert->execute(
					[
						':id'       => $id['uid'],
						':username' => $id['name']
					]
				);
			}
		}

		//recursive call time
		if(!$flag) {
			$stmt3 = $this->_db->prepare("SELECT * FROM `character` WHERE username = :username");
			$stmt->execute([':username' => $username]);
		} else {
			//Looks like we have an ID number, lets check Twitch and Discord
			$stmt3 = $this->_db->prepare("SELECT * FROM `character` c LEFT JOIN users u ON u.uid = c.uid WHERE u.twitch_id = :id OR u.discord_id = :id");
			$stmt3->execute([':id' => $username]);
		}
		$this->_output = $stmt3->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
	}

	public function getClass($id)
	{
		$stmt = $this->_db->prepare("SELECT * FROM class WHERE id = :id");
		$stmt->execute([':id' => $id]);

		$this->_output['class'] = $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getGear($id)
	{
		$stmt = $this->_db->prepare("SELECT i.name, i.price, i.level_req, w.str_mod, w.def_mod, w.dex_mod, w.spd_mod, w.attack_msg 
			FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
			as i ON o.iid = i.id LEFT JOIN weapons as w on w.iid = i.id 
			WHERE uid = :id AND o.equipped = 1 AND i.type = 'Weapon'");
		$stmt->execute([':id' => $id]);

		$this->_output['weapons'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$stmt2 = $this->_db->prepare("SELECT i.name, i.material, i.price, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod, a.fit_position, a.defense_msg,
		i.level_req FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
		as i ON o.iid = i.id LEFT JOIN armour as a ON i.id = a.iid WHERE uid = :id AND o.equipped = 1 AND i.type = 'Armour'");
		$stmt2->execute([':id' => $id]);

		$this->_output['armour'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
		
		return $this->_output;
	}

	public function getUserItems($name)
	{
		$stmt = $this->_db->prepare("SELECT i.id, i.name, i.price, i.level_req, w.str_mod, w.def_mod, w.dex_mod, w.spd_mod, w.attack_msg, o.equipped 
			FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
			as i ON o.iid = i.id LEFT JOIN weapons as w on w.iid = i.id 
			WHERE username = :uname AND i.type = 'Weapon'");
		$stmt->execute([':uname' => $name]);

		$this->_output['weapons'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$stmt2 = $this->_db->prepare("SELECT i.id, i.name, i.material, i.price, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod, a.fit_position, a.defense_msg,
		i.level_req, o.equipped FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
		as i ON o.iid = i.id LEFT JOIN armour as a ON i.id = a.iid WHERE username = :uname AND i.type = 'Armour'");
		$stmt2->execute([':uname' => $name]);

		$this->_output['armour'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

		$stmt3 = $this->_db->prepare("SELECT i.id, i.name, i.material, i.price, i.level_req FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
		as i ON o.iid = i.id WHERE username = :uname AND i.type = 'Healing'");
		$stmt3->execute([':uname' => $name]);

		$this->_output['items'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);
				
		return $this->_output;
	}

	public function updateCoin($user, $amount, $win)
	{
		$stmt = ($win == true) ? $this->_db->prepare("UPDATE `character` SET pouch = pouch + :amount WHERE username = :user") : 
								$this->_db->prepare("UPDATE `character` SET pouch = pouch - :amount WHERE username = :user");

		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

		return true;
	}

	public function updateXP($user, $amount, $win)
	{
		$stmt = ($win == true) ? $this->_db->prepare("UPDATE `character` SET xp = xp + :amount WHERE username = :user") : 
								 $this->_db->prepare("UPDATE `character` SET xp = xp - :amount WHERE username = :user");
		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

		return true;
	}

	public function updatePveStats($cid, $mid, $win)
	{
		if($win)
		{
			$stmt = $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, win) VALUES(:cid, :mid, 1) ON DUPLICATE KEY UPDATE win = win + 1");
			$stmt->execute(
				[
					':cid' => $cid,
					':mid' => $mid
				]
			);

			$stmt2 = $this->_db->prepare("INSERT INTO play_fight_stats (character_id, mon_win) VALUES(:cid, 1) ON DUPLICATE KEY UPDATE mon_win = mon_win + 1");
			$stmt2->execute([':cid' => $cid]);
		} else {
			$stmt = $this->_db->prepare("INSERT INTO player_vs_monster (cid, mid, loss) VALUES(:cid, :mid, 1) ON DUPLICATE KEY UPDATE loss = loss + 1");
			$stmt->execute(
				[
					':cid' => $cid,
					':mid' => $mid
				]
			);

			$stmt2 = $this->_db->prepare("INSERT INTO play_fight_stats (character_id, mon_lose) VALUES(:cid, 1) ON DUPLICATE KEY UPDATE mon_lose = mon_lose + 1");
			$stmt2->execute([':cid' => $cid]);
		}

		return true;
	}

	public function getCoins($user, $flag)
	{
		$stmt = '';

		//We are looking at a username
		if(!$flag) {
			$stmt = $this->_db->prepare("SELECT pouch FROM `character` WHERE username = :username");
			$stmt->execute([':username' => $user]);
		} else {
			//Looks like we have an ID number, lets check Twitch and Discord
			$stmt = $this->_db->prepare("SELECT pouch FROM `character` c LEFT JOIN users u ON u.uid = c.uid WHERE u.twitch_id = :id OR u.discord_id = :id");
			$stmt->execute([':id' => $user]);
		}

		$tmp = $stmt->fetch(\PDO::FETCH_ASSOC)['pouch'];
		$this->_output = (is_null($tmp)) ? 0 : $tmp;

		return $this->_output;
	}

	public function level($user, $stat)
	{
		$string = 'UPDATE `character` SET level = level + 1, ';

		switch($stat)
		{
			case 1:
				$string = $string . "cur_hp = cur_hp + 1, max_hp = max_hp + 1";

				break;
			case 2:
				$string = $string . "str = str + 1";

				break;
			case 3:
				$string = $string . "def = def + 1";

				break;
			case 4:
				$string = $string . "dex = dex + 1";

				break;
			case 5:
				$string = $string . "spd = spd + 1";

				break;
		}

		$stmt = $this->_db->prepare($string . " WHERE username = :user");
		$stmt->execute([":user" => $user]);

		return true;
	}

	public function equip($user, $id, $on)
	{
		$stmt = $this->_db->prepare("UPDATE item_owned SET equipped = :state WHERE oid = :user AND iid = :item");
		$stmt->execute(
			[
				':state' => ($on == true) ? 1 : 0,
				':user'  => $user,
				':item'  => $id
			]
		);

		return $stmt->rowCount();
	}
}
