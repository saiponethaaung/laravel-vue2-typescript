<?php

use Illuminate\Database\Seeder;

use App\Models\ChatAttribute;

class SystemAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attributes = [
            'first name',
            'last name',
            'gender',
            'singed up',
            'last seen'
        ];

        foreach ($attributes as $attribute) {
            ChatAttribute::create([
                'attribute' => $attribute,
                'type' => 0,
                'is_system' => 1
            ]);
        }
    }
}
