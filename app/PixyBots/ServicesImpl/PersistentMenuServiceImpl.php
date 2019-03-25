<?php

namespace PixyBots\ServicesImpl;

use Validator;

use Illuminate\Http\Request;

use PixyBots\Services\PersistentMenuService;
use PixyBots\Repositories\PersistentMenuRepository;

class PersistentMenuServiceImpl implements PersistentMenuService
{
    private $repo;

    public function __construct(PersistentMenuRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getPersistentMenu(Request $request)
    {
        $persistentMenu = $this->repo->getAll();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function createFirstMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function createSecondMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function createThirdMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateFirstMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateSecondMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateThirdMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateFirstMenuOrder(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateSecondMenuOrder(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function updateThirdMenuOrder(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function deleteFirstMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function deleteSecondMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }

    public function deleteThirdMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'data' => [],
        ]);
    }
}