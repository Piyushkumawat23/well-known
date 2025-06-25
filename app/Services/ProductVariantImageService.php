<?php

namespace App\Services;

use App\Models\ProductVariantImage;
use App\Models\AttributeValue;
use App\Utility\ProductUtility;
use Combinations;

class ProductVariantImageService
{
    public function store(array $data, $product)
    {

        $collection = collect($data);
       //echo "<pre>";print_r($collection['variant_images']);die;
        if (isset($collection['variant_images']) && !empty($collection['variant_images'])) {
            ProductVariantImage::where('product_id', $collection['product_id'])->delete();
            foreach ($collection['variant_images'] as $key => $val) { 
                $attributeValue=AttributeValue::where('value', $key)->first(); 
                if (!empty($attributeValue)) {
                    $related_product = new ProductVariantImage();
                    $related_product->image = $val[0];
                    $related_product->product_id = $collection['product_id'];
                    $related_product->variant = $attributeValue->id;
                    $related_product->save();
                }              

                //RelatedProduct::create($related_product);
            }
        }
        
        
    }

}
