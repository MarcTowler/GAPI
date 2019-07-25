<?php
/**
 * Created by PhpStorm.
 * User: MarcT
 * Date: 09/04/2018
 * Time: 00:22
 */

namespace API\Exceptions;


class TwitterException extends \Exception
{
	/**
	 * @var string $message
	 * @var int $code
	 */
	public function __construct($message, $code = 0)
	{
		parent::__construct($message, $code);
	}
}