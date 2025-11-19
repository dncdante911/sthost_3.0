<?php
/**
 * Страница статуса серверов
 */

define('SECURE_ACCESS', true);

$page_title = "Статус серверів - StormHosting UA | Моніторинг серверів в реальному часі";
$page_description = "Моніторинг статусу наших серверів в реальному часі. ISPManager, Proxmox, HAProxy та мережеві канали.";
$page_keywords = "статус серверів, моніторинг, uptime, навантаження серверів";
$canonical_url = "https://sthost.pro/server-status";

// Подключение header
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/pages/server-status.css">

<main class="main-content server-status-page">
    <!-- Hero Section -->
    <section class="status-hero">
        <div class="container">
            <div class="hero-content text-center">
                <div class="hero-badge">
                    <i class="bi bi-activity"></i>
                    <span>Моніторинг в реальному часі</span>
                </div>

                <h1 class="hero-title">Статус серверів</h1>
                <p class="hero-subtitle">
                    Відстежуйте роботу наших серверів та мережевої інфраструктури в реальному часі
                </p>

                <div class="status-summary" id="statusSummary">
                    <div class="summary-item">
                        <div class="summary-value" id="totalServers">-</div>
                        <div class="summary-label">Всього серверів</div>
                    </div>
                    <div class="summary-item online">
                        <div class="summary-value" id="onlineServers">-</div>
                        <div class="summary-label">Онлайн</div>
                    </div>
                    <div class="summary-item offline">
                        <div class="summary-value" id="offlineServers">-</div>
                        <div class="summary-label">Офлайн</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value" id="lastUpdate">-</div>
                        <div class="summary-label">Останнє оновлення</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loading Indicator -->
    <div class="loading-indicator" id="loadingIndicator">
        <div class="spinner"></div>
        <p>Завантаження даних моніторингу...</p>
    </div>

    <!-- Error Message -->
    <div class="error-message" id="errorMessage" style="display: none;">
        <div class="container">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <span id="errorText"></span>
                <button onclick="ServerStatus.loadStatus()" class="btn btn-sm btn-outline-light">
                    Спробувати знову
                </button>
            </div>
        </div>
    </div>

    <!-- Servers Status Grid -->
    <section class="servers-section" id="serversSection" style="display: none;">
        <div class="container">
            <!-- ISPManager Servers -->
            <div class="server-type-section" id="ispmanagerSection">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-server"></i>
                        ISPManager Сервери
                    </h2>
                    <span class="server-count" id="ispmanagerCount">0</span>
                </div>
                <div class="servers-grid" id="ispmanagerGrid"></div>
            </div>

            <!-- Proxmox Servers -->
            <div class="server-type-section" id="proxmoxSection">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-hdd-rack"></i>
                        Proxmox VE Сервери
                    </h2>
                    <span class="server-count" id="proxmoxCount">0</span>
                </div>
                <div class="servers-grid" id="proxmoxGrid"></div>
            </div>

            <!-- HAProxy Servers -->
            <div class="server-type-section" id="haproxySection">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-diagram-3"></i>
                        HAProxy Load Balancers
                    </h2>
                    <span class="server-count" id="haproxyCount">0</span>
                </div>
                <div class="servers-grid" id="haproxyGrid"></div>
            </div>

            <!-- Network Interfaces -->
            <div class="server-type-section" id="networkSection">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="bi bi-ethernet"></i>
                        Мережеві канали
                    </h2>
                    <span class="server-count" id="networkCount">0</span>
                </div>
                <div class="servers-grid" id="networkGrid"></div>
            </div>
        </div>
    </section>

    <!-- Alerts Section -->
    <section class="alerts-section" id="alertsSection" style="display: none;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-bell"></i>
                    Активні алерти
                </h2>
            </div>
            <div class="alerts-list" id="alertsList"></div>
        </div>
    </section>

    <!-- Information -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <i class="bi bi-clock-history"></i>
                    <h3>Автоматичне оновлення</h3>
                    <p>Дані оновлюються кожні 30 секунд для актуальності інформації</p>
                </div>
                <div class="info-card">
                    <i class="bi bi-shield-check"></i>
                    <h3>99.9% Uptime</h3>
                    <p>Ми гарантуємо високу доступність наших серверів</p>
                </div>
                <div class="info-card">
                    <i class="bi bi-speedometer2"></i>
                    <h3>Моніторинг в реальному часі</h3>
                    <p>Відстежуємо CPU, пам'ять, диски та мережу</p>
                </div>
                <div class="info-card">
                    <i class="bi bi-bell"></i>
                    <h3>Система алертів</h3>
                    <p>Миттєво реагуємо на будь-які проблеми</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Legend -->
    <section class="legend-section">
        <div class="container">
            <h3>Легенда статусів</h3>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="status-badge online"></span>
                    <span>Онлайн - сервер працює нормально</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge maintenance"></span>
                    <span>Обслуговування - планові роботи</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge offline"></span>
                    <span>Офлайн - сервер недоступний</span>
                </div>
                <div class="legend-item">
                    <span class="status-badge error"></span>
                    <span>Помилка - проблема з отриманням даних</span>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/server-status.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
