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

    public function getEventList()
    {
        $stmt = $this->_db->prepare("SELECT e.*, c.username FROM `event` e INNER JOIN `character` c ON c.cid = e.created_by");
        $stmt->execute();

        $output = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $output;
    }

    public function addEvent(array $input)
    {
        $stmt = $this->_db->prepare("INSERT INTO `event` (name, description, start_date, end_date, min_level, max_level, created_by, active) 
                                    VALUES (:name, :desc, :start, :end, :min, :max, :id, :active)");
        $stmt->execute(
            [
                ':name'  => $input['event_name'],
                ':desc'  => $input['description'],
                ':start' => $input['start_date'],
                ':end'   => $input['end_date'],
                ':min'   => $input['min_level'],
                ':max'   => $input['max_level'],
                ':id'    => $input['cid']
            ]
        );

        return ($stmt->lastInsertId() > 0) ? true : false;
    }

    public function editEvent(array $input)
    {
        $stmt = $this->_db->prepare("UPDATE `event` SET name = :name, description = :desc, start_date = :start, end_date = :end, min_level = :min, 
                                    max_level = :max, created_by = :id, active = :active) WHERE eid = :eid");
        $stmt->execute(
            [
                ':name'  => $input['event_name'],
                ':desc'  => $input['description'],
                ':start' => $input['start_date'],
                ':end'   => $input['end_date'],
                ':min'   => $input['min_level'],
                ':max'   => $input['max_level'],
                ':id'    => $input['cid'],
                ':eid'   => $input['eid']
            ]
        );

        return true;
    }

    public function removeEvent($id)
    {
        $stmt = $this->_db->prepare("DELETE FROM `event` WHERE eid = :id");
        $stmt->execute(
            [
                ':eid'  => $id
            ]
        );

        return true;
    }
}