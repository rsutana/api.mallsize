<?php

namespace Database\Seeders;

use App\Models\BatchSet;
use App\Models\Enrollment;
use App\Models\Location;
use App\Models\Lookups\EducationLevel;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentAmountType;
use App\Models\Lookups\PaymentMethod;
use App\Models\Lookups\PaymentStatus;
use App\Models\Lookups\Sex;
use App\Models\Lookups\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo students, enrollments and payments matching the design. The demo
 * student logs in as juan.delacruz@email.com / password.
 */
class DemoEnrollmentSeeder extends Seeder
{
    private int $receipt = 10480;

    public function run(): void
    {
        $panabo = Location::query()->where('name', 'Dela Peña Pork House')->firstOrFail();
        $santos = Location::query()->where('name', 'Santos Meat Shop')->firstOrFail();

        $swine13a = $this->set('Meat Processing – Swine', 'Batch 13', 'Set A');
        $swine13b = $this->set('Meat Processing – Swine', 'Batch 13', 'Set B');
        $beef6a = $this->set('Beef Contora / Deboning', 'Batch 6', 'Set A');
        $beef7a = $this->set('Beef Contora / Deboning', 'Batch 7', 'Set A');
        $poultry1a = $this->set('Poultry Dressing & Processing', 'Batch 1', 'Set A');

        $juan = $this->student('Juan', 'Dela Cruz', 'juan.delacruz@email.com', '0917 555 0148', 'MS-2026-0148');
        $this->enroll($juan, $swine13a, $panabo, [[5_000, PaymentMethod::GCASH, PaymentStatus::VERIFIED, '2026-10-20']]);
        $this->enroll($juan, $beef6a, $panabo, [[32_500, PaymentMethod::CARD, PaymentStatus::VERIFIED, '2026-10-21']]);
        $this->enroll($juan, $poultry1a, $panabo, [[4_000, PaymentMethod::MAYA, PaymentStatus::VERIFIED, '2026-10-28']]);

        $ana = $this->student('Ana', 'Reyes', 'ana.reyes@email.com', '0918 222 0131', 'MS-2026-0131');
        $this->enroll($ana, $swine13a, $santos, [[22_500, PaymentMethod::BANK_TRANSFER, PaymentStatus::VERIFIED, '2026-10-15']]);

        $mark = $this->student('Mark', 'Villanueva', 'mark.v@email.com', '0927 111 0137', 'MS-2026-0137');
        $this->enroll($mark, $beef7a, $santos, [[8_000, PaymentMethod::GCASH, PaymentStatus::VERIFIED, '2026-10-30'], [12_000, PaymentMethod::GCASH, PaymentStatus::PAID, '2026-11-02']]);

        $liza = $this->student('Liza', 'Gomez', 'liza.gomez@email.com', '0905 444 0140', 'MS-2026-0140');
        $this->enroll($liza, $poultry1a, $santos, [[4_000, PaymentMethod::MAYA, PaymentStatus::VERIFIED, '2026-10-29']]);

        $carlo = $this->student('Carlo', 'Mendoza', 'carlo.m@email.com', '0916 333 0142', 'MS-2026-0142');
        $this->enroll($carlo, $swine13b, $panabo, [[10_000, PaymentMethod::QRPH, PaymentStatus::VERIFIED, '2026-10-25'], [12_500, PaymentMethod::MAYA, PaymentStatus::PAID, '2026-11-01']]);

        $grace = $this->student('Grace', 'Tan', 'grace.tan@email.com', '0919 666 0145', 'MS-2026-0145');
        $this->enroll($grace, $beef6a, $santos, [[32_500, PaymentMethod::CARD, PaymentStatus::VERIFIED, '2026-10-18']]);

        // Fill the remaining taken slots shown in the design.
        foreach ([[$swine13a, 12], [$swine13b, 5], [$beef6a, 13], [$beef7a, 2]] as [$set, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $student = Student::factory()->create(['reference_no' => 'MS-2026-'.str_pad((string) (200 + Student::query()->count()), 4, '0', STR_PAD_LEFT)]);
                $dp = $set->batch->course->min_down_payment / 100;
                $this->enroll($student, $set, $santos, [[$dp, PaymentMethod::GCASH, PaymentStatus::VERIFIED, '2026-10-10']]);
            }
        }
    }

    private function set(string $course, string $batchPrefix, string $set): BatchSet
    {
        return BatchSet::query()
            ->where('name', $set)
            ->whereHas('batch', fn ($q) => $q->where('name', 'like', $batchPrefix.' —%')->whereHas('course', fn ($c) => $c->where('name', $course)))
            ->with('batch.course')
            ->firstOrFail();
    }

    private function student(string $first, string $last, string $email, string $mobile, string $reference): Student
    {
        $user = User::query()->updateOrCreate(['email' => $email], [
            'name' => $first.' '.$last,
            'mobile' => $mobile,
            'role' => UserRole::STUDENT,
            'password' => 'password',
        ]);

        return Student::query()->updateOrCreate(['user_id' => $user->id], [
            'reference_no' => $reference,
            'first_name' => $first,
            'last_name' => $last,
            'birthdate' => '1998-03-14',
            'sex' => Sex::MALE,
            'education' => EducationLevel::SENIOR_HIGH_SCHOOL,
            'address' => '[Complete address], Davao City, Davao del Sur',
        ]);
    }

    /**
     * @param  list<array{0: int|float, 1: string, 2: string, 3: string}>  $payments  [pesos, method, status, date]
     */
    private function enroll(Student $student, BatchSet $set, Location $location, array $payments): void
    {
        $course = $set->batch->course;

        $enrollment = Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'batch_id' => $set->batch_id,
            'batch_set_id' => $set->id,
            'location_id' => $location->id,
            'fee' => $course->fee,
            'min_down_payment' => $course->min_down_payment,
            'status' => EnrollmentStatus::ENROLLED,
            'enrolled_at' => $payments[0][3],
        ]);

        $paid = 0;
        foreach ($payments as [$pesos, $method, $status, $date]) {
            $amount = (int) round($pesos * 100);
            $type = match (true) {
                $paid === 0 && $amount >= $course->fee => PaymentAmountType::FULL,
                $paid === 0 => PaymentAmountType::DOWN_PAYMENT,
                $paid + $amount >= $course->fee => PaymentAmountType::BALANCE,
                default => PaymentAmountType::PARTIAL,
            };

            $payment = $student->payments()->create([
                'reference_no' => 'MS-RC-'.$this->receipt++,
                'amount' => $amount,
                'method' => $method,
                'status' => $status,
                'provider' => 'fake',
                'provider_intent_id' => 'seed_'.$this->receipt,
                'paid_at' => $date,
                'verified_at' => $status === PaymentStatus::VERIFIED ? $date : null,
            ]);
            $payment->forceFill(['created_at' => $date])->save();

            $payment->allocations()->create(['enrollment_id' => $enrollment->id, 'amount' => $amount, 'amount_type' => $type]);
            $paid += $amount;
        }
    }
}
