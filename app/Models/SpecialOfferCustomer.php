<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SpecialOfferCustomer
 *
 * @property int $id
 * @property int $user_id
 * @property int $loyalty_offer_id
 * @property int $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Employee $employee
 * @property LoyaltyOffer $loyalty_offer
 * @property User $user
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class SpecialOfferCustomer extends Model
{
	protected $table = 'special_offer_customers';

	protected $casts = [
		'user_id' => 'int',
		'loyalty_offer_id' => 'int',
		'created_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'loyalty_offer_id',
		'created_by'
	];

	public function createdBy()
	{
		return $this->belongsTo(Employee::class, 'created_by');
	}

	public function loyalty_offer()
	{
		return $this->belongsTo(LoyaltyOffer::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
