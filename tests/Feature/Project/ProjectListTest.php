<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Project;
use App\Models\ProjectUser;

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
        factory(Project::class, 10)->create(['user_id' => $this->user->id])->each(function($project) {
            factory(ProjectUser::class)->create([
                'project_id' => $project->id,
                'user_id' => $project->user_id
            ]);
        });

        $project = $this->withHeaders([
                'Authorization' => 'Bearer '.$this->token
            ])
            ->json('get', route('chatbot.project.list'), [])
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'code' => 200,
            ])
            ->assertJsonStructure([
                'status',
                'code',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'page_image',
                        'isOwner',
                        'pageConnected'
                    ]
                ]
            ]);
    }
    
    public function testGetProjectListUnauthorize()
    {
        $project = $this->withHeaders([
                'Authorization' => 'Bearer '.md5($this->token)
            ])
            ->json('get', route('chatbot.project.list'), [])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ])
            ->assertJsonStructure([
                'message'
            ]);
    }

    // public function testGetProjectInfo()
    // {
    //     $project = factory(Project::class, 10)->create(['user_id' => $this->user->id])->each(function($project) {
    //         factory(ProjectUser::class)->create([
    //             'project_id' => $project->id,
    //             'user_id' => $project->user_id
    //         ]);
    //     });

    //     $this->withHeaders([
    //         'Authorization' => 'Bearer '.md5($this->token)
    //     ]);
    // }
}
