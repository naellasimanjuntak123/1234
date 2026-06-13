-- Migration Revisi: tambah kolom shipping_service dan shipping_cost di tabel orders
-- Jalankan query ini sekali di database Anda

ALTER TABLE orders 
  ADD COLUMN IF NOT EXISTS shipping_service VARCHAR(20) DEFAULT 'gojek' AFTER payment_method,
  ADD COLUMN IF NOT EXISTS shipping_cost INT DEFAULT 15000 AFTER shipping_service;

-- Update data lama agar tidak NULL
UPDATE orders SET shipping_service = 'gojek', shipping_cost = 15000 WHERE shipping_service IS NULL;
