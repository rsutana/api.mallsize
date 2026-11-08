<?php

namespace App\Models;

use App\Models\Lookups\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $mobile
 * @property string $role
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property bool $is_email_notifications
 * @property bool $is_sms_notifications
 * @property string|null $remember_token
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Student|null $student
 */
#[Fillable(['name', 'email', 'mobile', 'role', 'password', 'is_email_notifications', 'is_sms_notifications'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_email_notifications' => 'boolean',
            'is_sms_notifications' => 'boolean',
        ];
    }

    /**
     * @return HasOne<Student, $this>
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Staff-level back-office access (staff and admins).
     */
    public function isStaff(): bool
    {
        return in_array($this->role, UserRole::BACK_OFFICE, true);
    }

    /**
     * Mobile number used by the SMS notification channel.
     */
    public function routeNotificationForSms(): ?string
    {
        return $this->mobile;
    }
}
