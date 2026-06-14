<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$status_filter = $_GET['status'] ?? '';

$allowed_status = [
    'pending',
    'paid',
    'processing',
    'shipped',
    'completed',
    'cancelled'
];

if (!in_array($status_filter, $allowed_status)) {
    $status_filter = '';
}

$sql = "SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];

if ($status_filter) {
    $sql .= " WHERE o.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

function statusLabel($status) {
    $labels = [
        'pending'    => 'Menunggu',
        'paid'       => 'Dibayar',
        'processing' => 'Diproses',
        'shipped'    => 'Dikirim',
        'completed'  => 'Selesai',
        'cancelled'  => 'Dibatalkan',
    ];
    return $labels[$status] ?? ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <main class="container">
        <h1>Manajemen Pesanan</h1>

        <div class="filter-bar">
            <a href="orders.php" class="btn <?= !$status_filter ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="?status=pending"
                class="btn <?= $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Menunggu
                Pembayaran</a>
            <a href="?status=paid"
                class="btn <?= $status_filter === 'paid' ? 'btn-primary' : 'btn-secondary' ?>">Dibayar</a>
            <a href="?status=processing"
                class="btn <?= $status_filter === 'processing' ? 'btn-primary' : 'btn-secondary' ?>">Diproses</a>
            <a href="?status=shipped"
                class="btn <?= $status_filter === 'shipped' ? 'btn-primary' : 'btn-secondary' ?>">Dikirim</a>
            <a href="?status=completed"
                class="btn <?= $status_filter === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Selesai</a>
        </div>

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
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
                    <td><span
                            class="status-badge status-<?= htmlspecialchars($order['status']) ?>"><?= statusLabel($order['status']) ?></span>
                    </td>
                    <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                    <td><a href="order_manage.php?id=<?= $order['id'] ?>" class="btn btn-small">Kelola</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>