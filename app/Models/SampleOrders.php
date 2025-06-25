<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleOrders extends Model
{
  use SoftDeletes; // Soft delete trait ka use karein

  protected $table = 'sample_orders'; // Explicitly set the table name
  protected $fillable = ['user_id', 'order_id','product_id','size','gemstone', 'quantity', 'metal', 'description','status','order_number'];
  
  protected $dates = ['deleted_at']; // Soft delete ke liye dates property

  protected $guarded = [];


  public function Product(){
    return $this->belongsTo(Product::class);
  }

  public function user(){
    return $this->belongsTo(User::class);
  }


}

