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

        Route::group(['prefix' => 'block/{blockId}', 'middleware' => 'verifyChatBlock'], function() {
            Route::delete('/', 'V1\\Api\\ChatBotController@deleteBlock')->name('chatbot.block.delete');
            Route::post('section', 'V1\\Api\\ChatBotController@createSection')->name('chatbot.section.create');

            Route::group(['prefix' => 'section/{sectionId}/content', 'middleware' => 'verifyChatBlockSection'], function() {
                Route::get('/', 'V1\\Api\\ChatBotContentController@getContents')->name('chatbot.content.get');
                Route::post('/', 'V1\\Api\\ChatBotContentController@createContents')->name('chatbot.content.create');
                Route::group(['prefix' => '{contentId}'], function() {
                    Route::put('/', 'V1\\Api\\ChatBotContentController@updateContent')->name('chatbot.content.update');
                    Route::delete('/', 'V1\\Api\\ChatBotContentController@getContent')->name('chatbot.content.delete');
                    Route::post('/image', 'V1\\Api\\ChatBotContentController@getContent')->name('chatbot.content.image.upload');
                    Route::delete('/image', 'V1\\Api\\ChatBotContentController@getContents')->name('chatbot.content.image.delete');
                });
            });
        });
    });
});