<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ChatBlock;
use App\Models\ChatBlockSection;

class ChatBlockSectionTest extends TestCase
{
    public function testCreateSection()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id
        ]);

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('post', route('chatbot.section.create', [
            'projectId' => $this->project->id,
            'blockId' => $block->id
        ]))
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'title' => 'New Section'
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'title'
            ]
        ]);
    }
 
    public function testUpdateSection()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id
        ]);

        $section = factory(ChatBlockSection::class)->create([
            'block_id' => $block->id
        ]);

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('post', route('chatbot.section.update', [
            'projectId' => $this->project->id,
            'blockId' => $block->id,
            'sectionId' => $section->id
        ]), [
            'title' => '123321',
            '_method' => 'put'
        ])
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
 
    public function testUpdateSectionEmptyTitle()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id
        ]);

        $section = factory(ChatBlockSection::class)->create([
            'block_id' => $block->id
        ]);

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('post', route('chatbot.section.update', [
            'projectId' => $this->project->id,
            'blockId' => $block->id,
            'sectionId' => $section->id
        ]), [
            'title' => '',
            '_method' => 'put'
        ])
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'The title field is required.'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
 
    public function testDeleteSection()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id
        ]);

        $section = factory(ChatBlockSection::class)->create([
            'block_id' => $block->id
        ]);

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('delete', route('chatbot.section.update', [
            'projectId' => $this->project->id,
            'blockId' => $block->id,
            'sectionId' => $section->id
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'mesg' => 'success'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
 
    public function testDeleteLockSection()
    {
        $block = factory(ChatBlock::class)->create([
            'title' => 'Landing',
            'project_id' => $this->project->id,
            'is_lock' => 1
        ]);

        $section = factory(ChatBlockSection::class)->create([
            'block_id' => $block->id
        ]);

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('delete', route('chatbot.section.update', [
            'projectId' => $this->project->id,
            'blockId' => $block->id,
            'sectionId' => $section->id
        ]))
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'Cannot delete section that is locked by parent!'
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg'
        ]);
    }
 
    public function testSearchSection()
    {
        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('get', route('chatbot.section.search', [
            'projectId' => $this->project->id,
            'keyword' => substr($this->section->title, 0, 1)
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'data' => [
                [
                    'id' => $this->block->id,
                    'title' => $this->block->title,
                    'contents' => [
                        [
                            'id' => $this->section->id,
                            'title' => $this->section->title
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
                    'id',
                    'title',
                    'contents' => [
                        '*' => [
                            'id',
                            'title'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
