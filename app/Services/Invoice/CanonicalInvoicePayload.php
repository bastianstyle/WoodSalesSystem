<?php

namespace App\Services\Invoice;

use App\Models\Sale;

class CanonicalInvoicePayload
{
    public function make(Sale $sale): array
    {
        $sale->loadMissing(['details', 'customer', 'cashier']);

        return [
            'schema_version' => '1',
            'invoice_number' => $sale->invoice_number,
            'issued_at' => $sale->issued_at->toIso8601String(),
            'cashier_id' => $sale->user_id,
            'customer' => [
                'code' => $sale->customer->code,
                'name' => $sale->customer->name,
            ],
            'items' => $sale->details->values()->map(fn ($detail) => [
                'product_code' => $detail->product_code_snapshot,
                'name' => $detail->product_name_snapshot,
                'wood_type' => $detail->wood_type_snapshot,
                'size' => $detail->size_snapshot,
                'unit' => $detail->unit_snapshot,
                'quantity' => number_format((float) $detail->quantity, 3, '.', ''),
                'unit_price' => number_format((float) $detail->unit_price, 2, '.', ''),
                'subtotal' => number_format((float) $detail->subtotal, 2, '.', ''),
            ])->all(),
            'discount' => number_format((float) $sale->discount, 2, '.', ''),
            'grand_total' => number_format((float) $sale->grand_total, 2, '.', ''),
            'amount_paid' => number_format((float) $sale->amount_paid, 2, '.', ''),
            'change_amount' => number_format((float) $sale->change_amount, 2, '.', ''),
        ];
    }

    public function encode(array $payload): string
    {
        return json_encode($this->sortKeys($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }

    public function hash(array $payload): string
    {
        return hash('sha256', $this->encode($payload));
    }

    private function sortKeys(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item) && ! array_is_list($item)) {
                $value[$key] = $this->sortKeys($item);
            }
        }
        ksort($value);

        return $value;
    }
}
