<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Location;
use App\Models\Lookups\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private readonly ReferenceNumberService $references,
        private readonly EnrollmentService $enrollments,
    ) {}

    /**
     * Create the student's login, profile and slot reservations in one go.
     *
     * @param  array<string, mixed>  $data  Validated registration input
     * @return array{user: User, enrollments: Collection<int, Enrollment>}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'role' => UserRole::STUDENT,
                'is_email_notifications' => (bool) ($data['is_email_notifications'] ?? true),
                'is_sms_notifications' => (bool) ($data['is_sms_notifications'] ?? true),
            ]);

            /** @var Student $student */
            $student = $user->student()->create([
                'reference_no' => $this->references->nextStudentReference(),
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'birthdate' => $data['birthdate'] ?? null,
                'sex' => $data['sex'] ?? null,
                'education' => $data['education'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            $enrollments = $this->enrollments->createPending(
                $student,
                Location::query()->findOrFail((int) $data['location_id']),
                array_values(array_map('intval', $data['set_ids'])),
            );

            return ['user' => $user, 'enrollments' => $enrollments];
        });
    }
}
