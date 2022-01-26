<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model;

/**
 * Class UserCard
 * 
 * @property int $id
 * @property int $user_id
 * @property string $auth_code
 * @property string $last_four
 * @property string $card_type
 * @property string $exp_month
 * @property string $exp_year
 * @property string $bank
 * @property string $signature
 * @property string $meta
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class UserCard extends Model
{
	protected $table = 'user_cards';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'auth_code',
		'last_four',
		'card_type',
		'exp_month',
		'exp_year',
		'bank',
		'signature',
		'meta'
	];

    public function user(){

        return $this->belongsTo(User::class);
    }
}
