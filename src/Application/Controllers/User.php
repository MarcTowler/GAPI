<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class User extends Library\BaseController
{
	private $_db;
	protected $_validUser;

	public function __construct()
	{
		parent::__construct();

		$this->_db = new Model\UserModel();
	}

	public function __destruct()
	{
		parent::__destruct(); // TODO: Change the autogenerated stub
	}

	public function registerUser()
	{
		if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
		{
			return $this->_output->output(401, "Auhtorization failed", false);
		}

		$data = file_get_contents('php://input');

		$success = false;
		$output = '';
		$data = json_decode($data, true);
		//user would type !register, bot will DM them some details...

		//check that $_POST exists and is not empty
		if(!isset($data) || empty($data))
		{
			$output = "ERROR: POST data is missing, try again!";
		} else {
			$tmp = ($this->_headers['user'] == 'discord_bot') ? $this->_db->getUser($data['did'], true) : $this->_db->getUser($data['tid'], true);

			//check if user exists
			if(is_array($tmp))
			{
				$success = false;
				$output = "User " . $data['name'] . " already exists";
			} else {
				//bot to handle validation of data before tranmission.... Next, lets check the user doesn't exist then submit
				$success = ($this->_db->registerPlayer($data, ($this->_headers['user'] == 'discord_bot') ? 'discord' : 'twitch') == true) ? true : false;
				$output = "User " . $data['name'] . " has been registered!";
			}
		}

		return (!$success) ? $this->_output->output(409, $output, false) : $this->_output->output(200, $output, false);
	}

	public function registerPlayer()
	{
		if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
		{
			return $this->_output->output(401, "Auhtorization failed", false);
		}

		$data = file_get_contents('php://input');

		$success = false;
		$output = '';
		$data = json_decode($data, true);
		//user would type !register, bot will DM them some details...

		//check that $_POST exists and is not empty
		if(!isset($data) || empty($data))
		{
			$output = "ERROR: POST data is missing, try again!";
		} else {
			$tmp = $this->_db->getPlayer($data['name'], false);

			//check if user exists
			if(is_array($tmp))
			{
				$success = false;
				$output = "User " . $data['name'] . " already exists";
			} else {
				//bot to handle validation of data before tranmission.... Next, lets check the user doesn't exist then submit
				$success = ($this->_db->registerPlayer($data, ($this->_headers['user'] == 'discord_bot') ? 'discord' : 'twitch') == true) ? true : false;
				$output = "User " . $data['name'] . " has been registered!";
			}
		}

		return (!$success) ? $this->_output->output(409, $output, false) : $this->_output->output(200, $output, false);
	}

	public function updatePlayer() //setter
	{
		if($this->_auth->validate_token($this->_headers['token'], $this->_headers['user'])['level'] != 4)
		{
			return $this->_output->output(401, "Auhtorization failed", false);
		}

		//lets see what needs updating and check that POST is actually set
		$success = false;
		$output = '';

		if(!isset($_POST) || empty($_POST) || !isset($this->_params[0]))
		{
			$output = "ERROR: POST data is missing, try again!";
		} else {
			//store in the DB and check for update
			$output = ($this->_db->updatePlayer($_POST, $this->_params[0]) == true) ? "Player Updated" : "Something went wrong";

			$success = ($output == "Something went wrong") ? false : true;
		}


		return (!$success) ? $this->_output->output(400, $output, false) : $this->_output->output(200, $output, false);
	}

	public function getPlayer() //getter
	{
		$output = [];
		$success = false;

		//check to see what username is being pulled
		//api/User/getPlayer/PLAYERNAME
		if(isset($this->_params[0]))
		{
			//this is set if you are calling your own stats as we will pull via ID
			$id_flag = (isset($this->_params[1])) ? $this->_params[1] : false;

			//lets check the db for the player name
			$u = $this->_db->getPlayer($this->_params[0], $id_flag);
			$output = $this->_db->getClass($u['class']);
			$output = $this->_db->getGear($u['uid']);

			if(is_array($output))
			{
				$success = true;
			} else {
				$output = "User does not exist";
			}
		} else {
			$output = "You forgot to specify a player name to check";
		}

		return (!$success) ? $this->_output->output(400, $output, false) : $this->_output->output(200, $output, false);
	}
}