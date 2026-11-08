<?php

namespace App\Models\Lookups;

/**
 * User roles. Staff manage payments and content; admins also manage courses and locations.
 */
class UserRole extends LookupModel
{
    protected $table = 'user_roles';

    public const STUDENT = 'student';
    public const STAFF = 'staff';
    public const ADMIN = 'admin';

    /**
     * Roles with back-office access.
     */
    public const BACK_OFFICE = [self::STAFF, self::ADMIN];
}
