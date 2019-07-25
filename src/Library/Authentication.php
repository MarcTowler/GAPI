<?php
/**
 * Authentication Library
 *

 * Creates and authenticates JWT tokens as an authentication method
 * Level 1 = bot only token
 * Level 2 = web token
 * Level 3 = admin token
 *
 * Each token can be used for the level it is issued at and below
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


class Authentication
{
    private $_config;
    private $_db;
    private $_log;
    private $_JWT;

    public function __construct()
    {
        $this->_JWT = new JWT();
        $this->_config = new Config();
        $this->_db = $this->_config->database();
        $this->_log = new Logger();
    }

    public function __destruct()
    {
        $this->_log->saveMessage();
    }

    /**
     * Creates the JWT token for a user
     *
     * @param String $username
     * @param Integer $level
     * @return bool
     * @throws \Exception
     */
    public function create_token($username, $level)
    {

        //$this->_log->set_message("Creating new token for $username from " . $_SERVER['REMOTE_ADDR'], "INFO");

        $enc_token = $this->_JWT->encode(['user' => $username, 'level' => $level], $this->_config->getSettings('TOKEN'));

        $stmt = $this->_db->prepare("INSERT INTO auth_token (name, token, level) VALUES (:name, :token, :level)");
        $stmt->execute(
            [
                ':name' => $username,
                ':level' => $level,
                ':token' => $enc_token
            ]
        );

        return ($stmt->rowCount() > 0) ? true : false;
    }

    /**
     * Validate the authentication token
     *
     * @param String $token
     * @param String $user
     * @return int|mixed|string
     */
    public function validate_token($token, $user)
    {
        try
        {
            $stmt = $this->_db->prepare("SELECT level FROM auth_token WHERE name = :name AND token = :token");
            $stmt->execute(
                [
                    ':name' => $user,
                    ':token' => $token
                ]
            );

            return ($stmt->rowCount() > 0) ? $stmt->fetch() : 0;
        } catch(\PDOException $e)
        {
            return $e->getMessage();
        }
    }
}