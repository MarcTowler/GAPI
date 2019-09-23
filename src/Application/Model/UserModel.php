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
		$stmt = $this->_db->prepare("INSERT INTO `character` (uid, username, level, class, cur_hp, max_hp, str, def, dex, spd, pouch) VALUES
										(:uid, :name, 1, :class, :hp, :hp, :str, :def, :dex, :spd, 0)");
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

	public function updatePlayer(array $playerArray, $user)
	{
		$sql = "UPDATE character SET ";
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
			}

		}

		$sql = rtrim($sql, ',');
		$sql .= " WHERE username = :name";

		$stmt = $this->_db->prepare($sql);
		$stmt->execute([":name" => $user]);

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

	public function updateCoin($user, $amount)
	{
		$stmt = $this->_db->prepare("UPDATE `character` SET pouch = pouch + :amount WHERE username = :user");
		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

		return true;
	}

	public function updateXP($user, $amount)
	{
		$stmt = $this->_db->prepare("UPDATE `character` SET xp = xp + :amount WHERE username = :user");
		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

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
}
