<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class TicketRules
{
    public function ticketRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in(['PAID', 'FREE'])],
            'max_per_order' => ['required', 'integer', 'min:1', 'max:100'],
            'min_per_order' => ['required', 'integer', 'min:1', 'max:100'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date', 'after:sale_start_date'],
            'hide_before_sale_start_date' => ['boolean'],
            'hide_after_sale_end_date' => ['boolean'],
            'show_quantity_remaining' => ['boolean'],
            'hide_when_sold_out' => ['boolean'],
            'is_hidden_without_promo_code' => ['boolean'],
            'tax_and_fee_ids' => ['array'],
            'tax_and_fee_ids.*' => ['exists:taxes_and_fees,id'],
            'position' => ['nullable', 'string', 'max:50'],
            'seat_number' => ['nullable', 'string', 'max:10'],
        ];
    }
} 