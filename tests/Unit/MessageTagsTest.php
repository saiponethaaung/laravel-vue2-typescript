<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Artisan;
use App\Models\Project;
use App\Models\ProjectUser;

class MessageTagsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetMessageTags()
    {
        $project = factory(Project::class)->create(['user_id' => $this->user->id]);
        $projectUser = factory(ProjectUser::class)->create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'user_type' => 1
        ]);
        
        Artisan::call('db:seed', ['--class' => 'ProjectMessageTagSeeder']);
        $unitTest = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('get', route('chatbot.project.message-tags', [
            'projectId' => $project->id
        ]))
        ->assertStatus(200)
        ->assertJson([
            'status' => true,
            'code' => 200
        ])
        ->assertJsonStructure([
            'status',
            'code',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'tag_format',
                    'mesg',
                    'notice',
                    'is_primary'
                ]
            ]
        ]);
    }
}
