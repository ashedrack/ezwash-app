<?php

namespace App\Http\Requests;

use App\Classes\Meta;
use App\Models\Order;
use App\Models\OrdersLocker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * @var Order $order
     */
    protected $order;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->order = $this->route('order');
        return [
            'services' => 'required|array',
            'services.*.id' => 'required|distinct|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'lockers' => [
                'nullable',
                Rule::requiredIf(function() {
                    return ($this->order->order_type === Meta::DROP_OFF_ORDER_TYPE);
                }),
                'array'
            ],
            'lockers.*' => ['bail', 'nullable',
                function ($attribute, $value, $fail){
                    //locker is 0 when out of locker is selected (multiple orders can be out of locker)
                    if ($value != 0) {
                        $occupiedLockers = OrdersLocker::whereHas('locker', function ($q) use ($value) {
                                    $q->where('location_id', $this->order->location_id)
                                        ->where('occupied', 1)
                                        ->where('locker_number', $value);
                                })
                                ->where('order_id', '<>', $this->order->id);
                        if ($occupiedLockers->count() > 0) {
                            $fail('Locker ' . $value . ' is already occupied');
                        }
                    }
                }
            ],
            'payment_method' => [
                'nullable',
                Rule::requiredIf(function() {
                    return ($this->order->order_type === Meta::SELF_SERVICE_ORDER_TYPE);
                }),
                'exists:payment_methods,name'
            ]
        ];
    }
}
