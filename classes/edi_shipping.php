<?php

/**
 * Cart total weight + weight-tier and district shipping (see sql/migration_shipping_and_weight_kg.sql).
 */
class EdiShipping
{
    /** When tiers table is missing or has no usable rows, keep previous flat behaviour. */
    public const LEGACY_FLAT_WEIGHT_FEE = 450.0;

    /**
     * Unit weight in kg from a product row (prefers weight_kg; falls back to parsing legacy `weight` text).
     */
    public static function productKgFromRow(array $row): float
    {
        if (array_key_exists('weight_kg', $row) && $row['weight_kg'] !== null && $row['weight_kg'] !== '') {
            return max(0.0, (float) $row['weight_kg']);
        }
        $w = isset($row['weight']) ? trim((string) $row['weight']) : '';
        if ($w === '') {
            return 0.0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)/', $w, $m)) {
            return max(0.0, (float) $m[1]);
        }

        return 0.0;
    }

    /**
     * Total cart weight (kg) for a user.
     */
    public static function cartTotalKg(USER $user, int $userId): float
    {
        if ($userId <= 0) {
            return 0.0;
        }
        $items = $user->fetchAll(
            array('product_id', 'quantity'),
            array('cart'),
            array('user_id' => $userId)
        );
        $sum = 0.0;
        foreach ($items as $it) {
            $pid = (int) ($it['product_id'] ?? 0);
            $qty = (int) ($it['quantity'] ?? 0);
            if ($pid < 1 || $qty < 1) {
                continue;
            }
            $rows = $user->fetchAll(
                '',
                array('products'),
                array('id' => $pid)
            );
            if (empty($rows)) {
                continue;
            }
            $sum += self::productKgFromRow($rows[0]) * $qty;
        }

        return $sum;
    }

    public static function weightTiersTableReady(PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT 1 FROM edi_shipping_weight_tiers LIMIT 1');

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function districtsTableReady(PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT 1 FROM edi_shipping_districts LIMIT 1');

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return list<array{id: int|string, max_weight_kg: ?string, fee_lkr: string, sort_order?: int|string}>
     */
    public static function fetchWeightTiers(PDO $pdo): array
    {
        if (!self::weightTiersTableReady($pdo)) {
            return array();
        }
        $sql = 'SELECT id, max_weight_kg, fee_lkr, sort_order FROM edi_shipping_weight_tiers ORDER BY sort_order ASC, (max_weight_kg IS NULL) ASC, max_weight_kg ASC';
        $st = $pdo->query($sql);
        if (!$st) {
            return array();
        }
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : array();
    }

    /**
     * @return list<array{id: int|string, name: string, fee_lkr: string}>
     */
    public static function fetchDistricts(PDO $pdo): array
    {
        if (!self::districtsTableReady($pdo)) {
            return array();
        }
        $st = $pdo->query('SELECT id, name, fee_lkr FROM edi_shipping_districts ORDER BY name ASC');
        if (!$st) {
            return array();
        }
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : array();
    }

    public static function weightShippingFee(PDO $pdo, float $kg): float
    {
        $kg = max(0.0, $kg);
        $rows = self::fetchWeightTiers($pdo);
        if ($rows === array()) {
            return self::LEGACY_FLAT_WEIGHT_FEE;
        }
        $fee = self::LEGACY_FLAT_WEIGHT_FEE;
        foreach ($rows as $r) {
            $maxRaw = $r['max_weight_kg'] ?? null;
            $feeRow = isset($r['fee_lkr']) ? (float) $r['fee_lkr'] : 0.0;
            if ($maxRaw === null || $maxRaw === '') {
                $fee = $feeRow;
                break;
            }
            $max = (float) $maxRaw;
            if ($kg <= $max) {
                $fee = $feeRow;
                break;
            }
            $fee = $feeRow;
        }

        return max(0.0, $fee);
    }

    public static function districtShippingFee(PDO $pdo, string $districtName): float
    {
        if (!self::districtsTableReady($pdo)) {
            return 0.0;
        }
        $d = trim($districtName);
        if ($d === '') {
            return 0.0;
        }
        $st = $pdo->prepare('SELECT fee_lkr FROM edi_shipping_districts WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1');
        $st->execute(array($d));
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 0.0;
        }

        return max(0.0, (float) ($row['fee_lkr'] ?? 0));
    }

    /**
     * @return array{weight_kg: float, weight_fee: float, district_fee: float, shipping_total: float}
     */
    public static function quote(PDO $pdo, float $cartKg, string $districtName): array
    {
        $w = self::weightShippingFee($pdo, $cartKg);
        $d = self::districtShippingFee($pdo, $districtName);

        return array(
            'weight_kg' => max(0.0, $cartKg),
            'weight_fee' => $w,
            'district_fee' => $d,
            'shipping_total' => max(0.0, $w + $d),
        );
    }
}
