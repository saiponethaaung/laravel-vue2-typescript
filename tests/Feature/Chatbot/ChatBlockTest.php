<?php

namespace Tests\Feature\Chatbot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ChatBlock;

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
}
