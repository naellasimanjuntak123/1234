<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id  = $_SESSION['user_id'];
$order_id = $_GET['id'] ?? 0;
$is_new   = isset($_GET['new']);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: my_orders.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$upload_message = '';
$upload_error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {
    $file = $_FILES['payment_proof'];
    $allowed = ['image/jpeg','image/png','image/jpg','image/gif'];
    if ($file['error'] === 0 && in_array($file['type'], $allowed) && $file['size'] < 5*1024*1024) {
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "payment_{$order_id}_" . time() . ".$ext";
        $dest     = "uploads/payments/$filename";
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            try {
                $pdo->prepare("UPDATE orders SET status='paid', bukti_pembayaran='".$filename."' WHERE id=? AND user_id=?")->execute([$order_id, $user_id]);
                $msg2 = "Bukti pembayaran pesanan #$order_id telah dikirim dan sedang diverifikasi.";
                $pdo->prepare("INSERT INTO notifications (user_id, order_id, message) VALUES (?,?,?)")->execute([$user_id, $order_id, $msg2]);
                $upload_message = "Bukti pembayaran berhasil dikirim! Tim kami akan memverifikasi dalam 1×24 jam.";
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
                $stmt->execute([$order_id, $user_id]);
                $order = $stmt->fetch();
            } catch(Exception $e) {
                $upload_message = "Bukti pembayaran berhasil dikirim!";
            }
        } else {
            $upload_error = "Gagal mengunggah file. Silakan coba lagi.";
        }
    } else {
        $upload_error = "File tidak valid. Gunakan JPG/PNG maksimal 5MB.";
    }
}

$status_labels = [
    'pending'    => 'Menunggu Pembayaran',
    'paid'       => 'Pembayaran Dikonfirmasi',
    'processing' => 'Sedang Diproses',
    'shipped'    => 'Dalam Pengiriman',
    'completed'  => 'Pesanan Selesai',
    'cancelled'  => 'Dibatalkan',
];

$payment_labels = [
    'bank_transfer' => 'Transfer Bank',
    'cod'           => 'COD (Bayar di Tempat)',
    'e_wallet'      => 'E-Wallet',
];

$status_seq = ['pending','paid','processing','shipped','completed'];
$cur_idx    = array_search($order['status'], $status_seq);

