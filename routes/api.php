<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\Public;
use App\Http\Controllers\Api\V1\Student;
use App\Http\Controllers\Api\V1\UserController;
use App\Models\Lookups\UserRole;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    /*
    | Public
    */
    Route::get('lookups', Public\LookupController::class)->name('lookups');
    Route::get('payment-config', Public\PaymentConfigController::class)->name('payment-config');
    Route::get('courses', [Public\CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/{course:slug}', [Public\CourseController::class, 'show'])->name('courses.show');
    Route::get('locations', [Public\LocationController::class, 'index'])->name('locations.index');
    Route::get('news', [Public\NewsController::class, 'index'])->name('news.index');
    Route::get('news/{newsPost:slug}', [Public\NewsController::class, 'show'])->name('news.show');
    Route::get('orientations', [Public\OrientationController::class, 'index'])->name('orientations.index');
    Route::get('gallery-photos', [Public\GalleryPhotoController::class, 'index'])->name('gallery-photos.index');
    Route::get('testimonials', [Public\TestimonialController::class, 'index'])->name('testimonials.index');

    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('inquiries', [Public\InquiryController::class, 'store'])->name('inquiries.store');
        Route::post('registrations', [Public\RegistrationController::class, 'store'])->name('registrations.store');
    });

    Route::post('webhooks/paymongo', Public\PayMongoWebhookController::class)->name('webhooks.paymongo');

    /*
    | Authenticated
    */
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('user', [UserController::class, 'show'])->name('user');

        Route::middleware('role:'.UserRole::STUDENT)->prefix('student')->name('student.')->group(function (): void {
            Route::get('dashboard', Student\DashboardController::class)->name('dashboard');
            Route::get('enrollments', [Student\EnrollmentController::class, 'index'])->name('enrollments.index');
            Route::post('enrollments', [Student\EnrollmentController::class, 'store'])->name('enrollments.store');
            Route::get('payments', [Student\PaymentController::class, 'index'])->name('payments.index');
            Route::post('payments', [Student\PaymentController::class, 'store'])->middleware('throttle:20,1')->name('payments.store');
            Route::get('payments/{payment}', [Student\PaymentController::class, 'show'])->name('payments.show');
            Route::patch('profile', [Student\ProfileController::class, 'update'])->name('profile.update');
        });

        Route::middleware('role:'.implode(',', UserRole::BACK_OFFICE))->prefix('admin')->name('admin.')->group(function (): void {
            Route::get('overview', Admin\OverviewController::class)->name('overview');
            Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
            Route::post('payments/{payment}/verify', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
            Route::post('payments/{payment}/reject', [Admin\PaymentController::class, 'reject'])->name('payments.reject');
            Route::get('enrollments', [Admin\EnrollmentController::class, 'index'])->name('enrollments.index');
            Route::get('enrollments/export', [Admin\EnrollmentController::class, 'export'])->name('enrollments.export');
            Route::post('balance-reminders', [Admin\BalanceReminderController::class, 'store'])->name('balance-reminders.store');
            Route::apiResource('news-posts', Admin\NewsPostController::class);
            Route::apiResource('orientations', Admin\OrientationController::class);

            Route::middleware('role:'.UserRole::ADMIN)->group(function (): void {
                Route::apiResource('courses', Admin\CourseController::class);
                Route::apiResource('locations', Admin\LocationController::class);
            });
        });
    });
});
