<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hanya jalankan jika ada user pertama (untuk demo/development)
        $user = User::first();

        if (! $user) {
            return;
        }

        $categories = [
            // Pemasukan
            ['name' => 'Gaji',       'type' => 'income'],
            ['name' => 'Freelance',  'type' => 'income'],
            ['name' => 'Investasi',  'type' => 'income'],
            ['name' => 'Bonus',      'type' => 'income'],
            // Pengeluaran
            ['name' => 'Makanan',    'type' => 'expense'],
            ['name' => 'Transport',  'type' => 'expense'],
            ['name' => 'Tagihan',    'type' => 'expense'],
            ['name' => 'Kesehatan',  'type' => 'expense'],
            ['name' => 'Hiburan',    'type' => 'expense'],
            ['name' => 'Belanja',    'type' => 'expense'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $cat['name']],
                ['type' => $cat['type']]
            );
        }
    }
}
