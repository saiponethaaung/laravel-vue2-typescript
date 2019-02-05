<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectListTest extends TestCase
{
    public function testGetEmptyProjectList()
    {
        $project = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.project.list'), [])
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
                'data' => []
            ]);
    }
    
    public function testGetProjectList()
    {
        $project = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.project.list'), [])
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
                'data' => []
            ]);
    }
}
