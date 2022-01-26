<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 03 Dec 2019 13:53:44 +0100.
 */

namespace App\Models;

use App\Classes\Meta;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserDiscountStatus
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class UserDiscountStatus extends Model
{
    const UNUSED_DISCOUNT = 1;
    const DISCOUNT_APPLIED_TO_ORDER = 2;
    const USED_DISCOUNT = 3;
    const EXPIRED_DISCOUNT = 4;

	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'name'
	];
}
