<?php
/**
 * Twitch Library
 *
 * Working with Twitch
 *
 * @package		API
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2018 Marc Towler
 * @license		https://github.com/Design-Develop-Realize/api/blob/master/LICENSE.md
 * @link		https://api.itslit.uk
 * @since       Version 1.0
 * @filesource
 */


namespace API\Library;

use API\Exceptions\InvalidIdentifierException;
use GuzzleHttp\Client;

class Twitch
{
	const API_BASE = 'https://api.twitch.tv/kraken/';
	private $_client;
	private $_clientid;
	private $_clientsecret;

	public function __construct()
    {
		$this->_client = new Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));
		$tmp = new Config();
		$this->_clientid = $tmp->getSettings('CLIENT_ID');
		$this->_clientsecret = $tmp->getSettings('TWITCH_SECRET');
    }

	public function get($url = '', $override = false, $headers = [])
	{
		$settings['headers'] = $headers;
		$settings['headers']['Client-ID'] = $this->_clientid;

		if(empty($settings['headers']['Accept'])) {
			$settings['headers']['Accept'] = 'application/vnd.twitchtv.v5+json';
		}

		//Added this workaround for when we access old API's that fail if you pass an Accept header
		if(isset($headers['nover'])) {
			$settings = [];
			$settings['headers']['Client-ID'] = $this->_clientid;
		}

		$settings['http_errors'] = false;

		$result = $this->_client->request('GET', (!$override ? self::API_BASE : '') . $url, $settings);

		return json_decode($result->getBody(), true);
	}

	public function post($url = '', $headers = [])
	{
		$settings['headers'] = $headers;
		$settings['headers']['Client-ID'] = $this->_clientid;

		if(empty($settings['headers']['Accept'])) {
			$settings['headers']['Accept'] = 'application/vnd.twitchtv.v5+json';
		}

		//Added this workaround for when we access old API's that fail if you pass an Accept header
		if(isset($headers['nover'])) {
			$settings = [];
			$settings['headers']['Client-ID'] = $this->_clientid;
		}

		$settings['http_errors'] = false;

		$result = $this->_client->request('POST', $url, $settings);

		return json_decode($result->getBody(), true);
	}

	public function base($token = '', $headers = [])
	{
		if(!empty($token)) {
			$headers['Authorization'] = 'OAuth ' . $token;
		}

		return $this->get('', false, $headers);
	}

	public function get_user_id($username)
	{
		$getUser = $this->get('users?login=' . $username, false);

		if(empty($getUser['users'])) {
			throw new InvalidIdentifierException("No user with the name '$username' was found");
		}

		return $getUser['users'][0]['_id'];
	}
}