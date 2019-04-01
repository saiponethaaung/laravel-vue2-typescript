<?php

namespace App\Http\Controllers\V1\Api;

use DB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PersistentFirstMenu;
use App\Models\PersistentSecondMenu;
use App\Models\PersistentThirdMenu;

class PersistentMenuController extends Controller
{
    public function loadMenu(Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => PersistentFirstMenu::with(
                    'blocks',
                    'secondRelation',
                    'secondRelation.blocks',
                    'secondRelation.thirdRelation',
                    'secondRelation.thirdRelation.blocks'
                )
                ->where('project_id', $request->attributes->get('project')->id)
                ->get()
        ]);
    }

    public function createMenu(Request $request)
    {
        $total = PersistentFirstMenu::where('project_id', $request->attributes->get('project')->id)->count();

        if($total>=3) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Persistent menu at it\'s limit!'
            ], 422);
        }

        $menu = new PersistentFirstMenu();
        
        DB::beginTransaction();

        try {
            $menu->title = '';
            $menu->url = '';
            $menu->project_id = $request->attributes->get('project')->id;
            $menu->save();
        } catch (\Exception $e) {
            DB::rollback();

            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create persistent menu!'
            ];

            if(env('APP_ENV')!=='production') {
                $response['debugMesg'] = $e->getMessage();
            }

            return response()->json($response, 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => PersistentFirstMenu::with(
                    'blocks',
                    'secondRelation',
                    'secondRelation.blocks',
                    'secondRelation.thirdRelation',
                    'secondRelation.thirdRelation.blocks'    
                )
                ->find($menu->id)
        ]);
    }

    public function updateFirstMenu(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->title = (String) $request->input('title');
        $menu->type = $request->input('type');
        $menu->url = (String) $request->input('url');
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }

    public function updateFirstMenuBlock(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->block_id = (String) $request->input('section');
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }

    public function deleteFirstMenuBlock(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->block_id = null;
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }

    public function createSecondMenu(Request $request)
    {
        $total = PersistentSecondMenu::where('parent_id', $request->firstMenu)->count();

        if($total>=5) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Second persistent menu at it\'s limit!'
            ], 422);
        }

        $menu = new PersistentSecondMenu();
        
        DB::beginTransaction();

        try {
            $menu->title = '';
            $menu->url = '';
            $menu->parent_id = $request->firstMenu;
            $menu->save();
        } catch (\Exception $e) {
            DB::rollback();

            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to create second persistent menu!'
            ];

            if(env('APP_ENV')!=='production') {
                $response['debugMesg'] = $e->getMessage();
            }

            return response()->json($response, 422);
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => PersistentFirstMenu::with(
                    'blocks',
                    'secondRelation.thirdRelation',
                    'secondRelation.thirdRelation.blocks'    
                )
                ->find($menu->id)
        ]);
    }

    public function updateSecondMenu(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->title = (String) $request->input('title');
        $menu->type = $request->input('type');
        $menu->url = (String) $request->input('url');
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }

    public function updateSecondMenuBlock(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->block_id = (String) $request->input('section');
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }

    public function deleteSecondMenuBlock(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid first menu!'
            ], 422);
        }

        $menu->block_id = null;
        $menu->save();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!'
        ]);
    }
}
