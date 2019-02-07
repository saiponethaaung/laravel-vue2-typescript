<?php

namespace Tests\Feature\Project;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Project;
use App\Models\ProjectUser;

class ProjectStatusTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPublishProject()
    {
        factory(Project::class)->create(['user_id' => $this->user->id])->each(function($project) {
            factory(ProjectUser::class)->create([
                'project_id' => $project->id,
                'user_id' => $project->user_id
            ]);
        });
        $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])
        ->json('post', route('chatbot.project.page.publish.status'))
        ->assertStatus(true);
    }
}
