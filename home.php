<?php
session_start();
require_once 'config/database.php';

// Redirect ke halaman login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Ambil produk terbaru
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 8");
$products = $stmt->fetchAll();

// Ambil promo aktif
$stmt = $pdo->query("SELECT * FROM promos WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()");
$promos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrozenFood - Beranda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <section class="hero">
            <h1>Selamat Datang di FrozenFood</h1>
            <p>Produk frozen food berkualitas untuk keluarga Anda</p>
        </section>

        <?php if (count($promos) > 0): ?>
        <section class="promos">
            <h2>🎉 Promo Spesial Untukmu!</h2>
            <div class="promo-grid">
                <?php foreach ($promos as $promo): ?>
                <div class="promo-card" style="border:2px solid #f97316; border-radius:12px; padding:16px; background:linear-gradient(135deg,#fff7ed,#fff);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                        <h3 style="margin:0;color:#ea580c;"><?= htmlspecialchars($promo['name']) ?></h3>
                        <span class="discount" style="background:#ef4444;color:#fff;padding:4px 12px;border-radius:20px;font-weight:700;white-space:nowrap;"><?= $promo['discount_percent'] ?>% OFF</span>
                    </div>
                    <p style="color:#64748b;margin:8px 0;"><?= htmlspecialchars($promo['description']) ?></p>
                    <p style="font-size:12px;color:#94a3b8;margin:0;">
                        Berlaku: <?= date('d M Y', strtotime($promo['start_date'])) ?> &ndash; <?= date('d M Y', strtotime($promo['end_date'])) ?>
                    </p>
                    <a href="catalog.php" class="btn btn-primary" style="margin-top:10px;display:inline-block;">
                        <i class="fas fa-shopping-bag"></i> Belanja Sekarang
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="products">
            <h2>Produk Terbaru</h2>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h3 style="color: #000 !important;"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="price">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                    <p class="stock">Stok: <?= $product['stock'] ?></p>
                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary"><i class="fas fa-shopping-bag"></i> Pesan Yuk</a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="catalog.php" class="btn btn-secondary">Lihat Semua Produk</a>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
