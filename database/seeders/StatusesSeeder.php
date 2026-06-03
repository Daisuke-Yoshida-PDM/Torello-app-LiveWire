<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['未着手', '進行中', '完了',  '保留', 'キャンセル'];

        foreach($names as $name) {
            Status::firstOrCreate(['name' => $name]);
        }
    }
}
