<?php
$current_page = $_SERVER['REQUEST_URI'] ?? '';
?>

<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar" id="appSidebar">

    <!-- =====================================================
         BRAND / HEADER
    ====================================================== -->

    <div class="sidebar-brand">

        <div class="sidebar-logo">
            <img
                src="/inventory_mesin/assets/img/garudafood.png"
                alt="Garudafood"
                onerror="this.style.display='none'; document.getElementById('sidebarLogoFallback').style.display='flex';"
            >

            <div
                id="sidebarLogoFallback"
                class="sidebar-logo-fallback"
            >
                <i class="bi bi-building"></i>
            </div>
        </div>

        <div class="sidebar-brand-text">
            <div class="sidebar-company">
                GarudaFood
            </div>

            <div class="sidebar-system">
                INVENTORY MAINTENANCE
            </div>
        </div>

        <!-- MOBILE CLOSE -->
        <button
            type="button"
            class="sidebar-close-mobile"
            id="sidebarCloseMobile"
            title="Tutup Sidebar"
        >
            <i class="bi bi-x-lg"></i>
        </button>

    </div>


    <!-- =====================================================
         MENU
    ========================================================= -->

    <div class="sidebar-menu">

        <!-- DASHBOARD -->
        <a
            href="/inventory_mesin/dashboard/index.php"
            class="nav-item-link <?= (strpos($current_page, '/dashboard/') !== false) ? 'active' : ''; ?>"
            title="Dashboard"
        >
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>


        <!-- DAFTAR MESIN -->
        <a
            href="/inventory_mesin/hierarki.php"
            class="nav-item-link <?= (basename(parse_url($current_page, PHP_URL_PATH)) === 'hierarki.php') ? 'active' : ''; ?>"
            title="Daftar Mesin"
        >
            <i class="bi bi-diagram-3"></i>
            <span>Daftar Mesin</span>
        </a>


        <!-- MASTER DATA -->
        <div class="sidebar-heading">
            <span>Master Data</span>
        </div>


        <!-- AREA -->
        <a
            href="/inventory_mesin/master/area.php"
            class="nav-item-link <?= (strpos($current_page, '/master/area.php') !== false) ? 'active' : ''; ?>"
            title="Data Area"
        >
            <i class="bi bi-geo-alt"></i>
            <span>Data Area</span>
        </a>


        <!-- JENIS MESIN -->
        <a
            href="/inventory_mesin/master/jenis_mesin.php"
            class="nav-item-link <?= (strpos($current_page, '/master/jenis_mesin.php') !== false) ? 'active' : ''; ?>"
            title="Jenis Mesin"
        >
            <i class="bi bi-grid"></i>
            <span>Jenis Mesin</span>
        </a>


        <!-- MESIN -->
        <a
            href="/inventory_mesin/mesin/index.php"
            class="nav-item-link <?= (strpos($current_page, '/mesin/') !== false) ? 'active' : ''; ?>"
            title="Data Mesin"
        >
            <i class="bi bi-building"></i>
            <span>Data Mesin</span>
        </a>


        <!-- SUB MESIN -->
        <a
            href="/inventory_mesin/sub_mesin/index.php"
            class="nav-item-link <?= (strpos($current_page, '/sub_mesin/') !== false) ? 'active' : ''; ?>"
            title="Sub Mesin"
        >
            <i class="bi bi-diagram-3-fill"></i>
            <span>Sub Mesin</span>
        </a>


        <!-- KOMPONEN -->
        <a
            href="/inventory_mesin/komponen/index.php"
            class="nav-item-link <?= (strpos($current_page, '/komponen/') !== false) ? 'active' : ''; ?>"
            title="Data Komponen"
        >
            <i class="bi bi-cpu"></i>
            <span>Data Komponen</span>
        </a>


        <!-- TRANSAKSI -->
        <div class="sidebar-heading">
            <span>Transaksi & Lainnya</span>
        </div>


        <!-- MAINTENANCE -->
        <a
            href="/inventory_mesin/maintenance/index.php"
            class="nav-item-link <?= (strpos($current_page, '/maintenance/') !== false) ? 'active' : ''; ?>"
            title="Maintenance"
        >
            <i class="bi bi-tools"></i>
            <span>Maintenance</span>
        </a>

    </div>


    <!-- =====================================================
         FOOTER
    ========================================================= -->

    <div class="sidebar-footer">
        <div class="sidebar-footer-icon">
            <i class="bi bi-shield-check"></i>
        </div>

        <div class="sidebar-footer-text">
            <strong>System Maintenance</strong>
            <small>Inventory Management</small>
        </div>
    </div>

</aside>


<!-- =========================================================
     MOBILE OVERLAY
========================================================= -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>


<!-- =========================================================
     TOGGLE BUTTON (DESKTOP & MOBILE)
========================================================= -->
<button
    type="button"
    class="sidebar-toggle"
    id="sidebarToggle"
    title="Buka/Tutup Sidebar"
    aria-label="Buka/Tutup Sidebar"
>
    <i class="bi bi-list"></i>
</button>


<style>

