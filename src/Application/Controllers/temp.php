<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class temp extends Library\BaseController
{
	private $_twitch;
	public function __construct()
	{
		parent::__construct();
		$this->_twitch = new Library\Twitch();
	}

	public function genToken()
	{
		return $this->_auth->create_token('discord_bot', 4);
	}

	public function twitch()
	{
		var_dump($this->_twitch->get_user_id('itslittany'));
	}
}