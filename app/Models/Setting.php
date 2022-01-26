<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $value
 * @property string $additional_payload
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class Setting extends Model
{
    protected $casts = [
        'additional_payload' => 'json'
    ];

	protected $fillable = [
		'name',
		'description',
        'value',
        'additional_payload'
	];

}
