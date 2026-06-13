-- Script untuk menambahkan kolom payment_method ke tabel orders
-- Jalankan script ini jika database sudah ada

USE frozenfood;

-- Tambahkan kolom payment_method ke tabel orders
ALTER TABLE orders 
ADD COLUMN payment_method ENUM('bank_transfer', 'cod', 'e_wallet') DEFAULT 'bank_transfer' 
AFTER status;

-- Selesai
SELECT 'Kolom payment_method berhasil ditambahkan ke tabel orders' AS status;
