<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionType
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class TransactionType extends Model
{

	use \Illuminate\Database\Eloquent\SoftDeletes;
	const ORDER_PAYMENT = 'order_payment';
	const NEW_CARD = 'new_card';
	const ORDER_PAYMENT_ID = 1;
	const NEW_CARD_ID = 2;

	protected $fillable = [
		'name',
		'description'
	];
}
