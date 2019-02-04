<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectListTest extends TestCase
{
    public function testGetProjectList()
    {
        $project = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.project.list'), [])
        // print_r($project->getContent());
            ->assertStatus(200);
    }
}
