<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Tandai dibaca
if (isset($_GET['read'])) {
    $notif_id = (int)$_GET['read'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    header('Location: notifications.php');
    exit();
}

// Hapus satu notifikasi
if (isset($_GET['hapus'])) {
    $notif_id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    header('Location: notifications.php');
    exit();
}

// Hapus semua notifikasi
if (isset($_GET['hapus_semua'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header('Location: notifications.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - FrozenFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=6.0">
    <style>
        .notif-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
        .notification-card { position:relative; padding-right:48px; }
        .btn-hapus-notif {
            position:absolute; top:50%; right:12px; transform:translateY(-50%);
            background:none; border:none; color:#ef4444; font-size:16px;
            cursor:pointer; padding:6px; border-radius:6px; transition:background .15s;
        }
        .btn-hapus-notif:hover { background:#fef2f2; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="notif-header">
            <h1 style="margin:0">Notifikasi</h1>
            <?php if (count($notifications) > 0): ?>
            <a href="?hapus_semua=1" class="btn btn-secondary" onclick="return confirm('Hapus semua notifikasi?')"
               style="background:#ef4444;color:#fff;border-color:#ef4444;">
                <i class="fas fa-trash"></i> Hapus Semua
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (count($notifications) > 0): ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notif): ?>
            <div class="notification-card <?= $notif['is_read'] ? 'read' : 'unread' ?>">
                <p><?= htmlspecialchars($notif['message']) ?></p>
                <small><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                    <?php if (!$notif['is_read']): ?>
                    <a href="?read=<?= $notif['id'] ?>" class="btn btn-small">Tandai Dibaca</a>
                    <?php endif; ?>
                    <?php if ($notif['order_id']): ?>
                    <a href="order_detail.php?id=<?= $notif['order_id'] ?>" class="btn btn-small btn-primary">Lihat Pesanan</a>
                    <?php endif; ?>
                </div>
                <a href="?hapus=<?= $notif['id'] ?>" class="btn-hapus-notif" title="Hapus notifikasi"
                   onclick="return confirm('Hapus notifikasi ini?')">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:#64748b;text-align:center;padding:40px 0;">
            <i class="fas fa-bell-slash" style="font-size:32px;display:block;margin-bottom:12px;color:#cbd5e1"></i>
            Tidak ada notifikasi
        </p>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
