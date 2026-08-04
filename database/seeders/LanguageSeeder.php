<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::query()->updateOrCreate(
            [
                'code' => 'en',
            ],
            [
                'name' => 'ENGLISH',
                'native_name' => 'English',
                'is_rtl' => false,
                'status' => 'Active',
                'is_default_admin' => true,
                'is_default_site' => true,
                'is_required' => true,
                'sort_order' => 1,
            ]
        );

        Language::query()->updateOrCreate(
            [
                'code' => 'az',
            ],
            [
                'name' => 'AZERBAIJANI',
                'native_name' => 'Azərbaycan',
                'is_rtl' => false,
                'status' => 'Active',
                'is_default_admin' => false,
                'is_default_site' => false,
                'is_required' => false,
                'sort_order' => 2,
            ]
        );

        Language::query()->updateOrCreate(
            [
                'code' => 'ru',
            ],
            [
                'name' => 'RUSSIAN',
                'native_name' => 'Русский',
                'is_rtl' => false,
                'status' => 'Active',
                'is_default_admin' => false,
                'is_default_site' => false,
                'is_required' => false,
                'sort_order' => 3,
            ]
        );
    }
}
