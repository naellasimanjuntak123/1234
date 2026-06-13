<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$status_labels = [
    'pending'    => 'Menunggu',
    'paid'       => 'Dibayar',
    'processing' => 'Diproses',
    'shipped'    => 'Dikirim',
    'completed'  => 'Selesai',
    'cancelled'  => 'Dibatalkan',
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $notif_message = "Status pesanan #$order_id diubah menjadi " . ($status_labels[$new_status] ?? $new_status);
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, order_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$order['user_id'], $order_id, $notif_message]);
        
        $message = 'Status pesanan berhasil diperbarui';
        $order['status'] = $new_status;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan #<?= $order_id ?> - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container">
        <h1>Kelola Pesanan #<?= $order_id ?></h1>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="order-manage-container">
            <div class="order-info-section">
                <h2>Informasi Pelanggan</h2>
                <table class="info-table">
                    <tr><td>Nama:</td><td><?= htmlspecialchars($order['customer_name']) ?></td></tr>
                    <tr><td>Email:</td><td><?= htmlspecialchars($order['email']) ?></td></tr>
                    <tr><td>Telepon:</td><td><?= htmlspecialchars($order['phone'] ?? '-') ?></td></tr>
                    <tr><td>Alamat Pengiriman:</td><td><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></td></tr>
                </table>
                <h2>Bukti Pembayaran</h2>
                <?php if(!empty($order["bukti_pembayaran"])) { ?>
                    <img style="height: 300px; width: 300px; object-fit: cover;" src="http://localhost/FrozenFoodd/uploads/payments/<?php echo $order["bukti_pembayaran"]; ?>" alt="">
                <?php } ?>
                
                <h2>Informasi Pesanan</h2>
                <table class="info-table">
                    <tr><td>Tanggal:</td><td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td></tr>
                    <tr><td>Total:</td><td><strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></td></tr>
                    <tr>
                        <td>Metode Pembayaran:</td>
                        <td>
                            <?php
                            $payment_methods = [
                                'bank_transfer' => 'Transfer Bank',
                                'cod'           => 'COD (Bayar di Tempat)',
                                'e_wallet'      => 'E-Wallet'
                            ];
                            echo $payment_methods[$order['payment_method']] ?? 'Transfer Bank';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Layanan Pengiriman:</td>
                        <td>
                            <?php
                            $shipping_services = [
                                'gojek'   => 'GoSend (Gojek)',
                                'grab'    => 'GrabExpress',
                                'pickup'  => 'Ambil di Toko',
                            ];
                            echo $shipping_services[$order['shipping_service'] ?? ''] ?? '-';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Ongkos Kirim:</td>
                        <td>
                            <?php
                            $ongkir = $order['shipping_cost'] ?? 0;
                            echo $ongkir == 0 ? '<strong style="color:green">Gratis!</strong>' : 'Rp ' . number_format($ongkir, 0, ',', '.');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span class="status-badge status-<?= $order['status'] ?>"><?= $status_labels[$order['status']] ?? $order['status'] ?></span></td>
                    </tr>
                </table>
                
                <h2>Perbarui Status</h2>
                <form method="POST" action="">
                    <select name="status" class="form-control">
                        <?php foreach ($status_labels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Perbarui Status</button>
                </form>
            </div>
            
            <div class="order-items-section">
                <h2>Produk yang Dipesan</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="orders.php" class="btn btn-secondary">Kembali</a>
    </main>
</body>
</html>
