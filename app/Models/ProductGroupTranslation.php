<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGroupTranslation extends Model
{
  protected $fillable = ['name', 'lang', 'product_group_id'];

  public function brand(){
    return $this->belongsTo(ProductGroup::class);
  }
}
