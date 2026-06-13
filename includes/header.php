<?php
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$notif_count = $stmt->fetchColumn();
?>
<style>
.hamburger-btn {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    padding: 8px;
    z-index: 1100;
    position: relative;
}

.hamburger-btn span {
    display: block;
    width: 22px;
    height: 2px;
    background: #1e293b;
    border-radius: 4px;
    transition: all .3s;
}

.hamburger-btn.open span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}

.hamburger-btn.open span:nth-child(2) {
    opacity: 0;
    width: 0;
}

.hamburger-btn.open span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* Overlay gelap */
.mob-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .5);
    z-index: 1050;
}

.mob-overlay.open {
    display: block;
}

/* Drawer */
.mob-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 270px;
    height: 100vh;
    background: #fff;
    z-index: 1060;
    transform: translateX(110%);
    transition: transform .3s cubic-bezier(.4, 0, .2, 1);
    display: flex;
    flex-direction: column;
    box-shadow: -6px 0 32px rgba(0, 0, 0, .18);
    overflow-y: auto;
}

.mob-drawer.open {
    transform: translateX(0);
}

/* Drawer header */
.mob-head {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    padding: 40px 20px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
}

.mob-avatar {
    width: 52px;
    height: 52px;
    background: rgba(255, 255, 255, .2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
    border: 2px solid rgba(255, 255, 255, .4);
    flex-shrink: 0;
}

.mob-uname {
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    line-height: 1.3;
}

.mob-urole {
    color: rgba(255, 255, 255, .7);
    font-size: 12px;
    margin-top: 2px;
}

/* Icon bar */
.mob-iconbar {
    display: flex;
    gap: 10px;
    padding: 14px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.mob-iconbtn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 10px 6px;
    background: #fff;
    border-radius: 12px;
    text-decoration: none;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    position: relative;
    border: 1px solid #e2e8f0;
    transition: all .2s;
}

.mob-iconbtn:hover {
    background: #eff6ff;
    color: #3b82f6;
    border-color: #bfdbfe;
}

.mob-iconbtn i {
    font-size: 18px;
}

.mob-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #ef4444;
    color: #fff;
    font-size: 9px;
    font-weight: 800;
    min-width: 16px;
    height: 16px;
    border-radius: 99px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 3px;
}

/* Nav links */
.mob-nav {
    flex: 1;
    padding: 8px 0;
}

.mob-nav a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 13px 20px;
    color: #334155;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all .15s;
}

.mob-nav a:hover {
    background: #f1f5f9;
    color: #2563eb;
    border-left-color: #2563eb;
}

.mob-nav a i {
    width: 20px;
    text-align: center;
    font-size: 15px;
    color: #94a3b8;
}

.mob-nav a:hover i {
    color: #2563eb;
}

.mob-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 6px 16px;
}

/* Logout */
.mob-footer {
    padding: 14px 16px;
    flex-shrink: 0;
    border-top: 1px solid #f1f5f9;
}

.mob-logout {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 16px;
    background: #fef2f2;
    color: #dc2626;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: 1px solid #fecaca;
    transition: background .2s;
}

.mob-logout:hover {
    background: #fee2e2;
}

.mob-logout i {
    font-size: 15px;
}

@media (max-width: 768px) {
    .hamburger-btn {
        display: flex !important;
    }

    .main-nav {
        display: none !important;
    }

    .header-actions {
        display: none !important;
    }
}
</style>

<header class="main-header">
    <div class="container">
        <div class="header-content">
            <a href="home.php" class="logo">
                <i class="fas fa-snowflake"></i>
                <span>FrozenFood</span>
            </a>

            <nav class="main-nav">
                <a href="home.php"><i class="fas fa-home"></i> Beranda</a>
                <a href="catalog.php"><i class="fas fa-th-large"></i> Katalog</a>
                <a href="my_orders.php"><i class="fas fa-box"></i> Pesanan Saya</a>
            </nav>

            <div class="header-actions">
                <a href="notifications.php" class="icon-link" title="Notifikasi">
                    <i class="fas fa-bell"></i>
                    <?php if ($notif_count > 0): ?><span class="badge"><?= $notif_count ?></span><?php endif; ?>
                </a>
                <a href="cart.php" class="icon-link" title="Keranjang">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?><span class="badge"><?= $cart_count ?></span><?php endif; ?>
                </a>
                <div class="user-dropdown">
                    <button class="user-name" onclick="toggleUserMenu()">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="userMenu">
                        <a href="change_password.php"><i class="fas fa-key"></i> Ganti Password</a>
                        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <button class="hamburger-btn" id="hamBtn" onclick="toggleDrawer()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Overlay -->
<div class="mob-overlay" id="mobOverlay" onclick="closeDrawer()"></div>

<!-- Drawer -->
<div class="mob-drawer" id="mobDrawer">

    <!-- Profil -->
    <div class="mob-head">
        <div class="mob-avatar"><i class="fas fa-user"></i></div>
        <div>
            <div class="mob-uname"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            <div class="mob-urole">Customer</div>
        </div>
    </div>

    <!-- Lonceng & Keranjang -->
    <div class="mob-iconbar">
        <a href="notifications.php" class="mob-iconbtn">
            <i class="fas fa-bell"></i>
            <?php if ($notif_count > 0): ?><span class="mob-badge"><?= $notif_count ?></span><?php endif; ?>
            <span>Notifikasi</span>
        </a>
        <a href="cart.php" class="mob-iconbtn">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cart_count > 0): ?><span class="mob-badge"><?= $cart_count ?></span><?php endif; ?>
            <span>Keranjang</span>
        </a>
    </div>

    <!-- Menu navigasi -->
    <div class="mob-nav">
        <a href="home.php"><i class="fas fa-home"></i> Beranda</a>
        <a href="catalog.php"><i class="fas fa-th-large"></i> Katalog</a>
        <a href="my_orders.php"><i class="fas fa-box"></i> Pesanan Saya</a>
        <div class="mob-divider"></div>
        <a href="change_password.php"><i class="fas fa-key"></i> Ganti Password</a>
    </div>

    <!-- Logout -->
    <div class="mob-footer">
        <a href="logout.php" class="mob-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</div>

<script>
function toggleDrawer() {
    const drawer = document.getElementById('mobDrawer');
    const overlay = document.getElementById('mobOverlay');
    const btn = document.getElementById('hamBtn');
    const isOpen = drawer.classList.contains('open');
    if (isOpen) {
        closeDrawer();
    } else {
        drawer.classList.add('open');
        overlay.classList.add('open');
        btn.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeDrawer() {
    document.getElementById('mobDrawer').classList.remove('open');
    document.getElementById('mobOverlay').classList.remove('open');
    document.getElementById('hamBtn').classList.remove('open');
    document.body.style.overflow = '';
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('show');
}
window.onclick = function(e) {
    if (!e.target.matches('.user-name') && !e.target.closest('.user-name')) {
        const dd = document.getElementsByClassName('dropdown-menu');
        for (let i = 0; i < dd.length; i++) dd[i].classList.remove('show');
    }
}
</script>