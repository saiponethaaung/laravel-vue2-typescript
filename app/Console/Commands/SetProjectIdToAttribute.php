<?php

namespace App\Console\Commands;

use DB;

use Illuminate\Console\Command;
use App\Models\ChatAttribute;

class SetProjectIdToAttribute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixybots:setup-attribute-project-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create projet id to non system attribute.';

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
        try {

            $attributes = ChatAttribute::with(
                [
                    'chatValue',
                    'chatValue.user',
                    'chatValue.user.projectPage',
                    'chatUserInput',
                    'chatUserInput.content',
                    'chatUserInput.content.section',
                    'chatUserInput.content.section.block',
                    'chatQuickReply',
                    'chatQuickReply.content',
                    'chatQuickReply.content.section',
                    'chatQuickReply.content.section.block',
                    'broadcastTrigger',
                    'broadcastTrigger.projectBroadcast'
                ]
            )->where('is_system', 0)->get();
            
            foreach($attributes as $attribute) {
                // handle project page user attribute
                foreach($attribute->chatValue as $cv) {
                    if($cv->user && $cv->user->projectPage && $cv->user->projectPage->project_id) {
                        $validate = $this->chatAttributeHandler($attribute, $cv->user->projectPage->project_id);
                        $cv->attribute_id = $validate;
                        $cv->save();
                        print_r('chat attribute value set');
                        print_r("\n");
                    }
                }
                
                //handler chat user input
                foreach($attribute->chatUserInput as $cui) {
                    $validate = $this->chatUserInputAttributeHandler($attribute, $cui->content->section->block->project_id, in_array($cui->validation, [0, 1, 2]) ? $cui->validation : 0);
                    $cui->attribute_id = $validate;
                    $cui->save();
                    print_r('user input value set');
                    print_r("\n");
                }
                
                // handler for quick reply
                foreach($attribute->chatQuickReply as $cqr) {
                    $validate = $this->chatAttributeHandler($attribute, $cqr->content->section->block->project_id);
                    $cqr->attribute_id = $validate;
                    $cqr->save();
                    print_r('quick reply value set');
                    print_r("\n");
                }

                foreach($attribute->broadcastTrigger as $bt) {
                    if($bt->chat_attribute_id) {
                        $validate = $this->chatAttributeHandler($attribute, $bt->projectBroadcast->project_id);
                        $bt->chat_attribute_id = $validate;
                        $bt->save();
                        print_r('Trigger value set');
                        print_r("\n");
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            print_r("\n");
            print_r($e->getTraceAsString());
            print_r("\n");
            print_r('process failed');
        }
        DB::commit();
        // print_r($attributes->toArray());
        print_r("\n");

        print_r("hello\n");
    }

    private function chatAttributeHandler($attribute, $targetProject)
    {
        $attributeId = $attribute->id;
        // check project id is null on chat attribute
        if(is_null($attribute->project_id)) {
            // set target project id
            $attribute->project_id = $targetProject;
            $attribute->save();
        } else {
            // check target project id and attribute project id are matched
            if($attribute->project_id!==$targetProject) {
                // if it is not match check chat attribute with target project id already exists
                $attr = ChatAttribute::where(DB::raw('attribute COLLATE utf8mb4_bin'), 'LIKE', $attribute->attribute.'%')
                    ->where('project_id', $targetProject)
                    ->first();
                // create new attribute if it's not yet exists
                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $attribute->attribute,
                        'type' => $attribute->type,
                        'is_system' => 0,
                        'project_id' => $targetProject
                    ]);
                }
                $attributeId = $attr->id;
            }
        }
        return $attributeId;
    }

    private function chatUserInputAttributeHandler($attribute, $targetProject, $type)
    {
        $attributeId = $attribute->id;
        if(is_null($attribute->project_id)) {
            $attribute->project_id = $targetProject;
            $attribute->save();
        } else {
            if($attribute->project_id!==$targetProject) {
                $attr = ChatAttribute::where(DB::raw('attribute COLLATE utf8mb4_bin'), 'LIKE', $attribute->attribute.'%')
                    ->where('project_id', $targetProject)
                    ->first();
                if(empty($attr)) {
                    $attr = ChatAttribute::create([
                        'attribute' => $attribute->attribute,
                        'type' => $attribute->type,
                        'is_system' => 0,
                        'project_id' => $targetProject
                    ]);
                } else {
                    if($attr->type!==$type) {
                        $attr->type = $type;
                        $attr->save();
                    }
                }
                $attributeId = $attr->id;
            }
        }
        return $attributeId;
    }
}
