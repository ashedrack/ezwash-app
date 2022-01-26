<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class KwikTaskStatus
 *
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class KwikTaskStatus extends Model
{
	protected $table = 'kwik_task_statuses';
	public $incrementing = false;

    const UPCOMING = 0;
    const STARTED = 1;
    const ENDED = 2;
    const FAILED = 3;
    const ARRIVED = 4;
    const UNASSIGNED = 6;
    const ACCEPTED = 7;
    const DECLINE = 8;
    const CANCELED = 9;
    const DELETED = 10;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'name'
	];
}
