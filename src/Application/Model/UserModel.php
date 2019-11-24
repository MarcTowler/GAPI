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
}