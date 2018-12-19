<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ChatBlock;
use App\Models\ChatBlockSection;
use App\Models\ChatBlockSectionContent;
use App\Models\ChatButton;
use App\Models\ChatButtonBlock;
use App\Models\ChatGallery;
use App\Models\ChatQuickReply;
use App\Models\ChatQuickReplyBlock;
use App\Models\ChatUserInput;

class ChatBotProjectController extends Controller
{
    private $projectid = null;

    public function __construct($projectid)
    {
        parent::__construct();
        $this->projectid = $projectid;
    }

    public function process($input=null, $payload=true)
    {
        
    }

    public function aiValidation($keyword='')
    {

    }

    public function getDefault()
    {
        $block = ChatBlock::where('project_id', $this->projectid)->where('is_lock', false)->first();
        $section = ChatBlockSection::with('contents')->where('block_id', $block->id)->where('type', 2)->first();

        return [
            'status' => true,
            'data' => $this->contentParser($section->contents)
        ];
    }
    
    public function getGreeting()
    {
        
    }

    public function contentParser($contents)
    {
        
    }

    public function parseText()
    {

    }

    public function parseTyping()
    {

    }

    public function parseQuickReply()
    {

    }

    public function parseList()
    {

    }

    public function parseGallery()
    {

    }

    public function parseImage()
    {

    }
}
