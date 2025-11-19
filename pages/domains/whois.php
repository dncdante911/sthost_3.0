<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'whois';
$page_title = 'WHOIS Lookup - Перевірка доменів | StormHosting UA';
$meta_description = 'Безкоштовна перевірка WHOIS інформації доменів. Дізнайтесь власника, дату реєстрації, закінчення та DNS сервери.';
$meta_keywords = 'whois домен, whois lookup, інформація про домен, перевірка домену';

// Додаткові CSS та JS файли
$additional_css = [
    '/assets/css/pages/whois-lookup.css'
];

$additional_js = [
    '/assets/js/whois-lookup.js'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- WHOIS Hero Section -->
<section class="whois-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="bi bi-search"></i>
                <span>WHOIS Lookup</span>
            </div>
            <h1 class="hero-title">Перевірка WHOIS інформації</h1>
            <p class="hero-subtitle">Дізнайтесь всю публічну інформацію про будь-який домен:<br>власник, дати реєстрації, DNS сервери та більше</p>
        </div>

        <!-- Search Form -->
        <div class="whois-search-card">
            <form id="whoisForm" class="whois-search-form">
                <div class="search-input-group">
                    <i class="bi bi-globe search-icon"></i>
                    <input
                        type="text"
                        id="domainInput"
                        name="domain"
                        class="search-input"
                        placeholder="example.com або example.ua"
                        autocomplete="off"
                        required>
                    <button type="submit" class="search-btn" id="searchBtn">
                        <i class="bi bi-search"></i>
                        <span>Перевірити</span>
                    </button>
                </div>
                <div class="search-hint">Введіть повне ім'я домену для перевірки WHOIS інформації</div>
            </form>
        </div>

        <!-- Results Container -->
        <div id="whoisResults"></div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Що ви дізнаєтесь з WHOIS?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h3>Власник домену</h3>
                <p>Інформація про реєстранта домену, його контактні дані (якщо не приховані)</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <h3>Дати реєстрації</h3>
                <p>Коли домен був зареєстрований та коли закінчується термін реєстрації</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-hdd-network"></i>
                </div>
                <h3>DNS сервери</h3>
                <p>Список авторитетних DNS серверів, які обслуговують домен</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3>Реєстратор</h3>
                <p>Компанія, через яку зареєстрований домен</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h3>Статус домену</h3>
                <p>Поточний статус домену (активний, заблокований, трансфер і т.д.)</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3>Історія оновлень</h3>
                <p>Дата останнього оновлення WHOIS інформації</p>
            </div>
        </div>
    </div>
</section>

<!-- Privacy Section -->
<section class="privacy-section">
    <div class="container">
        <div class="privacy-content">
            <div class="privacy-text">
                <h2>Захист приватності WHOIS</h2>
                <p class="lead">Не хочете, щоб ваші особисті дані були видимі у WHOIS?</p>
                <ul class="privacy-list">
                    <li>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Приховання імені та адреси власника</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Захист email та телефону від спаму</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Безкоштовно при реєстрації у нас</span>
                    </li>
                </ul>
                <a href="/pages/domains/register.php" class="btn-primary-large">
                    <i class="bi bi-shield-lock"></i>
                    Зареєструвати домен з захистом
                </a>
            </div>
            <div class="privacy-visual">
                <div class="whois-comparison">
                    <div class="whois-before">
                        <div class="comparison-label">Без захисту</div>
                        <div class="whois-data">
                            <div class="data-line">Name: Іван Петренко</div>
                            <div class="data-line">Email: ivan@example.com</div>
                            <div class="data-line">Phone: +380501234567</div>
                            <div class="data-line">Address: Київ, Україна</div>
                        </div>
                    </div>
                    <div class="whois-after">
                        <div class="comparison-label protected">З захистом</div>
                        <div class="whois-data protected">
                            <div class="data-line">Name: REDACTED FOR PRIVACY</div>
                            <div class="data-line">Email: REDACTED FOR PRIVACY</div>
                            <div class="data-line">Phone: REDACTED FOR PRIVACY</div>
                            <div class="data-line">Address: REDACTED FOR PRIVACY</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Tools Section -->
<section class="tools-section">
    <div class="container">
        <h2 class="section-title">Інші інструменти</h2>
        <div class="tools-grid">
            <a href="/pages/domains/dns.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <h3>DNS Lookup</h3>
                <p>Перевірте DNS записи домену</p>
                <span class="tool-link">Перевірити DNS <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="/pages/domains/register.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <h3>Реєстрація доменів</h3>
                <p>Зареєструйте новий домен</p>
                <span class="tool-link">Зареєструвати <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="/pages/domains/transfer.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <h3>Трансфер доменів</h3>
                <p>Перенесіть домен до нас</p>
                <span class="tool-link">Перенести домен <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
