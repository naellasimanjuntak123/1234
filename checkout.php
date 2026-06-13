<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$is_direct_checkout = isset($_SESSION['direct_checkout']);
$cart_items = [];
$total = 0;

if ($is_direct_checkout) {
    $direct_item = $_SESSION['direct_checkout'];
    $cart_items[] = [
        'product_id' => $direct_item['product_id'],
        'name'       => $direct_item['name'],
        'price'      => $direct_item['price'],
        'quantity'   => $direct_item['quantity'],
        'image'      => $direct_item['image']
    ];
    $total = $direct_item['price'] * $direct_item['quantity'];
} else {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.stock, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

    if (count($cart_items) === 0) {
        header('Location: cart.php');
        exit();
    }

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$shipping_service = 'gojek';
$ongkir_map = ['gojek' => 15000, 'grab' => 15000, 'pickup' => 0];
$shipping_cost = 15000;
$grand_total = $total + $shipping_cost;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $notes            = trim($_POST['notes'] ?? '');
    $payment_method   = $_POST['payment_method'] ?? 'bank_transfer';
    $shipping_service = $_POST['shipping_service'] ?? 'gojek';

    // Hitung ongkos kirim
    $ongkir_map = [
        'gojek'  => 15000,
        'grab'   => 15000,
        'jnt'    => 12000,
        'pickup' => 0,
    ];
    $shipping_cost = $ongkir_map[$shipping_service] ?? 15000;
    $grand_total = $total + $shipping_cost;

    if ($shipping_address && $payment_method) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, notes, payment_method, shipping_service, shipping_cost, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $grand_total, $shipping_address, $notes, $payment_method, $shipping_service, $shipping_cost]);
            $order_id = $pdo->lastInsertId();

            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);

                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            if ($is_direct_checkout) {
                unset($_SESSION['direct_checkout']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }

            $message = "Pesanan baru #$order_id telah dibuat. Total: Rp " . number_format($grand_total, 0, ',', '.');
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, order_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $order_id, $message]);

            $pdo->commit();

            header("Location: order_detail.php?id=$order_id&new=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Alamat pengiriman dan metode pembayaran harus diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
    /* ── Checkout Steps ── */
    .checkout-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin: 28px 0 36px;
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
        transition: all .3s;
    }

    .step.active .step-circle {
        background: var(--primary, #3b82f6);
        color: #fff;
        border-color: var(--primary, #3b82f6);
    }

    .step.done .step-circle {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
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

    .step.done .step-label {
        color: #10b981;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 8px;
        margin-bottom: 20px;
    }

    /* ── Layout ── */
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }

    @media(max-width:768px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ── Form Card ── */
    .checkout-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        overflow: hidden;
    }

    .checkout-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .checkout-card-header i {
        color: var(--primary, #3b82f6);
        font-size: 18px;
    }

    .checkout-card-header h2 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }

    .checkout-card-body {
        padding: 24px;
    }

    /* ── Form Fields ── */
    .form-row {
        margin-bottom: 18px;
    }

    .form-row label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-row input,
    .form-row textarea,
    .form-row select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        box-sizing: border-box;
        transition: border-color .2s;
    }

    .form-row input:focus,
    .form-row textarea:focus {
        border-color: var(--primary, #3b82f6);
        outline: none;
    }

    .form-row input[readonly] {
        background: #f8fafc;
        color: #64748b;
    }

    /* ── Payment Options ── */
    .payment-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .payment-option-label {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all .2s;
    }

    .payment-option-label:hover {
        border-color: var(--primary, #3b82f6);
        background: #f0f7ff;
    }

    .payment-option-label input[type=radio] {
        display: none;
    }

    .payment-option-label.selected {
        border-color: var(--primary, #3b82f6);
        background: #eff6ff;
    }

    .pay-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: #f1f5f9;
        color: #475569;
        flex-shrink: 0;
    }

    .payment-option-label.selected .pay-icon {
        background: var(--primary, #3b82f6);
        color: #fff;
    }

    .pay-text strong {
        display: block;
        font-size: 14px;
        color: #1e293b;
    }

    .pay-text span {
        font-size: 12px;
        color: #94a3b8;
    }

    /* ── Order Summary Card ── */
    .summary-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        overflow: hidden;
        position: sticky;
        top: 20px;
    }

    .summary-header {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .summary-header i {
        color: var(--primary, #3b82f6);
    }

    .summary-header h2 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }

    .summary-items {
        padding: 16px 24px;
    }

    .summary-item {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-item img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .summary-item-info {
        flex: 1;
    }

    .summary-item-info .item-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    .summary-item-info .item-qty {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .summary-item-price {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
    }

    .summary-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 0 24px;
    }

    .summary-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
    }

    .summary-total-row span {
        font-size: 14px;
        color: #64748b;
    }

    .summary-total-row strong {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary, #3b82f6);
    }

    .summary-action {
        padding: 0 24px 24px;
    }

    .btn-checkout {
        width: 100%;
        padding: 14px;
        background: var(--primary, #3b82f6);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background .2s, transform .1s;
    }

    .btn-checkout:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-checkout:active {
        transform: translateY(0);
    }

    .secure-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px;
        color: #64748b;
        font-size: 12px;
    }

    .secure-badge i {
        color: #10b981;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>


    <main class="container" style="padding-top:24px; padding-bottom:48px;">

        <!-- Steps -->
        <div class="checkout-steps">
            <div class="step done">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-circle">2</div>
                <span class="step-label">Checkout</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <span class="step-label">Konfirmasi</span>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="checkoutForm">
            <div class="checkout-layout">

                <!-- Left: Form -->
                <div style="display:flex; flex-direction:column; gap:20px;">

                    <!-- Shipping Info -->
                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <h2>Informasi Pengiriman</h2>
                        </div>
                        <div class="checkout-card-body">
                            <div class="form-row">
                                <label>Nama Penerima</label>
                                <input type="text" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                            </div>
                            <div class="form-row">
                                <label>Nomor Telepon</label>
                                <input type="text" name="phone" placeholder="Masukkan nomor telepon">
                            </div>
                            <div class="form-row">
                                <label>Alamat Pengiriman <span style="color:#ef4444">*</span></label>
                                <textarea name="shipping_address" rows="4"
                                    placeholder="Masukkan alamat lengkap termasuk kota dan kode pos..."
                                    required></textarea>
                            </div>
                            <div class="form-row">
                                <label>Catatan untuk Penjual <span style="color:#94a3b8">(Opsional)</span></label>
                                <textarea name="notes" rows="2"
                                    placeholder="Contoh: Tolong dikemas dengan bubble wrap..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <div class="checkout-card-header">
                            <i class="fas fa-credit-card"></i>
                            <h2>Metode Pembayaran</h2>
                        </div>
                        <div class="checkout-card-body">
                            <div class="payment-options" id="paymentOptions">

                                <label class="payment-option-label selected" id="opt_bank_transfer">
                                    <input type="radio" name="payment_method" value="bank_transfer" checked>
                                    <div class="pay-icon"><i class="fas fa-university"></i></div>
                                    <div class="pay-text">
                                        <strong>Transfer Bank</strong>
                                        <span>BCA · Mandiri · BNI · BRI</span>
                                    </div>
                                </label>

                                <label class="payment-option-label" id="opt_e_wallet">
                                    <input type="radio" name="payment_method" value="e_wallet">
                                    <div class="pay-icon"><i class="fas fa-wallet"></i></div>
                                    <div class="pay-text">
                                        <strong>E-Wallet</strong>
                                        <span>GoPay · OVO · DANA · ShopeePay</span>
                                    </div>
                                </label>

                                <label class="payment-option-label" id="opt_cod">
                                    <input type="radio" name="payment_method" value="cod">
                                    <div class="pay-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="pay-text">
                                        <strong>COD (Bayar di Tempat)</strong>
                                        <span>Bayar tunai saat barang diterima</span>
                                    </div>
                                </label>

                            </div>
                        </div>
                    </div>

                </div>


                <!-- Layanan Pengiriman -->
                <div class="checkout-card">
                    <div class="checkout-card-header">
                        <i class="fas fa-motorcycle"></i>
                        <h2>Layanan Pengiriman</h2>
                    </div>
                    <div class="checkout-card-body">
                        <div class="payment-options" id="shippingOptions">

                            <label class="payment-option-label selected" id="opt_gojek">
                                <input type="radio" name="shipping_service" value="gojek" checked>
                                <div class="pay-icon" style="background:#00AA13;color:#fff"><i
                                        class="fas fa-motorcycle"></i></div>
                                <div class="pay-text">
                                    <strong>GoSend (Gojek)</strong>
                                    <span>Jakarta &amp; Sekitarnya &mdash; Rp 15.000</span>
                                </div>
                            </label>

                            <label class="payment-option-label" id="opt_grab">
                                <input type="radio" name="shipping_service" value="grab">
                                <div class="pay-icon" style="background:#00B14F;color:#fff"><i
                                        class="fas fa-bicycle"></i></div>
                                <div class="pay-text">
                                    <strong>GrabExpress</strong>
                                    <span>Jakarta &amp; Sekitarnya &mdash; Rp 15.000</span>
                                </div>
                            </label>

                            <label class="payment-option-label" id="opt_jnt">
                                <input type="radio" name="shipping_service" value="jnt">
                                <div class="pay-icon" style="background:#e31837;color:#fff"><i class="fas fa-box"></i>
                                </div>
                                <div class="pay-text">
                                    <strong>J&T Express</strong>
                                    <span>Estimasi 1-3 hari &mdash; Rp 12.000</span>
                                </div>
                            </label>

                            <label class="payment-option-label" id="opt_pickup">
                                <input type="radio" name="shipping_service" value="pickup">
                                <div class="pay-icon" style="background:#10b981;color:#fff"><i class="fas fa-store"></i>
                                </div>
                                <div class="pay-text">
                                    <strong>Ambil di Toko</strong>
                                    <span style="color:#10b981;font-weight:700">Gratis!</span>
                                </div>
                            </label>
                            <div id="pickupMapBox"
                                style="display:none;margin-top:10px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:14px;">
                                <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#166534;">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Toko
                                </p>
                                <p style="margin:0 0 10px;font-size:13px;color:#166534;">
                                    Jl. Bali 3 No. 55A, RT. 9/RW. 3, Kalideres, Jakarta Barat
                                </p>
                                <a href="https://www.google.com/maps/search/?api=1&query=Jl.+Bali+3+No.+55A+Kalideres+Jakarta+Barat"
                                    target="_blank"
                                    style="display:inline-flex;align-items:center;gap:8px;background:#10b981;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">
                                    <i class="fas fa-map"></i> Buka Google Maps
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Right: Summary -->
                <div>
                    <div class="summary-card">
                        <div class="summary-header">
                            <i class="fas fa-receipt"></i>
                            <h2>Ringkasan Pesanan</h2>
                        </div>
                        <div class="summary-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <img src="<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                    onerror="this.src='assets/images/default.jpg'">
                                <div class="summary-item-info">
                                    <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="item-qty">x<?= $item['quantity'] ?></div>
                                </div>
                                <div class="summary-item-price">Rp
                                    <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-total-row" style="padding-bottom:4px">
                            <span>Subtotal Produk</span>
                            <span style="font-weight:600">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <div class="summary-total-row" style="padding-top:4px;padding-bottom:4px" id="ongkirRow">
                            <span>Ongkos Kirim</span>
                            <span style="font-weight:600" id="ongkirDisplay">Rp 15.000</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-total-row">
                            <span>Total Pembayaran</span>
                            <strong id="grandTotalDisplay">Rp <?= number_format($total + 15000, 0, ',', '.') ?></strong>
                        </div>
                        <div class="summary-action">
                            <button type="submit" class="btn-checkout">
                                <i class="fas fa-lock"></i> Buat Pesanan
                            </button>
                            <div class="secure-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Transaksi aman &amp; terenkripsi</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/javascript/checkout.js">
    const baseTotal = <?= $total ?>;
    </script>
</body>

</html>