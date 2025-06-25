<?php

namespace App\Services;

use App\Models\RelatedProduct;
use App\Utility\ProductUtility;
use Combinations;

class ProductRelatedService
{
    public function store(array $data, $product)
    {

        $collection = collect($data);
        if ($collection['parent_id']) {
            RelatedProduct::where('product_id', $collection['product_id'])->delete();
            foreach ($collection['parent_id'] as $key => $val) {                
                $related_product = new RelatedProduct();
                $related_product->parent_id = (!empty($val))?$val:0;
                $related_product->product_id = $collection['product_id'];
                $related_product->save();
                //RelatedProduct::create($related_product);
            }
        }
        
        
    }

}
