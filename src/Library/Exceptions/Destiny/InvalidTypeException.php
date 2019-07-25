<?php
/**
 * Invalid Type Exception
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

namespace API\Exceptions\Destiny;


class InvalidTypeException extends \Exception
{
    /**
     * @var string $name
     * @var string $expects
     * @var string $given
     */
    public function __construct($name, $expects, $given)
    {
        parent::__construct(sprintf('%s expects to be of type \'%s\', \'%s\' given instead.', $name, $expects, $given));
    }
}