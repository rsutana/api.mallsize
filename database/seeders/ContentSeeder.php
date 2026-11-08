<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\GalleryPhoto;
use App\Models\Lookups\GalleryTag;
use App\Models\Lookups\NewsCategory;
use App\Models\Lookups\OrientationFormat;
use App\Models\Lookups\OrientationLinkMode;
use App\Models\Lookups\PostStatus;
use App\Models\Lookups\SocialPlatform;
use App\Models\NewsPost;
use App\Models\NotificationLog;
use App\Models\Orientation;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * News, orientations, gallery and reviews from the design (placeholders kept).
 */
class ContentSeeder extends Seeder
{
    private const LOREM = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.\n\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\n\nInteger in mauris eu nibh euismod gravida. Duis ac tellus et risus vulputate vehicula. Donec lobortis risus a elit.";

    public function run(): void
    {
        $news = [
            ['Enrollment now open for December batches', NewsCategory::ENROLLMENT, 'Reserve your slot in Meat Processing – Swine and Beef Contora / Deboning with a down payment.', '2026-11-02', PostStatus::PUBLISHED, true],
            ['Batch 12 completes TESDA assessment', NewsCategory::GRADUATION, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae justo eget magna fermentum iaculis eu non diam phasellus.', '2026-10-24', PostStatus::PUBLISHED, false],
            ['New Beef Contora / Deboning schedule', NewsCategory::ANNOUNCEMENT, 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.', '2026-10-15', PostStatus::PUBLISHED, false],
            ['Open house at the training center', NewsCategory::EVENT, 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi.', '2026-10-05', PostStatus::PUBLISHED, false],
            ['Holiday schedule for the training center office', NewsCategory::ANNOUNCEMENT, '[Office hours during the holidays.]', '2026-12-15', PostStatus::SCHEDULED, false],
        ];

        foreach ($news as [$title, $category, $summary, $date, $status, $featured]) {
            NewsPost::query()->updateOrCreate(['title' => $title], [
                'category' => $category, 'summary' => $summary, 'body' => self::LOREM,
                'published_on' => $date, 'status' => $status, 'is_featured' => $featured,
            ]);
        }

        $swine = Course::query()->where('name', 'Meat Processing – Swine')->value('id');
        $beef = Course::query()->where('name', 'Beef Contora / Deboning')->value('id');

        $orientations = [
            ['Online orientation for new applicants', null, 'Learn about courses, fees and the enrollment process.', '2026-11-15', '7:00 PM – 8:00 PM', OrientationFormat::FACEBOOK_LIVE, 'MallSize Training Center Facebook page', true, SocialPlatform::FACEBOOK, 'https://www.facebook.com/', OrientationLinkMode::DIRECT, PostStatus::PUBLISHED],
            ['Pre-training orientation — December Swine batch', $swine, 'For enrolled students of the Dec 1 batch. Bring a valid ID, closed shoes and a notebook.', '2026-11-28', '9:00 AM – 11:00 AM', OrientationFormat::ON_SITE, '[Training room, MallSize Training Center]', true, SocialPlatform::FACEBOOK, 'https://www.facebook.com/', OrientationLinkMode::DETAILS, PostStatus::PUBLISHED],
            ['Pre-training orientation — December Beef batch', $beef, 'For enrolled students of the Dec 8 batch. Bring a valid ID, closed shoes and a notebook.', '2026-12-05', '9:00 AM – 11:00 AM', OrientationFormat::ON_SITE, '[Training room, MallSize Training Center]', false, null, null, OrientationLinkMode::DETAILS, PostStatus::PUBLISHED],
        ];

        foreach ($orientations as [$title, $courseId, $details, $date, $time, $format, $venue, $linked, $platform, $url, $mode, $status]) {
            Orientation::query()->updateOrCreate(['title' => $title], [
                'course_id' => $courseId, 'details' => $details, 'date' => $date, 'time_label' => $time,
                'format' => $format, 'venue' => $venue, 'is_social_linked' => $linked, 'social_platform' => $platform,
                'social_url' => $url, 'link_mode' => $mode, 'status' => $status, 'is_notify_students' => false,
            ]);
        }

        $photos = [
            ['Our trainers in Mall Size Butchery uniforms', GalleryTag::TEAM, '/images/hero-banner.png'],
            ['Swine breakdown – Batch 12', GalleryTag::TRAINING, null],
            ['Beef deboning practicals', GalleryTag::TRAINING, null],
            ['Batch 11 graduation', GalleryTag::GRADUATION, null],
            ['Cold room & cutting floor', GalleryTag::FACILITY, null],
            ['Knife skills session', GalleryTag::TRAINING, null],
            ['TESDA assessment day', GalleryTag::GRADUATION, null],
        ];

        foreach ($photos as $i => [$title, $tag, $path]) {
            GalleryPhoto::query()->updateOrCreate(['title' => $title], ['tag' => $tag, 'image_path' => $path, 'sort_order' => $i]);
        }

        $reviews = [
            ['[Student review — replace with a real testimonial from a graduate, e.g. what they learned and where they work now.]', 'Meat Processing – Swine · Batch [#]', 5],
            ['[Student review — replace with a real testimonial about the trainers and hands-on practice.]', 'Beef Contora / Deboning · Batch [#]', 5],
            ['[Student review — replace with a real testimonial about the enrollment and payment experience.]', 'Meat Processing – Swine · Batch [#]', 4],
            ['[Student review — replace with a real testimonial.]', 'Beef Contora / Deboning · Batch [#]', 5],
            ['[Student review — replace with a real testimonial.]', '[Course name] · Batch [#]', 5],
            ['[Student review — replace with a real testimonial.]', '[Course name] · Batch [#]', 5],
        ];

        foreach ($reviews as $i => [$quote, $course, $rating]) {
            Testimonial::query()->updateOrCreate(['sort_order' => $i], [
                'quote' => $quote, 'student_name' => '[Student name]', 'course_label' => $course, 'rating' => $rating, 'is_published' => true,
            ]);
        }

        NotificationLog::query()->firstOrCreate(['title' => 'Payment verified — Ana Reyes ₱22,500'], ['channels' => 'Email + SMS', 'recipients_count' => 1, 'sent_at' => '2026-10-15 10:00:00']);
        NotificationLog::query()->firstOrCreate(['title' => 'Payment verified — Grace Tan ₱32,500'], ['channels' => 'Email + SMS', 'recipients_count' => 1, 'sent_at' => '2026-10-18 10:00:00']);
    }
}
