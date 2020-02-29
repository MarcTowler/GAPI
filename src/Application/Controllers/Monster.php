<?php
namespace API\Controllers;

use API\Library;
use API\Model;

class Monster extends Library\BaseController
{
    private $_db;
    
    public function __construct()
    {
        parent::__construct();

        $this->_db = new Model\MonsterModel();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function getMonster()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $name = str_replace('%20', ' ', $this->_params[0]);

        $mon  = $this->_db->getSpecificMonster($name);
        $info = [
            'hp'    => '?',
            'str'   => '?',
            'def'   => '?',
            'dex'   => '?',
            'spd'   => '?',
            'level' => '?',
            'lore'  => '?'
        ];

        /*
        check multiples of 10 to reveal info
        1=level
        2=hp
        3=str
        4=def
        5=dex
        6=spd
        7=lore
        */
        for($i = 0; $i < floor($mon['loss'] / 10); $i++)
        {
            switch($i+1)
            {
                case 1:
                    $info['level'] = $mon['level'];

                    break;
                case 2:
                    $info['hp'] = $mon['hp'];

                    break;
                case 3:
                    $info['str'] = $mon['str'];

                    break;
                case 4:
                    $info['def'] = $mon['def'];

                    break;
                case 5:
                    $info['dex'] = $mon['dex'];

                    break;
                case 6:
                    $info['spd'] = $mon['spd'];

                    break;
                case 7:
                    $info['lore'] = $mon['lore'];

                    break;
            }
        }

        return $this->_output->output(200, $info, false);
    }

    public function getRandomMonster()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }

        $tier = $this->_params[0];
        $min  = 0;
        $max  = 0;

        switch($tier)
        {
            case '1':
                $min = 1;
                $max = 5;

                break;
            case '2':
                $min = 6;
                $max = 10;

                break;
            case '3':
                $min = 11;
                $max = 30;

                break;
            case '4':
                $min = 31;
                $max = 50;

                break;
            case '5':
                $min = 51;
                $max = 70;

                break;
            case '6':
                $min = 71;
                $max = 90;

                break;
            case '7':
                $min = 91;
                $max = 100;

                break;
            default:
                $min = 0;
                $max = 1;
        }

        $mon = $this->_db->get_monster(['min' => $min, 'max' => $max]);
        $mon['ItemDrop'] = $this->getdrops($mon['nid']);
            
        return $this->_output->output(200, $mon, false);
    }

    public function listMonster()
    {
        //flag 0 = all, 1 = friendly, 2 = monster, 3 = quest giver
        return $this->_output->output(501, "Function not implemented", false);
    }

    public function updateStats()
    {
        if(!$this->authenticate()) { return $this->_output->output(401, 'Authentication failed', false); }
        if(!$this->validRequest('GET')) { return $this->_output->output(405, "Method Not Allowed", false); }
        
        //0 = monster id, 1 = status either true for win or false for loss
        $this->_db->update_stats($this->_params[0], $this->_params[1]);

        return $this->_output->output(200, "Stats Updated", false);
    }

    private function getdrops($mid, $active = 1, $specific = true)
    {
        $rng = (rand(1, 100) / 100);

        $output = $this->_db->get_drops($mid, $active);

        //rate - 1 = 100%, 0.01 = 1% chance of drop
        //IF RND <= rate drop happened.
        $dItem = [];

        if($specific)
        {
            foreach($output as $drop)
            {
                if($drop['rate'] >= $rng)
                {
                    $dItem = [
                        'id' => $drop['iid'],
                        'name' => $drop['name']
                    ];
                }
            }
        } else {
            $dItem = $output;
        }

        return $dItem;
    }
}
