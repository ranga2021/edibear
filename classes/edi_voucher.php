<?php

class EdiVoucher
{
    public static function tableReady(PDO $pdo)
    {
        try {
            $st = $pdo->query("SHOW TABLES LIKE 'edi_vouchers'");
            return $st && $st->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function fetchAll(PDO $pdo)
    {
        return $pdo->query("SELECT * FROM edi_vouchers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(PDO $pdo, $id)
    {
        $st = $pdo->prepare("SELECT * FROM edi_vouchers WHERE id = ?");
        $st->execute(array((int) $id));
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByCode(PDO $pdo, $code)
    {
        $st = $pdo->prepare("SELECT * FROM edi_vouchers WHERE code = ?");
        $st->execute(array(strtoupper(trim($code))));
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Validate a voucher code against the current cart subtotal.
     * Returns array with 'valid' (bool), 'error' (string|null), and voucher data on success.
     */
    public static function validate(PDO $pdo, $code, $subtotal)
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return array('valid' => false, 'error' => 'Please enter a voucher code.');
        }

        $voucher = self::findByCode($pdo, $code);
        if (!$voucher) {
            return array('valid' => false, 'error' => 'Invalid voucher code.');
        }

        if ((int) $voucher['status'] !== 1) {
            return array('valid' => false, 'error' => 'This voucher is no longer active.');
        }

        $today = date('Y-m-d');
        if ($voucher['starts_at'] !== null && $today < $voucher['starts_at']) {
            return array('valid' => false, 'error' => 'This voucher is not yet valid.');
        }
        if ($voucher['expires_at'] !== null && $today > $voucher['expires_at']) {
            return array('valid' => false, 'error' => 'This voucher has expired.');
        }

        $maxUses = (int) $voucher['max_uses'];
        if ($maxUses > 0 && (int) $voucher['used_count'] >= $maxUses) {
            return array('valid' => false, 'error' => 'This voucher has reached its usage limit.');
        }

        $minOrder = (float) $voucher['min_order_total'];
        if ($minOrder > 0 && (float) $subtotal < $minOrder) {
            return array(
                'valid' => false,
                'error' => 'Minimum order total of Rs. ' . number_format($minOrder, 2) . ' required for this voucher.'
            );
        }

        $discount = self::calculateDiscount($voucher, $subtotal);

        return array(
            'valid' => true,
            'error' => null,
            'voucher' => $voucher,
            'discount' => $discount,
        );
    }

    public static function calculateDiscount($voucher, $subtotal)
    {
        $subtotal = (float) $subtotal;
        if ($voucher['discount_type'] === 'percentage') {
            $discount = round($subtotal * (float) $voucher['discount_value'] / 100, 2);
        } else {
            $discount = (float) $voucher['discount_value'];
        }
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
        return $discount;
    }

    public static function incrementUsage(PDO $pdo, $voucherId)
    {
        $st = $pdo->prepare("UPDATE edi_vouchers SET used_count = used_count + 1 WHERE id = ?");
        $st->execute(array((int) $voucherId));
    }
}
