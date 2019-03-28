<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Project;
use App\Models\ProjectUser;

class ProjectCreateTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateProjectWithoutName()
    {
        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('post', route('chatbot.project.create'))
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'Project name is required!'
        ]);
    }

    public function testCreateProject()
    {
        $data = [
            'name' => 'new project'
        ];

        $featureTest = $this->withHeaders([
            'User-Agent' => $this->agent,
            'Authorization' => 'Bearer '.$this->token,
            'sessionIdentifier' => $this->identifier
        ])
        ->json('post', route('chatbot.project.create'), $data)
        ->assertStatus(201)
        ->assertJson([
            'status' => true,
            'code' => 201,
            'data' => [
                'name' => $data['name']
            ]
        ]);
    }
}
