<?php
/**
 * Created by PhpStorm.
 * User: MarcT
 * Date: 21/11/2018
 * Time: 18:10
 */

namespace API\Library;
use GuzzleHttp\Client;

abstract class BaseController
{
	protected $_params;
	protected $_output;
	protected $_log;
	protected $_router;
	protected $_auth;
	protected $_headers;
	protected $_guzzle;
	protected $_config;

	public function __construct()
	{
		$this->_config  = new Config();
		$this->_router  = new Router();
		$this->_params  = $this->_router->getAllParameters();
		$this->_output  = new Output();
		$this->_log     = new Logger();
		$this->_auth    = new Authentication();
		$this->_headers = $this->_router->getAllHeaders();
		$this->_guzzle  = new Client();
	}

	public function __destruct()
	{
		$this->_log->saveMessage();
	}

	/**
	 * Covers the router's default method incase a part of the URL was missed
	 *
	 * @return array|string
	 * @throws \Exception
	 */
	public function main()
	{
		//$this->_log->set_message("main() Called from " . $_SERVER['REMOTE_ADDR'] . ", returning a 501", "INFO");

		return $this->_output->output(501, "Function not implemented", false);
	}
}