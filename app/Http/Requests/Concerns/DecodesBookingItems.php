<?php

namespace App\Http\Requests\Concerns;

trait DecodesBookingItems
{
    /**
     * @return list<array<string, mixed>>
     */
    public function decodedBookingItems(): array
    {
        $items = $this->input('booking_items');

        if (is_array($items)) {
            return array_is_list($items) ? $items : [];
        }

        $decoded = json_decode((string) $items, true);

        return is_array($decoded) && array_is_list($decoded) ? $decoded : [];
    }
}
