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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/user/login', 'V1\\Api\\UserAuthController@login');

Route::any('/facebook/chatbot/$2y$12$uyP735FKW7vuSYmlAEhF/OOoo1vCaWZN7zIEeFEhYbAw2qv8X4ffe', 'V1\\Api\\FacebookChatbotController@index');

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function() {
    Route::group(['prefix' => 'user'], function() {
        Route::get('/', 'V1\\Api\\UserController@getProfile');
        Route::post('facebook-linked', 'V1\\Api\\UserController@connectFacebook');
    });

    Route::group(['prefix' => 'project'], function(){
        Route::get('list', 'V1\\Api\\ProjectController@list')->name('chatbot.project.list');
    });

    Route::group(['prefix' => 'project/{projectId}'], function(){
        Route::get('/', 'V1\\Api\\ProjectController@projectInfo')->name('chatbot.project.info');
        Route::get('/pages', 'V1\\Api\\ProjectController@getPage')->name('chatbot.project.page');
        Route::group(['prefix' => 'chat-bot'], function() {
            Route::post('block', 'V1\\Api\\ChatBotController@createBlock')->name('chatbot.block.create');
            Route::get('blocks', 'V1\\Api\\ChatBotController@getBlocks')->name('chatbot.blocks.get');

            Route::get('blocks/search', 'V1\\Api\\ChatBotController@serachSection')->name('chatbot.section.serach');

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

                        Route::group(['prefix' => 'gallery'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewList')->name('chatbot.content.list.create');
                            Route::put('/{listId}', 'V1\\Api\\ChatContent\\UpdateController@updateList')->name('chatbot.content.list.update');
                            Route::post('/{listId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadListImage')->name('chatbot.content.list.image.upload');
                        });

                        Route::group(['prefix' => 'gallery'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewgallery')->name('chatbot.content.gallery.create');
                            Route::put('/{galleId}', 'V1\\Api\\ChatContent\\UpdateController@updateGallery')->name('chatbot.content.gallery.update');
                            Route::post('/{galleId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadGalleryImage')->name('chatbot.content.gallery.image.upload');
                        });

                        Route::group(['prefix' => 'quick-reply'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewQuickReply')->name('chatbot.content.qr.create');
                            Route::put('/{qrId}', 'V1\\Api\\ChatContent\\UpdateController@updateQuickReply')->name('chatbot.content.qr.update');
                            Route::post('/{qrId}/block', 'V1\\Api\\ChatContent\\UpdateController@addQuickReplyBlock')->name('chatbot.content.qr.block.create');
                        });
                        
                        Route::group(['prefix' => 'user-input'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewUserInput')->name('chatbot.content.ui.create');
                            Route::put('/{uiId}', 'V1\\Api\\ChatContent\\UpdateController@updateUserInput')->name('chatbot.content.ui.update');
                        });
                    });
                });
            });
        });
    });
});