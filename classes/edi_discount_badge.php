<?php

/**
 * Integer discount percent (1–99) for on-sale products, or null when not discounted.
 */
function edi_discount_badge_pct(array $row) {
    if ((float) ($row['discounted_price'] ?? 0) <= 0) {
        return null;
    }
    $stored = (float) ($row['discount_percentage'] ?? 0);
    if ($stored > 0.004) {
        return max(1, min(99, (int) round($stored)));
    }
    $orig = (float) ($row['price'] ?? 0);
    $sale = (float) ($row['discounted_price'] ?? 0);
    if ($orig <= 0) {
        return null;
    }
    return max(1, min(99, (int) round((1.0 - $sale / $orig) * 100.0)));
}
