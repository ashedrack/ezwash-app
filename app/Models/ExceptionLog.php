<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExceptionLog
 *
 * @property int $id
 * @property string $message
 * @property string $url
 * @property int $line
 * @property string $file
 * @property string $trace_string
 * @property string $additional_info
 * @property int $occurrence_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class ExceptionLog extends Model
{
    protected $table = 'exception_logs';

    protected $casts = [
        'line' => 'int',
        'occurrence_count' => 'int'
    ];

    protected $fillable = [
        'message',
        'url',
        'line',
        'file',
        'trace_string',
        'additional_info',
        'occurrence_count'
    ];
}
