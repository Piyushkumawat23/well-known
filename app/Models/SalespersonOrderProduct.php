<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class SalespersonOrderProduct extends Model
{

    public function customer()
    {
        return $this->belongsTo(User::class ,'customer_id')->select(['id','name']);
    }
    public function salesperson()
    {
        return $this->belongsTo(User::class ,'salesperson_id')->select(['id','name']);
    }

    public function category()
    {
        return $this->belongsTo(Category::class ,'category_id')->select(['id','name']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class ,'product_id')->select(['id','name','thumbnail_img','slug']);
    }

}
