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
     * @param int 0 = username, 1 = discord id, 2 = twitch id, 3 = uid
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function getPlayer($identifier, $flag)
    {
        $stmt = $this->_db->prepare("SELECT * FROM users WHERE " . (($flag == 0) ? 'username = :id' : (($flag == 1) ? 'discord_id = :id' : (($flag == 2) ? 'twitch_id = :id' : 'uid = :id'))));
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

            $this->getGear($this->_output['uid']);
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
			FROM users u LEFT JOIN item_owned as o ON u.uid = o.oid LEFT JOIN items 
			as i ON o.iid = i.id LEFT JOIN weapons as w on w.iid = i.id 
			WHERE uid = :id AND o.equipped = 1 AND i.type = 'Weapon'");
		$stmt->execute([':id' => $id]);

		$this->_output['weapon'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		
		$stmt2 = $this->_db->prepare("SELECT i.name, i.material, i.price, a.hp_mod, a.ap_mod, a.str_mod, a.def_mod, a.dex_mod, a.spd_mod, a.fit_position, a.defense_msg,
		i.level_req FROM users u LEFT JOIN item_owned as o ON u.uid = o.oid LEFT JOIN items 
		as i ON o.iid = i.id LEFT JOIN armour as a ON i.id = a.iid WHERE u.uid = :id AND o.equipped = 1 AND i.type = 'Armour'");
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
     * @todo update to work with new DB layout
     *
     * @param array The player's stats
     * @param int Source - 0 = discord, 1 = twitch
     *
     * @return array|NULL DB array of results or NULL on fail
     */
    public function registerPlayer($playerArray, $source)
	{
		//Check to see if they have a userID, if so update else insert
        $user = $this->getUser($playerArray['id'], $source);
        
        $stmt = $this->_db->prepare("INSERT INTO users (twitch_id, discord_id, follower, subscriber, vip, username, level, class, race, cur_hp, max_hp, cur_ap, max_ap, str, def, dex, spd, pouch, registered) VALUES
									(:tid, :did, :follow, :sub, :vip, :name, 1, :class, :race, :hp, :hp, :ap, :ap, :str, :def, :dex, :spd, 0, 1) ON DUPLICATE " . 
                                    "KEY UPDATE class = :class, race = :race, cur_hp = :hp, max_hp = :hp, cur_ap = :ap, max_ap = :ap, str = :str, def = :def, dex = :dex, spd = :spd, pouch = 0, registered = 1");
        $stmt->execute(
            [
                ':tid'    => ($source == 0) ? NULL : $playerArray['id'],
                ':did'    => ($source == 0) ? $playerArray['id'] : NULL,
                ':name'   => $playerArray['name'],
                ':follow' => ($source == 0) ? 0 : $playerArray['follow'],
                ':sub'    => ($source == 0) ? 0 : $playerArray['sub'],
                ':vip'    => ($source == 0) ? 0 : $playerArray['vip'],
                ':class'  => $playerArray['class'],
                ':race'   => $this->getRace($playerArray['race'], false)['id'],
                ':hp'     => $playerArray['hp'],
                ':ap'     => $playerArray['ap'],
                ':str'    => $playerArray['str'],
                ':def'    => $playerArray['def'],
                ':dex'    => $playerArray['dex'],
                ':spd'    => $playerArray['spd']
            ]
        );

    	$success = ($this->_db->lastInsertId() > 0) ? true : false;
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
        if($flag == 0)
        {
            $stmt = $this->_db->prepare("SELECT * FROM users WHERE discord_id = :id");
        } else {
            $stmt = $this->_db->prepare("SELECT * FROM users WHERE twitch_id = :id");
        }            
		$stmt->execute([':id' => $id]);

		$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);

		return $this->_output;
    }
    
    /**
     * UserModel::level()
     *
     * Increase's a player's level and increases a specified stat
     *
     * @param int user id
     * @param int ID for stat to be modified
     *
     * @return bool Success or failure
     */
    public function level($id, $stat)
	{
		$string = 'UPDATE users SET level = level + 1, cur_hp = cur_hp + 3, max_hp = max_hp + 3, cur_ap = cur_ap + 1, max_ap = max_ap + 1,';

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

		$stmt = $this->_db->prepare($string . " WHERE uid = :user");
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
     * @param int user ID
     *
     * @return bool true or false
     */
    public function reroll($stats, $uid)
	{
		$stmt = $this->_db->prepare("UPDATE users SET cur_hp = :hp, max_hp = :hp, str = :str, def = :def, dex = :dex, spd = :spd, reroll_count = reroll_count + 1 WHERE uid = :id LIMIT 1");
		$stmt->execute(
			[
				':hp'  => $stats['max_hp'],
				':str' => $stats['str'],
				':def' => $stats['def'],
				':dex' => $stats['dex'],
				':spd' => $stats['spd'],
				':id'  => $uid
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
     * @param int origination flag, 0 = username, 1 = discord id, 2 = twitch id, 3 = uid
     *
     * @return int the number of coins, or 0
     */
    public function getCoins($user, $flag)
	{
		$stmt = '';

		//We are looking at a username
        $stmt = $this->_db->prepare('SELECT pouch FROM users WHERE ' . 
            (($flag == 0) ? 'username = :id' : 
            (($flag == 1) ? 'discord_id = :id' : 
            (($flag == 2) ? 'twitch_id = :id' : 'uid = :id'))));
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
     * @param int user ID
     * @param int amount to modify
     * @param bool Increase (true) / decrease (false)
     *
     * @return bool success or failure
     */
    public function updateCoin($user, $amount, $increase)
	{
		$stmt = ($increase == true) ? $this->_db->prepare("UPDATE users SET pouch = pouch + :amount WHERE uid = :user AND gathering = 0 AND travelling = 0") : 
								$this->_db->prepare("UPDATE users SET pouch = pouch - :amount WHERE uid = :user AND gathering = 0 AND travelling = 0");

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
     * @param int user ID
     * @param int amount to modify
     * @param bool Increase (true) / decrease (false)
     *
     * @return bool success or failure
     */
    public function updateXP($user, $amount, $increase)
	{
		$stmt = ($increase == true) ? $this->_db->prepare("UPDATE users SET xp = xp + :amount WHERE uid = :user") : 
								 $this->_db->prepare("UPDATE users SET xp = xp - :amount WHERE uid = :user");
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
        $stmt = $this->_db->prepare("SELECT uid, cur_hp, cur_ap, max_hp, max_ap FROM users WHERE cur_hp > 0 AND gathering = 0 AND travelling = 0");

        $stmt->execute();

        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        for($i = 0; $i < count($users); $i++)
        {
            if($users[$i]['cur_hp'] < $users[$i]['max_hp'])
            {
                $hp = $this->_db->prepare("UPDATE users SET cur_hp = cur_hp + :amt WHERE uid = :id");
                $hp->execute(
                    [
                        ':amt' => $amount,
                        ':id'  => $users[$i]['uid']
                    ]
                );
            }

            if($users[$i]['cur_ap'] < $users[$i]['max_ap'])
            {
                $ap = $this->_db->prepare("UPDATE users SET cur_ap = cur_ap + :amt WHERE uid = :id");
                $ap->execute(
                    [
                        ':amt' => $amount,
                        ':id'  => $users[$i]['uid']
                    ]
                );
            }

            $hp = '';
            $ap = '';
        }
    }

    public function toggleStatus($user, $mode)
    {
        if($mode == 'gathering')
        {
            $stmt = $this->_db->prepare("UPDATE users SET gathering = !gathering WHERE uid = :cid");
            $stmt->execute([':cid' => $user]);
        } else if($mode == 'travelling') {
            $stmt = $this->_db->prepare("UPDATE users SET travelling = !travelling WHERE uid = :cid");
            $stmt->execute([':cid' => $user]);
        }
        
        return true;
    }

    public function update_player($type, $change, $id, $flag)
    {
        try {
            $stmt = $this->_db->prepare("UPDATE users u SET $type = :change WHERE " .
                (($flag == 0) ? 'u.username = :id' : 
                (($flag == 1) ? 'u.discord_id = :id' : 
                (($flag == 2) ? 'u.twitch_id = :id' : 'u.uid = :id'))));
            $stmt->execute(
                [
                    ':id'     => $id,
                    //':type'   => $type,
                    ':change' => $change
                ]
            );

            return true;
        } catch(\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function listAllPlayers()
    {
        $stmt = $this->_db->prepare("Select * FROM users");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRace($raceID, $id = true)
    {
        if($id == true)
        {
            $stmt = $this->_db->prepare("SELECT * FROM race WHERE id = :id");
            $stmt->execute([':id' => $raceID]);
        } else {
            $stmt = $this->_db->prepare("SELECT * FROM race WHERE name = :id");
            $stmt->execute([':id' => $raceID]);
        }    

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }
}