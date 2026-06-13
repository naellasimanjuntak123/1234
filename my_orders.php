<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Filter status
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM orders WHERE user_id = ?";
$params = [$user_id];

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND id LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Hitung statistik
$stats = [
    'all' => 0,
    'pending' => 0,
    'paid' => 0,
    'processing' => 0,
    'shipped' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
$stmt->execute([$user_id]);
$status_counts = $stmt->fetchAll();

foreach ($status_counts as $row) {
    $stats[$row['status']] = $row['count'];
    $stats['all'] += $row['count'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="page-header-orders">
            <div>
                <h1><i class="fas fa-shopping-bag"></i> Pesanan Saya</h1>
                <p class="page-subtitle">Kelola dan lacak semua pesanan Anda</p>
            </div>
            <div class="header-stats">
                <div class="stat-item-small">
                    <i class="fas fa-box"></i>
                    <div>
                        <strong><?= $stats['all'] ?></strong>
                        <span>Total Pesanan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="orders-filter-tabs">
            <a href="?status=all" class="filter-tab <?= $status_filter === 'all' ? 'active' : '' ?>">
                <i class="fas fa-list"></i> Semua
                <?php if ($stats['all'] > 0): ?><span class="tab-badge"><?= $stats['all'] ?></span><?php endif; ?>
            </a>
            <a href="?status=pending" class="filter-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Menunggu Pembayaran
                <?php if ($stats['pending'] > 0): ?><span
                    class="tab-badge"><?= $stats['pending'] ?></span><?php endif; ?>
            </a>
            <a href="?status=paid" class="filter-tab <?= $status_filter === 'paid' ? 'active' : '' ?>">
                <i class="fas fa-check-circle"></i> Dibayar
                <?php if ($stats['paid'] > 0): ?><span class="tab-badge"><?= $stats['paid'] ?></span><?php endif; ?>
            </a>
            <a href="?status=processing" class="filter-tab <?= $status_filter === 'processing' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Diproses
                <?php if ($stats['processing'] > 0): ?><span
                    class="tab-badge"><?= $stats['processing'] ?></span><?php endif; ?>
            </a>
            <a href="?status=shipped" class="filter-tab <?= $status_filter === 'shipped' ? 'active' : '' ?>">
                <i class="fas fa-truck"></i> Dikirim
                <?php if ($stats['shipped'] > 0): ?><span
                    class="tab-badge"><?= $stats['shipped'] ?></span><?php endif; ?>
            </a>
            <a href="?status=completed" class="filter-tab <?= $status_filter === 'completed' ? 'active' : '' ?>">
                <i class="fas fa-check-double"></i> Selesai
                <?php if ($stats['completed'] > 0): ?><span
                    class="tab-badge"><?= $stats['completed'] ?></span><?php endif; ?>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="orders-search">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Cari nomor pesanan..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>

        <?php if (count($orders) > 0): ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): 
                // Get order items untuk preview
                $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? LIMIT 3");
                $stmt->execute([$order['id']]);
                $items = $stmt->fetchAll();
                
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                $stmt->execute([$order['id']]);
                $total_items = $stmt->fetchColumn();
                
                $status_labels = [
                    'pending' => 'Menunggu Pembayaran',
                    'paid' => 'Sudah Dibayar',
                    'processing' => 'Diproses',
                    'shipped' => 'Dikirim',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan'
                ];
                
                $status_icons = [
                    'pending' => 'fa-clock',
                    'paid' => 'fa-check-circle',
                    'processing' => 'fa-cog',
                    'shipped' => 'fa-truck',
                    'completed' => 'fa-check-double',
                    'cancelled' => 'fa-times-circle'
                ];
            ?>
            <div class="order-card-modern">
                <div class="order-card-header">
                    <div class="order-number">
                        <i class="fas fa-receipt"></i>
                        <span>Pesanan #<?= $order['id'] ?></span>
                    </div>
                    <span class="status-badge status-<?= $order['status'] ?>">
                        <i class="fas <?= $status_icons[$order['status']] ?>"></i>
                        <?= $status_labels[$order['status']] ?>
                    </span>
                </div>

                <div class="order-card-body">
                    <div class="order-date-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB</span>
                    </div>

                    <div class="order-items-preview">
                        <?php foreach ($items as $item): ?>
                        <div class="item-preview">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="item-preview-info">
                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                <span><?= $item['quantity'] ?>x</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($total_items > 3): ?>
                        <div class="more-items">
                            <i class="fas fa-plus-circle"></i>
                            <span>+<?= $total_items - 3 ?> produk lainnya</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="order-total-info">
                        <span>Total Belanja</span>
                        <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div class="order-card-footer">
                    <?php if ($order['status'] === 'pending'): ?>
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-credit-card"></i> Bayar Sekarang
                    </a>
                    <?php elseif ($order['status'] === 'completed'): ?>
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="catalog.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Belanja Lagi
                    </a>
                    <?php else: ?>
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state-modern">
            <div class="empty-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>Belum Ada Pesanan</h3>
            <p>Anda belum memiliki pesanan. Mulai belanja sekarang dan temukan produk frozen food berkualitas!</p>
            <a href="catalog.php" class="btn btn-primary btn-large">
                <i class="fas fa-shopping-cart"></i> Mulai Belanja
            </a>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>