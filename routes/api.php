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

Route::any('/queue-tester', 'TestController@startQueue');

Route::post('/user/login', 'V1\\Api\\UserAuthController@login')->name('api.login');
Route::post('/user/register', 'V1\\Api\\UserAuthController@register')->name('api.register');
Route::post('/user/verify', 'V1\\Api\\UserAuthController@verifyToken')->name('api.verify');

Route::any('/facebook/chatbot/$2y$12$uyP735FKW7vuSYmlAEhF/OOoo1vCaWZN7zIEeFEhYbAw2qv8X4ffe', 'V1\\Api\\FacebookChatbotController@index');

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function() {

    Route::group(['prefix' => 'user'], function() {
        Route::get('/', 'V1\\Api\\UserController@getProfile');
        Route::post('facebook-linked', 'V1\\Api\\UserController@connectFacebook');
    });

    Route::group(['prefix' => 'project'], function(){
        Route::post('/', 'V1\\Api\\ProjectController@create')->name('chatbot.project.create');
        Route::get('list', 'V1\\Api\\ProjectController@list')->name('chatbot.project.list');
    });

    
    Route::group(['prefix' => 'project/{projectId}', 'middleware' => ['verifyProject', 'verifyProjectMember']], function() {
        Route::get('/', 'V1\\Api\\ProjectController@projectInfo')->name('chatbot.project.info');
        Route::get('/pages', 'V1\\Api\\ProjectController@getPage')->name('chatbot.project.page');
        Route::post('/pages/link', 'V1\\Api\\ProjectController@linkProject')->name('chatbot.project.page.link');
        Route::delete('/pages/link', 'V1\\Api\\ProjectController@unlinkProject')->name('chatbot.project.page.unlink');
        Route::post('/pages/change-publish-status', 'V1\\Api\\ProjectController@changePublishStatusPage')->name('chatbot.project.page.publish.status');
        
        Route::get('message-tags', 'V1\\Api\\MessageTagsController@getList')->name('chatbot.project.message-tags');

        Route::group(['prefix' => 'ai-setup'], function() {
            Route::get('/', 'V1\\Api\\AIController@getList')->name('chatbot.ai.list');
            Route::post('/', 'V1\\Api\\AIController@create')->name('chatbot.ai.create');
            Route::group(['prefix' => '{kfGroupId}', 'middleware' => 'verifyAIGroup'], function() {
                Route::put('/', 'V1\\Api\\AIController@updateGroupName')->name('chatbot.ai.group.rename');
                Route::delete('/', 'V1\\Api\\AIController@deleteGroup')->name('chatbot.ai.group.delete');
                Route::group(['prefix' => 'rules'], function() {
                    Route::get('/', 'V1\\Api\\AIController@getRules')->name('chatbot.ai.group.rule.list');
                    Route::post('/', 'V1\\Api\\AIController@createRule')->name('chatbot.ai.group.rule.create');
                    Route::group(['prefix' => '{ruleid}', 'middleware' => 'verifyAIGroupRule'], function() {
                        Route::post('/keywords', 'V1\\Api\\AIController@updateKeywords')->name('chatbot.ai.group.rule.keywords.update');
                        Route::post('/response', 'V1\\Api\\AIController@createResponse')->name('chatbot.ai.group.rule.response.create');
                        Route::group(['prefix' => 'response/{responseid}', 'middleware' => 'verifyAIGroupRuleResponse'], function() {
                            Route::put('/', 'V1\\Api\\AIController@updateResponse')->name('chatbot.ai.group.rule.response.update');
                            Route::delete('/', 'V1\\Api\\AIController@deleteResponse')->name('chatbot.ai.group.rule.response.delete');
                        });
                    });
                });
            });
        });

        Route::group(['prefix' => 'chat-bot'], function() {
            
            Route::post('block', 'V1\\Api\\ChatBotController@createBlock')->name('chatbot.block.create');
            Route::get('blocks', 'V1\\Api\\ChatBotController@getBlocks')->name('chatbot.blocks.get');
            Route::get('blocks/search', 'V1\\Api\\ChatBotController@searchSection')->name('chatbot.section.search');
            Route::post('blocks/order', 'V1\\Api\\ChatBotController@updateBlockOrder')->name('chatbot.block.update.order');

            Route::group(['prefix' => 'block/{blockId}', 'middleware' => 'verifyChatBlock'], function() {

                Route::put('/', 'V1\\Api\\ChatBotController@updateBlock')->name('chatbot.block.update');
                Route::delete('/', 'V1\\Api\\ChatBotController@deleteBlock')->name('chatbot.block.delete');
                Route::post('section', 'V1\\Api\\ChatBotController@createSection')->name('chatbot.section.create');
                Route::post('section/order', 'V1\\Api\\ChatBotController@updateSectionOrder')->name('chatbot.section.update.order');

                Route::group(['prefix' => 'section/{sectionId}', 'middleware' => 'verifyChatBlockSection'], function() {
                    Route::put('/', 'V1\\Api\\ChatBotController@updateSection')->name('chatbot.section.update');
                    Route::delete('/', 'V1\\Api\\ChatBotController@deleteSection')->name('chatbot.section.delete');
                });

                Route::group(['prefix' => 'section/{sectionId}/content', 'middleware' => 'verifyChatBlockSection'], function() {

                    Route::get('/', 'V1\\Api\\ChatContent\\GetController@getContents')->name('chatbot.content.get');
                    Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createContents')->name('chatbot.content.create');
                    Route::post('/order', 'V1\\Api\\ChatContent\\UpdateController@updateContentsOrder')->name('chatbot.content.order');
                    
                    Route::group(['prefix' => '{contentId}', 'middleware' => 'verifychatBlockSectionContent'], function() {
                        
                        Route::put('/', 'V1\\Api\\ChatContent\\UpdateController@updateContent')->name('chatbot.content.update');
                        Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteContent')->name('chatbot.content.delete');
                        Route::post('/image', 'V1\\Api\\ChatContent\\GetController@getContent')->name('chatbot.content.image.upload');
                        Route::delete('/image', 'V1\\Api\\ChatContent\\GetController@getContents')->name('chatbot.content.image.delete');

                        Route::group(['prefix' => 'button'], function() {
                            Route::post('text', 'V1\\Api\\ChatContent\\CreateController@createTextButton')->name('chatbot.content.text.button.create');
                            Route::post('list', 'V1\\Api\\ChatContent\\CreateController@createListButton')->name('chatbot.content.list.button.create');
                            Route::post('list/{listid}', 'V1\\Api\\ChatContent\\CreateController@createListItemButton')->name('chatbot.content.list.itme.button.create');
                            Route::post('gallery/{galleryid}', 'V1\\Api\\ChatContent\\CreateController@createGalleryButton')->name('chatbot.content.gallery.button.create');
                            Route::group(['prefix' => '{buttonid}'], function() {
                                Route::put('/', 'V1\\Api\\ChatContent\\UpdateController@updateButtonInfo')->name('chatbot.content.button.update');
                                Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteButton')->name('chatbot.content.button.delete');
                                Route::put('block', 'V1\\Api\\ChatContent\\UpdateController@updateTextButtonBlock')->name('chatbot.content.button.block.update');
                                Route::delete('block', 'V1\\Api\\ChatContent\\DeleteController@deleteButtonBlock')->name('chatbot.content.button.block.delete');
                            });
                        });

                        Route::group(['prefix' => 'list'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewList')->name('chatbot.content.list.create');
                            Route::put('/{listId}', 'V1\\Api\\ChatContent\\UpdateController@updateList')->name('chatbot.content.list.item.update');
                            Route::delete('/{listId}', 'V1\\Api\\ChatContent\\DeleteController@deleteListItem')->name('chatbot.content.list.item.delete');
                            Route::post('/{listId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadListImage')->name('chatbot.content.list.image.upload');
                            Route::delete('/{listId}/image', 'V1\\Api\\ChatContent\\DeleteController@deleteListImage')->name('chatbot.content.list.image.delete');
                        });

                        Route::group(['prefix' => 'gallery'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewgallery')->name('chatbot.content.gallery.create');
                            Route::post('/order', 'V1\\Api\\ChatContent\\UpdateController@updateGalleryOrder')->name('chatbot.content.gallery.update.order');
                            Route::put('/{galleId}', 'V1\\Api\\ChatContent\\UpdateController@updateGallery')->name('chatbot.content.gallery.item.update');
                            Route::delete('/{galleId}', 'V1\\Api\\ChatContent\\DeleteController@deleteGalleryItem')->name('chatbot.content.gallery.item.delete');
                            Route::post('/{galleId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadGalleryImage')->name('chatbot.content.gallery.image.upload');
                            Route::delete('/{galleId}/image', 'V1\\Api\\ChatContent\\DeleteController@deleteGalleryImage')->name('chatbot.content.gallery.image.delete');
                        });

                        Route::group(['prefix' => 'quick-reply'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewQuickReply')->name('chatbot.content.qr.create');
                            Route::post('/order', 'V1\\Api\\ChatContent\\UpdateController@updateQuickReplyOrder')->name('chatbot.content.qr.update.order');
                            Route::put('/{qrId}', 'V1\\Api\\ChatContent\\UpdateController@updateQuickReply')->name('chatbot.content.qr.update');
                            Route::delete('/{qrId}', 'V1\\Api\\ChatContent\\DeleteController@deleteQuickReplyItem')->name('chatbot.content.qr.item.delete');
                            Route::post('/{qrId}/block', 'V1\\Api\\ChatContent\\UpdateController@addQuickReplyBlock')->name('chatbot.content.qr.block.create');
                            Route::delete('/{qrId}/block', 'V1\\Api\\ChatContent\\DeleteController@deleteQuickReplyBlock')->name('chatbot.content.qr.block.delete');
                        });
                        
                        Route::group(['prefix' => 'user-input'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewUserInput')->name('chatbot.content.ui.create');
                            Route::put('/{uiId}', 'V1\\Api\\ChatContent\\UpdateController@updateUserInput')->name('chatbot.content.ui.item.update');
                            Route::delete('/{uiId}', 'V1\\Api\\ChatContent\\DeleteController@deleteUserInputItem')->name('chatbot.content.ui.item.delete');
                        });
                        
                        Route::group(['prefix' => 'image'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\UpdateController@uploadImageImage')->name('chatbot.content.image.image.upload');
                            Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteImageImage')->name('chatbot.content.image.image.delete');
                        });
                    });
                });
            });
        });

        Route::group(['prefix' => 'broadcast'], function() {
            Route::post('/', 'V1\\Api\\BroadcastController@create');
            Route::get('/sendnow', 'V1\\Api\\BroadcastController@getSendNow');
            Route::get('/schedule', 'V1\\Api\\BroadcastController@getSchedule');
            Route::get('/trigger', 'V1\\Api\\BroadcastController@getTrigger');

            Route::group(['prefix' => 'schedule/{broadcastId}', 'middleware' => 'verifyBroadcast'], function(){
                Route::get('/', 'V1\\Api\\BroadcastController@getScheduleDetail');
                Route::post('/', 'V1\\Api\\BroadcastController@updateSchedule');
            });

            Route::group(['prefix' => 'trigger/{broadcastId}', 'middleware' => 'verifyBroadcast'], function(){
                Route::get('/', 'V1\\Api\\BroadcastController@getTriggerDetail');
                Route::post('/', 'V1\\Api\\BroadcastController@updateTrigger');
            });
            
            Route::group(['prefix' => '{broadcastId}', 'middleware' => 'verifyBroadcast'], function() {

                Route::delete('/', 'V1\\Api\\BroadcastController@deleteBroadcast');
                Route::post('/send', 'V1\\Api\\BroadcastController@publishBroadcast');
                Route::get('filters', 'V1\\Api\\BroadcastController@getFilters');
                Route::post('filters', 'V1\\Api\\BroadcastController@createFilters');
                Route::delete('filters/{filterId}', 'V1\\Api\\BroadcastController@deleteFilters');
                Route::post('filters/{filterId}', 'V1\\Api\\BroadcastController@updateFilters');
                Route::post('status', 'V1\\Api\\BroadcastController@updateStatus');
                Route::post('message-tag', 'V1\\Api\\BroadcastController@updateMessageTag');

                Route::group(['prefix' => 'section/{sectionId}/content', 'middleware' => 'verifyChatBlockSection'], function() {

                    Route::get('/', 'V1\\Api\\ChatContent\\GetController@getContents')->name('broadcast.chatbot.content.get');
                    Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createContents')->name('broadcast.chatbot.content.create');
                    Route::post('/order', 'V1\\Api\\ChatContent\\UpdateController@updateContentsOrder')->name('broadcast.chatbot.content.order');

                    Route::group(['prefix' => '{contentId}', 'middleware' => 'verifychatBlockSectionContent'], function() {
                    
                        Route::put('/', 'V1\\Api\\ChatContent\\UpdateController@updateContent')->name('broadcast.chatbot.content.update');
                        Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteContent')->name('broadcast.chatbot.content.delete');
                        Route::post('/image', 'V1\\Api\\ChatContent\\GetController@getContent')->name('broadcast.chatbot.content.image.upload');
                        Route::delete('/image', 'V1\\Api\\ChatContent\\GetController@getContents')->name('broadcast.chatbot.content.image.delete');

                        Route::group(['prefix' => 'button'], function() {
                            Route::post('text', 'V1\\Api\\ChatContent\\CreateController@createTextButton')->name('broadcast.chatbot.content.text.button.create');
                            Route::post('list', 'V1\\Api\\ChatContent\\CreateController@createListButton')->name('broadcast.chatbot.content.list.button.create');
                            Route::post('list/{listid}', 'V1\\Api\\ChatContent\\CreateController@createListItemButton')->name('broadcast.chatbot.content.list.itme.button.create');
                            Route::post('gallery/{galleryid}', 'V1\\Api\\ChatContent\\CreateController@createGalleryButton')->name('broadcast.chatbot.content.gallery.button.create');
                            Route::group(['prefix' => '{buttonid}'], function() {
                                Route::put('/', 'V1\\Api\\ChatContent\\UpdateController@updateButtonInfo')->name('broadcast.chatbot.content.button.update');
                                Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteButton')->name('broadcast.chatbot.content.button.delete');
                                Route::put('block', 'V1\\Api\\ChatContent\\UpdateController@updateTextButtonBlock')->name('broadcast.chatbot.content.button.block.update');
                                Route::delete('block', 'V1\\Api\\ChatContent\\DeleteController@deleteButtonBlock')->name('broadcast.chatbot.content.button.block.delete');
                            });
                        });

                        Route::group(['prefix' => 'list'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewList')->name('broadcast.chatbot.content.list.create');
                            Route::put('/{listId}', 'V1\\Api\\ChatContent\\UpdateController@updateList')->name('broadcast.chatbot.content.list.item.update');
                            Route::delete('/{listId}', 'V1\\Api\\ChatContent\\DeleteController@deleteListItem')->name('broadcast.chatbot.content.list.item.delete');
                            Route::post('/{listId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadListImage')->name('broadcast.chatbot.content.list.image.upload');
                            Route::delete('/{listId}/image', 'V1\\Api\\ChatContent\\DeleteController@deleteListImage')->name('broadcast.chatbot.content.list.image.delete');
                        });

                        Route::group(['prefix' => 'gallery'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewgallery')->name('broadcast.chatbot.content.gallery.create');
                            Route::put('/{galleId}', 'V1\\Api\\ChatContent\\UpdateController@updateGallery')->name('broadcast.chatbot.content.gallery.item.update');
                            Route::delete('/{galleId}', 'V1\\Api\\ChatContent\\DeleteController@deleteGalleryItem')->name('broadcast.chatbot.content.gallery.item.delete');
                            Route::post('/{galleId}/image', 'V1\\Api\\ChatContent\\UpdateController@uploadGalleryImage')->name('broadcast.chatbot.content.gallery.image.upload');
                            Route::delete('/{galleId}/image', 'V1\\Api\\ChatContent\\deleteController@deleteGalleryImage')->name('broadcast.chatbot.content.gallery.image.delete');
                        });

                        Route::group(['prefix' => 'quick-reply'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewQuickReply')->name('broadcast.chatbot.content.qr.create');
                            Route::put('/{qrId}', 'V1\\Api\\ChatContent\\UpdateController@updateQuickReply')->name('broadcast.chatbot.content.qr.update');
                            Route::delete('/{qrId}', 'V1\\Api\\ChatContent\\DeleteController@deleteQuickReplyItem')->name('broadcast.chatbot.content.qr.item.delete');
                            Route::post('/{qrId}/block', 'V1\\Api\\ChatContent\\UpdateController@addQuickReplyBlock')->name('broadcast.chatbot.content.qr.block.create');
                            Route::delete('/{qrId}/block', 'V1\\Api\\ChatContent\\DeleteController@deleteQuickReplyBlock')->name('broadcast.chatbot.content.qr.block.delete');
                        });
                        
                        Route::group(['prefix' => 'user-input'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\CreateController@createNewUserInput')->name('broadcast.chatbot.content.ui.create');
                            Route::put('/{uiId}', 'V1\\Api\\ChatContent\\UpdateController@updateUserInput')->name('broadcast.chatbot.content.ui.item.update');
                            Route::delete('/{uiId}', 'V1\\Api\\ChatContent\\DeleteController@deleteUserInputItem')->name('broadcast.chatbot.content.ui.item.delete');
                        });
                        
                        Route::group(['prefix' => 'image'], function() {
                            Route::post('/', 'V1\\Api\\ChatContent\\UpdateController@uploadImageImage')->name('broadcast.chatbot.content.image.image.upload');
                            Route::delete('/', 'V1\\Api\\ChatContent\\DeleteController@deleteImageImage')->name('broadcast.chatbot.content.image.image.delete');
                        });
                    });
                });
            });
        });

        Route::group(['prefix' => 'chat', 'middleware' => 'verifyProjectHasPage'], function() {
            Route::get('user', 'V1\\Api\\InboxController@getInboxList');
            Route::group(['prefix' => 'user/{pageUserId}', 'middleware' => 'verifyProjectPageUser'], function() {
                Route::get('load-new', 'V1\\Api\\InboxController@getNewMesg');
                Route::get('load-mesg', 'V1\\Api\\InboxController@getMesg');
                Route::post('live-chat', 'V1\\Api\\InboxController@changeLiveChatStatus');
                Route::post('urgent', 'V1\\Api\\InboxController@changeUrgentChatStatus');
                Route::post('reply', 'V1\\Api\\InboxController@sendReply');
                Route::post('fav', 'V1\\Api\\InboxController@favUser');
                Route::get('note', 'V1\\Api\\AdminNoteController@getNote');
                Route::post('note', 'V1\\Api\\AdminNoteController@createNote');
                Route::get('save-reply', 'V1\\Api\\SavedReplyController@getReply');
                Route::post('save-reply', 'V1\\Api\\SavedReplyController@createReply');
            });
        });

        Route::group(['prefix' => 'users', 'middleware' => 'verifyProjectHasPage'], function() {
            Route::get('/', 'V1\\Api\\ChatUserController@getUserList');
            Route::get('attributes', 'V1\\Api\\ChatUserController@getFilterAttributes');

            Route::group(['prefix' => 'segments'], function() {
                Route::get('/', 'V1\\Api\\SegmentController@getList');
                Route::post('/', 'V1\\Api\\SegmentController@createSegment');
                Route::post('/user-filter', 'V1\\Api\\SegmentController@createSegmentFromUserFilter');
                Route::group(['prefix' => '{segmentId}', 'middleware' => 'verifySegment'], function() {
                    Route::put('/', 'V1\\Api\\SegmentController@updateSegment');
                    Route::delete('/', 'V1\\Api\\SegmentController@deleteSegment');
                    Route::get('users', 'V1\\Api\\ChatUserController@getUsersBySegment');
                    Route::post('filters', 'V1\\Api\\SegmentController@createSingleFilter');
                    Route::get('filters', 'V1\\Api\\SegmentController@getFilters');
                    Route::group(['prefix' => 'filters/{filterId}', 'middleware' => 'verifySegmentFilter'], function() {
                        Route::delete('/', 'V1\\Api\\SegmentController@deleteFilter');
                    });
                });
            });

            Route::group(['prefix' => '{pageUserId}', 'middleware' => 'verifyProjectPageUser'], function() {
                Route::get('attributes', 'V1\\Api\\ChatUserController@getUserAttributes');
                Route::post('attributes', 'V1\\Api\\ChatUserController@createUserAttributes');
                Route::group(['prefix' => 'attributes/{attributeId}', 'middleware' => 'verifyProjectPageUserAttribute'], function() {
                    Route::delete('/', 'V1\\Api\\ChatUserController@deleteUserAttribute');
                    Route::post('name', 'V1\\Api\\ChatUserController@updateUserAttributeName');
                    Route::post('value', 'V1\\Api\\ChatUserController@updateUserAttributeValue');
                });
            });
        });

        Route::group(['prefix' => 'member'], function() {
            Route::post('/', 'V1\\Api\\ProjectController@inviteMember')->name('chatbot.project.member.invite');
            Route::get('/', 'V1\\Api\\ProjectController@getAllMembers')->name('chatbot.project.member.list');
            Route::get('/invite', 'V1\\Api\\ProjectController@getAllInvite')->name('chatbot.project.member.invite.list');
            Route::delete('/invite/{inviteId}', 'V1\\Api\\ProjectController@cancelInvite')->name('chatbot.project.member.invite.cancel');
            Route::delete('/{projectUserId}', 'V1\\Api\\ProjectController@deleteMember')->name('chatbot.project.member.delete');
        });
    });
});

Route::any('{any}/{all?}', function() {
    return response()->json([
        'status' => false,
        'code' => 404,
        'mesg' => 'Endpoint not found!'
    ], 404);
})->where('all', '.+');