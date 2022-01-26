<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailLog
 * 
 * @property int $id
 * @property string $email_type
 * @property string $to
 * @property string $request_payload
 * @property string $response_payload
 * @property string $status
 * @property bool $multiple_recipients
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class EmailLog extends Model
{
	public $timestamps = false;

	protected $casts = [
		'multiple_recipients' => 'bool'
	];

	protected $fillable = [
		'email_type',
		'to',
		'request_payload',
		'response_payload',
		'status',
		'multiple_recipients'
	];
}
