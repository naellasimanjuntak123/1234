<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$total_orders    = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_revenue   = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('paid', 'processing', 'shipped', 'completed')")->fetchColumn();

$recent_orders = $pdo->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=5.0">
    <style>
    /* ─── Mobile table fix ─────────────────────────── */
    @media (max-width: 768px) {

        /* Wrapper scroll horizontal sebagai fallback */
        .recent-orders {
            overflow-x: hidden;
        }

        /* Tabel jadi layout kartu per baris */
        .admin-table thead {
            display: none;
            /* sembunyikan header kolom */
        }

        .admin-table,
        .admin-table tbody,
        .admin-table tr,
        .admin-table td {
            display: block;
            width: 100%;
        }

        /* Setiap baris = kartu */
        .admin-table tr {
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            background: #fff;
        }

        /* Setiap sel punya label di kiri */
        .admin-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border: none;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }

        .admin-table td:last-child {
            border-bottom: none;
        }

        /* Label otomatis dari data-label */
        .admin-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            font-size: 12px;
            min-width: 80px;
        }
    }
    </style>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <main class="container">
        <h1>Dashboard Admin</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <p class="stat-number"><?= $total_orders ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Produk</h3>
                <p class="stat-number"><?= $total_products ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pelanggan</h3>
                <p class="stat-number"><?= $total_customers ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pendapatan</h3>
                <p class="stat-number">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Pesanan Terbaru</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td data-label="ID">#<?= $order['id'] ?></td>
                        <td data-label="Pelanggan"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td data-label="Total">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                        <td data-label="Status">
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= strtoupper($order['status']) ?>
                            </span>
                        </td>
                        <td data-label="Tanggal"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        <td data-label="Aksi">
                            <a href="order_manage.php?id=<?= $order['id'] ?>" class="btn btn-small">Kelola</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>