<?php

use Illuminate\Database\Seeder;

use DB;

use App\Models\ProjectMessageTag;

class ProjectMessageTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            [
                'name' => 'UPDATE',
                'tag_format' => 'UPDATE',
                'mesg' => 'Message may include promotional and non-promotional content and will be delivered only to those users who are inside the <a href="https://developers.facebook.com/docs/messenger-platform/policy-overview#24hours_window">24-hour standard messaging window</a> in accordance with the \'24+1 policy\'.'
            ]
        ];
    }
}
