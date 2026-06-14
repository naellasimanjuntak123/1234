<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
$description = trim($_POST['description']);
$price = $_POST['price'];
$stock = $_POST['stock'];
$category = trim($_POST['category']);

// 1. Validasi data wajib
if (empty($name) || empty($category)) {
    $message = 'Nama produk dan kategori wajib diisi';
}

// 2. Validasi harga
elseif ($price <= 0) {
    $message = 'Harga harus lebih dari 0';
}

// 3. Validasi stok
elseif ($stock < 0) {
    $message = 'Stok tidak boleh negatif';
}}

else {
    
    $image_path = 'assets/images/default.jpg';

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    // 4. Validasi tipe file
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        $message = 'Format gambar harus JPG, JPEG, PNG, atau WEBP';
    }

    // 5. Validasi ukuran file (2MB)
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
}}
    
    if (empty($message)) {

    $stmt = $pdo->prepare("
        INSERT INTO products
        (name, description, price, stock, category, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // 6 & 7. Simpan dan tampilkan notifikasi sukses
    if ($stmt->execute([
        $name,
        $description,
        $price,
        $stock,
        $category,
        $image_path
    ])) {

        header('Location: products.php?success=added');
        exit();

    } else {
        $message = 'Gagal menambahkan produk';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container">
        <h1>Tambah Produk</h1>
        
        <?php if ($message): ?>
        <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label for="name">Nama Produk *</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="category">Kategori *</label>
                <input type="text" id="category" name="category" required>
            </div>
            
            <div class="form-group">
                <label for="price">Harga *</label>
                <input type="number" id="price" name="price" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok *</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Gambar Produk</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="products.php" class="btn btn-secondary">Batal</a>
        </form>
    </main>
</body>
</html>
