<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Order\Models\Order;

class Payment extends Model
{
    protected $fillable = ['order_id', 'amount', 'reference_id', 'status'];

    /**
     * هر پرداخت متعلق به یک سفارش است
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}