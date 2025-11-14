<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'site-check';
$page_title = 'Перевірка доступності сайту - StormHosting UA';
$meta_description = 'Безкоштовний інструмент перевірки доступності сайту. Перевірте статус, час відповіді, HTTP коди з різних локацій.';
$meta_keywords = 'перевірка сайту, site checker, uptime monitor, доступність сайту, ping сайту';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/tools-site-check.css'
];

$additional_js = [
    '/assets/js/tools-site-check.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<!-- Site Check Hero -->
<section class="site-check-hero">
    <div class="container">
        <div class="text-center">
            <div class="tool-icon mb-3">
                <i class="bi bi-globe-americas"></i>
            </div>
            <h1 class="display-4 fw-bold text-white mb-4">
                Перевірка доступності сайту
            </h1>
            <p class="lead text-white-50 mb-5">
                Миттєва перевірка статусу вашого сайту з різних точок світу. 
                Дізнайтеся час відповіді, HTTP статус та доступність ресурсу.
            </p>
            
            <!-- Site Check Form -->
            <div class="site-check-form">
                <form id="siteCheckForm" method="post">
                    <div class="form-group">
                        <label for="siteUrl" class="form-label">URL для перевірки:</label>
                        <div class="input-group">
                            <input type="url" 
                                   id="siteUrl" 
                                   name="url" 
                                   class="form-control" 
                                   placeholder="https://example.com" 
                                   required>
                            <button type="submit" class="btn-check">
                                <i class="bi bi-search me-1"></i>
                                Перевірити
                            </button>
                        </div>
                    </div>
                    
                    <!-- Локації будуть додані динамічно через JavaScript -->
                    
                    <!-- CSRF Token -->
                    <?php if (function_exists('generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Можливості інструменту</h2>
            <p class="lead text-muted">Комплексна перевірка доступності та продуктивності</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h5>Глобальна перевірка</h5>
                    <p class="text-muted">
                        Перевірка доступності з 6 різних географічних локацій по всьому світу
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h5>Час відповіді</h5>
                    <p class="text-muted">
                        Вимірювання швидкості завантаження та часу відповіді сервера
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>SSL перевірка</h5>
                    <p class="text-muted">
                        Аналіз SSL сертифіката, термін дії та коректність налаштування
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h5>DNS аналіз</h5>
                    <p class="text-muted">
                        Перевірка DNS записів, швидкість резолвінгу та налаштування
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-file-code"></i>
                    </div>
                    <h5>HTTP заголовки</h5>
                    <p class="text-muted">
                        Детальний аналіз HTTP заголовків відповіді сервера
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5>Історія перевірок</h5>
                    <p class="text-muted">
                        Збереження та порівняння результатів попередніх перевірок
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Як це працює</h2>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Введіть URL</h5>
                    <p class="text-muted">Вкажіть адресу сайту для перевірки</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Оберіть локації</h5>
                    <p class="text-muted">Виберіть точки для тестування</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Запустіть перевірку</h5>
                    <p class="text-muted">Натисніть кнопку для початку аналізу</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>Отримайте звіт</h5>
                    <p class="text-muted">Детальні результати за кілька секунд</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- API Section -->
<section class="api-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold text-white mb-3">API для розробників</h2>
            <p class="lead text-white-50">
                Інтегруйте перевірку доступності у ваші додатки через наш REST API
            </p>
        </div>

        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="api-feature-box text-center">
                    <div class="api-feature-icon">
                        <i class="bi bi-code-slash"></i>
                    </div>
                    <h5 class="text-white">RESTful API</h5>
                    <p class="text-white-50 small">JSON відповіді</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="api-feature-box text-center">
                    <div class="api-feature-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5 class="text-white">Автентифікація</h5>
                    <p class="text-white-50 small">API ключі</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="api-feature-box text-center">
                    <div class="api-feature-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h5 class="text-white">Rate Limit</h5>
                    <p class="text-white-50 small">1000 запитів/год</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="api-feature-box text-center">
                    <div class="api-feature-icon">
                        <i class="bi bi-globe"></i>
                    </div>
                    <h5 class="text-white">6 Локацій</h5>
                    <p class="text-white-50 small">По всьому світу</p>
                </div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="api-documentation">
            <!-- Endpoint Info -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-link-45deg me-2"></i>Endpoint</h4>
                <div class="code-example">
                    <div class="code-header">
                        <span class="code-lang">POST</span>
                    </div>
                    <pre><code>https://sthost.pro/v1/site-check</code></pre>
                </div>
            </div>

            <!-- Authentication -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-key me-2"></i>Автентифікація</h4>
                <p class="text-white-50">Використовуйте Bearer токен в заголовку Authorization:</p>
                <div class="code-example">
                    <div class="code-header">
                        <span class="code-lang">Headers</span>
                        <button class="btn btn-sm btn-outline-light copy-btn" data-code="auth">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <pre id="code-auth"><code>Authorization: Bearer YOUR_API_KEY
Content-Type: application/json</code></pre>
                </div>
            </div>

            <!-- Request Example -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-send me-2"></i>Приклад запиту</h4>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="code-example">
                            <div class="code-header">
                                <span class="code-lang">cURL</span>
                                <button class="btn btn-sm btn-outline-light copy-btn" data-code="curl">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <pre id="code-curl"><code>curl -X POST https://sthost.pro/v1/site-check \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://example.com",
    "locations": ["kyiv", "frankfurt"],
    "check_ssl": true,
    "follow_redirects": true
  }'</code></pre>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="code-example">
                            <div class="code-header">
                                <span class="code-lang">JavaScript</span>
                                <button class="btn btn-sm btn-outline-light copy-btn" data-code="js">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <pre id="code-js"><code>const response = await fetch(
  'https://sthost.pro/v1/site-check',
  {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_API_KEY',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      url: 'https://example.com',
      locations: ['kyiv', 'frankfurt'],
      check_ssl: true
    })
  }
);
const data = await response.json();</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Parameters -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-list-ul me-2"></i>Параметри запиту</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th>Параметр</th>
                                <th>Тип</th>
                                <th>Обов'язковий</th>
                                <th>Опис</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>url</code></td>
                                <td>string</td>
                                <td><span class="badge bg-danger">Так</span></td>
                                <td>URL сайту для перевірки (включаючи протокол)</td>
                            </tr>
                            <tr>
                                <td><code>locations</code></td>
                                <td>array</td>
                                <td><span class="badge bg-warning">Ні</span></td>
                                <td>Масив кодів локацій: kyiv, frankfurt, london, nyc, singapore, tokyo</td>
                            </tr>
                            <tr>
                                <td><code>check_ssl</code></td>
                                <td>boolean</td>
                                <td><span class="badge bg-warning">Ні</span></td>
                                <td>Перевіряти SSL сертифікат (за замовчуванням: true)</td>
                            </tr>
                            <tr>
                                <td><code>follow_redirects</code></td>
                                <td>boolean</td>
                                <td><span class="badge bg-warning">Ні</span></td>
                                <td>Слідувати за редіректами (за замовчуванням: true)</td>
                            </tr>
                            <tr>
                                <td><code>timeout</code></td>
                                <td>integer</td>
                                <td><span class="badge bg-warning">Ні</span></td>
                                <td>Таймаут в секундах (1-30, за замовчуванням: 10)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Response Example -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-check2-circle me-2"></i>Приклад відповіді</h4>
                <div class="code-example">
                    <div class="code-header">
                        <span class="code-lang">JSON Response</span>
                        <button class="btn btn-sm btn-outline-light copy-btn" data-code="response">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <pre id="code-response"><code>{
  "success": true,
  "url": "https://example.com",
  "timestamp": "2024-11-14T10:30:00Z",
  "results": [
    {
      "location": "kyiv",
      "status": "up",
      "http_code": 200,
      "response_time": 145,
      "dns_time": 12,
      "connect_time": 45,
      "total_time": 202,
      "ssl": {
        "valid": true,
        "issuer": "Let's Encrypt",
        "expires": "2025-02-14",
        "days_remaining": 92
      }
    },
    {
      "location": "frankfurt",
      "status": "up",
      "http_code": 200,
      "response_time": 89,
      "dns_time": 8,
      "connect_time": 23,
      "total_time": 120,
      "ssl": {
        "valid": true,
        "issuer": "Let's Encrypt",
        "expires": "2025-02-14",
        "days_remaining": 92
      }
    }
  ]
}</code></pre>
                </div>
            </div>

            <!-- Error Codes -->
            <div class="doc-section mb-4">
                <h4 class="text-white mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Коди помилок</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th>Код</th>
                                <th>Опис</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>400</code></td>
                                <td>Невірний запит (відсутні обов'язкові параметри)</td>
                            </tr>
                            <tr>
                                <td><code>401</code></td>
                                <td>Невірний або відсутній API ключ</td>
                            </tr>
                            <tr>
                                <td><code>429</code></td>
                                <td>Перевищено ліміт запитів</td>
                            </tr>
                            <tr>
                                <td><code>500</code></td>
                                <td>Внутрішня помилка сервера</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Get API Key -->
            <div class="text-center mt-5">
                <a href="/pages/contacts.php?subject=api-key" class="btn btn-primary btn-lg">
                    <i class="bi bi-key me-2"></i>Отримати API ключ
                </a>
                <p class="text-white-50 mt-3">Для клієнтів StormHosting API ключ безкоштовний</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Tools -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Інші корисні інструменти</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5>WHOIS lookup</h5>
                    <p class="text-muted">Інформація про власника домену</p>
                    <a href="/pages/domains/whois.php" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <h5>DNS lookup</h5>
                    <p class="text-muted">Перевірка DNS записів</p>
                    <a href="/pages/domains/dns.php" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>IP lookup</h5>
                    <p class="text-muted">Геолокація IP адреси</p>
                    <a href="/tools/ip-check" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-code-square"></i>
                    </div>
                    <h5>HTTP Headers</h5>
                    <p class="text-muted">Аналіз HTTP заголовків</p>
                    <a href="/tools/http-headers" class="btn btn-outline-primary btn-sm">
                        Перевірити
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js для графіків -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>