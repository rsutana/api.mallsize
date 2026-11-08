<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Not wrapped in "data": nuxt-auth-sanctum reads the identity object as-is.
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @var string|null
     */
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'role_label' => Lookups::label('user_roles', $this->role),
            'is_email_notifications' => $this->is_email_notifications,
            'is_sms_notifications' => $this->is_sms_notifications,
            'student' => new StudentResource($this->whenLoaded('student')),
        ];
    }
}
