<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

class ChatBlockTest extends TestCase
{
    public function testCreateChatBlock()
    {
        $featureTest = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('post', route('chatbot.block.create', [
                'projectId' => $this->project->id
            ]))
            ->assertStatus(201)
            ->assertJson([
                'status' => true,
                'code' => 201,
                'data' => [
                    'project' => md5($this->project->id),
                    'title' => 'Untitle block',
                    'lock' => false
                ]
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'data' => [
                    'id',
                    'project',
                    'title',
                    'lock'
                ]
            ]);
    }

    public function testGetAllChatBlock()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id,
            'is_lock' => 1
        ]);

        factory(ChatBlockSection::class)->create([
            'block_id' => $block->id,
            'title' => 'Welcome',
            'order' => 1
        ]);

        factory(ChatBlockSection::class)->create([
            'block_id' => $block->id,
            'title' => 'Default Answer',
            'order' => 2
        ]);

        $featureTest = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.blocks.get', [
                'projectId' => $this->project->id
            ]))
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
                'data' => [
                    [
                        'block' => [
                            'id' => $this->block->id,
                            'project' => md5($this->project->id),
                            'title' => 'Landing',
                            'lock' => true
                        ],
                        'sections' => [
                            [
                                'title' => 'Welcome'
                            ],
                            [
                                'title' => 'Default Answer'
                            ]
                        ]
                    ],
                    [
                        'block' => [
                            'id' => $block->id,
                            'project' => md5($this->project->id),
                            'title' => 'Landing',
                            'lock' => true
                        ],
                        'sections' => [
                            [
                                'title' => 'Welcome'
                            ],
                            [
                                'title' => 'Default Answer'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'data' => [
                    '*' => [
                        'block' => [
                            'id',
                            'project',
                            'title',
                            'lock'
                        ],
                        'sections' => [
                            '*' => [
                                'id',
                                'title'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function testProjectNotMember()
    {
        $project = factory(\App\Models\Project::class)->create();

        $featureTest = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.blocks.get', [
                'projectId' => $project->id
            ]))
            ->assertStatus(422)
            ->assertJson([
                'status' => false,
                'code' => 422,
                'mesg' => 'You are not a memeber of the project!'
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'mesg'
            ]);
    }

    public function testDeleteChatBlock()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id
        ]);

        factory(ChatBlockSection::class)->create([
            'block_id' => $block->id,
            'title' => 'Welcome',
            'order' => 1
        ]);

        factory(ChatBlockSection::class)->create([
            'block_id' => $block->id,
            'title' => 'Default answer',
            'order' => 2
        ]);

        $featureTest = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('delete', route('chatbot.block.delete', [
                'projectId' => $this->project->id,
                'blockId' => $block->id
            ]))
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
                'mesg' => 'Success delete'
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'mesg'
            ]);
    }

    public function testInvalidChatBlock()
    {
        $featureTest = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('delete', route('chatbot.block.delete', [
                'projectId' => $this->project->id,
                'blockId' => rand(2000,3000)
            ]))
            ->assertStatus(422)
            ->assertJson([
                'status' => false,
                'code' => 422,
                'mesg' => 'Invalid block!'
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'mesg'
            ]);
    }
}
