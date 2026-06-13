-- Script untuk menghapus kolom payment_proof dari tabel orders
-- Jalankan script ini jika database sudah ada

USE frozenfood;

-- Hapus kolom payment_proof dari tabel orders
ALTER TABLE orders DROP COLUMN IF EXISTS payment_proof;

-- Selesai
SELECT 'Kolom payment_proof berhasil dihapus dari tabel orders' AS status;
