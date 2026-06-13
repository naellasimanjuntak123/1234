<header class="admin-header">
    <div class="admin-header-inner">

        <!-- Logo -->
        <a href="dashboard.php" class="admin-logo">
            <i class="fas fa-snowflake"></i>
            <span>FrozenFood Admin</span>
        </a>

        <!-- Desktop Nav -->
        <nav class="admin-nav-desktop">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-box-open"></i> Produk</a>
            <a href="orders.php"><i class="fas fa-shopping-bag"></i> Pesanan</a>
            <a href="reports.php"><i class="fas fa-chart-line"></i> Laporan</a>
        </nav>

        <!-- Desktop: user dropdown -->
        <div class="admin-header-actions">
            <div class="user-dropdown">
                <button class="user-name" onclick="toggleUserMenu()">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="userMenu">
                    <a href="change_password.php"><i class="fas fa-key"></i> Ganti Password</a>
                    <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Mobile: hamburger button -->
        <button class="admin-hamburger" id="adminHamburger" onclick="toggleMobileMenu()" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</header>

<!-- Mobile Sidebar Overlay -->
<div class="admin-overlay" id="adminOverlay" onclick="closeMobileMenu()"></div>

<!-- Mobile Sidebar -->
<div class="admin-sidebar" id="adminSidebar">

    <!-- Profile section -->
    <div class="sidebar-profile">
        <div class="sidebar-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <div class="sidebar-name">Admin</div>
            <div class="sidebar-role">Administrator</div>
        </div>
        <button class="sidebar-close" onclick="closeMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Nav Links -->
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="products.php" class="sidebar-link">
            <i class="fas fa-box-open"></i> Produk
        </a>
        <a href="orders.php" class="sidebar-link">
            <i class="fas fa-shopping-bag"></i> Pesanan
        </a>
        <a href="reports.php" class="sidebar-link">
            <i class="fas fa-chart-line"></i> Laporan
        </a>
        <a href="change_password.php" class="sidebar-link">
            <i class="fas fa-key"></i> Ganti Password
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="../logout.php" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</div>

<style>
/* ══════════════════════════════════════
   ADMIN HEADER
══════════════════════════════════════ */
.admin-header {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
}

.admin-header-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    height: 60px;
    display: flex;
    align-items: center;
    gap: 24px;
}

/* Logo */
.admin-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-size: 18px;
    font-weight: 800;
    color: var(--primary, #3b82f6);
    white-space: nowrap;
}

.admin-logo i {
    font-size: 20px;
}

/* Desktop Nav */
.admin-nav-desktop {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
}

.admin-nav-desktop a {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
}

.admin-nav-desktop a:hover,
.admin-nav-desktop a.active {
    background: #eff6ff;
    color: var(--primary, #3b82f6);
}

/* Desktop user dropdown */
.admin-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.user-dropdown {
    position: relative;
}

.user-name {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all .2s;
}

.user-name:hover {
    border-color: var(--primary, #3b82f6);
    color: var(--primary, #3b82f6);
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    min-width: 180px;
    overflow: hidden;
    z-index: 200;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    font-size: 14px;
    color: #475569;
    text-decoration: none;
    transition: background .15s;
}

.dropdown-menu a:hover {
    background: #f8fafc;
    color: #1e293b;
}

.dropdown-menu .logout-link {
    color: #ef4444;
    border-top: 1px solid #f1f5f9;
}

.dropdown-menu .logout-link:hover {
    background: #fef2f2;
}

/* Hamburger button */
.admin-hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 38px;
    height: 38px;
    padding: 6px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    margin-left: auto;
    transition: border-color .2s;
}

.admin-hamburger:hover {
    border-color: var(--primary, #3b82f6);
}

.admin-hamburger span {
    display: block;
    height: 2px;
    background: #475569;
    border-radius: 2px;
    transition: all .3s;
}

.admin-hamburger.open span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}

.admin-hamburger.open span:nth-child(2) {
    opacity: 0;
}

.admin-hamburger.open span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* ══════════════════════════════════════
   OVERLAY
══════════════════════════════════════ */
.admin-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .45);
    z-index: 300;
    backdrop-filter: blur(2px);
}

.admin-overlay.show {
    display: block;
}

/* ══════════════════════════════════════
   MOBILE SIDEBAR
══════════════════════════════════════ */
.admin-sidebar {
    position: fixed;
    top: 0;
    right: -300px;
    width: 280px;
    height: 100vh;
    background: #fff;
    z-index: 400;
    display: flex;
    flex-direction: column;
    box-shadow: -4px 0 24px rgba(0, 0, 0, .15);
    transition: right .3s cubic-bezier(.4, 0, .2, 1);
    overflow-y: auto;
}

.admin-sidebar.open {
    right: 0;
}

/* Sidebar Profile */
.sidebar-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 16px 16px;
    background: var(--primary, #3b82f6);
    color: #fff;
}

.sidebar-avatar {
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, .25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.sidebar-name {
    font-size: 15px;
    font-weight: 700;
}

.sidebar-role {
    font-size: 12px;
    opacity: .8;
    margin-top: 1px;
}

.sidebar-close {
    margin-left: auto;
    background: rgba(255, 255, 255, .2);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .2s;
}

.sidebar-close:hover {
    background: rgba(255, 255, 255, .35);
}

/* Sidebar Nav */
.sidebar-nav {
    display: flex;
    flex-direction: column;
    padding: 12px 12px 0;
    flex: 1;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 14px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    transition: all .2s;
    margin-bottom: 4px;
}

.sidebar-link i {
    width: 18px;
    text-align: center;
    color: #94a3b8;
    font-size: 15px;
}

.sidebar-link:hover,
.sidebar-link.active {
    background: #eff6ff;
    color: var(--primary, #3b82f6);
}

.sidebar-link:hover i,
.sidebar-link.active i {
    color: var(--primary, #3b82f6);
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 12px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #ef4444;
    text-decoration: none;
    background: #fef2f2;
    transition: background .2s;
}

.sidebar-logout:hover {
    background: #fee2e2;
}

.sidebar-logout i {
    font-size: 15px;
}

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media (max-width: 768px) {

    .admin-nav-desktop,
    .admin-header-actions {
        display: none;
    }

    .admin-hamburger {
        display: flex;
    }

    .admin-header-inner {
        padding: 0 16px;
    }
}
</style>

<script>
function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('show');
}

function toggleMobileMenu() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const hamburger = document.getElementById('adminHamburger');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
    hamburger.classList.toggle('open');
}

function closeMobileMenu() {
    document.getElementById('adminSidebar').classList.remove('open');
    document.getElementById('adminOverlay').classList.remove('show');
    document.getElementById('adminHamburger').classList.remove('open');
}

// Highlight active link
(function() {
    const path = window.location.pathname.split('/').pop();
    document.querySelectorAll('.admin-nav-desktop a, .sidebar-link').forEach(a => {
        if (a.getAttribute('href') === path) {
            a.classList.add('active');
        }
    });
})();

// Close dropdown when clicking outside
window.onclick = function(e) {
    if (!e.target.matches('.user-name') && !e.target.closest('.user-name')) {
        const d = document.getElementById('userMenu');
        if (d) d.classList.remove('show');
    }
}
</script>