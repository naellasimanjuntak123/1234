<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
} 
    header('Location: products.php');
    exit();
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Kelola Produk</h1>
            <a href="product_add.php" class="btn btn-primary">Tambah Produk</a>
        </div>
        <?php if(isset($_GET['success']) && $_GET['success'] == 'added'): ?>
        <div class="alert alert-success">
            Produk berhasil ditambahkan
        </div>
    <?php endif; ?>

    <table class="admin-table"></table>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?php if (!empty($product['image'])): ?>
                    <img src="../<?= htmlspecialchars($product['image']) ?>" class="table-img">
                    <?php else: ?>
                    <?php endif; ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category']) ?></td>
                    <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                    <td><?= $product['stock'] ?></td>
                    <td>
                        <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-small">Edit</a>
                        <?php if(isset($_GET['success'])): ?><div class="alert-success">Produk berhasil dihapus</div><?php endif; ?>
                        <?php if(empty($products)): ?><tr><td colspan="7">Belum ada produk</td></tr><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
