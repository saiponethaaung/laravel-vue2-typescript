<?php

namespace PixyBots\Services;

use Illuminate\Http\Request;

interface PersistentMenuService
{
    public function getPersistentMenu(Request $request);

    public function createFirstMenu(Request $request);

    public function createSecondMenu(Request $request);

    public function createThirdMenu(Request $request);

    public function updateFirstMenu(Request $request);

    public function updateSecondMenu(Request $request);

    public function updateThirdMenu(Request $request);

    public function updateFirstMenuOrder(Request $request);

    public function updateSecondMenuOrder(Request $request);

    public function updateThirdMenuOrder(Request $request);

    public function deleteFirstMenu(Request $request);

    public function deleteSecondMenu(Request $request);

    public function deleteThirdMenu(Request $request);
}