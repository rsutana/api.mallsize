<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PayMongoGateway;
use App\Services\Sms\LogSmsClient;
use App\Services\Sms\SemaphoreSmsClient;
use App\Services\Sms\SmsClient;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function (): PaymentGateway {
            $config = config('mallsize.payments');

            return match ($config['driver']) {
                'paymongo' => new PayMongoGateway(
                    (string) $config['paymongo']['secret_key'],
                    $config['paymongo']['public_key'],
                    $config['paymongo']['webhook_secret'],
                    (string) $config['paymongo']['base_url'],
                ),
                default => new FakeGateway,
            };
        });

        $this->app->singleton(SmsClient::class, function (): SmsClient {
            $config = config('mallsize.sms');

            return match ($config['driver']) {
                'semaphore' => new SemaphoreSmsClient(
                    (string) $config['semaphore']['api_key'],
                    (string) $config['semaphore']['sender_name'],
                    (string) $config['semaphore']['base_url'],
                ),
                default => new LogSmsClient,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        ResetPassword::createUrlUsing(fn (User $user, string $token): string => rtrim((string) config('app.frontend_url'), '/')
            .'/reset-password?'.http_build_query(['token' => $token, 'email' => $user->email]));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : Password::min(8),
        );
    }
}
