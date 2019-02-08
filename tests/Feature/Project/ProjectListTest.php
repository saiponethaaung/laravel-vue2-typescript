<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectPage;

class ProjectListTest extends TestCase
{
    public function testGetEmptyProjectList()
    {
        $featureTest = $this->withHeaders([
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

        $featureTest = $this->withHeaders([
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
        $featureTest = $this->withHeaders([
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
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'user_type' => 0
        ]);

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('get', route('chatbot.project.info', [
            'projectId' => $project->id
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => md5($project->id),
                'name' => $project->name,
                'image' => '',
                'isOwner' => true,
                'pageId' => config('facebook.defaultPageId'),
                'testingPageId' => config('facebook.defaultPageId'),
                'pageConnected' => false,
                'publish' => false
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'name',
                'image',
                'isOwner',
                'pageId',
                'testingPageId',
                'pageConnected',
                'publish'
            ]
        ]);
    }

    public function testGetProjectInfoNotOwner()
    {
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'user_type' => 1
        ]);

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('get', route('chatbot.project.info', [
            'projectId' => $project->id
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'data' => [
                'id' => md5($project->id),
                'name' => $project->name,
                'image' => '',
                'isOwner' => false,
                'pageId' => config('facebook.defaultPageId'),
                'testingPageId' => config('facebook.defaultPageId'),
                'pageConnected' => false,
                'publish' => false
            ]
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                'id',
                'name',
                'image',
                'isOwner',
                'pageId',
                'testingPageId',
                'pageConnected',
                'publish'
            ]
        ]);
    }

    public function testChangeProjectStatus()
    {
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'user_type' => 1
        ]);
        $projectPage = factory(ProjectPage::class)->create([
            'project_id' => $project->id
        ]);

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.project.page.publish.status', [
            'projectId' => $project->id
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200,
            'mesg' => 'Success',
            'publishStatus' => true
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg',
            'publishStatus',
        ]);
    }

    public function testChangeProjectStatusNoPage()
    {
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'user_type' => 1
        ]);

        $featureTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.project.page.publish.status', [
            'projectId' => $project->id
        ]))
        ->assertStatus(422)
        ->assertJson([
            'status' => false,
            'code' => 422,
            'mesg' => 'This project didn\'t have a page linked! Refresh a page to get up to date data!',
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'mesg',
        ]);
    }
}
