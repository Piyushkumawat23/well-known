<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    
    protected $fillable = [
        'combined_order_id',
        'user_id',
        'shipping_address',
        'shipping_type',
        'pickup_point_id',
        'payment_type',
        'delivery_viewed',
        'payment_status_viewed',
        'code',
        'date',
        'grand_total',
        'wholesale_commission',
        'coupon_discount',
        'seller_id',
        'salesperson_id',
        'for_customer_id',
        'shipping_type_payment',
    ];

    public function combinedOrder()
    {
        return $this->belongsTo(CombinedOrder::class);
    }
    

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function refund_requests()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function salesperson()
    {
        return $this->belongsTo(User::class,'salesperson_id')->select(['id','name']);
    }
    public function salespersonCustomer()
    {
        return $this->belongsTo(User::class,'for_customer_id')->select(['id','name']);
    }
    public function salespersonCustomerName()
    {
        return $this->belongsTo(User::class,'user_id')->select(['id','name']);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id', 'seller_id');
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function affiliate_log()
    {
        return $this->hasMany(AffiliateLog::class);
    }

    public function club_point()
    {
        return $this->hasMany(ClubPoint::class);
    }

    public function delivery_boy()
    {
        return $this->belongsTo(User::class, 'assign_delivery_boy', 'id');
    }

    public function proxy_cart_reference_id()
    {
        return $this->hasMany(ProxyPayment::class)->select('reference_id');
    }
}
