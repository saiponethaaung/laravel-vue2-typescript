<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

class ChatbotCreateContentTest extends TestCase
{
    public function testCreateTextContent()
    {
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'section' => $this->section->id
        ]), [
            'type' => 1
        ])
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
            'section' => $this->section->id
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

    public function testCreateQuickReplyContent()
    {
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'section' => $this->section->id
        ]), [
            'type' => 3
        ])
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

    public function testCreateUserInputContent()
    {
        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.content.create', [
            'projectId' => $this->project->id,
            'blockId' => $this->block->id,
            'section' => $this->section->id
        ]), [
            'type' => 4
        ])
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
}
