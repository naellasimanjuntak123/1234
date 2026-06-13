<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: catalog.php');
    exit();
}

// Ambil review produk
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// Hitung rata-rata rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?");
$stmt->execute([$product_id]);
$rating_data = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'] ?? 1;
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['buy_now'])) {
        // Beli sekarang - langsung ke checkout
        // Simpan data sementara di session
        $_SESSION['direct_checkout'] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price'],
            'name' => $product['name'],
            'image' => $product['image']
        ];
        header('Location: checkout.php');
        exit();
    } elseif (isset($_POST['add_to_cart'])) {
        // Tambah ke keranjang
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $user_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        $message = 'Produk berhasil ditambahkan ke keranjang!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="product-detail">
            <div class="product-image">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <?php if ($rating_data['total_reviews'] > 0): ?>
                <div class="rating">
                    <span class="stars">★ <?= number_format($rating_data['avg_rating'], 1) ?></span>
                    <span>(<?= $rating_data['total_reviews'] ?> ulasan)</span>
                </div>
                <?php endif; ?>
                
                <p class="price">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                <p class="stock">Stok tersedia: <?= $product['stock'] ?></p>
                
                <div class="description">
                    <h3>Deskripsi Produk</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <form method="POST" action="" class="add-to-cart-form">
                    <div class="quantity-selector">
                        <label for="quantity">Jumlah:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                    </div>
                    <div class="product-actions">
                        <button type="submit" name="add_to_cart" class="btn btn-secondary btn-large">
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                        <button type="submit" name="buy_now" class="btn btn-primary btn-large">
                            <i class="fas fa-bolt"></i> Beli Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="reviews-section">
            <h2>Ulasan Produk</h2>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                        <span class="rating">★ <?= $review['rating'] ?></span>
                    </div>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <small><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada ulasan untuk produk ini.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
