-- Admin order list expects `order_status` (see admin-area/order.php).
-- Run once on databases created from an older schema without this column.

ALTER TABLE `orders`
  ADD COLUMN `order_status` varchar(50) NOT NULL DEFAULT 'Order Placed' AFTER `payment_status`;
