<?php
namespace API\Model;

use API\Library;

class StatsModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
    }

    public function gold_stats($limit)
    {
        $stmt = $this->_db->prepare("SELECT name, pouch FROM character ORDER BY pouch DESC LIMIT $limit");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}