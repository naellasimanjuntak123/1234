<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: products.php');
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
$description = trim($_POST['description']);
$price = $_POST['price'];
$stock = $_POST['stock'];
$category = trim($_POST['category']);
    // 3. Validasi data wajib
    if (empty($name) || empty($category)) {
        $message = 'Nama produk dan kategori wajib diisi';
    }

    // 4. Validasi harga
    elseif ($price <= 0) {
        $message = 'Harga harus lebih dari 0';
    }

    // 5. Validasi stok
    elseif ($stock < 0) {
        $message = 'Stok tidak boleh negatif';
    }

    else {
    $image_path = $product['image'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $message = 'Format gambar harus JPG, JPEG, PNG atau WEBP';
            }
            elseif ($_FILES['image']['size'] > 2097152) {
                $message = 'Ukuran gambar maksimal 2 MB';
            }
            else {
                $filename = 'product_' . time() . '.' . $ext;
                $upload_path = '../assets/images/' . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = 'assets/images/' . $filename;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image = ? WHERE id = ?");

        if ($stmt->execute([$name, $description, $price, $stock, $category, $image_path, $id])) {
            header('Location: products.php?success=updated');
            exit();
        } else {
            $message = 'Gagal mengupdate produk';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container">
        <h1>Edit Produk</h1>
        
        <?php if ($message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="name">Nama Produk *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Kategori *</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Harga *</label>
                <input type="number" id="price" name="price" value="<?= $product['price'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok *</label>
                <input type="number" id="stock" name="stock" value="<?= $product['stock'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Gambar Saat Ini</label>
                <img src="../<?= htmlspecialchars($product['image']) ?>" alt="" style="max-width: 200px;">
            </div>
            
            <div class="form-group">
                <label for="image">Ganti Gambar</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="products.php" class="btn btn-secondary">Batal</a>
        </form>
    </main>
</body>
</html>
