<?php
/**
 * Destiny 2 Invalid Item Instance ID Exception
 *
 *
 * @package       API
 * @author        Marc Towler <marc@marctowler.co.uk>
 * @copyright     Copyright (c) 2018 Marc Towler
 * @license       https://github.com/Design-Develop-Realize/api/blob/master/LICENSE.md
 * @link          https://api.itslit.uk
 * @since         Version 1.1
 * @filesource
 */

namespace API\Exceptions\Destiny;


class InvalidItemInstanceId extends \Exception
{
	public function __construct($value)
	{
		parent::__construct(sprintf('%s is an invalid Item Instance ID', $value));
	}
}