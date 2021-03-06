<?php
/**
 * Event Model
 *
 * All Event related database called are stored here
 *
 * @package		GAPI
 * @author		Marc Towler <marc@marctowler.co.uk>
 * @copyright	Copyright (c) 2019 Marc Towler
 * @link		https://gapi.itslit.uk
 * @since       Version 1.0
 */
namespace API\Model;

use API\Library;

class EventModel extends Library\BaseModel
{
    public function __construct()
    {
		    parent::__construct();
    }

}