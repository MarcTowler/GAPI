<?php
/**
 * Created by PhpStorm.
 * User: MarcT
 * Date: 21/11/2018
 * Time: 20:07
 */

namespace API\Library;


abstract class BaseModel
{
	protected $_db;
	protected $_config;
	protected $_output;

	public function __construct()
	{
		$this->_config = new Config();
		$this->_db = $this->_config->database();
	}
}