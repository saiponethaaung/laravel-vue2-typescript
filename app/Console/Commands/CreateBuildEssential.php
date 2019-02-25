<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use App\Models\Project;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;
use App\Models\KeywordFilterGroup;

class CreateBuildEssential extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixybots:build-essential';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing content for project. Mainly for testing project or newly added function.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::get();
        
        DB::beginTransaction();

        try {
            foreach($projects as $project) {
                $block = ChatBlock::where('is_lock', 1)
                    ->where('project_id', $project->id)
                    ->where('type', 1)
                    ->first();

                if(empty($block)) {
                    $block = ChatBlock::create([
                        'title' => 'Landing',
                        'is_lock' => true,
                        'project_id' => $project->id,
                        'type' => 1
                    ]);
                }

                $welcomeSection = ChatBlockSection::where('block_id', $block->id)
                    ->whereNull('broadcast_id')
                    ->where('title', 'Welcome')
                    ->first();

                if(empty($welcomeSection)) {
                    ChatBlockSection::create([
                        'block_id' => $block->id,
                        'broadcast_id' => null,
                        'title' => 'Welcome',
                        'order' => 1
                    ]);
                }

                $defaultSection = ChatBlockSection::where('block_id', $block->id)
                    ->whereNull('broadcast_id')
                    ->where('title', 'Default Answer')
                    ->first();

                if(empty($defaultSection)) {
                    ChatBlockSection::create([
                        'block_id' => $block->id,
                        'broadcast_id' => null,
                        'title' => 'Default Answer',
                        'order' => 2
                    ]);
                }

                $keywordFilterGroup = KeywordFilterGroup::where('project_id', $project->id)->count();

                if($keywordFilterGroup===0) {
                    KeywordFilterGroup::create([
                        'name' => 'Default group',
                        'project_id' => $project->id,
                        'created_by' => null,
                        'updated_by' => null
                    ]);
                }
            }
        } catch(\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
        }

        DB::commit();
    }
}
