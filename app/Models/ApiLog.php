<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiLog
 * 
 * @property int $id
 * @property string $url
 * @property string $method
 * @property string $request_header
 * @property string $data_param
 * @property string $response
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class ApiLog extends Model
{
	protected $fillable = [
		'url',
		'method',
		'request_header',
		'data_param',
		'response',
        'channel'
	];
}
