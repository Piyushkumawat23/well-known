<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Utility\ProductUtility;
use Combinations;
use Illuminate\Support\Facades\Log;

class ProductStockService
{
    public function store(array $data, $product)
    {
        $collection = collect($data);

        $options = ProductUtility::get_attribute_options($collection);

        //Generates the combinations of customer choice options
        $combinations = Combinations::makeCombinations($options);

        $variant = '';
        if (count($combinations[0]) > 0) {
            $product->variant_product = 1;
            $product->save();
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);
                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;
                $product_stock->price = request()['price_' . str_replace('.', '_', $str)];
                $product_stock->sku = request()['sku_' . str_replace('.', '_', $str)];
                $product_stock->qty = request()['qty_' . str_replace('.', '_', $str)];
                $product_stock->image = request()['img_' . str_replace('.', '_', $str)];
                $product_stock->save();
            }
        } else {
            unset($collection['colors_active'], $collection['colors'], $collection['choice_no']);
            $qty = $collection['current_stock'];
            $price = $collection['unit_price'];
            unset($collection['current_stock']);

            $data = $collection->merge(compact('variant', 'qty', 'price'))->toArray();

            ProductStock::create($data);
        }
    }

    public function product_duplicate_store($product_stocks, $product_new)
    {
        foreach ($product_stocks as $key => $stock) {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product_new->id;
            $product_stock->variant     = $stock->variant;
            $product_stock->price       = $stock->price;
            $product_stock->sku         = $stock->sku;
            $product_stock->qty         = $stock->qty;
            $product_stock->save();
        }
    }

    // public function updateOrInsertVariants(array $data, $product)
    // {
    //     $collection = collect($data);
    //     $options = ProductUtility::get_attribute_options($collection);
    //     $combinations = \Combinations::makeCombinations($options);

    //     // 1. Existing variants for this product (with their full stock objects)
    //     $existingVariants = ProductStock::where('product_id', $product->id)
    //         ->get()
    //         ->mapWithKeys(function ($stock) {
    //             return [strtolower(trim($stock->variant)) => $stock];
    //         });

    //     // 2. Used SKUs in the whole table
    //     $usedSkus = ProductStock::whereNotNull('sku')
    //         ->pluck('sku')
    //         ->map(fn ($sku) => intval($sku))
    //         ->toArray();

    //     // 3. SKU generator
    //     $skuGenerator = function () use (&$usedSkus) {
    //         $i = 1;
    //         while (in_array($i, $usedSkus)) {
    //             $i++;
    //         }
    //         $usedSkus[] = $i;
    //         return str_pad($i, 4, '0', STR_PAD_LEFT);
    //     };

    //     foreach ($combinations as $combination) {
    //         $variantStr = ProductUtility::get_combination_string($combination, $collection);
    //         $normalized = strtolower(trim($variantStr));

    //         if ($existingVariants->has($normalized)) {
    //             $stock = $existingVariants[$normalized];
    //             // If SKU is missing, update it
    //             if (empty($stock->sku)) {
    //                 $stock->sku = $skuGenerator();
    //                 $stock->save();
    //             }
    //             continue;
    //         }

    //         // Insert new variant with new SKU
    //         ProductStock::create([
    //             'product_id' => $product->id,
    //             'variant' => $variantStr,
    //             'sku' => $skuGenerator(),
    //             'price' => request('price_' . str_replace('.', '_', $variantStr)),
    //             'qty' => request('qty_' . str_replace('.', '_', $variantStr)),
    //             'image' => request('img_' . str_replace('.', '_', $variantStr)),
    //         ]);
    //     }
    // }

    public function updateOrInsertVariants(array $data, $product)
    {
        Log::info('🟢 Step 1: Starting updateOrInsertVariants for product ID ' . $product->id);
    
        $existingVariants = ProductStock::where('product_id', $product->id)
            ->get()
            ->mapWithKeys(fn($stock) => [strtolower(trim($stock->variant)) => $stock]);
    
        Log::info('🟡 Step 2: Existing Variants', $existingVariants->toArray());
    
        $usedSkus = ProductStock::whereNotNull('sku')
            ->pluck('sku')
            ->map(fn($sku) => intval($sku))
            ->toArray();
    
        Log::info('🟠 Step 3: Used SKUs', $usedSkus);
    
        $skuGenerator = function () use (&$usedSkus) {
            $i = 1;
            while (in_array($i, $usedSkus)) {
                $i++;
            }
            $usedSkus[] = $i;
            return str_pad($i, 4, '0', STR_PAD_LEFT);
        };
    
        $submittedVariants = $data['variant'] ?? [];
    
        Log::info('🔵 Step 4: Submitted Variants', $submittedVariants);
    
        foreach ($submittedVariants as $variantStr) {
            $normalized = strtolower(trim($variantStr));
            $fieldKey = str_replace([' ', '.', '-', '/', '(', ')'], '_', $variantStr);
    
            Log::info("🧩 Checking Variant: $variantStr | Key: $fieldKey");
    
            $price = $data['price_' . $fieldKey] ?? null;
            $qty   = $data['qty_' . $fieldKey] ?? null;
            $img   = $data['img_' . $fieldKey] ?? null;
    
            Log::info("➡️ Data for $variantStr → price: $price, qty: $qty, img: $img");
    
            if ($price === null || $qty === null) {
                Log::warning("⛔ Skipping $variantStr due to missing price/qty");
                continue;
            }
    
            if ($existingVariants->has($normalized)) {
                Log::info("✏️ Updating variant: $variantStr");
    
                $stock = $existingVariants[$normalized];
                $stock->sku   = $stock->sku ?: $skuGenerator();
                $stock->price = $price;
                $stock->qty   = $qty;
                $stock->image = $img;
                $stock->save();
    
                Log::info("✅ Updated: " . $stock->variant);
            } else {
                Log::info("➕ Creating new variant: $variantStr");
    
                $newStock = ProductStock::create([
                    'product_id' => $product->id,
                    'variant'    => $variantStr,
                    'sku'        => $skuGenerator(),
                    'price'      => $price,
                    'qty'        => $qty,
                    'image'      => $img,
                ]);
    
                Log::info("✅ Created: " . $newStock->variant);
            }
        }
    
        Log::info('✅ Step 5: Finished variant update');
    }
    
    
}