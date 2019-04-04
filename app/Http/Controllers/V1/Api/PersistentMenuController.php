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

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!',
            'pm' => $updatePageMenu
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
        
        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!',
            'pm' => $updatePageMenu
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

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'First menu has been saved!',
            'pm' => $updatePageMenu
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
            'data' => PersistentSecondMenu::with(
                    'blocks',
                    'thirdRelation',
                    'thirdRelation.blocks'    
                )
                ->find($menu->id)
        ]);
    }

    public function updateSecondMenu(Request $request)
    {
        $menu = PersistentSecondMenu::find($request->secondMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->title = (String) $request->input('title');
        $menu->type = $request->input('type');
        $menu->url = (String) $request->input('url');
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function updateSecondMenuBlock(Request $request)
    {
        $menu = PersistentSecondMenu::find($request->secondMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->block_id = (String) $request->input('section');
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function deleteSecondMenuBlock(Request $request)
    {
        $menu = PersistentSecondMenu::find($request->secondMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->block_id = null;
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function createThirdMenu(Request $request)
    {
        $total = PersistentThirdMenu::where('parent_id', $request->secondMenu)->count();

        if($total>=5) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Third persistent menu at it\'s limit!'
            ], 422);
        }

        $menu = new PersistentThirdMenu();
        
        DB::beginTransaction();

        try {
            $menu->title = '';
            $menu->url = '';
            $menu->parent_id = $request->secondMenu;
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
            'data' => PersistentThirdMenu::with(
                    'blocks' 
                )
                ->find($menu->id)
        ]);
    }

    public function updateThirdMenu(Request $request)
    {
        $menu = PersistentThirdMenu::find($request->thirdMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->title = (String) $request->input('title');
        $menu->type = $request->input('type');
        $menu->url = (String) $request->input('url');
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function updateThirdMenuBlock(Request $request)
    {
        $menu = PersistentThirdMenu::find($request->thirdMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->block_id = (String) $request->input('section');
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function deleteThirdMenuBlock(Request $request)
    {
        $menu = PersistentThirdMenu::find($request->thirdMenu);

        if(empty($menu)) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid second menu!'
            ], 422);
        }

        $menu->block_id = null;
        $menu->save();

        $updatePageMenu = app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);

        if(!$updatePageMenu['status']) {
            return response()->json($updatePageMenu, $updatePageMenu['code']);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Second menu has been saved!',
            'pm' => $updatePageMenu
        ]);
    }

    public function deleteFirstMenu(Request $request)
    {
        $menu = PersistentFirstMenu::find($request->firstMenu);

        DB::beginTransaction();
        try {
            $menu->delete();
            app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete menu!'
            ];

            if(env('APP_ENV')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTraceAsString();
            }

            return response()->json($response, 422);
        }
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Menu has been deleted!'
        ]);
    }

    public function deleteSecondMenu(Request $request)
    {
        $menu = PersistentSecondMenu::find($request->secondMenu);

        DB::beginTransaction();
        try {
            $menu->delete();
            app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete menu!'
            ];

            if(env('APP_ENV')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTraceAsString();
            }

            return response()->json($response, 422);
        }
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Menu has been deleted!'
        ]);
    }

    public function deleteThirdMenu(Request $request)
    {
        $menu = PersistentThirdMenu::find($request->thirdMenu);

        DB::beginTransaction();
        try {
            $menu->delete();
            app('App\Http\Controllers\V1\Api\ProjectController')->updatePersistentMenu($request->attributes->get('project')->id);
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => false,
                'code' => 422,
                'mesg' => 'Failed to delete menu!'
            ];

            if(env('APP_ENV')) {
                $response['debugMesg'] = $e->getMessage();
                $response['trace'] = $e->getTraceAsString();
            }

            return response()->json($response, 422);
        }
        DB::commit();

        return response()->json([
            'status' => true,
            'code' => 200,
            'mesg' => 'Menu has been deleted!'
        ]);
    }
}