// Step 3 (Konfirmasi) = done kalau status sudah completed, active kalau selain itu
$step3_class = ($order['status'] === 'completed') ? 'done' : 'active';
$step3_content = ($order['status'] === 'completed') ? '<i class="fas fa-check"></i>' : '3';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan #<?= $order_id ?> - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
    .order-success-banner {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border-radius: 14px;
        padding: 24px 28px;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 28px;
        box-shadow: 0 4px 20px rgba(16, 185, 129, .25);
    }

    .success-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, .2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .success-text h2 {
        margin: 0 0 4px;
        font-size: 20px;
        font-weight: 800;
    }

    .success-text p {
        margin: 0;
        font-size: 14px;
        opacity: .88;
    }

    .checkout-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin: 0 0 32px;
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

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        align-items: start;
    }

    @media(max-width:768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .od-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .od-card-header {
        padding: 16px 22px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .od-card-header i {
        color: var(--primary, #3b82f6);
        font-size: 17px;
    }

    .od-card-header h2 {
        font-size: 15px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
        flex: 1;
    }

    .od-card-body {
        padding: 22px;
    }

    .order-item-row {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .order-item-row:last-child {
        border: none;
    }

    .order-item-row img {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .oi-info {
        flex: 1;
    }

    .oi-info .oi-name {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    .oi-info .oi-qty {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    .oi-price {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
    }

    .oi-review {
        margin-left: 8px;
    }

    .total-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 22px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }

    .total-bar span {
        font-size: 14px;
        color: #64748b;
    }

    .total-bar strong {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary, #3b82f6);
    }

    .info-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #f8fafc;
    }

    .info-row:last-child {
        border: none;
    }

    .info-row>i {
        color: #94a3b8;
        width: 20px;
        text-align: center;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .info-row-content label {
        display: block;
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .info-row-content p {
        margin: 2px 0 0;
        font-size: 14px;
        color: #1e293b;
    }

    .payment-box {
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 12px;
        padding: 18px 20px;
    }

    .payment-box h4 {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 700;
        color: #1e40af;
    }

    .bank-row {
        display: flex;
        gap: 14px;
        align-items: center;
        background: #fff;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        border: 1px solid #dbeafe;
    }

    .bank-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #3b82f6;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
    }

    .bank-info .bank-name {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
    }

    .bank-info .bank-number {
        font-size: 20px;
        font-weight: 800;
        color: #1e40af;
        letter-spacing: 1px;
        margin: 2px 0;
    }

    .bank-info .bank-holder {
        font-size: 12px;
        color: #64748b;
    }

    .copy-btn {
        margin-left: auto;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        background: #3b82f6;
        color: #fff;
        border: none;
        border-radius: 7px;
        cursor: pointer;
        transition: background .2s;
    }

    .copy-btn:hover {
        background: #2563eb;
    }

    .amount-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border-radius: 10px;
        padding: 12px 16px;
        border: 1px solid #dbeafe;
        margin-bottom: 12px;
    }

    .amount-box span {
        font-size: 13px;
        color: #64748b;
    }

    .amount-box strong {
        font-size: 18px;
        font-weight: 800;
        color: #10b981;
    }

    .pay-note {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }

    .pay-note i {
        color: #f59e0b;
        margin-top: 2px;
    }

    .upload-box {
        border: 2px dashed #bfdbfe;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all .2s;
    }

    .upload-box:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .upload-box i {
        font-size: 32px;
        color: #94a3b8;
    }

    .upload-box p {
        margin: 8px 0 4px;
        font-size: 14px;
        color: #475569;
    }

    .upload-box span {
        font-size: 12px;
        color: #94a3b8;
    }

    #proofInput {
        display: none;
    }

    #previewImg {
        max-width: 100%;
        border-radius: 10px;
        margin-top: 12px;
        display: none;
    }

    .btn-upload {
        width: 100%;
        margin-top: 14px;
        padding: 13px;
        background: #10b981;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background .2s;
    }

    .btn-upload:hover {
        background: #059669;
    }

    .btn-upload.show {
        display: flex;
    }

    .timeline {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .tl-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        position: relative;
    }

    .tl-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
    }

    .tl-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        background: #e2e8f0;
        color: #94a3b8;
        border: 2px solid #e2e8f0;
        transition: all .3s;
        z-index: 1;
    }

    .tl-item.done .tl-dot {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
    }

    .tl-item.active .tl-dot {
        background: var(--primary, #3b82f6);
        color: #fff;
        border-color: var(--primary, #3b82f6);
    }

    .tl-line {
        width: 2px;
        flex: 1;
        background: #e2e8f0;
        min-height: 30px;
    }

    .tl-item.done .tl-line {
        background: #10b981;
    }

    .tl-item:last-child .tl-line {
        display: none;
    }

    .tl-content {
        padding-bottom: 20px;
    }

    .tl-content strong {
        display: block;
        font-size: 14px;
        color: #1e293b;
        font-weight: 700;
    }

    .tl-content span {
        font-size: 12px;
        color: #94a3b8;
    }

    .cod-box {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .cod-box i {
        color: #10b981;
        font-size: 20px;
        margin-top: 2px;
    }

    .cod-box p {
        margin: 0;
        font-size: 13px;
        color: #166534;
        line-height: 1.6;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-pill.paid {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pill.processing {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-pill.shipped {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-pill.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pill.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .page-title-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .page-title-row h1 {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .order-date {
        font-size: 13px;
        color: #94a3b8;
        margin: 4px 0 0;
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container" style="padding-top:24px; padding-bottom:48px;">

        <?php if ($is_new): ?>
        <div class="order-success-banner">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <div class="success-text">
                <h2>Pesanan Berhasil Dibuat!</h2>
                <p>Pesanan #<?= $order_id ?> sedang menunggu pembayaran Anda.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Steps -->
        <div class="checkout-steps">
            <div class="step done">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-line"></div>
            <div class="step done">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <span class="step-label">Checkout</span>
            </div>
            <div class="step-line"></div>
            <div class="step <?= $step3_class ?>">
                <div class="step-circle"><?= $step3_content ?></div>
                <span class="step-label">Konfirmasi</span>
            </div>
        </div>

        <div class="page-title-row">
            <div>
                <h1><i class="fas fa-receipt" style="color:var(--primary,#3b82f6);margin-right:8px;"></i>Detail Pesanan
                    #<?= $order_id ?></h1>
                <p class="order-date"><i class="fas fa-calendar-alt"></i>
                    <?= date('d F Y, H:i', strtotime($order['created_at'])) ?> WIB</p>
            </div>
            <span class="status-pill <?= $order['status'] ?>">
                <?= $status_labels[$order['status']] ?? strtoupper($order['status']) ?>
            </span>
        </div>

        <?php if ($upload_message): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($upload_message) ?></div>
        <?php endif; ?>
        <?php if ($upload_error): ?>
        <div class="alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($upload_error) ?></div>
        <?php endif; ?>

        <div class="detail-grid">
            <div>
                <div class="od-card">
                    <div class="od-card-header">
                        <i class="fas fa-box-open"></i>
                        <h2>Produk yang Dipesan</h2>
                        <span style="font-size:12px;color:#94a3b8;"><?= count($order_items) ?> item</span>
                    </div>
                    <div class="od-card-body" style="padding-bottom:0;">
                        <?php foreach ($order_items as $item): ?>
                        <div class="order-item-row">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                onerror="this.src='assets/images/default.jpg'">
                            <div class="oi-info">
                                <div class="oi-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="oi-qty">x<?= $item['quantity'] ?> &nbsp;·&nbsp; Rp
                                    <?= number_format($item['price'], 0, ',', '.') ?>/pcs</div>
                            </div>
                            <div class="oi-price">Rp
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></div>
                            <?php if ($order['status'] === 'completed'): ?>
                            <a href="review.php?order_id=<?= $order_id ?>&product_id=<?= $item['product_id'] ?>"
                                class="btn btn-primary btn-small oi-review">
                                <i class="fas fa-star"></i> Nilai
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-bar">
                        <span>Total Pembayaran</span>
                        <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div class="od-card">
                    <div class="od-card-header">
                        <i class="fas fa-shipping-fast"></i>
                        <h2>Informasi Pengiriman</h2>
                    </div>
                    <div class="od-card-body">
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="info-row-content">
                                <label>Alamat Pengiriman</label>
                                <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-credit-card"></i>
                            <div class="info-row-content">
                                <label>Metode Pembayaran</label>
                                <p><?= $payment_labels[$order['payment_method']] ?? $order['payment_method'] ?></p>
                            </div>
                        </div>
                        <?php if ($order['notes']): ?>
                        <div class="info-row">
                            <i class="fas fa-sticky-note"></i>
                            <div class="info-row-content">
                                <label>Catatan</label>
                                <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($order['status'] === 'pending'): ?>
                <div class="od-card">
                    <div class="od-card-header">
                        <i class="fas fa-money-check-alt"></i>
                        <h2>Instruksi Pembayaran</h2>
                    </div>
                    <div class="od-card-body">
                        <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                        <div class="payment-box">
                            <h4><i class="fas fa-info-circle"></i> Transfer ke rekening berikut:</h4>
                            <div class="bank-row">
                                <div class="bank-icon"><i class="fas fa-university"></i></div>
                                <div class="bank-info">
                                    <div class="bank-name">Bank BCA</div>
                                    <div class="bank-number">1234567890</div>
                                    <div class="bank-holder">a.n. FrozenFood Indonesia</div>
                                </div>
                                <button class="copy-btn" onclick="copyText('1234567890', this)">Salin</button>
                            </div>
                            <div class="bank-row">
                                <div class="bank-icon" style="background:#003d7a;"><i class="fas fa-university"></i>
                                </div>
                                <div class="bank-info">
                                    <div class="bank-name">Bank Mandiri</div>
                                    <div class="bank-number">1100009876543</div>
                                    <div class="bank-holder">a.n. FrozenFood Indonesia</div>
                                </div>
                                <button class="copy-btn" style="background:#003d7a;"
                                    onclick="copyText('1100009876543', this)">Salin</button>
                            </div>
                            <div class="amount-box">
                                <span>Jumlah yang harus ditransfer</span>
                                <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                            </div>
                            <div class="pay-note">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Pastikan nominal transfer <strong>tepat sama</strong> agar pesanan lebih cepat
                                    diverifikasi.</span>
                            </div>
                        </div>
                        <?php elseif ($order['payment_method'] === 'e_wallet'): ?>
                        <div class="payment-box" style="background:#fef3c7;border-color:#fde68a;">
                            <h4 style="color:#92400e;"><i class="fas fa-wallet"></i> Transfer ke E-Wallet:</h4>
                            <div class="bank-row">
                                <div class="bank-icon" style="background:#00aaa0;"><i class="fas fa-wallet"></i></div>
                                <div class="bank-info">
                                    <div class="bank-name">GoPay / OVO / DANA</div>
                                    <div class="bank-number" style="color:#00aaa0;">0812-3456-7890</div>
                                    <div class="bank-holder">a.n. FrozenFood</div>
                                </div>
                                <button class="copy-btn" style="background:#00aaa0;"
                                    onclick="copyText('081234567890', this)">Salin</button>
                            </div>
                            <div class="amount-box">
                                <span>Jumlah transfer</span>
                                <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong>
                            </div>
                        </div>
                        <?php elseif ($order['payment_method'] === 'cod'): ?>
                        <div class="cod-box">
                            <i class="fas fa-truck"></i>
                            <p>Anda memilih <strong>Bayar di Tempat (COD)</strong>. Siapkan uang tunai sebesar
                                <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong> saat kurir
                                tiba.</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($order['payment_method'] !== 'cod'): ?>
                        <div style="margin-top:20px;">
                            <p style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:12px;">
                                <i class="fas fa-upload" style="color:var(--primary,#3b82f6);"></i> Upload Bukti
                                Pembayaran
                            </p>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="upload-box" onclick="document.getElementById('proofInput').click()">
                                    <i class="fas fa-camera" id="uploadIcon"></i>
                                    <p id="uploadText">Klik untuk pilih gambar bukti transfer</p>
                                    <span>JPG, PNG – Maks. 5MB</span>
                                    <img id="previewImg" src="" alt="Preview">
                                </div>
                                <input type="file" id="proofInput" name="payment_proof" accept="image/*"
                                    onchange="previewImage(this)">
                                <button type="submit" class="btn-upload" id="btnUpload">
                                    <i class="fas fa-paper-plane"></i> Kirim Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT -->
            <div>
                <div class="od-card">
                    <div class="od-card-header">
                        <i class="fas fa-stream"></i>
                        <h2>Status Pesanan</h2>
                    </div>
                    <div class="od-card-body">
                        <div class="timeline">
                            <?php
                            $steps = [
                                ['pending',    'fas fa-shopping-cart', 'Pesanan Dibuat',      date('d M Y, H:i', strtotime($order['created_at']))],
                                ['paid',       'fas fa-check-circle',  'Pembayaran Diterima', $order['status'] !== 'pending' ? 'Sudah diverifikasi' : 'Menunggu pembayaran'],
                                ['processing', 'fas fa-cog',           'Sedang Diproses',     'Pesanan sedang disiapkan'],
                                ['shipped',    'fas fa-truck',         'Dalam Pengiriman',    'Produk dalam perjalanan'],
                                ['completed',  'fas fa-home',          'Pesanan Tiba',        'Pesanan diterima'],
                            ];
                            foreach ($steps as [$s, $icon, $label, $sub]):
                                $si = array_search($s, $status_seq);
                                // FIXED: kalau status completed, semua step jadi done termasuk yg terakhir
                                if ($order['status'] === 'completed') {
                                    $cls = 'done';
                                } else {
                                    $cls = ($si < $cur_idx) ? 'done' : (($si === $cur_idx) ? 'active' : '');
                                }
                            ?>
                            <div class="tl-item <?= $cls ?>">
                                <div class="tl-left">
                                    <div class="tl-dot"><i class="<?= $icon ?>"></i></div>
                                    <div class="tl-line"></div>
                                </div>
                                <div class="tl-content">
                                    <strong><?= $label ?></strong>
                                    <span><?= $sub ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="od-card">
                    <div class="od-card-header">
                        <i class="fas fa-headset"></i>
                        <h2>Butuh Bantuan?</h2>
                    </div>
                    <div class="od-card-body" style="display:flex;flex-direction:column;gap:10px;">
                        <a href="https://wa.me/6285719113750?text=Halo,%20saya%20ingin%20menanyakan%20pesanan%20%23<?= $order_id ?>"
                            target="_blank"
                            style="display:flex;align-items:center;gap:12px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;text-decoration:none;color:#166534;font-size:13px;font-weight:600;">
                            <i class="fab fa-whatsapp" style="font-size:20px;color:#25d366;"></i> Chat via WhatsApp
                        </a>
                        <a href="my_orders.php"
                            style="display:flex;align-items:center;gap:12px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#475569;font-size:13px;font-weight:600;">
                            <i class="fas fa-list" style="color:#94a3b8;"></i> Semua Pesanan Saya
                        </a>
                        <a href="catalog.php"
                            style="display:flex;align-items:center;gap:12px;padding:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;text-decoration:none;color:#1e40af;font-size:13px;font-weight:600;">
                            <i class="fas fa-shopping-bag" style="color:#3b82f6;"></i> Belanja Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.textContent;
            btn.textContent = '✓ Tersalin';
            btn.style.background = '#10b981';
            setTimeout(() => {
                btn.textContent = orig;
                btn.style.background = '';
            }, 2000);
        });
    }

    function previewImage(input) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('previewImg');
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById('uploadIcon').style.display = 'none';
            document.getElementById('uploadText').textContent = file.name;
            document.getElementById('btnUpload').classList.add('show');
        };
        reader.readAsDataURL(file);
    }
    </script>
</body>

</html>