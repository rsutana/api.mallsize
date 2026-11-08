<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('enrollments:expire-pending')->everyFiveMinutes()->withoutOverlapping();
