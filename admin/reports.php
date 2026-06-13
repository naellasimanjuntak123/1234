<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date']   ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue, AVG(total_amount) as avg_order_value
    FROM orders WHERE status IN ('paid','processing','shipped','completed')
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$sales_report = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('paid','processing','shipped','completed') AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue
    FROM orders WHERE status IN ('paid','processing','shipped','completed')
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at) ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - FrozenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.6.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
    .report-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
        flex-wrap: wrap;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: #fff;
        }
    }
    </style>
</head>

<body>
    <?php include 'includes/admin_header.php'; ?>

    <main class="container" id="reportContent">
        <h1>Laporan Penjualan</h1>

        <div class="no-print report-actions">
            <form method="GET" class="filter-form"
                style="margin:0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <label>Dari:</label>
                <input type="date" name="start_date" value="<?= $start_date ?>">
                <label>Sampai:</label>
                <input type="date" name="end_date" value="<?= $end_date ?>">
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>

            <button onclick="exportPDF()" class="btn btn-primary" style="background:#ef4444;border-color:#ef4444;">
                <i class="fas fa-file-pdf"></i> PDF
            </button>

            <button onclick="exportExcel()" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;">
                <i class="fas fa-file-excel"></i> Excel
            </button>

            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>

        <p style="color:#64748b;font-size:13px;">Periode: <?= date('d M Y', strtotime($start_date)) ?> &ndash;
            <?= date('d M Y', strtotime($end_date)) ?></p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pesanan</h3>
                <p class="stat-number"><?= $sales_report['total_orders'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Pendapatan</h3>
                <p class="stat-number">Rp <?= number_format($sales_report['total_revenue'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="stat-card">
                <h3>Rata-rata Nilai Pesanan</h3>
                <p class="stat-number">Rp <?= number_format($sales_report['avg_order_value'] ?? 0, 0, ',', '.') ?></p>
            </div>
        </div>

        <h2>Produk Terlaris</h2>
        <table class="admin-table" id="tableTopProducts">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Terjual</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= $product['total_sold'] ?></td>
                    <td>Rp <?= number_format($product['revenue'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Penjualan Harian</h2>
        <table class="admin-table" id="tableDailySales">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Pesanan</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_sales as $day): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($day['date'])) ?></td>
                    <td><?= $day['orders'] ?></td>
                    <td>Rp <?= number_format($day['revenue'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <script>
    // ── Export PDF ──
    function exportPDF() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text('Laporan Penjualan FrozenFood', 14, 18);
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.text('Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>',
            14, 26);

        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('Ringkasan', 14, 36);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(10);
        doc.text('Total Pesanan    : <?= $sales_report['total_orders'] ?>', 14, 44);
        doc.text('Total Pendapatan : Rp <?= number_format($sales_report['total_revenue'] ?? 0, 0, ',', '.') ?>', 14,
            51);
        doc.text('Rata-rata Pesanan: Rp <?= number_format($sales_report['avg_order_value'] ?? 0, 0, ',', '.') ?>', 14,
            58);

        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('Produk Terlaris', 14, 70);
        const topRows = [];
        document.querySelectorAll('#tableTopProducts tbody tr').forEach(tr => {
            topRows.push([...tr.querySelectorAll('td')].map(td => td.innerText));
        });
        doc.autoTable({
            startY: 74,
            head: [
                ['Produk', 'Terjual', 'Pendapatan']
            ],
            body: topRows,
            styles: {
                fontSize: 9
            }
        });

        const afterTop = doc.lastAutoTable.finalY + 10;
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('Penjualan Harian', 14, afterTop);
        const dailyRows = [];
        document.querySelectorAll('#tableDailySales tbody tr').forEach(tr => {
            dailyRows.push([...tr.querySelectorAll('td')].map(td => td.innerText));
        });
        doc.autoTable({
            startY: afterTop + 4,
            head: [
                ['Tanggal', 'Jumlah Pesanan', 'Pendapatan']
            ],
            body: dailyRows,
            styles: {
                fontSize: 9
            }
        });

        doc.save('laporan-penjualan-<?= $start_date ?>-<?= $end_date ?>.pdf');
    }

    // ── Export Excel ──
    function exportExcel() {
        const wb = XLSX.utils.book_new();

        // Sheet 1: Ringkasan
        const ringkasan = [
            ['Laporan Penjualan FrozenFood'],
            ['Periode',
                '<?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>'
            ],
            [],
            ['RINGKASAN'],
            ['Total Pesanan', <?= $sales_report['total_orders'] ?>],
            ['Total Pendapatan', <?= $sales_report['total_revenue'] ?? 0 ?>],
            ['Rata-rata Nilai Pesanan', <?= $sales_report['avg_order_value'] ?? 0 ?>],
        ];
        const ws1 = XLSX.utils.aoa_to_sheet(ringkasan);
        XLSX.utils.book_append_sheet(wb, ws1, 'Ringkasan');

        // Sheet 2: Produk Terlaris
        const topHeader = [
            ['Produk', 'Terjual', 'Pendapatan (Rp)']
        ];
        const topRows = [];
        document.querySelectorAll('#tableTopProducts tbody tr').forEach(tr => {
            const cols = [...tr.querySelectorAll('td')].map(td => td.innerText);
            // Pendapatan: hapus 'Rp ' dan titik
            cols[2] = cols[2].replace(/[Rp\s\.]/g, '').replace(',', '.');
            topRows.push(cols);
        });
        const ws2 = XLSX.utils.aoa_to_sheet([...topHeader, ...topRows]);
        XLSX.utils.book_append_sheet(wb, ws2, 'Produk Terlaris');

        // Sheet 3: Penjualan Harian
        const dailyHeader = [
            ['Tanggal', 'Jumlah Pesanan', 'Pendapatan (Rp)']
        ];
        const dailyRows = [];
        document.querySelectorAll('#tableDailySales tbody tr').forEach(tr => {
            const cols = [...tr.querySelectorAll('td')].map(td => td.innerText);
            cols[2] = cols[2].replace(/[Rp\s\.]/g, '').replace(',', '.');
            dailyRows.push(cols);
        });
        const ws3 = XLSX.utils.aoa_to_sheet([...dailyHeader, ...dailyRows]);
        XLSX.utils.book_append_sheet(wb, ws3, 'Penjualan Harian');

        XLSX.writeFile(wb, 'laporan-penjualan-<?= $start_date ?>-<?= $end_date ?>.xlsx');
    }
    </script>
</body>

</html>