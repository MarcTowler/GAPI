<?php
/**
 * User Model
 *
 * All user related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
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

    /**
     * UserModel::getPlayer()
     *
     * Pulls player info from database, identified by $identifier and will return just player if $full is false or everything on true
     *
     * @param string|int Either a username or ID number
     * @param int 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function getPlayer($identifier, $flag)
    {
        $stmt = $this->_db->prepare("SELECT * FROM `character` c INNER JOIN users u ON u.uid = c.uid WHERE " . (($flag == 0) ? 'c.username = :id' : (($flag == 1) ? 'u.discord_id = :id' : (($flag == 2) ? 'u.twitch_id = :id' : 'c.cid = :id'))));
        $stmt->execute([':id' => $identifier]);

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($this->_output)
        {
            $class = $this->_db->prepare("SELECT name, str_mod, def_mod, dex_mod, spd_mod, hp_mod, ap_mod FROM class WHERE id = :id");
            $class->execute([':id' => $this->_output['class']]);

            $this->_output['class'] = $class->fetch(\PDO::FETCH_ASSOC);

            $race = $this->_db->prepare("SELECT name, str_mod, def_mod, dex_mod, spd_mod, hp_mod, ap_mod FROM race WHERE id = :id");
            $race->execute([':id' => $this->_output['race']]);

            $this->_output['race'] = $race->fetch(\PDO::FETCH_ASSOC);

            $this->getGear($this->_output['cid']);
        }

        return $this->_output;
    }

    /**
     * UserModel::getGear()
     *
     * Pulls weapons and armour that a player has equipped
     *
     * @param int Character ID
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function getGear($id)
    {
        $tmp = [];
        $tmp['head'] = [
            'hp_mod'  => 0,
            'ap_mod'  => 0,
            'str_mod' => 0,
            'def_mod' => 0,
            'dex_mod' => 0,
            'spd_mod' => 0
        ];
        $tmp['chest'] = [
            'hp_mod'  => 0,
            'ap_mod'  => 0,
            'str_mod' => 0,
            'def_mod' => 0,
            'dex_mod' => 0,
            'spd_mod' => 0
        ];
        $tmp['arms'] = [
            'hp_mod'  => 0,
            'ap_mod'  => 0,
            'str_mod' => 0,
            'def_mod' => 0,
            'dex_mod' => 0,
            'spd_mod' => 0
        ];
        $tmp['legs'] = [
            'hp_mod'  => 0,
            'ap_mod'  => 0,
            'str_mod' => 0,
            'def_mod' => 0,
            'dex_mod' => 0,
            'spd_mod' => 0
        ];
        $tmp['feet'] = [
            'hp_mod'  => 0,
            'ap_mod'  => 0,
            'str_mod' => 0,
            'def_mod' => 0,
            'dex_mod' => 0,
            'spd_mod' => 0
        ];

        $stmt = $this->_db->prepare("SELECT i.name, i.price, i.level_req, w.hp_mod, w.ap_mod, w.str_mod, w.def_mod, w.dex_mod, w.spd_mod, w.attack_msg 
			FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
			as i ON o.iid = i.id LEFT JOIN weapons as w on w.iid = i.id 
			WHERE uid = :id AND o.equipped = 1 AND i.type = 'Weapon'");
		$stmt->execute([':id' => $id]);

		$this->_output['weapon'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		
		$stmt2 = $this->_db->prepare("SELECT i.name, i.material, i.price, a.hp_mod, a.ap_mod, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod, a.fit_position, a.defense_msg,
		i.level_req FROM `character` as c LEFT JOIN item_owned as o ON c.uid = o.oid LEFT JOIN items 
		as i ON o.iid = i.id LEFT JOIN armour as a ON i.id = a.iid WHERE uid = :id AND o.equipped = 1 AND i.type = 'Armour'");
		$stmt2->execute([':id' => $id]);

        $armour = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        for($i = 0; $i < sizeof($armour); $i++)
		{
			switch(strtolower($armour[$i]['fit_position']))
			{
				case 'head':
					$tmp['head'] = $armour[$i];

					break;
				case 'chest':
					$tmp['chest'] = $armour[$i];

					break;
				case 'arms':
					$tmp['arms'] = $armour[$i];

					break;
				case 'legs':
					$tmp['legs'] = $armour[$i];

					break;
				case 'feet':
					$tmp['feet'] = $armour[$i];

					break;
			}
        }
        
        $this->_output['armour'] = $tmp;
    }

    /**
     * UserModel::registerPlayer()
     *
     * Registers a new RPG Player
     *
     * @param array The player's stats
     * @param int Source - 0 = discord, 1 = twitch
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function registerPlayer($playerArray, $source)
	{
		//First lets get their user ID then insert them into the character array
        $user = $this->getUser($playerArray['id'], $source);
        
		$stmt = $this->_db->prepare("INSERT INTO `character` (uid, username, level, class, cur_hp, max_hp, cur_ap, max_ap, str, def, dex, spd, pouch, registered) VALUES
										(:uid, :name, 1, :class, :hp, :hp, :ap, :ap, :str, :def, :dex, :spd, 0, 1) ON DUPLICATE " . 
										"KEY UPDATE class = :class, cur_hp = :hp, max_hp = :hp, cur_ap = :ap, max_ap = :ap, str = :str, def = :def, dex = :dex, spd = :spd, registered = 1");
		$stmt->execute(
            [
                ':uid'   => $user['uid'],
                ':name'  => $playerArray['name'],
                ':class' => $playerArray['class'],
                ':hp'    => $playerArray['hp'],
                ':ap'    => $playerArray['ap'],
                ':str'   => $playerArray['str'],
                ':def'   => $playerArray['def'],
                ':dex'   => $playerArray['dex'],
                ':spd'   => $playerArray['spd']
		]);

		$success = ($this->_db->lastInsertId() > 0) ? true : false;
		return $success;
    }

    /**
     * UserModel::registerUser()
     *
     * Registers a new user and shadow RPG player
     *
     * @param array The user's details
     * @param int Source - 0 = discord, 1 = twitch
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function registerUser($playerArray, $source)
	{
		$stmt = $this->_db->prepare("INSERT INTO users (twitch_id, discord_id, name, follower, subscriber, vip, staff) VALUES
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

        if($success)
        {
            //Now we create a shadow player account so they can earn coins
            $ins = $this->_db->prepare("INSERT INTO `character` (uid, username, class, race, level, xp, cur_hp, max_hp, cur_ap, max_ap, str, def, dex, spd, pouch, registered, alpha_tester, beta_tester, reroll_count)"
                . " VALUES(:uid, :name, 1, 1, 1, 10, 10, 10, 10, 0, 0, 0, 0, 0, :coin, 0, 0, 0, 0)");
            $ins->execute(
                [
                    ':uid'  => $this->_db->lastInsertId(),
                    ':name' => $playerArray['username'],
                    ':coin' => ($playerArray['follow'] == 1) ? 100 : 0
                ]
            );

            $success = ($this->_db->lastInsertId() > 0) ? true : false;
        }

		return $success;
	}
    
    /**
     * UserModel::getUser()
     *
     * Pulls user info
     *
     * @param array User ID
     * @param int Source - 0 = discord, 1 = twitch
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function getUser($id, $flag)
	{
		$stmt = $this->_db->prepare("SELECT * FROM users WHERE " . ($flag == 0) ? 'discord_id = ' : 'twitch_id = ' . ':id');
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
    }
    
    /**
     * UserModel::level()
     *
     * Increase's a player's level and increases a specified stat
     *
     * @param int character's id
     * @param int ID for stat to be modified
     *
     * @return bool Success or failure
     */
    public function level($id, $stat)
	{
		$string = 'UPDATE `character` SET level = level + 1, cur_hp = cur_hp + 3, max_hp = max_hp + 3, cur_ap = cur_ap + 1, max_ap = max_ap + 1,';

		switch($stat)
		{
			case 1:
				$string = $string . "str = str + 1";

				break;
			case 2:
				$string = $string . "def = def + 1";

				break;
			case 3:
				$string = $string . "dex = dex + 1";

				break;
			case 4:
				$string = $string . "spd = spd + 1";

				break;
		}

		$stmt = $this->_db->prepare($string . " WHERE cid = :user");
		$stmt->execute([":user" => $id]);

		return true;
    }

    /**
     * UserModel::xpNeeded()
     *
     * Returns the amount of XP needed to hit specified level
     *
     * @param int $level
     *
     * @return int The XP Needed for level up
     */
    public function xpNeeded($level)
    {
        $stmt = $this->_db->prepare("SELECT xp_needed FROM level_xp WHERE level = :level");
        $stmt->execute([':level' => $level]);

        $this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $this->_output;
    }

    /**
     * UserModel::reroll()
     *
     * Retrieves the value of a user's pouch
     *
     * @param array a list of all the stats
     * @param int character ID
     *
     * @return bool true or false
     */
    public function reroll($stats, $cid)
	{
		$stmt = $this->_db->prepare("UPDATE `character` SET cur_hp = :hp, max_hp = :hp, str = :str, def = :def, dex = :dex, spd = :spd, reroll_count = reroll_count + 1 WHERE cid = :id LIMIT 1");
		$stmt->execute(
			[
				':hp'  => $stats['max_hp'],
				':str' => $stats['str'],
				':def' => $stats['def'],
				':dex' => $stats['dex'],
				':spd' => $stats['spd'],
				':id'  => $cid
			]
        );
        
        return ($stmt->rowCount() > 0) ? true : false;
    }

    /**
     * UserModel::getCoins()
     *
     * Retrieves the value of a user's pouch
     *
     * @param int user identifier
     * @param int origination flag, 0 = username, 1 = discord id, 2 = twitch id, 3 = cid
     *
     * @return int the number of coins, or 0
     */
    public function getCoins($user, $flag)
	{
		$stmt = '';

		//We are looking at a username
        $stmt = $this->_db->prepare('SELECT c.pouch FROM `character` c INNER JOIN users u ON c.uid = u.uid WHERE ' . 
            (($flag == 0) ? 'c.username = :id' : 
            (($flag == 1) ? 'u.discord_id = :id' : 
            (($flag == 2) ? 'u.twitch_id = :id' : 'c.cid = :id'))));
		$stmt->execute([':id' => $user]);

		$tmp = $stmt->fetch(\PDO::FETCH_ASSOC)['pouch'];
		$this->_output = (is_null($tmp)) ? 0 : $tmp;

		return $this->_output;
	}

    /**
     * UserModel::updateCoin()
     *
     * Update's coins in a user's pouch
     *
     * @param int character ID
     * @param int amount to modify
     * @param bool Increase (true) / decrease (false)
     *
     * @return bool success or failure
     */
    public function updateCoin($user, $amount, $increase)
	{
		$stmt = ($increase == true) ? $this->_db->prepare("UPDATE `character` SET pouch = pouch + :amount WHERE cid = :user") : 
								$this->_db->prepare("UPDATE `character` SET pouch = pouch - :amount WHERE cid = :user");

		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

		return ($stmt->rowCount() > 0) ? true : false;
	}

    /**
     * UserModel::updateXP()
     *
     * Update's coins in a user's pouch
     *
     * @param int character ID
     * @param int amount to modify
     * @param bool Increase (true) / decrease (false)
     *
     * @return bool success or failure
     */
    public function updateXP($user, $amount, $increase)
	{
		$stmt = ($increase == true) ? $this->_db->prepare("UPDATE `character` SET xp = xp + :amount WHERE cid = :user") : 
								 $this->_db->prepare("UPDATE `character` SET xp = xp - :amount WHERE cid = :user");
		$stmt->execute(
			[
				":user"   => $user,
				":amount" => $amount
			]
		);

		return true;
    }
    
    public function regen($amount = 1)
    {
        $stmt = $this->_db->prepare("SELECT cid, cur_hp, cur_ap, max_hp, max_ap FROM `character`");
        $stmt->execute();

        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        for($i = 0; $i < count($users); $i++)
        {
            if($users[$i]['cur_hp'] < $users[$i]['max_hp'])
            {
                $hp = $this->_db->prepare("UPDATE `character` SET cur_hp = cur_hp + :amt WHERE cid = :id");
                $hp->execute(
                    [
                        ':amt' => $amount,
                        ':id'  => $users[$i]['cid']
                    ]
                );
            }

            if($users[$i]['cur_ap'] < $users[$i]['max_ap']);
            {
                $ap = $this->_db->prepare("UPDATE `character` SET cur_ap = cur_ap + :amt WHERE cid = :id");
                $ap->execute(
                    [
                        ':amt' => $amount,
                        ':id'  => $users[$i]['cid']
                    ]
                );
            }

            $hp = '';
            $ap = '';
        }
    }

    public function update_player($type, $change, $id, $flag)
    {
        $stmt = $this->_db->prepare("UPDATE `character` c INNER JOIN users u ON u.uid = c.uid SET $type = :change WHERE " .
            (($flag == 0) ? 'c.username = :id' : 
            (($flag == 1) ? 'u.discord_id = :id' : 
            (($flag == 2) ? 'u.twitch_id = :id' : 'c.cid = :id'))));
        $stmt->execute(
            [
                ':id'     => $id,
                //':type'   => $type,
                ':change' => $change
            ]
        );
    }
}