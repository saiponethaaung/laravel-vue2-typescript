<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

class ChatbotCreateContentTest extends TestCase
{
    public function createTextContentRequest()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 1
        ]);
    }
    public function testCreateTextContent()
    {
        $featureTest = $this->createTextContentRequest()
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 1,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    'text' => 'Text Content',
                    'button' => []
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    'text',
                    'button'
                ]
            ]
        ]);
    }

    public function testCreateTypingContent()
    {
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 2
        ])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 2,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    'duration' => 1
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    'duration'
                ]
            ]
        ]);
    }

    public function createQuickReplyRequest()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 3
        ]);
    }

    public function testCreateQuickReplyContent()
    {
        $featureTest = $this->createQuickReplyRequest()
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 3,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    [
                        'title' => '',
                        'attribute' => [
                            'title' => '',
                            'value' => ''
                        ],
                        'block' => []
                    ]
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    [
                        'id',
                        'title',
                        'attribute' => [
                            'id',
                            'title',
                            'value'
                        ],
                        'content_id',
                        'block'
                    ]
                ]
            ]
        ]);
    }

    public function testCreateSubQuickReplyContent()
    {
        $quickReply = json_decode($this->createQuickReplyRequest()->getContent());
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.qr.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $quickReply->data->id
        ]))
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'title' => '',
                'attribute' => [
                    'title' => '',
                    'value' => ''
                ],
                'block' => []
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'title',
                'attribute' => [
                    'id',
                    'title',
                    'value'
                ],
                'content_id',
                'block'
            ]
        ]);
    }

    public function testCreateSubQuickReplyContentOverMax()
    {
        $quickReply = json_decode($this->createQuickReplyRequest()->getContent());

        for($count=0; $count<10; $count++) {
            $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('post', route('chatbot.content.qr.create', [
                'projectId' => $this->project->id,
                'blockId' => $this->block->id,
                'sectionId' => $this->section->id,
                'contentId' => $quickReply->data->id
            ]));
        }

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.qr.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $quickReply->data->id
        ]))
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'Quick replay already created at max!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }

    public function createUserInputRequest()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 4
        ]);
    }

    public function testCreateUserInputContent()
    {
        $featureTest = $this->createUserInputRequest()
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 4,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    [
                        'question' => '',
                        'attribute' => [
                            'title' => ''
                        ],
                        'validation' => 0
                    ]
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    [
                        'id',
                        'question',
                        'attribute' => [
                            'id',
                            'title'
                        ],
                        'content_id',
                        'validation'
                    ]
                ]
            ]
        ]);
    }

    public function createListRequest()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 5
        ]);
    }

    public function testCreateListContent()
    {
        $featureTest = $this->createListRequest()
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 5,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    'content' => [
                        [
                            'title' => '',
                            'sub' => '',
                            'image' => '',
                            'url' => '',
                            'button' => null
                        ],
                    ],
                    'button' => null
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    'content' => [
                        [
                            'id',
                            'title',
                            'sub',
                            'image',
                            'url',
                            'content_id',
                            'button'
                        ],
                    ],
                    'button'
                ]
            ]
        ]);
    }

    public function testCreateSubListContent()
    {
        $list = json_decode($this->createListRequest()->getContent());
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.list.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $list->data->id
        ]))
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'mesg' => 'Success',
            'content' => [
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'button' => null
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg',
            'content' => [
                'id',
                'title',
                'sub',
                'image',
                'url',
                'content_id',
                'button'
            ]
        ]);
    }

    public function createGalleryRequest()
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 6
        ]);
    }

    public function testCreateGalleryContent()
    {
        $featureTest = $this->createGalleryRequest()
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 6,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    [
                        'title' => '',
                        'sub' => '',
                        'image' => '',
                        'url' => '',
                        'button' => []
                    ]
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    [
                        'id',
                        'title',
                        'sub',
                        'image',
                        'url',
                        'content_id',
                        'button'
                    ]
                ]
            ]
        ]);
    }

    public function testCreateSubGalleryContent()
    {
        $gallery = json_decode($this->createGalleryRequest()->getContent());
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.gallery.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $gallery->data->id
        ]))
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'mesg' => 'Success',
            'content' => [
                'title' => '',
                'sub' => '',
                'image' => '',
                'url' => '',
                'button' => []
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg',
            'content' => [
                'id',
                'title',
                'sub',
                'image',
                'url',
                'content_id',
                'button'
            ]
        ]);
    }

    public function testCreateImageContent()
    {
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id
        ]), [
            'type' => 7
        ])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'type' => 7,
                'section_id' => $this->section->id,
                'block_id' => $this->block->id,
                'broadcast_id' => null,
                'project' => md5($this->project->id),
                'content' => [
                    [
                        'image' => '',
                    ]
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'type',
                'section_id',
                'block_id',
                'broadcast_id',
                'project',
                'content' => [
                    [
                        'image',
                    ]
                ]
            ]
        ]);
    }

    public function testCreateNewUserInput()
    {
        $userInput = json_decode($this->createUserInputRequest()->getContent(), true);
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.ui.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $userInput['data']['id']
        ]))
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'question' => '',
                'attribute' => [
                    'title' => '',
                ],
                'content_id' => (int) $userInput['data']['content'][0]['id'],
                'validation' => 0
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'question',
                'attribute' => [
                    'id',
                    'title',
                ],
                'content_id',
                'validation'
            ]
        ]);
    }

    public function createTextButton($textContentId)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.text.button.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $textContentId
        ]));
    }

    public function testCreateTextButton()
    {
        $textContent = json_decode($this->createTextContentRequest()->getContent(), true);

        $featureTest = $this->createTextButton($textContent['data']['id'])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'button' => [
                'type' => 0,
                'title' => '',
                'block' => [],
                'url' => '',
                'phone' => [
                    'countryCode' => 95,
                    'number' => null
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'button' => [
                'id',
                'type',
                'title',
                'block',
                'url',
                'phone' => [
                    'countryCode',
                    'number',
                ]
            ]
        ]);
    }

    public function testCreateTextButtonOverThree()
    {
        $textContent = json_decode($this->createTextContentRequest()->getContent(), true);

        $this->createTextButton($textContent['data']['id']);
        $this->createTextButton($textContent['data']['id']);
        $this->createTextButton($textContent['data']['id']);

        $featureTest = $this->createTextButton($textContent['data']['id'])
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'Text button at it\'s limit!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
    
    public function createGalleryButton($galleryContentId, $galleryId)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.gallery.button.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'sectionId' => $this->section->id,
            'contentId' => $galleryContentId,
            'galleryId' => $galleryId
        ]));
    }

    public function testCreateGalleryButton()
    {
        $galleryContent = json_decode($this->createGalleryRequest()->getContent(), true);

        $featureTest = $this->createGalleryButton($galleryContent['data']['id'], $galleryContent['data']['content'][0]['id'])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'button' => [
                'type' => 0,
                'title' => '',
                'block' => [],
                'url' => '',
                'phone' => [
                    'countryCode' => 95,
                    'number' => null
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'button' => [
                'id',
                'type',
                'title',
                'block',
                'url',
                'phone' => [
                    'countryCode',
                    'number',
                ]
            ]
        ]);
    }

    public function testCreateGalleryButtonOverThree()
    {
        $galleryContent = json_decode($this->createGalleryRequest()->getContent(), true);

        $this->createGalleryButton($galleryContent['data']['id'], $galleryContent['data']['content'][0]['id']);
        $this->createGalleryButton($galleryContent['data']['id'], $galleryContent['data']['content'][0]['id']);
        $this->createGalleryButton($galleryContent['data']['id'], $galleryContent['data']['content'][0]['id']);

        $featureTest = $this->createGalleryButton($galleryContent['data']['id'], $galleryContent['data']['content'][0]['id'])
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'Gallery button at it\'s limit!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
    
    public function createListButton($listContentId, $listId)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.list.button.create', [
                'projectId' => $this->project->id,
                'blockId' => $this->block->id,
                'sectionId' => $this->section->id,
                'contentId' => $listContentId
            ]));
    }

    public function testCreateListButton()
    {
        $listContent = json_decode($this->createListRequest()->getContent(), true);

        // print_r($listContent);
        $featureTest = $this->createListButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id'])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'button' => [
                'type' => 0,
                'title' => '',
                'block' => [],
                'url' => '',
                'phone' => [
                    'countryCode' => 95,
                    'number' => null
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'button' => [
                'id',
                'type',
                'title',
                'block',
                'url',
                'phone' => [
                    'countryCode',
                    'number',
                ]
            ]
        ]);
    }

    public function testCreateListButtonOverThree()
    {
        $listContent = json_decode($this->createListRequest()->getContent(), true);

        $this->createListButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id']);

        $featureTest = $this->createListButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id'])
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'List button at it\'s limit!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
    
    public function createListItemButton($listContentId, $listId)
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.list.itme.button.create', [
                'projectId' => $this->project->id,
                'blockId' => $this->block->id,
                'sectionId' => $this->section->id,
                'contentId' => $listContentId,
                'galleryId' => $listId
            ]));
    }

    public function testCreateListItemButton()
    {
        $listContent = json_decode($this->createListRequest()->getContent(), true);

        // print_r($listContent);
        $featureTest = $this->createListItemButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id'])
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'button' => [
                'type' => 0,
                'title' => '',
                'block' => [],
                'url' => '',
                'phone' => [
                    'countryCode' => 95,
                    'number' => null
                ]
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'button' => [
                'id',
                'type',
                'title',
                'block',
                'url',
                'phone' => [
                    'countryCode',
                    'number',
                ]
            ]
        ]);
    }

    public function testCreateListItemButtonOverThree()
    {
        $listContent = json_decode($this->createListRequest()->getContent(), true);

        $this->createListItemButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id']);

        $featureTest = $this->createListItemButton($listContent['data']['id'], $listContent['data']['content']['content'][0]['id'])
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'List item button at it\'s limit!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
}
