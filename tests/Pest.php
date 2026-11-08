<?php

use App\Models\Batch;
use App\Models\BatchSet;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * An open course (fee ₱22,500 / min. down ₱5,000) with one batch and one set.
 *
 * @param  array<string, mixed>  $set
 * @param  array<string, mixed>  $course
 */
function makeSet(array $set = [], array $course = []): BatchSet
{
    $course = Course::factory()->create($course);
    $batch = Batch::factory()->for($course)->create();

    return BatchSet::factory()->for($batch)->create($set)->load('batch.course');
}

function makeStudent(): Student
{
    return Student::factory()->create();
}

function staffUser(): User
{
    return User::factory()->staff()->create();
}

function adminUser(): User
{
    return User::factory()->admin()->create();
}

/**
 * Headers that make Sanctum treat a test request as coming from the SPA.
 *
 * @return array<string, string>
 */
function spaHeaders(): array
{
    return ['Origin' => 'http://localhost:3000', 'Referer' => 'http://localhost:3000/', 'Accept' => 'application/json'];
}
