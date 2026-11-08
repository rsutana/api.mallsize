<?php

namespace App\Console\Commands;

use App\Services\EnrollmentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('enrollments:expire-pending')]
#[Description('Release slot reservations whose down payment was never paid')]
class ExpirePendingEnrollments extends Command
{
    public function handle(EnrollmentService $enrollments): int
    {
        $count = $enrollments->expireStale();

        $this->info("Expired {$count} pending enrollment(s).");

        return self::SUCCESS;
    }
}
