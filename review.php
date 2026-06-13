<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id    = $_SESSION['user_id'];
$order_id   = $_GET['order_id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

// Cek apakah pesanan milik user dan sudah completed
$stmt = $pdo->prepare("SELECT o.*, p.name as product_name, p.image as product_image 
    FROM orders o 
    JOIN order_items oi ON oi.order_id = o.id 
    JOIN products p ON p.id = oi.product_id
    WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'");
$stmt->execute([$order_id, $user_id, $product_id]);
$data = $stmt->fetch();

if (!$data) {
    header('Location: my_orders.php');
    exit();
}

// Cek apakah sudah pernah review
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt->execute([$user_id, $product_id, $order_id]);
$already_reviewed = $stmt->fetch();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_reviewed) {
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Pilih rating bintang terlebih dahulu.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, order_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $product_id, $order_id, $rating, $comment]);
            $success = 'Ulasan berhasil dikirim! Terima kasih.';
            $already_reviewed = true;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan ulasan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Ulasan - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
    .review-container {
        max-width: 560px;
        margin: 40px auto;
        padding: 0 16px 60px;
    }

    .review-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, .09);
        overflow: hidden;
    }

    .review-header {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #fff;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .review-header img {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, .3);
        flex-shrink: 0;
    }

    .review-header h2 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 700;
    }

    .review-header p {
        margin: 0;
        font-size: 13px;
        opacity: .85;
    }

    .review-body {
        padding: 28px;
    }

    /* Star rating */
    .star-group {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 6px;
        margin: 16px 0;
    }

    .star-group input {
        display: none;
    }

    .star-group label {
        font-size: 36px;
        color: #e2e8f0;
        cursor: pointer;
        transition: color .15s, transform .15s;
    }

    .star-group label:hover,
    .star-group label:hover~label,
    .star-group input:checked~label {
        color: #f59e0b;
    }

    .star-group label:hover {
        transform: scale(1.15);
    }

    .rating-text {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 16px;
        min-height: 20px;
    }

    .review-body textarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 14px;
        resize: vertical;
        box-sizing: border-box;
        transition: border .2s;
        font-family: inherit;
    }

    .review-body textarea:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .btn-submit {
        width: 100%;
        margin-top: 16px;
        padding: 14px;
        background: #3b82f6;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s;
    }

    .btn-submit:hover {
        background: #2563eb;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .already-box {
        text-align: center;
        padding: 20px 0 8px;
    }

    .already-box i {
        font-size: 48px;
        color: #10b981;
    }

    .already-box h3 {
        color: #1e293b;
        margin: 12px 0 6px;
    }

    .already-box p {
        color: #64748b;
        font-size: 14px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: #3b82f6;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .back-link:hover {
        color: #2563eb;
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="review-container">
        <a href="order_detail.php?id=<?= $order_id ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Pesanan
        </a>

        <div class="review-card">
            <div class="review-header">
                <img src="<?= htmlspecialchars($data['product_image']) ?>" alt="produk"
                    onerror="this.src='assets/images/default.jpg'">
                <div>
                    <h2><?= htmlspecialchars($data['product_name']) ?></h2>
                    <p>Pesanan #<?= $order_id ?></p>
                </div>
            </div>

            <div class="review-body">
                <?php if ($error): ?>
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success || $already_reviewed): ?>
                <div class="already-box">
                    <i class="fas fa-check-circle"></i>
                    <h3>Ulasan Terkirim!</h3>
                    <p>Terima kasih sudah memberikan ulasan. Pendapatmu sangat berarti bagi kami!</p>
                    <a href="order_detail.php?id=<?= $order_id ?>" class="btn-submit"
                        style="display:inline-block;margin-top:16px;text-decoration:none;text-align:center;">
                        Kembali ke Pesanan
                    </a>
                </div>
                <?php else: ?>
                <p style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 4px;">Bagaimana produk ini?</p>
                <p style="font-size:13px;color:#64748b;margin:0 0 4px;">Berikan rating dan ulasanmu</p>

                <form method="POST" action="">
                    <!-- Star Rating -->
                    <div class="star-group">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>"
                            onchange="updateRatingText(<?= $i ?>)">
                        <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-text" id="ratingText">Klik bintang untuk memberi nilai</div>

                    <label style="font-size:14px;font-weight:600;color:#1e293b;display:block;margin-bottom:8px;">
                        Tulis Ulasan <span style="color:#94a3b8;font-weight:400">(Opsional)</span>
                    </label>
                    <textarea name="comment" rows="4"
                        placeholder="Ceritakan pengalamanmu dengan produk ini..."></textarea>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Kirim Ulasan
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    const ratingLabels = {
        1: '😞 Sangat Buruk',
        2: '😐 Buruk',
        3: '🙂 Cukup',
        4: '😊 Bagus',
        5: '🤩 Sangat Bagus!'
    };

    function updateRatingText(val) {
        document.getElementById('ratingText').textContent = ratingLabels[val] || '';
    }
    </script>
</body>

</html>