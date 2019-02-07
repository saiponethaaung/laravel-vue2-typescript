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

    public function testGetProjectInfo()
    {
        factory(Project::class)->create(['user_id' => $this->user->id])->each(function($p) {
            factory(ProjectUser::class)->create([
                'project_id' => $p->id,
                'user_id' => $p->user_id
            ]);
        });
        $project = factory(Project::class)->create(['user_id' => $this->user->id])->each(function($p) {
            factory(ProjectUser::class)->create([
                'project_id' => $p->id,
                'user_id' => $p->user_id
            ]);
        });

        print_r($project);

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.md5($this->token)
        ])
        ->json('get', route('chatbot.project.info', [
            'projectId' => $project->id
        ]));

        print_r($featureTest);
    }
}
