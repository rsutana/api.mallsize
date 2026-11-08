<?php

namespace App\Support;

use App\Models\Lookups\BatchStatus;
use App\Models\Lookups\CourseStatus;
use App\Models\Lookups\EducationLevel;
use App\Models\Lookups\EnrollmentPaymentStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\GalleryTag;
use App\Models\Lookups\LocationType;
use App\Models\Lookups\NewsCategory;
use App\Models\Lookups\OrientationFormat;
use App\Models\Lookups\OrientationLinkMode;
use App\Models\Lookups\PaymentAmountType;
use App\Models\Lookups\PaymentMethod;
use App\Models\Lookups\PaymentStatus;
use App\Models\Lookups\PostStatus;
use App\Models\Lookups\Sex;
use App\Models\Lookups\SocialPlatform;
use App\Models\Lookups\UserRole;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Cached access to lookup tables: labels for resources, option lists for
 * the SPA, and validation rules for form requests.
 */
final class Lookups
{
    private const CACHE_KEY = 'lookups.all';

    /**
     * Lookup tables exposed to the SPA, keyed by table name.
     *
     * @var array<string, class-string<\App\Models\Lookups\LookupModel>>
     */
    public const TABLES = [
        'user_roles' => UserRole::class,
        'course_statuses' => CourseStatus::class,
        'batch_statuses' => BatchStatus::class,
        'enrollment_statuses' => EnrollmentStatus::class,
        'enrollment_payment_statuses' => EnrollmentPaymentStatus::class,
        'payment_statuses' => PaymentStatus::class,
        'payment_methods' => PaymentMethod::class,
        'payment_amount_types' => PaymentAmountType::class,
        'location_types' => LocationType::class,
        'news_categories' => NewsCategory::class,
        'post_statuses' => PostStatus::class,
        'orientation_formats' => OrientationFormat::class,
        'social_platforms' => SocialPlatform::class,
        'orientation_link_modes' => OrientationLinkMode::class,
        'sexes' => Sex::class,
        'education_levels' => EducationLevel::class,
        'gallery_tags' => GalleryTag::class,
    ];

    /**
     * @var array<string, array<string, array{code: string, label: string, sort_order: int, is_active: bool}>>|null
     */
    private static ?array $memo = null;

    /**
     * @return array<string, array<string, array{code: string, label: string, sort_order: int, is_active: bool}>>
     */
    public static function all(): array
    {
        return self::$memo ??= Cache::rememberForever(self::CACHE_KEY, function (): array {
            $out = [];

            foreach (self::TABLES as $table => $model) {
                $out[$table] = $model::query()
                    ->orderBy('sort_order')
                    ->get(['code', 'label', 'sort_order', 'is_active'])
                    ->mapWithKeys(fn ($row): array => [$row->code => [
                        'code' => $row->code,
                        'label' => $row->label,
                        'sort_order' => $row->sort_order,
                        'is_active' => $row->is_active,
                    ]])
                    ->all();
            }

            return $out;
        });
    }

    public static function label(string $table, ?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return self::all()[$table][$code]['label'] ?? $code;
    }

    /**
     * Active options of one table, in display order.
     *
     * @return list<array{code: string, label: string}>
     */
    public static function options(string $table): array
    {
        return array_values(array_map(
            fn (array $row): array => ['code' => $row['code'], 'label' => $row['label']],
            array_filter(self::all()[$table] ?? [], fn (array $row): bool => $row['is_active']),
        ));
    }

    /**
     * Validation rule: value must be an active code of the given table.
     */
    public static function rule(string $table): Exists
    {
        return Rule::exists($table, 'code')->where('is_active', true);
    }

    public static function flush(): void
    {
        self::$memo = null;
        Cache::forget(self::CACHE_KEY);
    }
}
