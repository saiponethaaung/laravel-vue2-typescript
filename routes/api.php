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
                Route::get('/', 'V1\\Api\\ChatContent\\GetController@getContents')->name('chatbot.content.get');
                Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createContents')->name('chatbot.content.create');
                Route::group(['prefix' => '{contentId}', 'middleware' => 'verifychatBlockSectionContent'], function() {
                    Route::put('/', 'V1\\Api\\ChatContent\\UpdateController@updateContent')->name('chatbot.content.update');
                    Route::delete('/', 'V1\\Api\\ChatContent\\GetController@getContent')->name('chatbot.content.delete');
                    Route::post('/image', 'V1\\Api\\ChatContent\\GetController@getContent')->name('chatbot.content.image.upload');
                    Route::delete('/image', 'V1\\Api\\ChatContent\\GetController@getContents')->name('chatbot.content.image.delete');

                    Route::post('/list', 'V1\\Api\\ChatContent\\CreateController@createNewList')->name('chatbot.content.list.create');
                    Route::put('/list/{listId}', 'V1\\Api\\ChatContent\\UpdateController@updateList')->name('chatbot.content.list.update');
                    Route::post('/list/{listId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadListImage')->name('chatbot.content.list.image.upload');

                    Route::post('/gallery', 'V1\\Api\\ChatContent\\CreateController@createNewgallery')->name('chatbot.content.gallery.create');
                    Route::put('/gallery/{galleId}', 'V1\\Api\\ChatContent\\UpdateController@updateGallery')->name('chatbot.content.gallery.update');
                    Route::post('/gallery/{galleId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadGalleryImage')->name('chatbot.content.gallery.image.upload');
                    
                    Route::post('/quick-reply', 'V1\\Api\\ChatContent\\CreateController@createNewQuickReply')->name('chatbot.content.qr.create');
                    Route::put('/gallery/{galleId}', 'V1\\Api\\ChatContent\\UpdateController@updateGallery')->name('chatbot.content.gallery.update');
                });
            });
        });
    });
});