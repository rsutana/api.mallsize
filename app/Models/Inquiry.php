<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Message sent from the public contact form.
 *
 * @property int $id
 * @property string $name
 * @property string|null $mobile
 * @property string $email
 * @property string $message
 */
#[Fillable(['name', 'mobile', 'email', 'message'])]
class Inquiry extends Model {}