/* =========================================================
   VARIABLES
========================================================= */
:root {
    --sidebar-width: 240px;
    --sidebar-collapsed-width: 70px;
    --sidebar-bg: #ffffff;
    --sidebar-border: #e2e8f0;
    --sidebar-primary: #005baa;
    --sidebar-primary-dark: #003f7d;
    --sidebar-primary-light: #eef5ff;
    --sidebar-text: #334155;
    --sidebar-muted: #94a3b8;
    --sidebar-hover: #f8fafc;
}

/* =========================================================
   SIDEBAR BASE
========================================================= */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    border-right: 1px solid var(--sidebar-border) !important;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow: hidden !important; /* Mencegah elemen melimpah ke kanan */
    transition: width 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
}

/* =========================================================
   BRAND / HEADER SIDEBAR
========================================================= */
.sidebar-brand {
    position: relative;
    width: 100%;
    height: 110px;
    min-height: 110px;
    padding: 15px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    border-bottom: 1px solid #eef1f5;
    background: #ffffff;
    flex-shrink: 0;
    transition: height 0.25s ease, padding 0.25s ease;
}

.sidebar-logo {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
    transition: all 0.25s ease;
}

.sidebar-logo img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

.sidebar-logo-fallback {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #005baa, #0076c8);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.sidebar-brand-text {
    width: 100%;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.2s ease;
    white-space: nowrap;
}

.sidebar-company {
    color: #005baa;
    font-size: 16px;
    line-height: 1.2;
    font-weight: 800;
}

.sidebar-system {
    margin-top: 3px;
    color: #94a3b8;
    font-size: 8px;
    letter-spacing: 0.8px;
    font-weight: 700;
    text-transform: uppercase;
}

.sidebar-close-mobile {
    display: none;
    position: absolute;
    right: 10px;
    top: 10px;
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* =========================================================
   MENU CONTAINER
========================================================= */
.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 12px 10px;
}

.sidebar-menu::-webkit-scrollbar {
    width: 4px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: #dbe3ea;
    border-radius: 20px;
}

/* =========================================================
   NAV ITEM
========================================================= */
.nav-item-link {
    position: relative;
    display: flex !important;
    align-items: center;
    width: 100%;
    min-height: 42px;
    padding: 8px 12px;
    margin-bottom: 4px;
    gap: 10px;
    border-radius: 8px;
    color: var(--sidebar-text);
    background: transparent;
    text-decoration: none !important;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    transition: background 0.18s ease, color 0.18s ease;
}

.nav-item-link i {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: 20px;
    min-width: 20px;
    height: 20px;
    color: #64748b;
    font-size: 16px;
    flex-shrink: 0;
    transition: color 0.18s ease;
}

.nav-item-link span {
    display: inline-block !important;
    white-space: nowrap;
}

.nav-item-link:hover {
    background: #f4f8fc;
    color: var(--sidebar-primary);
}

.nav-item-link:hover i {
    color: var(--sidebar-primary);
}

.nav-item-link.active {
    background: var(--sidebar-primary);
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 91, 170, 0.2);
}

.nav-item-link.active i {
    color: #ffffff;
}

/* =========================================================
   SIDEBAR HEADING
========================================================= */
.sidebar-heading {
    padding: 14px 10px 6px;
    color: #94a3b8;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.sidebar-heading::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 4px;
    margin-right: 6px;
    margin-bottom: 1px;
    border-radius: 50%;
    background: #005baa;
}

/* =========================================================
   FOOTER
========================================================= */
.sidebar-footer {
    min-height: 55px;
    padding: 10px 12px;
    border-top: 1px solid #eef1f5;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
    flex-shrink: 0;
}

.sidebar-footer-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
    background: #eef5ff;
    color: #005baa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}

.sidebar-footer-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    white-space: nowrap;
}

.sidebar-footer-text strong {
    font-size: 10px;
    color: #334155;
}

.sidebar-footer-text small {
    font-size: 8px;
    color: #94a3b8;
}

/* =========================================================
   TOGGLE BUTTON STYLING
========================================================= */
.sidebar-toggle {
    position: fixed;
    left: calc(var(--sidebar-width) - 14px);
    top: 20px;
    width: 28px;
    height: 28px;
    border-radius: 50% !important;
    border: 1px solid var(--sidebar-border);
    background: #ffffff;
    color: #005baa;
    z-index: 1060;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: left 0.25s ease, background 0.2s ease, color 0.2s ease;
}

.sidebar-toggle:hover {
    background: #005baa;
    color: #ffffff;
    border-color: #005baa;
}

/* =========================================================
   DESKTOP COLLAPSED STATE (RAMPING)
========================================================= */
body.sidebar-collapsed .sidebar {
    width: var(--sidebar-collapsed-width) !important;
}

body.sidebar-collapsed .sidebar-brand {
    height: 75px !important;
    min-height: 75px !important;
    padding: 10px 5px !important;
}

body.sidebar-collapsed .sidebar-logo {
    width: 36px !important;
    height: 36px !important;
    margin-bottom: 0 !important;
}

