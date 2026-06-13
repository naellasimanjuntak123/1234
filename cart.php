<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $cart_id  = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity > 0) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    }
}

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header('Location: cart.php');
    exit();
}

// Ambil data keranjang
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
    /* ── Steps ── */
    .checkout-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin: 24px 0 32px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        background: #e2e8f0;
        color: #94a3b8;
        border: 2px solid #e2e8f0;
    }

    .step.active .step-circle {
        background: var(--primary, #3b82f6);
        color: #fff;
        border-color: var(--primary, #3b82f6);
    }

    .step-label {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }

    .step.active .step-label {
        color: var(--primary, #3b82f6);
        font-weight: 700;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 8px;
        margin-bottom: 20px;
    }

    /* ── Layout ── */
    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }

    @media(max-width:768px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ── Card ── */
    .cart-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        overflow: hidden;
    }

    .cart-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cart-card-header i {
        color: var(--primary, #3b82f6);
        font-size: 17px;
    }

    .cart-card-header h2 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
        flex: 1;
    }

    /* ── Cart Item ── */
    .cart-item {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 16px 22px;
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }

    .cart-item:hover {
        background: #f8fafc;
    }

    .cart-item:last-child {
        border: none;
    }

    .cart-item img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .ci-info {
        flex: 1;
    }

    .ci-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    .ci-price {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
    }

    .ci-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
    }

    .qty-btn:hover {
        background: #e2e8f0;
    }

    .qty-input {
        width: 36px;
        height: 32px;
        border: none;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        border-left: 1.5px solid #e2e8f0;
        border-right: 1.5px solid #e2e8f0;
    }

    .ci-subtotal {
        font-size: 14px;
        font-weight: 800;
        color: #1e293b;
    }

    .ci-del {
        background: none;
        border: none;
        color: #cbd5e1;
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        transition: color .15s;
    }

    .ci-del:hover {
        color: #ef4444;
    }

    /* ── Summary Card ── */
    .summary-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .summary-head {
        padding: 16px 22px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .summary-head i {
        color: var(--primary, #3b82f6);
    }

    .summary-head h2 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }

    .summary-body {
        padding: 16px 22px;
    }

    .sum-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
        color: #64748b;
    }

    .sum-row.total {
        border-top: 2px solid #f1f5f9;
        margin-top: 8px;
        padding-top: 14px;
    }

    .sum-row.total span {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }

    .sum-row.total strong {
        font-size: 22px;
        font-weight: 800;
        color: var(--primary, #3b82f6);
    }

    .btn-to-checkout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        margin-top: 16px;
        background: var(--primary, #3b82f6);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background .2s, transform .1s;
    }

    .btn-to-checkout:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-continue {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px;
        margin-top: 10px;
        background: #f8fafc;
        color: #475569;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
    }

    .btn-continue:hover {
        background: #e2e8f0;
    }

    /* ── Empty ── */
    .empty-cart-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        padding: 64px 32px;
        text-align: center;
    }

    .empty-cart-wrap i {
        font-size: 56px;
        color: #e2e8f0;
    }

    .empty-cart-wrap h2 {
        color: #1e293b;
        margin: 16px 0 8px;
    }

    .empty-cart-wrap p {
        color: #94a3b8;
        margin-bottom: 24px;
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="padding-top:24px; padding-bottom:48px;">

        <!-- Steps -->
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-circle">1</div>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">2</div>
                <span class="step-label">Checkout</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <span class="step-label">Konfirmasi</span>
            </div>
        </div>

        <?php if (count($cart_items) > 0): ?>
        <div class="cart-layout">

            <!-- Left: Items -->
            <div class="cart-card">
                <div class="cart-card-header">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Keranjang Belanja</h2>
                    <span style="font-size:12px;color:#94a3b8;"><?= count($cart_items) ?> item</span>
                </div>

                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                        onerror="this.src='assets/images/default.jpg'">
                    <div class="ci-info">
                        <div class="ci-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="ci-price">Rp <?= number_format($item['price'], 0, ',', '.') ?> / pcs</div>
                    </div>
                    <div class="ci-right">
                        <form method="POST" action="" style="display:contents;">
                            <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                            <div class="qty-control">
                                <button type="button" class="qty-btn"
                                    onclick="changeQty(this,-1,<?= $item['stock'] ?>)">−</button>
                                <input type="number" class="qty-input" name="quantity" value="<?= $item['quantity'] ?>"
                                    min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()">
                                <button type="button" class="qty-btn"
                                    onclick="changeQty(this,1,<?= $item['stock'] ?>)">+</button>
                            </div>
                            <div class="ci-subtotal">Rp
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                            <button type="submit" name="update_cart" style="display:none;"
                                id="updateBtn_<?= $item['id'] ?>"></button>
                        </form>
                        <a href="?remove=<?= $item['id'] ?>" class="ci-del"
                            onclick="return confirm('Hapus produk ini dari keranjang?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Right: Summary -->
            <div class="summary-card">
                <div class="summary-head">
                    <i class="fas fa-receipt"></i>
                    <h2>Ringkasan</h2>
                </div>
                <div class="summary-body">
                    <?php
                    $item_count = array_sum(array_column($cart_items, 'quantity'));
                    ?>
                    <div class="sum-row">
                        <span>Subtotal (<?= $item_count ?> item)</span>
                        <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                    <div class="sum-row">
                        <span>Ongkos kirim</span>
                        <span style="color:#10b981;font-weight:600;">Gratis</span>
                    </div>
                    <div class="sum-row total">
                        <span>Total</span>
                        <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong>
                    </div>
                    <a href="checkout.php" class="btn-to-checkout">
                        <i class="fas fa-lock"></i> Lanjut ke Pembayaran
                    </a>
                    <a href="catalog.php" class="btn-continue">
                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                    </a>
                </div>
            </div>

        </div>

        <?php else: ?>
        <div class="empty-cart-wrap">
            <i class="fas fa-shopping-cart"></i>
            <h2>Keranjang Kosong</h2>
            <p>Belum ada produk di keranjang Anda. Yuk, mulai belanja!</p>
            <a href="catalog.php" class="btn btn-primary btn-large">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
        <?php endif; ?>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/javascript/cart.js"></script>
</body>

</html>