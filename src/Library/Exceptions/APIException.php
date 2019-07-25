<?php
/**
 * API Exception
 *
 *
 * @package       API
 * @author        Marc Towler <marc@marctowler.co.uk>
 * @copyright     Copyright (c) 2018 Marc Towler
 * @license       https://github.com/Design-Develop-Realize/api/blob/master/LICENSE.md
 * @link          https://api.itslit.uk
 * @since         Version 1.0
 * @filesource
 */

namespace API\Exceptions;


class APIException extends \Exception
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