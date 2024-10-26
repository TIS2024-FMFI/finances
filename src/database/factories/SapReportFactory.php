<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SapReport>
 */
class SapReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'path' => function (array $attributes) {
                $userId = Account::find($attributes['account_id'])->user_id;
                $userReportsDirectory = "reports/user_${userId}";
                $reportFile = UploadedFile::fake()
                                ->create('test', 0, 'text/plain');

                $filePath = Storage::putFile($userReportsDirectory, $reportFile);

                return $filePath;
            },
            'exported_or_uploaded_on' => fake()->date
        ];
    }
}
