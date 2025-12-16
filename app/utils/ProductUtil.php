<?php

namespace App\Utils;

use Modules\Purchase\Models\PurchaseLine;
use App\Models\Product;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;

class ProductUtil extends Util
{
    /**
     * Add/Edit transaction purchase lines - Adapted from ERP
     *
     * @param object $transaction
     * @param array $input_data
     * @param array $currency_details
     * @param boolean $enable_product_editing
     * @param string $before_status = null
     *
     * @return array
     */
    public function createOrUpdatePurchaseLines($transaction, $input_data, $currency_details, $enable_product_editing, $store_id, $before_status = null)
    {
        $updated_purchase_lines = [];
        $updated_purchase_line_ids = [0];
        $exchange_rate = !empty($transaction->exchange_rate) ? $transaction->exchange_rate : 1;

        foreach ($input_data as $data) {
            $multiplier = 1;
            
            $new_quantity = ($this->num_uf($data['quantity']) * $multiplier);
            
            // Update existing purchase line
            if (isset($data['purchase_line_id'])) {
                $purchase_line = PurchaseLine::findOrFail($data['purchase_line_id']);
                $updated_purchase_line_ids[] = $purchase_line->id;
                
                // Update stock if status was received
                if ($before_status == 'received' && $transaction->status == 'received') {
                    $this->updateProductStock($transaction,$product_id, $data['variation_id'], $new_quantity, $purchase_line->quantity, $store_id);
                }
            } else {
                // Create newly added purchase lines
                $purchase_line = new PurchaseLine();
                $purchase_line->product_id = $data['product_id'];
                $purchase_line->variation_id = $data['variation_id'] ?? null;
                
                // Increase quantity only if status is received
                if ($transaction->status == 'received') {
                    $this->updateProductQuantity($transaction->location_id, $data['product_id'], $data['variation_id'], $new_quantity, $store_id);
                }
            }
            
            $purchase_line->quantity = $new_quantity;
            $purchase_line->pp_without_discount = ($this->num_uf($data['pp_without_discount'], $currency_details) * $exchange_rate) / $multiplier;
            $purchase_line->discount_percent = $this->num_uf($data['discount_percent'] ?? 0, $currency_details);
            $purchase_line->purchase_price = ($this->num_uf($data['purchase_price'], $currency_details) * $exchange_rate) / $multiplier;
            $purchase_line->purchase_price_inc_tax = ($this->num_uf($data['purchase_price_inc_tax'], $currency_details) * $exchange_rate) / $multiplier;
            $purchase_line->item_tax = ($this->num_uf($data['item_tax'] ?? 0, $currency_details) * $exchange_rate) / $multiplier;
            $purchase_line->tax_id = $data['purchase_line_tax_id'] ?? null;
            $purchase_line->lot_number = !empty($data['lot_number']) ? $data['lot_number'] : null;
            $purchase_line->mfg_date = !empty($data['mfg_date']) ? $this->uf_date($data['mfg_date']) : null;
            $purchase_line->exp_date = !empty($data['exp_date']) ? $this->uf_date($data['exp_date']) : null;
            
            $updated_purchase_lines[] = $purchase_line;
        }

        // Delete removed purchase lines
        if (!empty($updated_purchase_line_ids)) {
            $delete_purchase_lines = PurchaseLine::where('transaction_id', $transaction->id)
                ->whereNotIn('id', $updated_purchase_line_ids)
                ->get();
            
            if ($delete_purchase_lines->count()) {
                foreach ($delete_purchase_lines as $delete_purchase_line) {
                    // Decrease stock only if previous status was received
                    if ($before_status == 'received') {
                        $this->decreaseProductQuantity(
                            $delete_purchase_line->product_id,
                            $delete_purchase_line->variation_id,
                            $transaction->location_id,
                            $delete_purchase_line->quantity,
                            $store_id
                        );
                    }
                }
                
                // Delete purchase lines
                PurchaseLine::where('transaction_id', $transaction->id)
                    ->whereNotIn('id', $updated_purchase_line_ids)
                    ->delete();
            }
        }

        // Save  all purchase lines
        if (!empty($updated_purchase_lines)) {
            $transaction->purchase_lines()->saveMany($updated_purchase_lines);
        }

        return true;
    }

    /**
     * Updates product stock after adding or updating purchase
     */
    public function updateProductStock($transaction, $product_id, $variation_id, $new_quantity, $old_quantity, $store_id)
    {
        // Simplified stock management
        // In a full implementation, this would update VariationLocationDetails table
        // For now, placeholder
        return true;
    }

    /**
     * Update product quantity when purchase is received
     */
    public function updateProductQuantity($location_id, $product_id, $variation_id, $quantity, $store_id)
    {
        // Simplified - would update product stock tables
        // Placeholder for now
        return true;
    }

    /**
     * Decrease product quantity
     */
    public function decreaseProductQuantity($product_id, $variation_id, $location_id, $quantity, $store_id)
    {
        // Simplified - would decrease product stock
        // Placeholder for now
        return true;
    }

    /**
     * Adjust stock over selling with purchases
     */
    public function adjustStockOverSelling($transaction)
    {
        if ($transaction->status != 'received') {
            return false;
        }
        
        // Simplified version - full version would map purchases to sales
        // Placeholder for now
        return true;
    }
}