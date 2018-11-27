<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'chat-bot'], function() {
        Route::post('block', 'V1\\Api\\ChatBotController@createBlock')->name('chatbot.block.create');
        Route::get('blocks', 'V1\\Api\\ChatBotController@getBlocks')->name('chatbot.blocks.get');

        Route::delete('block/{blockId}', 'V1\\Api\\ChatBotController@deleteBlock')->name('chatbot.section.create');
    });
});