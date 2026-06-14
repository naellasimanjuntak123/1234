<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Delete promo
if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM promos WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: promos.php');
    exit();
}

// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE promos SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: promos.php');
    exit();
}

// Add promo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $discount_percent = $_POST['discount_percent'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validasi
    if (empty($name)) {
        die("Nama promo tidak boleh kosong");
    }

    if ($discount_percent < 1 || $discount_percent > 100) {
        die("Diskon harus antara 1-100%");
    }

    if ($start_date > $end_date) {
        die("Tanggal mulai tidak boleh lebih besar dari tanggal berakhir");
    }
    
    $stmt = $pdo->prepare("INSERT INTO promos (name, description, discount_percent, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $discount_percent, $start_date, $end_date]);
    header('Location: promos.php');
    exit();
}

$promos = $pdo->query("SELECT * FROM promos ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Promo - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container">
        <h1>Kelola Promo</h1>
        
        <div class="promo-form">
            <h2>Tambah Promo Baru</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Nama Promo *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="discount_percent">Diskon (%) *</label>
                    <input type="number" id="discount_percent" name="discount_percent" min="1" max="100" required>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Tanggal Mulai *</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Tanggal Berakhir *</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Tambah Promo</button>
            </form>
        </div>
        
        <h2>Daftar Promo</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Diskon</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promos as $promo): ?>
                <tr>
                    <td><?= htmlspecialchars($promo['name']) ?></td>
                    <td><?= $promo['discount_percent'] ?>%</td>
                    <td><?= date('d M Y', strtotime($promo['start_date'])) ?> - <?= date('d M Y', strtotime($promo['end_date'])) ?></td>
                    <td><?= $promo['is_active'] ? '<span class="badge-success">Aktif</span>' : '<span class="badge-danger">Nonaktif</span>' ?></td>
                    <td>
                        <a href="?toggle=<?= $promo['id'] ?>" class="btn btn-small"><?= $promo['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></a>
                        <form method="POST" style="display:inline;">
    <input type="hidden" name="delete_id" value="<?= $promo['id'] ?>">
    <button type="submit" class="btn btn-small btn-danger"
        onclick="return confirm('Hapus promo ini?')">
        Hapus
    </button>
</form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
