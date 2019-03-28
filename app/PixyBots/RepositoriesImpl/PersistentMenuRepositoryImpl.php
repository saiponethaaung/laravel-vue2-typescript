<?php

namespace PixyBots\RepositoriesImpl;

use DB;

use PixyBots\Repositories\PersistentMenuRepository;

use App\Models\PersistentFirstMenu;
use App\Models\PersistentSecondMenu;
use App\Models\PersistentThirdMenu;

class PersistentMenyRepositoryImpl implements PersistentMenuRepository
{
    public function getAll()
    {
        return true;
    }

    public function getFirst($id)
    {
        return true;
    }

    public function getSecond($id)
    {
        return true;
    }

    public function getThird($id)
    {
        return true;
    }

    public function updateFirst($id, $input)
    {
        return true;
    }

    public function updateThird($id, $input)
    {
        return true;
    }

    public function updateFirstOrder($id, $order)
    {
        return true;
    }
    
    public function updateSecondOrder($id, $order)
    {
        return true;
    }

    public function updateThirdOrder($id, $order)
    {
        return true;
    }

    public function deleteFirst($id)
    {
        return true;
    }

    public function deleteSecond($id)
    {
        return true;
    }

    public function deleteThird($id)
    {
        return true;
    }

    public function getFirstTotal()
    {
        return true;
    }

    public function getSecondTotal($parentId)
    {
        return true;
    }

    public function getThirdTotal($parentId)
    {
        return true;
    }
}