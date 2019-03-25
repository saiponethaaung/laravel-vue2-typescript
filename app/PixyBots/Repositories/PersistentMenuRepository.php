<?php

namespace PixyBots\Repositories;

interface PersistentMenuRepository
{
    public function getAll();

    public function getFirst($id);

    public function getSecond($id);

    public function getThird($id);

    public function updateFirst($id, $input);

    public function updateThird($id, $input);

    public function updateFirstOrder($id, $order);
    
    public function updateSecondOrder($id, $order);

    public function updateThirdOrder($id, $order);

    public function deleteFirst($id);

    public function deleteSecond($id);

    public function deleteThird($id);

    public function getFirstTotal();

    public function getSecondTotal($parentId);

    public function getThirdTotal($parentId);
}