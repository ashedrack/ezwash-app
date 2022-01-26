<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 07 Sep 2019 22:22:31 +0000.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionStatus
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 *
 * @package App\Models
 * @mixin \Eloquent
 */
class TransactionStatus extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];
    const PENDING = 1;
    const COMPLETED = 2;
    const FAILED = 3;
    const REFUNDED = 4;
    const ABANDONED = 5;

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'transaction_status_id');
    }
}