body.sidebar-collapsed .sidebar-brand-text {
    display: none !important;
}

body.sidebar-collapsed .sidebar-toggle {
    left: calc(var(--sidebar-collapsed-width) - 14px) !important;
}

body.sidebar-collapsed .nav-item-link {
    justify-content: center !important;
    padding: 10px 0 !important;
    gap: 0 !important;
}

body.sidebar-collapsed .nav-item-link span {
    display: none !important;
}

body.sidebar-collapsed .nav-item-link i {
    width: auto !important;
    min-width: 0 !important;
    font-size: 18px !important;
}

body.sidebar-collapsed .sidebar-heading {
    font-size: 0 !important;
    text-align: center;
    padding: 8px 0 !important;
}

body.sidebar-collapsed .sidebar-heading span {
    display: none !important;
}

body.sidebar-collapsed .sidebar-heading::before {
    margin: 0 auto;
    width: 5px;
    height: 5px;
}

body.sidebar-collapsed .sidebar-footer {
    justify-content: center !important;
    padding: 10px 0 !important;
}

body.sidebar-collapsed .sidebar-footer-text {
    display: none !important;
}

/* =========================================================
   SYNCHRONIZE MAIN CONTENT MARGIN
========================================================= */
.main-content,
.content-wrapper,
main {
    margin-left: var(--sidebar-width) !important;
    width: calc(100% - var(--sidebar-width)) !important;
    transition: margin-left 0.25s ease, width 0.25s ease !important;
}

body.sidebar-collapsed .main-content,
body.sidebar-collapsed .content-wrapper,
body.sidebar-collapsed main {
    margin-left: var(--sidebar-collapsed-width) !important;
    width: calc(100% - var(--sidebar-collapsed-width)) !important;
}

/* =========================================================
   MOBILE & OVERLAY
========================================================= */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 1040;
    backdrop-filter: blur(2px);
}

@media (max-width: 768px) {
    .sidebar {
        width: 240px !important;
        transform: translateX(-100%);
        box-shadow: 8px 0 30px rgba(15, 23, 42, 0.15);
    }

    body.sidebar-mobile-open .sidebar {
        transform: translateX(0);
    }

    .sidebar-close-mobile {
        display: flex;
    }

    .sidebar-toggle {
        left: 15px;
        top: 15px;
        width: 38px;
        height: 38px;
        border-radius: 8px !important;
        z-index: 1030;
    }

    body.sidebar-mobile-open .sidebar-toggle {
        display: none;
    }

    body.sidebar-mobile-open .sidebar-overlay {
        display: block;
    }

    .main-content,
    .content-wrapper,
    main {
        margin-left: 0 !important;
        width: 100% !important;
    }

    body.sidebar-collapsed .sidebar {
        width: 240px !important;
        transform: translateX(-100%);
    }

    body.sidebar-collapsed.sidebar-mobile-open .sidebar {
        transform: translateX(0);
    }

    body.sidebar-collapsed .sidebar-brand-text {
        display: block !important;
    }

    body.sidebar-collapsed .nav-item-link {
        justify-content: flex-start !important;
        padding: 8px 12px !important;
        gap: 10px !important;
    }

    body.sidebar-collapsed .nav-item-link span {
        display: inline-block !important;
    }

    body.sidebar-collapsed .sidebar-footer-text {
        display: flex !important;
    }
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("sidebarToggle");
    const closeButton = document.getElementById("sidebarCloseMobile");
    const overlay = document.getElementById("sidebarOverlay");
    const body = document.body;

    // 1. Restore status sidebar dari localStorage
    const savedState = localStorage.getItem("inventorySidebarCollapsed");
    if (savedState === "true" && window.innerWidth > 768) {
        body.classList.add("sidebar-collapsed");
    }

    function isMobile() {
        return window.innerWidth <= 768;
    }

    // 2. Toggle Handler
    if (toggleButton) {
        toggleButton.addEventListener("click", function () {
            if (isMobile()) {
                body.classList.add("sidebar-mobile-open");
            } else {
                body.classList.toggle("sidebar-collapsed");
                const isCollapsed = body.classList.contains("sidebar-collapsed");
                localStorage.setItem("inventorySidebarCollapsed", isCollapsed);
            }
        });
    }

    if (closeButton) {
        closeButton.addEventListener("click", function () {
            body.classList.remove("sidebar-mobile-open");
        });
    }

    if (overlay) {
        overlay.addEventListener("click", function () {
            body.classList.remove("sidebar-mobile-open");
        });
    }

    // 3. Tutup sidebar mobile saat menu diklik
    document.querySelectorAll(".nav-item-link").forEach(function (link) {
        link.addEventListener("click", function () {
            if (isMobile()) {
                body.classList.remove("sidebar-mobile-open");
            }
        });
    });

    // 4. Tutup dengan tombol ESC
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            body.classList.remove("sidebar-mobile-open");
        }
    });

    // 5. Reset status saat window di-resize
    window.addEventListener("resize", function () {
        if (!isMobile()) {
            body.classList.remove("sidebar-mobile-open");
        }
    });
});
</script>