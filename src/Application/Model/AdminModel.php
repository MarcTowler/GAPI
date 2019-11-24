<?php
namespace API\Model;

use API\Library;

class AdminModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
    }

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

    public function listNpcs()
    {
        $stmt = $this->_db->prepare("SELECT * FROM npc LIMIT 10");
        $stmt->execute();

        $this->_output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->_output;
    }
}