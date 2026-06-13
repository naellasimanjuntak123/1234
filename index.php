<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: home.php');
    }
    exit();
}

// Ambil produk populer untuk ditampilkan
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 6");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FrozenFood - Frozen Food Berkualitas Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body class="landing-page">
    <!-- Landing Header -->
    <header class="landing-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-snowflake"></i>
                    <span>FrozenFood</span>
                </a>
                <nav class="landing-nav">
                    <a href="#beranda"><i class="fas fa-home"></i> Beranda</a>
                    <a href="#produk"><i class="fas fa-box-open"></i> Produk</a>
                    <a href="#fitur"><i class="fas fa-star"></i> Fitur</a>
                    <a href="#tentang"><i class="fas fa-info-circle"></i> Tentang</a>
                </nav>
                <div class="header-actions">
                    <a href="login.php" class="btn btn-secondary btn-small"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php" class="btn btn-primary btn-small"><i class="fas fa-user-plus"></i> Daftar</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="beranda" class="landing-hero">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1>Frozen Food Berkualitas Premium untuk Keluarga Indonesia</h1>
                        <p>Nikmati kemudahan berbelanja frozen food berkualitas tinggi dengan harga terjangkau. Produk segar, higienis, dan siap diantar ke rumah Anda.</p>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn btn-primary btn-large">Mulai Belanja</a>
                            <a href="#produk" class="btn btn-secondary btn-large">Lihat Produk</a>
                        </div>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <strong>500+</strong>
                                <span>Produk</span>
                            </div>
                            <div class="stat-item">
                                <strong>10K+</strong>
                                <span>Pelanggan</span>
                            </div>
                            <div class="stat-item">
                                <strong>4.8★</strong>
                                <span>Rating</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-image">
                        <div class="hero-image-wrapper">
                            <img src="assets/images/hero-frozen.svg" alt="Frozen Food" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="fitur" class="features-section">
            <div class="container">
                <div class="section-header">
                    <h2>Kenapa Memilih FrozenFood?</h2>
                    <p>Kami memberikan pengalaman belanja terbaik untuk Anda</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                        <h3>Kualitas Terjamin</h3>
                        <p>Produk frozen food berkualitas premium dengan standar keamanan pangan tertinggi</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                        <h3>Pengiriman Cepat</h3>
                        <p>Pengiriman dengan cold chain system untuk menjaga kesegaran produk</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-dollar-sign"></i></div>
                        <h3>Harga Terjangkau</h3>
                        <p>Harga kompetitif dengan promo menarik setiap bulannya</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Pembayaran Aman</h3>
                        <p>Sistem pembayaran yang aman dan terpercaya</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-star"></i></div>
                        <h3>Rating & Review</h3>
                        <p>Sistem review transparan dari pelanggan asli</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h3>Mudah Digunakan</h3>
                        <p>Interface yang user-friendly dan responsive di semua device</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Preview Section -->
        <section id="produk" class="products-preview">
            <div class="container">
                <div class="section-header">
                    <h2>Produk Populer Kami</h2>
                    <p>Pilihan favorit pelanggan FrozenFood</p>
                </div>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h3 style="color: #000 !important;"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="price">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                        <p class="stock">Stok: <?= $product['stock'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center">
                    <a href="register.php" class="btn btn-primary btn-large">Daftar untuk Belanja</a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="tentang" class="about-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-text">
                        <h2>Tentang FrozenFood</h2>
                        <p>FrozenFood adalah platform e-commerce terpercaya yang menyediakan berbagai produk frozen food berkualitas premium untuk keluarga Indonesia.</p>
                        <p>Kami berkomitmen untuk memberikan produk terbaik dengan harga terjangkau, pengiriman cepat, dan pelayanan yang memuaskan.</p>
                        <ul class="about-list">
                            <li>✓ Produk bersertifikat BPOM & Halal</li>
                            <li>✓ Pengiriman dengan cold chain system</li>
                            <li>✓ Customer service responsif 24/7</li>
                            <li>✓ Garansi uang kembali 100%</li>
                        </ul>
                    </div>
                    <div class="about-image">
                        <div class="about-stats-card">
                            <h3>Dipercaya Sejak 2020</h3>
                            <div class="stats-list">
                                <div class="stat">
                                    <strong>50K+</strong>
                                    <span>Transaksi Berhasil</span>
                                </div>
                                <div class="stat">
                                    <strong>98%</strong>
                                    <span>Kepuasan Pelanggan</span>
                                </div>
                                <div class="stat">
                                    <strong>100+</strong>
                                    <span>Kota Terjangkau</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Siap Mulai Belanja?</h2>
                    <p>Daftar sekarang dan dapatkan promo spesial untuk member baru!</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="btn btn-primary btn-large">Daftar Gratis</a>
                        <a href="login.php" class="btn btn-secondary btn-large">Sudah Punya Akun?</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Landing Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>FrozenFood</h3>
                    <p>Platform frozen food terpercaya untuk keluarga Indonesia</p>
                </div>
                <div class="footer-col">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#produk">Produk</a></li>
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Akun</h4>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Daftar</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Kontak</h4>
                    <ul>
                        <li>Email: info@frozenfood.com</li>
                        <li>Telp: (021) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> FrozenFood. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
