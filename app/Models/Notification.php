<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 *
 * @property int $id
 * @property int $employee_id
 * @property string $message
 * @property bool $status
 * @property string $tag
 * @property string $heading
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Notification extends Model
{
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'employee_id' => 'int',
		'status' => 'bool'
	];

	protected $fillable = [
		'employee_id',
		'message',
		'status',
		'tag',
		'heading',
        'url'
	];
	const UNREAD = 0;
	const READ = 1;

	public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }
}
