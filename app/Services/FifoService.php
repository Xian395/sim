<?php

namespace App\Services;

use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class FifoService
{
    /**
     * Process stock deduction using FIFO method
     *
     * @param int $productId
     * @param int $quantityToDeduct
     * @return array Returns batch allocations and total cost
     */
    public static function deductStock($productId, $quantityToDeduct)
    {
        $remainingToDeduct = $quantityToDeduct;
        $batchAllocations = [];
        $totalCost = 0;

        // Get all stock-in transactions with remaining quantity, ordered by date (FIFO)
        $stockBatches = StockTransaction::where('product_id', $productId)
            ->where('type', 'in')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($stockBatches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $quantityFromThisBatch = min($batch->remaining_quantity, $remainingToDeduct);

            // Update remaining quantity in the batch
            $batch->decrement('remaining_quantity', $quantityFromThisBatch);

            // Calculate cost for this batch
            $batchCost = $quantityFromThisBatch * ($batch->acquisition_price ?? 0);
            $totalCost += $batchCost;

            // Record batch allocation
            $batchAllocations[] = [
                'batch_id' => $batch->id,
                'transaction_date' => $batch->transaction_date,
                'quantity' => $quantityFromThisBatch,
                'unit_cost' => $batch->acquisition_price ?? 0,
                'subtotal' => $batchCost,
            ];

            $remainingToDeduct -= $quantityFromThisBatch;
        }

        if ($remainingToDeduct > 0) {
            // This shouldn't happen if stock validation is done properly
            // But we handle it gracefully
            throw new \Exception("Insufficient stock batches to fulfill the request. Missing: {$remainingToDeduct} units");
        }

        return [
            'batch_allocations' => $batchAllocations,
            'total_cost' => $totalCost,
            'average_cost' => $quantityToDeduct > 0 ? ($totalCost / $quantityToDeduct) : 0,
        ];
    }

    /**
     * Get current stock value using FIFO
     *
     * @param int $productId
     * @return array
     */
    public static function getStockValue($productId)
    {
        $stockBatches = StockTransaction::where('product_id', $productId)
            ->where('type', 'in')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalQuantity = 0;
        $totalValue = 0;

        foreach ($stockBatches as $batch) {
            $totalQuantity += $batch->remaining_quantity;
            $totalValue += $batch->remaining_quantity * ($batch->acquisition_price ?? 0);
        }

        return [
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'average_cost' => $totalQuantity > 0 ? ($totalValue / $totalQuantity) : 0,
        ];
    }

    /**
     * Get stock batches with remaining quantities
     *
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getStockBatches($productId)
    {
        return StockTransaction::where('product_id', $productId)
            ->where('type', 'in')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}
