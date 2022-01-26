<?php

namespace App\Classes;


use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DropoffOrderHandler
{
    protected $order;
    protected $services;
    protected $collectServices;
    protected $requestParams;

    /**
     * DropoffOrderHandler constructor.
     * @param array $requestParams
     * @param Order $order
     */
    public function __construct($requestParams, Order $order)
    {
        $this->order = $order;
        $this->requestParams = $requestParams;
        $this->collectServices = collect($requestParams['services'])->pluck('quantity', 'id')->toArray();
        $this->services = Service::whereIn('id', Arr::pluck($requestParams['services'], 'id'))->get()->toArray();
    }

    /**
     * @return void
     */
    public function updateOrder(): void
    {
        $requestParams = $this->requestParams;
        $paymentMethod = $this->getPaymentMethod();
        $totalAmount = $this->getTotalAmount();

        $orderStatus = Meta::ORDER_STATUS_PENDING;
        if(!is_null($paymentMethod)){
            $orderStatus = PaymentMethod::methodIsPosOrCash($paymentMethod) ? Meta::ORDER_STATUS_COMPLETED : Meta::ORDER_STATUS_PENDING ;
        }

        //Update the order
        $this->order->update([
            'status' => $orderStatus,
            'amount' => $totalAmount,
            'payment_method' => $paymentMethod
        ]);

        $orderServices = $this->getOrderServices();
        $this->order->services()->sync($orderServices);

        if(array_key_exists('lockers', $requestParams)){
            $selectedLockers = $requestParams['lockers'];
            $previouslySelected = $this->order->lockers();
            if($previouslySelected->count() > 0){
                $previouslySelected->update([
                    'occupied' => 0
                ]);
            }
            $lockers = $this->order->location->lockers()->whereIn('locker_number', $selectedLockers);
            $this->order->lockers()->sync($lockers->get());
            $lockers->update([
                'occupied' => 1
            ]);
        }
    }

    /**
     * @return integer
     */
    public function getPaymentMethod()
    {
        return (!is_null($this->requestParams['payment_method']))? PaymentMethod::where('name', $this->requestParams['payment_method'])->first()->id: null;
    }

    /**
     * @return float;
     */
    public function getTotalAmount() : float
    {
        $collectServices = $this->collectServices;
        $totalPerService = array_map(function ($s) use ($collectServices) {
            return ($collectServices[$s['id']] * $s['price']);
        }, $this->services);
        $totalAmount = array_sum($totalPerService);

        return $totalAmount;
    }

    /**
     */
    public function getOrderServices(): array
    {
        $orderServices = [];
        foreach($this->services as $s){
            $orderServices[$s['id']] = [
                'quantity' => $this->collectServices[$s['id']],
                'price' => $s['price']
            ];
        };
        return $orderServices;
    }
}
