<?php
/**
 * Invalid Email Address Exception
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


class InvalidEmailAddressException extends APIException
{
    public function __construct()
    {
        parent::__construct('Invalid email address provied.');
    }
}