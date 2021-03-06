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
	 * $type:
	 * 0 = user id
	 * 1 = twitch id
	 * 2 = discord id
	 */
	public function getUser($id, $type)
	{

	}

	public function registerUser(array $input)
	{

	}

	public function getUser($id, $flag)
	{

	}

	public function getCoins($id, $flag)
	{
		$sql = "SELECT p.pouch FROM player p INNER JOIN user u ON u.pid = p.pid WHERE ";
		$sql .= ($flag == 0) ? "u.uid = :id" ? (($flag == 1) ? "u.twitch = : id" ? "u.discord = :id");
		$stmt = $this->_db->prepare($sql);
		$stmt->execute(
			[
				':id' => $id
			]
		);

		$output = $stmt->fetch(\PDO::FETCH_ASSOC)['pouch'];

		return (is_null($output) ? 0 : $output);
	}
}